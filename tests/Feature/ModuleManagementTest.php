<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Services\Modules\ModuleService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Module Test Spa', 'slug' => 'mod-spa', 'status' => 'active']);
        TenantContext::setTenant($this->tenant);
    }

    public function test_core_modules_cannot_be_disabled(): void
    {
        $result = ModuleService::disable('services', $this->tenant->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('cannot be disabled', $result['message']);
        $this->assertTrue(ModuleService::isEnabled('services', $this->tenant->id));
    }

    public function test_module_dependency_validation_prevents_disabling_required_dependency(): void
    {
        // Suppliers depends on inventory; Purchasing depends on inventory and suppliers.
        ModuleService::enable('inventory', $this->tenant->id);
        ModuleService::enable('suppliers', $this->tenant->id);

        $this->assertTrue(ModuleService::isEnabled('suppliers', $this->tenant->id));

        // Attempting to disable 'inventory' while 'suppliers' is active must be blocked
        $result = ModuleService::disable('inventory', $this->tenant->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('active modules depend on it', $result['message']);
        $this->assertTrue(ModuleService::isEnabled('inventory', $this->tenant->id));
    }

    public function test_can_disable_and_enable_independent_module(): void
    {
        $resultDisable = ModuleService::disable('blog', $this->tenant->id);
        $this->assertTrue($resultDisable['success']);
        $this->assertFalse(ModuleService::isEnabled('blog', $this->tenant->id));

        $resultEnable = ModuleService::enable('blog', $this->tenant->id);
        $this->assertTrue($resultEnable['success']);
        $this->assertTrue(ModuleService::isEnabled('blog', $this->tenant->id));
    }
}
