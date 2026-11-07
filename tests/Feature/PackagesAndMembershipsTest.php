<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MembershipPlan;
use App\Models\PackageItem;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\Tenant;
use App\Services\Memberships\MembershipService;
use App\Services\Packages\PackageService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagesAndMembershipsTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Customer $customer;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Royal Spa', 'slug' => 'royal-spa', 'status' => 'active']);
        TenantContext::setTenant($this->tenant);

        $this->customer = Customer::create([
            'first_name' => 'Grace',
            'last_name' => 'Kelly',
            'email' => 'grace@test.test',
        ]);

        $this->service = Service::create([
            'name' => 'Signature Facial',
            'slug' => 'sig-facial',
            'price' => 100.00,
            'duration_minutes' => 60,
        ]);
    }

    public function test_package_purchase_and_credit_redemption(): void
    {
        $package = ServicePackage::create([
            'name' => 'Facial Trio',
            'slug' => 'facial-trio',
            'price' => 250.00,
            'validity_days' => 90,
            'is_active' => true,
        ]);
        PackageItem::create(['package_id' => $package->id, 'service_id' => $this->service->id, 'quantity' => 3]);

        $packageService = new PackageService();
        $customerPackage = $packageService->purchasePackage($this->customer, $package, 'card');

        $this->assertEquals(3, $customerPackage->total_credits);
        $this->assertEquals(3, $customerPackage->remaining_credits);
        $this->assertEquals('active', $customerPackage->status);

        // Redeem 1 credit
        $redemption = $packageService->redeemPackageCredit($customerPackage, $this->service->id, null, 1);
        $customerPackage->refresh();

        $this->assertEquals(2, $customerPackage->remaining_credits);
        $this->assertEquals('active', $customerPackage->status);
        $this->assertEquals(1, $redemption->credits_used);

        // Redeem remaining 2 credits
        $packageService->redeemPackageCredit($customerPackage, $this->service->id, null, 2);
        $customerPackage->refresh();

        $this->assertEquals(0, $customerPackage->remaining_credits);
        $this->assertEquals('exhausted', $customerPackage->status);

        // Trying to redeem from exhausted package throws exception
        $this->expectException(\RuntimeException::class);
        $packageService->redeemPackageCredit($customerPackage, $this->service->id, null, 1);
    }

    public function test_membership_subscription_and_discount_calculation(): void
    {
        $plan = MembershipPlan::create([
            'name' => 'Gold VIP',
            'slug' => 'gold-vip',
            'price' => 99.00,
            'billing_frequency' => 'monthly',
            'discount_percentage' => 20.0,
            'free_service_credits' => 1,
            'priority_booking' => true,
            'is_active' => true,
        ]);

        $membershipService = new MembershipService();
        $membership = $membershipService->subscribeCustomer($this->customer, $plan, 'card');

        $this->assertEquals('active', $membership->status);
        $this->assertEquals(1, $membership->remaining_free_credits);

        // Check benefits on $200 subtotal
        $benefits = $membershipService->applyMembershipBenefits($this->customer, 200.00);

        $this->assertTrue($benefits['has_membership']);
        $this->assertEquals(20.0, $benefits['discount_percentage']);
        $this->assertEquals(40.00, $benefits['discount_amount']); // 20% of 200
    }
}
