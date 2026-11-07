<?php

namespace App\Services\Tenancy;

use App\Models\Tenant;

class TenantContext
{
    protected static ?Tenant $currentTenant = null;
    protected static bool $bypass = false;

    public static function setTenant(?Tenant $tenant): void
    {
        static::$currentTenant = $tenant;
    }

    public static function getTenant(): ?Tenant
    {
        return static::$currentTenant;
    }

    public static function getTenantId(): ?int
    {
        return static::$currentTenant?->id;
    }

    public static function hasTenant(): bool
    {
        return static::$currentTenant !== null;
    }

    public static function isBypassed(): bool
    {
        return static::$bypass;
    }

    public static function enableBypass(): void
    {
        static::$bypass = true;
    }

    public static function disableBypass(): void
    {
        static::$bypass = false;
    }

    public static function bypass(callable $callback): mixed
    {
        $previous = static::$bypass;
        static::$bypass = true;

        try {
            return $callback();
        } finally {
            static::$bypass = $previous;
        }
    }
}
