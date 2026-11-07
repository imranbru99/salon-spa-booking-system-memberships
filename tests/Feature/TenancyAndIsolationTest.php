<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyAndIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_data_is_isolated_between_businesses(): void
    {
        $tenantA = Tenant::create(['name' => 'Salon Alpha', 'slug' => 'salon-alpha', 'status' => 'active']);
        $tenantB = Tenant::create(['name' => 'Spa Beta', 'slug' => 'spa-beta', 'status' => 'active']);

        // Create branch for Tenant A
        TenantContext::setTenant($tenantA);
        $branchA = Branch::create(['name' => 'Downtown Alpha', 'slug' => 'downtown-alpha', 'is_active' => true]);
        $customerA = Customer::create(['first_name' => 'Alice', 'last_name' => 'Alpha', 'email' => 'alice@alpha.test']);

        // Verify that under Tenant A context, only Tenant A's data is returned
        $this->assertEquals(1, Branch::count());
        $this->assertEquals($tenantA->id, $branchA->tenant_id);
        $this->assertEquals(1, Customer::count());

        // Switch context to Tenant B
        TenantContext::setTenant($tenantB);
        $this->assertEquals(0, Branch::count());
        $this->assertEquals(0, Customer::count());

        // Create data for Tenant B
        $branchB = Branch::create(['name' => 'Uptown Beta', 'slug' => 'uptown-beta', 'is_active' => true]);
        $this->assertEquals(1, Branch::count());
        $this->assertEquals($branchB->id, Branch::first()->id);

        // Switch back to Tenant A
        TenantContext::setTenant($tenantA);
        $this->assertEquals(1, Branch::count());
        $this->assertEquals($branchA->id, Branch::first()->id);

        // Test bypass allows seeing all records
        TenantContext::bypass(function () {
            $this->assertEquals(2, Branch::count());
        });
    }

    public function test_automatic_tenant_id_injection_on_model_creation(): void
    {
        $tenant = Tenant::create(['name' => 'Luxe Beauty', 'slug' => 'luxe-beauty', 'status' => 'active']);
        TenantContext::setTenant($tenant);

        $service = Service::create([
            'name' => 'Gold Facial',
            'slug' => 'gold-facial',
            'price' => 150.00,
            'duration_minutes' => 60,
        ]);

        $this->assertEquals($tenant->id, $service->tenant_id);
    }
}
