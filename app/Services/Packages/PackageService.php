<?php

namespace App\Services\Packages;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\PackageRedemption;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Services\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PackageService
{
    /**
     * Purchase a prepaid service package for a customer.
     */
    public function purchasePackage(Customer $customer, ServicePackage $package, ?string $paymentMethod = 'cash'): CustomerPackage
    {
        return DB::transaction(function () use ($customer, $package, $paymentMethod) {
            $tenantId = TenantContext::getTenantId();
            $totalCredits = $package->items()->sum('quantity') ?: 1;

            $purchaseDate = now()->toDateString();
            $expiresAt = now()->addDays($package->validity_days)->toDateString();

            $customerPackage = CustomerPackage::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'package_id' => $package->id,
                'purchase_date' => $purchaseDate,
                'expires_at' => $expiresAt,
                'total_credits' => $totalCredits,
                'remaining_credits' => $totalCredits,
                'status' => 'active',
            ]);

            // Record payment for package
            Payment::create([
                'tenant_id' => $tenantId,
                'payable_type' => CustomerPackage::class,
                'payable_id' => $customerPackage->id,
                'amount' => $package->price,
                'currency' => 'USD',
                'method' => $paymentMethod,
                'transaction_reference' => 'PKG-' . strtoupper(Str::random(8)),
                'status' => 'completed',
                'notes' => "Purchased {$package->name}",
            ]);

            return $customerPackage->load(['package.items.service', 'customer']);
        });
    }

    /**
     * Redeem credit from customer package towards a service.
     */
    public function redeemPackageCredit(
        CustomerPackage $customerPackage,
        int $serviceId,
        ?Appointment $appointment = null,
        int $credits = 1,
        ?string $notes = null
    ): PackageRedemption {
        return DB::transaction(function () use ($customerPackage, $serviceId, $appointment, $credits, $notes) {
            if (!$customerPackage->isUsable()) {
                throw new \RuntimeException("This package is either expired, exhausted or inactive.");
            }

            if ($customerPackage->remaining_credits < $credits) {
                throw new \RuntimeException("Insufficient remaining credits in this package.");
            }

            $service = Service::findOrFail($serviceId);

            $redemption = PackageRedemption::create([
                'customer_package_id' => $customerPackage->id,
                'appointment_id' => $appointment?->id,
                'service_id' => $service->id,
                'credits_used' => $credits,
                'redeemed_at' => now(),
                'notes' => $notes ?? "Redeemed for {$service->name}",
            ]);

            $remaining = $customerPackage->remaining_credits - $credits;
            $customerPackage->update([
                'remaining_credits' => $remaining,
                'status' => $remaining <= 0 ? 'exhausted' : 'active',
            ]);

            return $redemption;
        });
    }
}
