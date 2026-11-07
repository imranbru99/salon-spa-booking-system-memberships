@extends('layouts.public')

@section('title', 'Salon & Spa SaaS — Complete Business Management Platform')

@section('content')
{{-- Hero Section --}}
<section class="relative overflow-hidden bg-gradient-to-b from-stone-100 via-rose-50/30 to-stone-50 py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-brand-900 mb-6">
                ✨ Enterprise Multi-Tenant SaaS Platform
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-serif font-bold text-stone-900 tracking-tight leading-tight">
                The Complete Operating System for <span class="text-brand-900 italic">Salons, Spas & Wellness</span>
            </h1>
            <p class="mt-6 text-base sm:text-lg text-stone-600 leading-relaxed font-normal">
                Empower your beauty enterprise with an integrated, production-ready SaaS suite: friction-free online booking, multi-branch management, POS checkout, prepaid service packages, recurring memberships, inventory tracking, and staff commissions.
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="/salon/booking" class="px-8 py-3.5 rounded-full bg-brand-900 hover:bg-brand-950 text-white font-bold text-sm shadow-lg hover:shadow-xl transition">
                    Explore Live Online Booking
                </a>
                <a href="#demo-accounts" class="px-8 py-3.5 rounded-full bg-white hover:bg-stone-100 text-stone-800 font-bold text-sm border border-stone-200 shadow-sm transition">
                    Explore Demo Panels
                </a>
            </div>
        </div>

        {{-- Metrics Highlights --}}
        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto text-center">
            <div class="bg-white/80 backdrop-blur p-6 rounded-2xl border border-stone-200 shadow-sm">
                <span class="block font-serif text-3xl font-bold text-brand-900">40+</span>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider mt-1">Configurable Modules</span>
            </div>
            <div class="bg-white/80 backdrop-blur p-6 rounded-2xl border border-stone-200 shadow-sm">
                <span class="block font-serif text-3xl font-bold text-brand-900">100%</span>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider mt-1">Tenant Isolated</span>
            </div>
            <div class="bg-white/80 backdrop-blur p-6 rounded-2xl border border-stone-200 shadow-sm">
                <span class="block font-serif text-3xl font-bold text-brand-900">5</span>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider mt-1">Role-Specific Panels</span>
            </div>
            <div class="bg-white/80 backdrop-blur p-6 rounded-2xl border border-stone-200 shadow-sm">
                <span class="block font-serif text-3xl font-bold text-brand-900">0</span>
                <span class="text-xs font-semibold text-stone-500 uppercase tracking-wider mt-1">Double-Booking Risk</span>
            </div>
        </div>
    </div>
</section>

