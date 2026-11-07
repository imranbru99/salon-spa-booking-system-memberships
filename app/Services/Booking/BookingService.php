<?php

namespace App\Services\Booking;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\StaffSchedule;
use App\Models\StaffLeave;
use App\Services\Finance\CommissionService;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Get available booking slots for a branch, service, and date.
     */
    public function getAvailableSlots(int $branchId, int $serviceId, ?int $staffId, string $date): array
    {
        $service = Service::findOrFail($serviceId);
        $totalDuration = $service->duration_minutes + $service->buffer_before + $service->buffer_after;

        $bookingDate = Carbon::parse($date);
        $dayOfWeek = $bookingDate->dayOfWeek; // 0 = Sun, 6 = Sat

        // Find available staff members
        $staffQuery = StaffProfile::where('status', 'active');
        if ($staffId) {
            $staffQuery->where('id', $staffId);
        } else {
            // Find staff assigned to branch and service
            $staffQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })->whereHas('services', function ($q) use ($serviceId) {
                $q->where('services.id', $serviceId);
            });
        }
        $availableStaff = $staffQuery->get();

        if ($availableStaff->isEmpty()) {
            return [];
        }

        // Standard operational hours: 09:00 to 19:00 if no explicit branch hours
        $startHour = 9;
        $endHour = 19;
        $slotIntervalMinutes = 30;

        $slots = [];
        $currentTime = $bookingDate->copy()->setHour($startHour)->setMinute(0)->setSecond(0);
        $dayEndTime = $bookingDate->copy()->setHour($endHour)->setMinute(0)->setSecond(0);

        while ($currentTime->copy()->addMinutes($totalDuration)->lte($dayEndTime)) {
            $slotStart = $currentTime->format('H:i:s');
            $slotEnd = $currentTime->copy()->addMinutes($totalDuration)->format('H:i:s');

            // Check if at least one qualified staff member is free
            $freeStaffId = null;
            foreach ($availableStaff as $staff) {
                if ($this->isStaffAvailable($staff->id, $branchId, $date, $slotStart, $slotEnd)) {
                    $freeStaffId = $staff->id;
                    break;
                }
            }

            if ($freeStaffId) {
                $slots[] = [
                    'time' => $currentTime->format('H:i'),
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'available_staff_id' => $freeStaffId,
                ];
            }

            $currentTime->addMinutes($slotIntervalMinutes);
        }

        return $slots;
    }

    /**
     * Check if a staff member is free from overlapping appointments and on schedule.
     */
    public function isStaffAvailable(int $staffId, int $branchId, string $date, string $startTime, string $endTime): bool
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        // Check if staff has approved leave on this date
        $onLeave = StaffLeave::where('staff_id', $staffId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();

        if ($onLeave) {
            return false;
        }

        // Check staff schedule if defined
        $schedule = StaffSchedule::where('staff_id', $staffId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if ($schedule && (!$schedule->is_working || $startTime < $schedule->start_time || $endTime > $schedule->end_time)) {
            return false;
        }

        // Check overlapping appointments
        $hasOverlap = AppointmentService::where('staff_id', $staffId)
            ->whereHas('appointment', function ($q) use ($date) {
                $q->whereDate('appointment_date', $date)
                  ->whereNotIn('status', ['cancelled', 'rejected']);
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($sub) use ($startTime, $endTime) {
                    $sub->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->exists();

        return !$hasOverlap;
    }

    /**
     * Create an appointment safely in a database transaction with availability validation.
     */
    public function createAppointment(array $data, ?int $userId = null): Appointment
    {
        return DB::transaction(function () use ($data, $userId) {
            $branchId = $data['branch_id'];
            $serviceId = $data['service_id'];
            $appointmentDate = $data['appointment_date'];
            $startTime = $data['start_time'];

            $service = Service::findOrFail($serviceId);
            $duration = $service->duration_minutes;
            $totalDuration = $duration + $service->buffer_before + $service->buffer_after;

            $start = Carbon::parse("{$appointmentDate} {$startTime}");
            $end = $start->copy()->addMinutes($totalDuration);
            $endTime = $end->format('H:i:s');

            $staffId = $data['staff_id'] ?? null;
            if (!$staffId) {
                // Pick first available staff
                $slots = $this->getAvailableSlots($branchId, $serviceId, null, $appointmentDate);
                foreach ($slots as $slot) {
                    if ($slot['start_time'] === $startTime || $slot['time'] === substr($startTime, 0, 5)) {
                        $staffId = $slot['available_staff_id'];
                        break;
                    }
                }

                if (!$staffId) {
                    $staffId = StaffProfile::where('status', 'active')
                        ->whereHas('services', fn ($q) => $q->where('services.id', $serviceId))
                        ->value('id');
                }
            }

            // Lock & verify no overlap
            if ($staffId && !$this->isStaffAvailable($staffId, $branchId, $appointmentDate, $startTime, $endTime)) {
                throw new \RuntimeException("The selected staff member is no longer available at this time slot.");
            }

            $customerId = $data['customer_id'];
            $depositAmount = $service->deposit_required ? (float) $service->deposit_amount : 0.0;
            $price = $service->effective_price;

            $reference = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $appointment = Appointment::create([
                'tenant_id' => TenantContext::getTenantId(),
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'booking_reference' => $reference,
                'appointment_date' => $appointmentDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'subtotal' => $price,
                'discount_amount' => $data['discount_amount'] ?? 0.0,
                'tax_amount' => $data['tax_amount'] ?? 0.0,
                'total_amount' => $price,
                'deposit_amount' => $depositAmount,
                'paid_amount' => $data['paid_amount'] ?? 0.0,
                'status' => $data['status'] ?? 'confirmed',
                'payment_status' => ($data['paid_amount'] ?? 0) >= $price ? 'paid' : (($data['paid_amount'] ?? 0) > 0 ? 'partially_paid' : 'unpaid'),
                'channel' => $data['channel'] ?? 'website',
                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => $userId,
            ]);

            AppointmentService::create([
                'appointment_id' => $appointment->id,
                'service_id' => $serviceId,
                'staff_id' => $staffId,
                'room_id' => $data['room_id'] ?? null,
                'price' => $price,
                'duration_minutes' => $duration,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'pending',
            ]);

            return $appointment->load(['services.service', 'services.staff.user', 'customer', 'branch']);
        });
    }

    /**
     * Reschedule appointment.
     */
    public function rescheduleAppointment(Appointment $appointment, string $newDate, string $newStartTime): Appointment
    {
        return DB::transaction(function () use ($appointment, $newDate, $newStartTime) {
            $serviceItem = $appointment->services()->first();
            $duration = $serviceItem ? $serviceItem->duration_minutes : 60;

            $start = Carbon::parse("{$newDate} {$newStartTime}");
            $endTime = $start->copy()->addMinutes($duration)->format('H:i:s');

            if ($serviceItem && $serviceItem->staff_id) {
                if (!$this->isStaffAvailable($serviceItem->staff_id, $appointment->branch_id, $newDate, $newStartTime, $endTime)) {
                    throw new \RuntimeException("Staff is not available at the new requested time.");
                }

                $serviceItem->update([
                    'start_time' => $newStartTime,
                    'end_time' => $endTime,
                ]);
            }

            $appointment->update([
                'appointment_date' => $newDate,
                'start_time' => $newStartTime,
                'end_time' => $endTime,
                'status' => 'rescheduled',
            ]);

            return $appointment;
        });
    }

    /**
     * Cancel appointment.
     */
    public function cancelAppointment(Appointment $appointment, ?string $reason = null): Appointment
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
        ]);

        $appointment->services()->update(['status' => 'cancelled']);

        return $appointment;
    }

    /**
     * Complete appointment and calculate commissions.
     */
    public function completeAppointment(Appointment $appointment): Appointment
    {
        return DB::transaction(function () use ($appointment) {
            $appointment->update([
                'status' => 'completed',
            ]);

            $appointment->services()->update(['status' => 'completed']);

            // Calculate commissions for staff
            $commissionService = new CommissionService();
            foreach ($appointment->services as $apptService) {
                $commissionService->calculateServiceCommission($apptService);
            }

            return $appointment;
        });
    }
}
