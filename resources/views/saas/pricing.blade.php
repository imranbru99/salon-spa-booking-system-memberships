@extends('layouts.public')

@section('title', 'Subscription Plans & Pricing — Salon & Spa SaaS')

@section('content')
<section class="py-20 bg-stone-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-brand-900 mb-4">
                Transparent Commercial SaaS Pricing
            </span>
            <h1 class="text-4xl font-serif font-bold text-stone-900">Choose the Ideal Plan for Your Business</h1>
            <p class="mt-4 text-stone-600 text-sm">
                Scale seamlessly from an independent single-chair studio to a global luxury spa franchise. No hidden fees. Cancel or switch plans anytime.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto">
            @foreach(\App\Models\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get() as $plan)
                <div class="rounded-3xl border {{ $plan->is_popular ? 'border-2 border-brand-500 shadow-xl bg-white relative' : 'border-stone-200 bg-white shadow-sm' }} p-8 flex flex-col justify-between">
                    @if($plan->is_popular)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-brand-900 text-white font-bold text-[10px] uppercase tracking-widest">
                            Most Popular for Spas
                        </span>
                    @endif
                    <div>
                        <h3 class="font-serif text-2xl font-bold text-stone-900">{{ $plan->name }}</h3>
                        <p class="mt-2 text-xs text-stone-600 leading-relaxed">{{ $plan->description }}</p>

                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="font-serif text-4xl font-bold text-stone-900">${{ number_format($plan->price_monthly, 0) }}</span>
                            <span class="text-xs text-stone-500 font-medium">/ month</span>
                        </div>
                        <div class="text-[11px] text-stone-400 mt-1">or ${{ number_format($plan->price_yearly, 0) }} billed annually (save 17%)</div>

                        <ul class="mt-8 space-y-3 text-xs text-stone-700">
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-600 font-bold">✓</span> Up to <strong>{{ $plan->max_branches }}</strong> {{ Str::plural('Branch', $plan->max_branches) }}
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-600 font-bold">✓</span> Up to <strong>{{ $plan->max_staff }}</strong> Employee Profiles
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-emerald-600 font-bold">✓</span> Up to <strong>{{ number_format($plan->max_bookings_per_month) }}</strong> Monthly Bookings
                            </li>
                            @if(!empty($plan->features))
                                @foreach($plan->features as $feat)
                                    <li class="flex items-center gap-2">
                                        <span class="text-emerald-600 font-bold">✓</span> {{ $feat }}
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <a href="/app/login" class="mt-10 block text-center py-3 rounded-full {{ $plan->is_popular ? 'bg-brand-900 hover:bg-brand-950 text-white font-bold shadow' : 'bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold' }} text-xs transition">
                        Select {{ $plan->name }} &rarr;
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