{{-- Live Demo Accounts Access Section --}}
<section id="demo-accounts" class="py-20 bg-white border-y border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-3xl font-serif font-bold text-stone-900">Interactive Role Dashboards</h2>
            <p class="mt-3 text-sm text-stone-600">
                Explore the platform instantly. Switch between Platform Administrator, Business Owner, Branch Manager, Receptionist, Staff Stylist, or Client.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Platform Super Admin --}}
            <div class="p-6 rounded-2xl border border-stone-200 bg-stone-50/50 hover:border-brand-500 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-900">Platform SaaS</span>
                        <span class="text-xs text-stone-400">/admin</span>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900">Platform Super Administrator</h3>
                    <p class="text-xs text-stone-600 mt-2">Manage all registered salon tenants, subscription plans, MRR revenue metrics, and system configuration.</p>
                    <div class="mt-4 p-3 bg-white rounded-lg border border-stone-200 text-xs font-mono space-y-1">
                        <div><span class="text-stone-400">Email:</span> platform@example.test</div>
                        <div><span class="text-stone-400">Password:</span> Password123!</div>
                    </div>
                </div>
                <a href="/admin/login" class="mt-6 block text-center py-2.5 rounded-xl bg-indigo-900 hover:bg-indigo-950 text-white font-bold text-xs transition">
                    Open Super Admin Panel &rarr;
                </a>
            </div>

            {{-- Business Owner --}}
            <div class="p-6 rounded-2xl border-2 border-brand-500 bg-rose-50/20 shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-rose-100 text-brand-900">Salon Owner</span>
                        <span class="text-xs text-stone-400">/app</span>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900">Glow & Grace Business Owner</h3>
                    <p class="text-xs text-stone-600 mt-2">Full control of branches, appointments, services catalogue, employees, prepaid packages, memberships, POS, and dynamic modules.</p>
                    <div class="mt-4 p-3 bg-white rounded-lg border border-stone-200 text-xs font-mono space-y-1">
                        <div><span class="text-stone-400">Email:</span> owner@glowgrace.test</div>
                        <div><span class="text-stone-400">Password:</span> Password123!</div>
                    </div>
                </div>
                <a href="/app/login" class="mt-6 block text-center py-2.5 rounded-xl bg-brand-900 hover:bg-brand-950 text-white font-bold text-xs shadow transition">
                    Open Business Owner Dashboard &rarr;
                </a>
            </div>

            {{-- Receptionist / Front Desk --}}
            <div class="p-6 rounded-2xl border border-stone-200 bg-stone-50/50 hover:border-brand-500 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-violet-100 text-violet-900">Front Desk</span>
                        <span class="text-xs text-stone-400">/reception</span>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900">Lead Receptionist</h3>
                    <p class="text-xs text-stone-600 mt-2">Check-in today's arriving guests, create walk-in appointments, handle payments, and manage fast checkout.</p>
                    <div class="mt-4 p-3 bg-white rounded-lg border border-stone-200 text-xs font-mono space-y-1">
                        <div><span class="text-stone-400">Email:</span> reception@glowgrace.test</div>
                        <div><span class="text-stone-400">Password:</span> Password123!</div>
                    </div>
                </div>
                <a href="/reception/login" class="mt-6 block text-center py-2.5 rounded-xl bg-violet-900 hover:bg-violet-950 text-white font-bold text-xs transition">
                    Open Front Desk Terminal &rarr;
                </a>
            </div>

            {{-- Staff / Stylist --}}
            <div class="p-6 rounded-2xl border border-stone-200 bg-stone-50/50 hover:border-brand-500 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900">Stylist & Therapist</span>
                        <span class="text-xs text-stone-400">/staff</span>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900">Stylist & Spa Specialist</h3>
                    <p class="text-xs text-stone-600 mt-2">Personal dashboard showing today's client bookings, service start/complete actions, and earned commissions.</p>
                    <div class="mt-4 p-3 bg-white rounded-lg border border-stone-200 text-xs font-mono space-y-1">
                        <div><span class="text-stone-400">Email:</span> stylist@glowgrace.test</div>
                        <div><span class="text-stone-400">Password:</span> Password123!</div>
                    </div>
                </div>
                <a href="/staff/login" class="mt-6 block text-center py-2.5 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs transition">
                    Open Staff Portal &rarr;
                </a>
            </div>

            {{-- Customer Portal --}}
            <div class="p-6 rounded-2xl border border-stone-200 bg-stone-50/50 hover:border-brand-500 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900">VIP Client</span>
                        <span class="text-xs text-stone-400">/portal</span>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900">Client Self-Service Portal</h3>
                    <p class="text-xs text-stone-600 mt-2">Self-service portal for guests: view upcoming appointments, view purchased packages and membership free credits, and download PDF receipts.</p>
                    <div class="mt-4 p-3 bg-white rounded-lg border border-stone-200 text-xs font-mono space-y-1">
                        <div><span class="text-stone-400">Email:</span> customer@example.test</div>
                        <div><span class="text-stone-400">Password:</span> Password123!</div>
                    </div>
                </div>
                <a href="/portal" class="mt-6 block text-center py-2.5 rounded-xl bg-stone-800 hover:bg-stone-900 text-white font-bold text-xs transition">
                    Open Client Portal &rarr;
                </a>
            </div>

            {{-- Live Online Booking Engine --}}
            <div class="p-6 rounded-2xl border border-stone-200 bg-stone-50/50 hover:border-brand-500 transition group flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-rose-100 text-brand-900">Public Site</span>
                        <span class="text-xs text-stone-400">/salon/booking</span>
                    </div>
                    <h3 class="text-lg font-bold text-stone-900">Guest Online Booking Flow</h3>
                    <p class="text-xs text-stone-600 mt-2">Public booking wizard: choose branch, service, specialist, and dynamic time slot with immediate booking reference generation.</p>
                    <div class="mt-4 p-3 bg-white rounded-lg border border-stone-200 text-xs font-mono space-y-1">
                        <div><span class="text-stone-400">No login required</span></div>
                        <div><span class="text-stone-400">Instant slot conflict checks</span></div>
                    </div>
                </div>
                <a href="/salon/booking" class="mt-6 block text-center py-2.5 rounded-xl bg-brand-900 hover:bg-brand-950 text-white font-bold text-xs transition">
                    Test Online Booking Wizard &rarr;
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Module Matrix Overview --}}
<section class="py-20 bg-stone-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl font-serif font-bold text-stone-900">Modular Architecture Engine</h2>
            <p class="mt-3 text-stone-600 text-sm">
                Every business is unique. Activate only the modules your salon or spa requires.
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach(\App\Services\Modules\ModuleRegistry::all() as $mKey => $m)
                <div class="p-4 rounded-xl bg-white border border-stone-200 shadow-sm flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-brand-900 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        ✓
                    </div>
                    <div>
                        <h4 class="font-bold text-xs text-stone-900">{{ $m['name'] }}</h4>
                        <span class="text-[10px] text-stone-500 uppercase tracking-wider">{{ $m['category'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
