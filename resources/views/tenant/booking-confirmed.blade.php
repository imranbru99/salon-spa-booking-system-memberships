@extends('layouts.public')

@section('title', 'Booking Confirmed — Glow & Grace')

@section('content')
<section class="py-16 bg-stone-50">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-8 sm:p-10 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl mx-auto mb-4 font-bold">
                ✓
            </div>
            <span class="text-xs font-bold uppercase tracking-widest text-emerald-700">Reservation Confirmed</span>
            <h1 class="text-3xl font-serif font-bold text-stone-900 mt-2">We Look Forward to Welcoming You</h1>
            <p class="text-xs text-stone-600 mt-2">A confirmation notice has been dispatched to {{ $appointment->customer->email }}.</p>

            <div class="mt-8 p-6 rounded-2xl bg-stone-50 border border-stone-200 text-left space-y-3 text-xs">
                <div class="flex justify-between items-center pb-2 border-b border-stone-200">
                    <span class="text-stone-500">Booking Reference</span>
                    <span class="font-mono font-bold text-sm text-brand-900">{{ $appointment->booking_reference }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Treatment</span>
                    <span class="font-bold text-stone-900">{{ $appointment->services->first()?->service?->name ?? 'Luxury Treatment' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Sanctuary Location</span>
                    <span class="font-bold text-stone-900">{{ $appointment->branch->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Date & Time</span>
                    <span class="font-bold text-stone-900">{{ $appointment->appointment_date->format('l, F j, Y') }} at {{ date('g:i A', strtotime($appointment->start_time)) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-stone-500">Specialist</span>
                    <span class="font-bold text-stone-900">{{ $appointment->services->first()?->staff?->user?->name ?? 'Assigned Beauty Specialist' }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-stone-200 font-bold text-sm">
                    <span>Total Amount</span>
                    <span class="text-rose-900">${{ number_format($appointment->total_amount, 2) }}</span>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap gap-4 justify-center">
                <a href="/portal" class="px-6 py-2.5 rounded-full bg-stone-900 hover:bg-stone-950 text-white font-bold text-xs transition">
                    View in Client Portal &rarr;
                </a>
                <a href="/salon/booking" class="px-6 py-2.5 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs transition">
                    Book Another Session
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
