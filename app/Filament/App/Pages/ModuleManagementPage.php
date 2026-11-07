<?php

namespace App\Filament\App\Pages;

use App\Services\Modules\ModuleRegistry;
use App\Services\Modules\ModuleService;
use App\Services\Tenancy\TenantContext;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ModuleManagementPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?string $navigationLabel = 'Module Engine';

    protected static ?string $navigationGroup = 'Administration & Settings';

    protected static string $view = 'filament.app.pages.module-management';

    public array $modules = [];

    public function mount(): void
    {
        $this->refreshModules();
    }

    public function refreshModules(): void
    {
        $tenantId = TenantContext::getTenantId();
        $this->modules = ModuleService::getModulesForTenant($tenantId);
    }

    public function toggleModule(string $moduleKey): void
    {
        $tenantId = TenantContext::getTenantId();
        $isCurrentlyEnabled = ModuleService::isEnabled($moduleKey, $tenantId);

        if ($isCurrentlyEnabled) {
            $result = ModuleService::disable($moduleKey, $tenantId, auth()->id());
        } else {
            $result = ModuleService::enable($moduleKey, $tenantId, auth()->id());
        }

        if ($result['success']) {
            Notification::make()
                ->title('Module Status Updated')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Dependency Constraint')
                ->body($result['message'])
                ->danger()
                ->send();
        }

        $this->refreshModules();
    }
}
