<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('booking_reference', 30);
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('status', 30)->default('pending'); 
            // pending, awaiting_payment, confirmed, checked_in, in_progress, completed, cancelled, rejected, no_show, rescheduled
            $table->string('payment_status', 30)->default('unpaid'); // unpaid, partially_paid, paid, refunded
            $table->string('channel', 30)->default('online'); // website, widget, receptionist, walk_in, ai_assistant, customer_portal
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'booking_reference']);
            $table->index(['tenant_id', 'branch_id', 'appointment_date']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('treatment_rooms')->nullOnDelete();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('duration_minutes');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status', 30)->default('pending'); // pending, in_progress, completed, cancelled
            $table->timestamps();

            $table->index(['appointment_id', 'service_id']);
            $table->index(['staff_id', 'start_time', 'end_time']);
        });

        Schema::create('booking_waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('preferred_staff_id')->nullable()->constrained('staff_profiles')->nullOnDelete();
            $table->date('preferred_date');
            $table->string('preferred_time_range')->nullable(); // morning, afternoon, evening, any
            $table->string('status', 30)->default('waiting'); // waiting, notified, booked, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'preferred_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_waitlists');
        Schema::dropIfExists('appointment_services');
        Schema::dropIfExists('appointments');
    }
};
