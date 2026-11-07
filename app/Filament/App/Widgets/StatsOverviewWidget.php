<?php

namespace App\Filament\App\Widgets;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\CustomerMembership;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $todayAppointments = Appointment::whereDate('appointment_date', $today)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        $todayRevenue = (float) Sale::whereDate('created_at', $today)->sum('total_amount');
        $monthRevenue = (float) Sale::where('created_at', '>=', $startOfMonth)->sum('total_amount');

        $activeClients = Customer::where('is_active', true)->count();
        $vipClients = Customer::where('segment', 'vip')->count();

        $activeMemberships = CustomerMembership::where('status', 'active')
            ->where('ends_at', '>=', $today->toDateString())
            ->count();

        return [
            Stat::make("Today's Appointments", $todayAppointments)
                ->description('Active bookings scheduled today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make("Today's Revenue", '$' . number_format($todayRevenue, 2))
                ->description('Month to date: $' . number_format($monthRevenue, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Active Clients', $activeClients)
                ->description("{$vipClients} VIP clients")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Active Memberships', $activeMemberships)
                ->description('Recurring subscriptions')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),
        ];
    }
}
