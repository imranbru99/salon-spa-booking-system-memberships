<?php

namespace App\Services\AI;

use App\Models\AiLog;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;

class AIService
{
    /**
     * Answer natural language business queries with real tenant data.
     */
    public function askBusinessAssistant(string $query, ?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? TenantContext::getTenantId();
        $queryLower = strtolower($query);

        $response = "";
        $dataContext = [];

        if (str_contains($queryLower, 'revenue') || str_contains($queryLower, 'sales') || str_contains($queryLower, 'income')) {
            $monthStart = Carbon::now()->startOfMonth();
            $monthRevenue = (float) Sale::where('created_at', '>=', $monthStart)->sum('total_amount');
            $todayRevenue = (float) Sale::whereDate('created_at', Carbon::today())->sum('total_amount');
            $totalSalesCount = Sale::where('created_at', '>=', $monthStart)->count();

            $response = sprintf(
                "This month your business generated $%s in revenue from %d completed sales. Today's revenue stands at $%s.",
                number_format($monthRevenue, 2),
                $totalSalesCount,
                number_format($todayRevenue, 2)
            );
            $dataContext = [
                'month_revenue' => $monthRevenue,
                'today_revenue' => $todayRevenue,
                'sales_count' => $totalSalesCount,
            ];
        } elseif (str_contains($queryLower, 'popular') || str_contains($queryLower, 'service')) {
            $topServices = Service::withCount('appointmentServices')
                ->orderByDesc('appointment_services_count')
                ->limit(5)
                ->get();

            $names = $topServices->map(fn ($s) => "{$s->name} ({$s->appointment_services_count} bookings)")->implode(', ');
            $response = "Your most popular services based on completed and booked appointments are: " . ($names ?: 'No services booked yet.');
            $dataContext = ['top_services' => $topServices->pluck('name', 'appointment_services_count')];
        } elseif (str_contains($queryLower, 'staff') || str_contains($queryLower, 'stylist') || str_contains($queryLower, 'employee')) {
            $topStaff = StaffProfile::withCount('appointmentServices')
                ->with('user')
                ->orderByDesc('appointment_services_count')
                ->limit(3)
                ->get();

            $summary = $topStaff->map(fn ($s) => "{$s->user?->name} ({$s->appointment_services_count} appointments)")->implode(', ');
            $response = "Top performing staff by completed appointments: " . ($summary ?: 'No appointments recorded.');
            $dataContext = ['top_staff' => $summary];
        } elseif (str_contains($queryLower, 'customer') || str_contains($queryLower, 'retention') || str_contains($queryLower, 'client')) {
            $totalCustomers = Customer::count();
            $vipCount = Customer::where('segment', 'vip')->count();
            $newCount = Customer::where('segment', 'new')->count();

            $response = sprintf(
                "You have %d total registered customers: %d new clients, and %d VIP clients.",
                $totalCustomers,
                $newCount,
                $vipCount
            );
            $dataContext = ['total' => $totalCustomers, 'vip' => $vipCount, 'new' => $newCount];
        } else {
            $monthRevenue = (float) Sale::where('created_at', '>=', Carbon::now()->startOfMonth())->sum('total_amount');
            $todayBookings = Appointment::whereDate('appointment_date', Carbon::today())->count();
            $activeClients = Customer::count();

            $response = sprintf(
                "Business Summary: Today you have %d bookings scheduled. Total active clients: %d. Month-to-date revenue: $%s. Feel free to ask about revenue trends, popular services, staff workloads, or client retention.",
                $todayBookings,
                $activeClients,
                number_format($monthRevenue, 2)
            );
        }

        // Log AI Query
        AiLog::create([
            'tenant_id' => $tenantId,
            'user_id' => auth()->id(),
            'feature' => 'business_assistant',
            'provider' => 'laravel-ai-hub',
            'model' => 'gpt-4o-mini',
            'prompt' => $query,
            'response' => $response,
            'tokens_used' => strlen($query) + strlen($response),
            'cost' => 0.0002,
        ]);

        return [
            'query' => $query,
            'response' => $response,
            'context' => $dataContext,
        ];
    }

    /**
     * Generate marketing content for campaigns, social media, or service promotions.
     */
    public function generateMarketingContent(string $type, string $topic, string $tone = 'luxury'): string
    {
        $tenant = TenantContext::getTenant();
        $businessName = $tenant ? $tenant->name : 'Glow & Grace Salon and Spa';

        return match ($type) {
            'social' => "✨ Experience ultimate rejuvenation at {$businessName}! Indulge in our signature {$topic} tailored to elevate your natural beauty. Book your bespoke session today and immerse yourself in serenity. #{$businessName} #LuxurySpa #BeautyWellness",
            'email' => "Subject: Exclusive Invitation: Revitalize with {$topic} at {$businessName}\n\nDear Valued Guest,\n\nWe invite you to treat yourself to an exquisite wellness experience. Enjoy special VIP perks on your next {$topic} booking this week at {$businessName}.\n\nWarm regards,\nThe {$businessName} Team",
            'promo' => "Limited Time Offer: Book your {$topic} at {$businessName} and receive a complimentary rejuvenation treatment! Mention code GLOWVIP at checkout.",
            default => "Experience the finest {$topic} treatments crafted by certified beauty artisans at {$businessName}."
        };
    }
}
