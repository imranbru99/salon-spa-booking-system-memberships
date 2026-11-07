<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Services\Booking\BookingService;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OnlineBookingController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::where('is_active', true)->get();
        $services = Service::where('is_active', true)->with('category')->get();
        $staffList = StaffProfile::where('status', 'active')->with('user')->get();

        $selectedBranchId = $request->query('branch', $branches->first()?->id);
        $selectedServiceId = $request->query('service', $services->first()?->id);

        return view('tenant.booking', compact('branches', 'services', 'staffList', 'selectedBranchId', 'selectedServiceId'));
    }

    public function getSlots(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:staff_profiles,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $bookingService = new BookingService();
        $slots = $bookingService->getAvailableSlots(
            branchId: (int) $validated['branch_id'],
            serviceId: (int) $validated['service_id'],
            staffId: $validated['staff_id'] ? (int) $validated['staff_id'] : null,
            date: $validated['date']
        );

        return response()->json([
            'success' => true,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:staff_profiles,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'customer_notes' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $tenantId = TenantContext::getTenantId();

        // 1. Find or create customer
        $customer = Customer::firstOrCreate(
            ['tenant_id' => $tenantId, 'email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? '',
                'phone' => $validated['phone'],
                'preferred_branch_id' => $validated['branch_id'],
                'preferred_staff_id' => $validated['staff_id'] ?? null,
                'segment' => 'new',
                'is_active' => true,
            ]
        );

        $service = Service::findOrFail($validated['service_id']);
        $subtotal = $service->effective_price;
        $discount = 0.0;

        // Apply coupon if given
        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();
            if ($coupon && $coupon->isValidForAmount($subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
                $coupon->increment('used_count');
            }
        }

        $deposit = $service->deposit_required ? (float) $service->deposit_amount : 0.0;

        $bookingService = new BookingService();
        $appointment = $bookingService->createAppointment([
            'branch_id' => $validated['branch_id'],
            'service_id' => $validated['service_id'],
            'staff_id' => $validated['staff_id'] ?? null,
            'customer_id' => $customer->id,
            'appointment_date' => $validated['appointment_date'],
            'start_time' => $validated['start_time'],
            'discount_amount' => $discount,
            'tax_amount' => round(($subtotal - $discount) * 0.085, 2),
            'paid_amount' => $deposit,
            'status' => 'confirmed',
            'channel' => 'online',
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        return redirect()->route('booking.confirm', ['reference' => $appointment->booking_reference]);
    }

    public function confirm(string $reference)
    {
        $appointment = Appointment::where('booking_reference', $reference)
            ->with(['branch', 'customer', 'services.service', 'services.staff.user'])
            ->firstOrFail();

        return view('tenant.booking-confirmed', compact('appointment'));
    }
}
