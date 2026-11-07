<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Services\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        try {
            // 1. Identify by authenticated user
            if ($user = $request->user()) {
                if ($user->tenant_id) {
                    $tenant = Tenant::find($user->tenant_id);
                }
            }

            // 2. Identify by custom domain or subdomain
            if (!$tenant) {
                $host = $request->getHost();
                
                // Check custom domain in tenant_domains
                $domainRecord = TenantDomain::where('domain', $host)->first();
                if ($domainRecord) {
                    $tenant = $domainRecord->tenant;
                } else {
                    // Check if subdomain (e.g., glowgrace.localhost or glowgrace.example.test)
                    $parts = explode('.', $host);
                    if (count($parts) >= 2 && !in_array($parts[0], ['www', 'admin', 'api', 'localhost', '127'])) {
                        $slug = $parts[0];
                        $tenant = Tenant::where('slug', $slug)->first();
                    }
                }
            }

            // 3. Identify by Route Parameter 'tenant' or Query Parameter 'tenant'
            if (!$tenant && $request->has('tenant')) {
                $param = $request->get('tenant');
                $tenant = is_numeric($param) 
                    ? Tenant::find($param) 
                    : Tenant::where('slug', $param)->first();
            }

            // 4. Identify by Header
            if (!$tenant && $request->header('X-Tenant-ID')) {
                $tenantId = $request->header('X-Tenant-ID');
                $tenant = Tenant::find($tenantId);
            }

            // 5. Fallback for demo / local session
            if (!$tenant && $request->hasSession() && $request->session()->has('active_tenant_id')) {
                $tenant = Tenant::find($request->session()->get('active_tenant_id'));
            }

            // If no tenant found and there is an active tenant in DB, default to first active tenant for local dev / public site
            if (!$tenant && !app()->runningInConsole()) {
                $tenant = Tenant::where('status', 'active')->first();
            }

            if ($tenant) {
                TenantContext::setTenant($tenant);

                if ($request->hasSession()) {
                    $request->session()->put('active_tenant_id', $tenant->id);
                }

                // Set timezone and locale if configured
                if ($tenant->timezone) {
                    date_default_timezone_set($tenant->timezone);
                    config(['app.timezone' => $tenant->timezone]);
                }
                if ($tenant->locale) {
                    app()->setLocale($tenant->locale);
                }
            }
        } catch (\Throwable $e) {
            // Silently continue if database or tables are not yet initialized (e.g. during migrations)
        }

        return $next($request);
    }
}
