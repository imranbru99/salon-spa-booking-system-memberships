<?php

namespace App\Http\Middleware;

use App\Services\Modules\ModuleRegistry;
use App\Services\Modules\ModuleService;
use App\Services\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $tenantId = TenantContext::getTenantId();

        if (!ModuleService::isEnabled($moduleKey, $tenantId)) {
            $meta = ModuleRegistry::all()[$moduleKey] ?? ['name' => ucfirst($moduleKey)];
            $moduleName = $meta['name'];

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'module_disabled',
                    'message' => "The '{$moduleName}' module is currently disabled for this business.",
                ], 403);
            }

            abort(403, "The '{$moduleName}' module is currently disabled for this business. Contact your business owner or activate it from Modules settings.");
        }

        return $next($request);
    }
}
