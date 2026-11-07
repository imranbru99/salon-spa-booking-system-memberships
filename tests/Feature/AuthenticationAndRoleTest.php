<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_admin_panel(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@platform.test',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $panel = Filament::getPanel('admin');
        $this->assertTrue($admin->canAccessPanel($panel));
        $this->assertTrue($admin->isSuperAdmin());
    }

    public function test_business_owner_can_access_app_panel_but_not_admin_panel(): void
    {
        $tenant = Tenant::create([
            'name' => 'Test Salon',
            'slug' => 'test-salon',
            'status' => 'active',
        ]);

        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => 'owner@test.test',
            'password' => bcrypt('password'),
            'role' => 'business_owner',
            'is_active' => true,
        ]);

        $appPanel = Filament::getPanel('app');
        $adminPanel = Filament::getPanel('admin');

        $this->assertTrue($owner->canAccessPanel($appPanel));
        $this->assertFalse($owner->canAccessPanel($adminPanel));
        $this->assertTrue($owner->isBusinessOwner());
    }

    public function test_stylist_can_access_staff_panel(): void
    {
        $stylist = User::create([
            'name' => 'Stylist Anna',
            'email' => 'anna@test.test',
            'password' => bcrypt('password'),
            'role' => 'stylist',
            'is_active' => true,
        ]);

        $staffPanel = Filament::getPanel('staff');
        $adminPanel = Filament::getPanel('admin');

        $this->assertTrue($stylist->canAccessPanel($staffPanel));
        $this->assertFalse($stylist->canAccessPanel($adminPanel));
        $this->assertTrue($stylist->isStaff());
    }
}
