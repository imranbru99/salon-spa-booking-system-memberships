<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Booking\BookingService;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Service $service;
    protected StaffProfile $staff;
    protected Customer $customer;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Serenity Salon', 'slug' => 'serenity', 'status' => 'active']);
        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::create(['name' => 'Central Branch', 'slug' => 'central', 'is_active' => true]);

        $this->service = Service::create([
            'name' => 'Deep Tissue Massage',
            'slug' => 'deep-tissue',
            'price' => 120.00,
            'duration_minutes' => 60,
            'buffer_before' => 5,
            'buffer_after' => 10,
            'deposit_required' => true,
            'deposit_amount' => 30.00,
            'is_active' => true,
        ]);

        $user = User::create(['name' => 'Therapist John', 'email' => 'john@test.test', 'password' => bcrypt('password'), 'role' => 'therapist']);
        $this->staff = StaffProfile::create([
            'user_id' => $user->id,
            'branch_id' => $this->branch->id,
            'employee_code' => 'STF-01',
            'job_title' => 'Therapist',
            'status' => 'active',
            'commission_rate' => 20.0,
        ]);

        $this->service->staff()->attach($this->staff->id);

        $this->customer = Customer::create([
            'first_name' => 'Emily',
            'last_name' => 'Watson',
            'email' => 'emily@test.test',
            'phone' => '+15551234567',
        ]);

        $this->bookingService = new BookingService();
    }

    public function test_can_create_appointment_with_deposit(): void
    {
        $date = Carbon::tomorrow()->toDateString();
        $startTime = '10:00:00';

        $appointment = $this->bookingService->createAppointment([
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'staff_id' => $this->staff->id,
            'customer_id' => $this->customer->id,
            'appointment_date' => $date,
            'start_time' => $startTime,
        ]);

        $this->assertNotNull($appointment->id);
        $this->assertEquals($this->tenant->id, $appointment->tenant_id);
        $this->assertEquals(120.00, $appointment->total_amount);
        $this->assertEquals(30.00, $appointment->deposit_amount);
        $this->assertStringStartsWith('BK-', $appointment->booking_reference);
        $this->assertEquals('confirmed', $appointment->status);
    }

    public function test_prevents_double_booking_same_staff_at_overlapping_time(): void
    {
        $date = Carbon::tomorrow()->toDateString();
        $startTime = '14:00:00';

        // 1. Create first appointment
        $this->bookingService->createAppointment([
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'staff_id' => $this->staff->id,
            'customer_id' => $this->customer->id,
            'appointment_date' => $date,
            'start_time' => $startTime,
        ]);

        // 2. Attempt to book overlapping appointment for same staff
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The selected staff member is no longer available');

        $this->bookingService->createAppointment([
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'staff_id' => $this->staff->id,
            'customer_id' => $this->customer->id,
            'appointment_date' => $date,
            'start_time' => '14:30:00', // overlaps with 14:00 - 15:15
        ]);
    }

    public function test_can_cancel_and_reschedule_appointment(): void
    {
        $date = Carbon::tomorrow()->toDateString();
        $appointment = $this->bookingService->createAppointment([
            'branch_id' => $this->branch->id,
            'service_id' => $this->service->id,
            'staff_id' => $this->staff->id,
            'customer_id' => $this->customer->id,
            'appointment_date' => $date,
            'start_time' => '09:00:00',
        ]);

        // Reschedule
        $newDate = Carbon::tomorrow()->addDay()->toDateString();
        $rescheduled = $this->bookingService->rescheduleAppointment($appointment, $newDate, '11:00:00');
        $this->assertEquals('rescheduled', $rescheduled->status);
        $this->assertEquals($newDate, $rescheduled->appointment_date->toDateString());

        // Cancel
        $cancelled = $this->bookingService->cancelAppointment($rescheduled, 'Client request');
        $this->assertEquals('cancelled', $cancelled->status);
        $this->assertEquals('Client request', $cancelled->cancellation_reason);
    }
}
