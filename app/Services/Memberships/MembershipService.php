<?php

namespace App\Services\Memberships;

use App\Models\Customer;
use App\Models\CustomerMembership;
use App\Models\MembershipPlan;
use App\Models\MembershipTransaction;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    /**
     * Subscribe customer to a recurring membership plan.
     */
    public function subscribeCustomer(Customer $customer, MembershipPlan $plan, ?string $paymentMethod = 'card'): CustomerMembership
    {
        return DB::transaction(function () use ($customer, $plan, $paymentMethod) {
            $tenantId = TenantContext::getTenantId();
            $startsAt = Carbon::today();

            $durationMonths = match ($plan->billing_frequency) {
                'annual' => 12,
                'quarterly' => 3,
                'lifetime' => 1200, // 100 years
                default => 1,
            };

            $endsAt = $startsAt->copy()->addMonths($durationMonths);

            $membership = CustomerMembership::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customer->id,
                'membership_plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt->toDateString(),
                'ends_at' => $endsAt->toDateString(),
                'auto_renew' => true,
                'remaining_free_credits' => $plan->free_service_credits,
                'notes' => "Subscribed to {$plan->name}",
            ]);

            MembershipTransaction::create([
                'customer_membership_id' => $membership->id,
                'amount' => $plan->price,
                'transaction_type' => 'subscription',
                'status' => 'successful',
                'payment_method' => $paymentMethod,
                'date' => $startsAt->toDateString(),
            ]);

            return $membership->load(['plan', 'customer']);
        });
    }

    /**
     * Calculate membership discount for a customer.
     */
    public function applyMembershipBenefits(Customer $customer, float $subtotal): array
    {
        $activeMembership = $customer->customerMemberships()
            ->where('status', 'active')
            ->where('ends_at', '>=', now()->toDateString())
            ->first();

        if (!$activeMembership || !$activeMembership->plan) {
            return [
                'has_membership' => false,
                'discount_amount' => 0.0,
                'free_credits' => 0,
            ];
        }

        $discountRate = (float) $activeMembership->plan->discount_percentage;
        $discountAmount = ($subtotal * $discountRate) / 100.0;

        return [
            'has_membership' => true,
            'plan_name' => $activeMembership->plan->name,
            'discount_percentage' => $discountRate,
            'discount_amount' => $discountAmount,
            'free_credits' => $activeMembership->remaining_free_credits,
        ];
    }

    /**
     * Pause an active membership.
     */
    public function pauseMembership(CustomerMembership $membership): bool
    {
        return $membership->update(['status' => 'paused']);
    }

    /**
     * Cancel an active membership.
     */
    public function cancelMembership(CustomerMembership $membership): bool
    {
        return $membership->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);
    }
}
