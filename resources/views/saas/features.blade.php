@extends('layouts.public')

@section('title', 'Platform Features & Capabilities — Salon & Spa SaaS')

@section('content')
<section class="py-20 bg-stone-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-brand-900 mb-4">
                Architecture & Modules
            </span>
            <h1 class="text-4xl font-serif font-bold text-stone-900">Enterprise Capabilities Directory</h1>
            <p class="mt-4 text-stone-600 text-sm">
                Every business tool required to operate, automate, and expand modern salon, spa, and beauty enterprises.
            </p>
        </div>

        <div class="space-y-12 max-w-5xl mx-auto">
            {{-- Feature 1: Booking --}}
            <div id="booking" class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                <div class="md:col-span-7 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-2.5 py-1 rounded">Module: Booking & Calendar</span>
                    <h3 class="text-2xl font-serif font-bold text-stone-900">Intelligent Scheduling & Conflict Prevention</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Transaction-safe scheduling engine that dynamically computes staff working hours, breaks, preparation buffers, cleanup buffers, and room requirements. Eliminates double bookings and supports group sessions, walk-in reception check-ins, and online self-booking.
                    </p>
                    <ul class="text-xs text-stone-700 space-y-2">
                        <li>• Buffer times before & after treatment sessions</li>
                        <li>• Deposit handling & partial payments</li>
                        <li>• Real-time client status: Confirmed, Checked In, In Progress, Completed</li>
                    </ul>
                </div>
                <div class="md:col-span-5 bg-stone-100 rounded-2xl p-6 text-center border border-stone-200">
                    <span class="block font-serif text-3xl font-bold text-brand-900">100%</span>
                    <span class="text-xs text-stone-600 font-medium">Conflict-Free Scheduling</span>
                </div>
            </div>

            {{-- Feature 2: POS --}}
            <div id="pos" class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                <div class="md:col-span-7 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-2.5 py-1 rounded">Module: Point of Sale (POS)</span>
                    <h3 class="text-2xl font-serif font-bold text-stone-900">High-Speed Front Desk Checkout Terminal</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Rapid checkout combining treatments, retail product sales, prepaid packages, gift cards, and coupons into a single transaction. Automatic stock movement deduction and instant tax invoice generation with Unicode PDF receipts.
                    </p>
                    <ul class="text-xs text-stone-700 space-y-2">
                        <li>• Cash drawer sessions with opening/closing balancing</li>
                        <li>• Multi-tender split payments (Card, Cash, Digital)</li>
                        <li>• Staff sales attribution for commission calculation</li>
                    </ul>
                </div>
                <div class="md:col-span-5 bg-stone-100 rounded-2xl p-6 text-center border border-stone-200">
                    <span class="block font-serif text-3xl font-bold text-brand-900">&lt; 15s</span>
                    <span class="text-xs text-stone-600 font-medium">Average POS Checkout Speed</span>
                </div>
            </div>

            {{-- Feature 3: Packages & Memberships --}}
            <div id="packages" class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                <div class="md:col-span-7 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-2.5 py-1 rounded">Module: Packages & Memberships</span>
                    <h3 class="text-2xl font-serif font-bold text-stone-900">Prepaid Bundles & Recurring Subscriptions</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Foster client loyalty and lock in predictable recurring revenue. Build custom prepaid bundles with credit ledgers, or offer monthly/annual memberships with automatic discount percentages, complimentary credits, and VIP priority booking.
                    </p>
                    <ul class="text-xs text-stone-700 space-y-2">
                        <li>• Granular credit redemptions with audit history</li>
                        <li>• Membership pause, resume, and cancellation safeguards</li>
                        <li>• Automatic benefit calculation during booking and checkout</li>
                    </ul>
                </div>
                <div class="md:col-span-5 bg-stone-100 rounded-2xl p-6 text-center border border-stone-200">
                    <span class="block font-serif text-3xl font-bold text-brand-900">+42%</span>
                    <span class="text-xs text-stone-600 font-medium">Average Client Retention Increase</span>
                </div>
            </div>

            {{-- Feature 4: Commissions & Payroll --}}
            <div id="commissions" class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                <div class="md:col-span-7 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-2.5 py-1 rounded">Module: Commissions & Payroll</span>
                    <h3 class="text-2xl font-serif font-bold text-stone-900">Transparent Staff Earnings & Automated Payroll</h3>
                    <p class="text-xs text-stone-600 leading-relaxed">
                        Configurable commission tiers for service executions and retail product sales. Commissions are credited upon verified service completion. Generate period payroll statements with base salary, commission totals, allowances, and approved deductions.
                    </p>
                </div>
                <div class="md:col-span-5 bg-stone-100 rounded-2xl p-6 text-center border border-stone-200">
                    <span class="block font-serif text-3xl font-bold text-brand-900">100%</span>
                    <span class="text-xs text-stone-600 font-medium">Auditable Commission Ledger</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
