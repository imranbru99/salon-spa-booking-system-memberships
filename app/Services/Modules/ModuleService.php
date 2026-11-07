<?php

namespace App\Services\Modules;

use App\Models\ModuleActivityLog;
use App\Models\TenantModule;
use App\Services\Tenancy\TenantContext;
use Illuminate\Support\Facades\Cache;

class ModuleService
{
    /**
     * Check if a module is enabled for the current or specified tenant.
     */
    public static function isEnabled(string $moduleKey, ?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? TenantContext::getTenantId();
        $definitions = ModuleRegistry::all();

        if (!isset($definitions[$moduleKey])) {
            return false;
        }

        // Core modules are always enabled
        if ($definitions[$moduleKey]['is_core'] ?? false) {
            return true;
        }

        if (!$tenantId) {
            return $definitions[$moduleKey]['default'] ?? true;
        }

        $cacheKey = "tenant_{$tenantId}_module_{$moduleKey}";

        return Cache::remember($cacheKey, 3600, function () use ($tenantId, $moduleKey, $definitions) {
            $record = TenantModule::where('tenant_id', $tenantId)
                ->where('module_key', $moduleKey)
                ->first();

            if (!$record) {
                return $definitions[$moduleKey]['default'] ?? true;
            }

            return (bool) $record->is_enabled;
        });
    }

    /**
     * Enable a module for a tenant with dependency checks.
     */
    public static function enable(string $moduleKey, int $tenantId, ?int $userId = null): array
    {
        $definitions = ModuleRegistry::all();

        if (!isset($definitions[$moduleKey])) {
            return ['success' => false, 'message' => "Module '{$moduleKey}' not recognized."];
        }

        // Check required dependencies
        $missing = [];
        foreach ($definitions[$moduleKey]['dependencies'] as $dep) {
            if (!static::isEnabled($dep, $tenantId)) {
                $depName = $definitions[$dep]['name'] ?? $dep;
                $missing[] = $depName;
            }
        }

        if (!empty($missing)) {
            return [
                'success' => false,
                'message' => "Cannot enable '{$definitions[$moduleKey]['name']}'. Required dependencies must be enabled first: " . implode(', ', $missing),
            ];
        }

        TenantModule::updateOrCreate(
            ['tenant_id' => $tenantId, 'module_key' => $moduleKey],
            [
                'is_enabled' => true,
                'activated_at' => now(),
            ]
        );

        Cache::forget("tenant_{$tenantId}_module_{$moduleKey}");

        ModuleActivityLog::create([
            'tenant_id' => $tenantId,
            'module_key' => $moduleKey,
            'action' => 'enabled',
            'user_id' => $userId,
            'payload' => ['module_name' => $definitions[$moduleKey]['name']],
        ]);

        return ['success' => true, 'message' => "Module '{$definitions[$moduleKey]['name']}' has been enabled successfully."];
    }

    /**
     * Disable a module for a tenant with reverse dependency checks.
     */
    public static function disable(string $moduleKey, int $tenantId, ?int $userId = null): array
    {
        $definitions = ModuleRegistry::all();

        if (!isset($definitions[$moduleKey])) {
            return ['success' => false, 'message' => "Module '{$moduleKey}' not recognized."];
        }

        if ($definitions[$moduleKey]['is_core'] ?? false) {
            return ['success' => false, 'message' => "Core module '{$definitions[$moduleKey]['name']}' cannot be disabled."];
        }

        // Check if any currently enabled module depends on this module
        $dependentModules = [];
        foreach ($definitions as $key => $meta) {
            if ($key === $moduleKey) {
                continue;
            }

            if (in_array($moduleKey, $meta['dependencies']) && static::isEnabled($key, $tenantId)) {
                $dependentModules[] = $meta['name'];
            }
        }

        if (!empty($dependentModules)) {
            return [
                'success' => false,
                'message' => "Cannot disable '{$definitions[$moduleKey]['name']}' because active modules depend on it: " . implode(', ', $dependentModules) . ". Disable them first.",
            ];
        }

        TenantModule::updateOrCreate(
            ['tenant_id' => $tenantId, 'module_key' => $moduleKey],
            [
                'is_enabled' => false,
                'deactivated_at' => now(),
            ]
        );

        Cache::forget("tenant_{$tenantId}_module_{$moduleKey}");

        ModuleActivityLog::create([
            'tenant_id' => $tenantId,
            'module_key' => $moduleKey,
            'action' => 'disabled',
            'user_id' => $userId,
            'payload' => ['module_name' => $definitions[$moduleKey]['name']],
        ]);

        return ['success' => true, 'message' => "Module '{$definitions[$moduleKey]['name']}' has been disabled successfully."];
    }

    /**
     * Get all modules for a tenant with their active status.
     */
    public static function getModulesForTenant(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? TenantContext::getTenantId();
        $definitions = ModuleRegistry::all();
        $result = [];

        foreach ($definitions as $key => $meta) {
            $meta['key'] = $key;
            $meta['is_enabled'] = static::isEnabled($key, $tenantId);
            $result[$key] = $meta;
        }

        return $result;
    }
}
