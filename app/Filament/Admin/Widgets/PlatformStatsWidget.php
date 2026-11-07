<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $tenantsCount = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();

        $totalPlatformBookings = Appointment::count();
        $totalPlans = SubscriptionPlan::where('is_active', true)->count();

        $mrr = (float) TenantSubscription::where('status', 'active')->sum('price');

        return [
            Stat::make('Active Tenants', "{$activeTenants} / {$tenantsCount}")
                ->description('Registered business accounts')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Estimated Platform MRR', '$' . number_format($mrr, 2))
                ->description('Monthly Recurring Subscription Revenue')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Total Platform Bookings', number_format($totalPlatformBookings))
                ->description('All time appointments across salons')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Subscription Plans', $totalPlans)
                ->description('Configured pricing tiers')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('warning'),
        ];
    }
}
