@extends('layouts.public')

@section('title', 'Client Portal — Glow & Grace')

@section('content')
<section class="py-12 bg-stone-100 border-b border-stone-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="inline-block text-xs uppercase font-bold tracking-widest text-rose-800">Welcome Back</span>
            <h1 class="text-3xl font-serif font-bold text-stone-900 mt-1">{{ $customer->full_name }}</h1>
            <p class="text-xs text-stone-600">Client ID: #CL-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }} &bull; {{ $customer->email }}</p>
        </div>

        <div class="flex items-center gap-4">
            <div class="bg-white rounded-2xl p-4 border border-stone-200 shadow-sm text-center">
                <span class="text-[10px] uppercase font-bold text-stone-400 block">VIP Tier</span>
                <span class="text-sm font-bold text-amber-600 uppercase">{{ $customer->segment }}</span>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-stone-200 shadow-sm text-center">
                <span class="text-[10px] uppercase font-bold text-stone-400 block">Loyalty Points</span>
                <span class="text-lg font-serif font-bold text-brand-900">{{ number_format($customer->loyalty_points) }} pts</span>
            </div>
            <a href="/salon/booking" class="px-5 py-3 rounded-full bg-brand-900 hover:bg-brand-950 text-white font-bold text-xs shadow transition">
                + New Booking
            </a>
        </div>
    </div>
</section>

<section class="py-12 bg-stone-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        {{-- Upcoming Appointments --}}
        <div>
            <h2 class="font-serif text-xl font-bold text-stone-900 mb-4 pb-2 border-b border-stone-200">Upcoming Reservations</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($upcomingAppointments as $appt)
                    <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono font-bold text-brand-900">{{ $appt->booking_reference }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-800">
                                {{ $appt->status }}
                            </span>
                        </div>
                        <h4 class="font-serif font-bold text-base text-stone-900">
                            {{ $appt->services->first()?->service?->name ?? 'Luxury Session' }}
                        </h4>
                        <div class="text-xs text-stone-600 space-y-1">
                            <p>📅 {{ $appt->appointment_date->format('l, F j, Y') }} at {{ date('g:i A', strtotime($appt->start_time)) }}</p>
                            <p>📍 {{ $appt->branch->name }}</p>
                            <p>👤 Specialist: {{ $appt->services->first()?->staff?->user?->name ?? 'Confirmed Specialist' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-2xl border border-stone-200 p-8 text-center text-xs text-stone-500">
                        No upcoming reservations. <a href="/salon/booking" class="text-rose-800 font-bold hover:underline">Book your next experience &rarr;</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Memberships & Packages --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Memberships --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm space-y-4">
                <h3 class="font-serif text-lg font-bold text-stone-900 border-b border-stone-100 pb-2">Active Memberships</h3>
                @forelse($memberships as $mem)
                    <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-100 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-stone-900 text-sm">{{ $mem->plan->name }}</span>
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold uppercase text-[10px]">Active</span>
                        </div>
                        <p class="text-stone-600">Valid until: {{ $mem->ends_at->format('M d, Y') }}</p>
                        <div class="pt-2 border-t border-rose-200/60 flex justify-between font-semibold">
                            <span>Remaining Free Credits:</span>
                            <span class="text-rose-900 font-bold">{{ $mem->remaining_free_credits }} Sessions</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-stone-400">No active memberships.</p>
                @endforelse
            </div>

            {{-- Packages --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm space-y-4">
                <h3 class="font-serif text-lg font-bold text-stone-900 border-b border-stone-100 pb-2">Prepaid Packages</h3>
                @forelse($packages as $cpkg)
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-stone-900 text-sm">{{ $cpkg->package->name }}</span>
                            <span class="px-2 py-0.5 rounded bg-stone-200 text-stone-800 font-bold text-[10px]">{{ $cpkg->remaining_credits }}/{{ $cpkg->total_credits }} Left</span>
                        </div>
                        <p class="text-stone-600">Expires on: {{ $cpkg->expires_at->format('M d, Y') }}</p>
                    </div>
                @empty
                    <p class="text-xs text-stone-400">No active prepaid packages.</p>
                @endforelse
            </div>
        </div>

        {{-- Invoices & Receipts --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm space-y-4">
            <h3 class="font-serif text-lg font-bold text-stone-900 border-b border-stone-100 pb-2">Billing History & Invoices</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-stone-200 text-stone-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5">Invoice #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($invoices->take(6) as $inv)
                            <tr>
                                <td class="py-3 font-mono font-bold text-brand-900">{{ $inv->invoice_number }}</td>
                                <td class="text-stone-600">{{ $inv->invoice_date->format('M d, Y') }}</td>
                                <td class="font-bold text-stone-900">${{ number_format($inv->total_amount, 2) }}</td>
                                <td><span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">{{ $inv->status }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('portal.invoice.download', $inv->id) }}" class="inline-flex items-center gap-1 px-3 py-1 rounded bg-stone-100 hover:bg-stone-200 font-bold text-stone-700 text-[11px] transition">
                                        Download PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-stone-400">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
