<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->string('source_type'); // AppointmentService, SaleItem
            $table->unsignedBigInteger('source_id');
            $table->decimal('amount', 10, 2);
            $table->string('rate_type', 20)->default('percentage'); // percentage, fixed
            $table->decimal('rate', 10, 2);
            $table->string('status', 30)->default('pending'); // pending, approved, paid, cancelled
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->index(['tenant_id', 'staff_id', 'status']);
        });

        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff_profiles')->cascadeOnDelete();
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('total_commission', 10, 2)->default(0);
            $table->decimal('allowances', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->default(0);
            $table->string('payment_method', 30)->default('bank_transfer'); // bank_transfer, cash, cheque
            $table->string('status', 30)->default('draft'); // draft, approved, paid
            $table->date('paid_at')->nullable();
            $table->string('payslip_pdf')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'staff_id', 'pay_period_start', 'pay_period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('commissions');
    }
};
