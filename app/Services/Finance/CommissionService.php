<?php

namespace App\Services\Finance;

use App\Models\AppointmentService;
use App\Models\Commission;
use App\Models\PayrollRecord;
use App\Models\SaleItem;
use App\Models\StaffProfile;
use App\Services\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate commission for an appointment service and record it.
     */
    public function calculateServiceCommission(AppointmentService $appointmentService): ?Commission
    {
        $staff = $appointmentService->staff;
        if (!$staff) {
            return null;
        }

        $rate = (float) $staff->commission_rate;
        if ($rate <= 0) {
            // Check service specific commission rate
            $rate = (float) ($appointmentService->service?->commission_rate ?? 0);
        }

        if ($rate <= 0) {
            return null;
        }

        $price = (float) $appointmentService->price;
        $commissionAmount = ($price * $rate) / 100.0;

        return Commission::create([
            'tenant_id' => TenantContext::getTenantId(),
            'staff_id' => $staff->id,
            'source_type' => AppointmentService::class,
            'source_id' => $appointmentService->id,
            'amount' => $commissionAmount,
            'rate_type' => 'percentage',
            'rate' => $rate,
            'status' => 'approved',
            'calculated_at' => now(),
        ]);
    }

    /**
     * Calculate commission on retail product sales by staff.
     */
    public function calculateProductCommission(SaleItem $saleItem): ?Commission
    {
        $staff = $saleItem->staff;
        if (!$staff) {
            return null;
        }

        // Retail product commission default: 10% or staff commission
        $rate = min((float) $staff->commission_rate, 15.0);
        if ($rate <= 0) {
            $rate = 10.0;
        }

        $itemTotal = (float) $saleItem->total_amount;
        $commissionAmount = ($itemTotal * $rate) / 100.0;

        return Commission::create([
            'tenant_id' => TenantContext::getTenantId(),
            'staff_id' => $staff->id,
            'source_type' => SaleItem::class,
            'source_id' => $saleItem->id,
            'amount' => $commissionAmount,
            'rate_type' => 'percentage',
            'rate' => $rate,
            'status' => 'approved',
            'calculated_at' => now(),
        ]);
    }

    /**
     * Generate payroll for a staff member over a date range.
     */
    public function generatePayroll(StaffProfile $staff, string $startDate, string $endDate): PayrollRecord
    {
        return DB::transaction(function () use ($staff, $startDate, $endDate) {
            $baseSalary = (float) $staff->base_salary;

            $totalCommission = (float) Commission::where('staff_id', $staff->id)
                ->where('status', 'approved')
                ->whereBetween('calculated_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                ->sum('amount');

            $allowances = 0.0;
            $deductions = 0.0;
            $netSalary = $baseSalary + $totalCommission + $allowances - $deductions;

            $payroll = PayrollRecord::create([
                'tenant_id' => $staff->tenant_id,
                'staff_id' => $staff->id,
                'pay_period_start' => $startDate,
                'pay_period_end' => $endDate,
                'base_salary' => $baseSalary,
                'total_commission' => $totalCommission,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'net_salary' => $netSalary,
                'payment_method' => 'bank_transfer',
                'status' => 'approved',
                'paid_at' => now()->toDateString(),
                'notes' => "Payroll period {$startDate} to {$endDate}",
            ]);

            // Mark included commissions as paid
            Commission::where('staff_id', $staff->id)
                ->where('status', 'approved')
                ->whereBetween('calculated_at', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                ->update(['status' => 'paid']);

            return $payroll;
        });
    }
}
