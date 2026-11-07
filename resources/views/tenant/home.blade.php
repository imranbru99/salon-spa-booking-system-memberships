@extends('layouts.public')

@section('title', 'Glow & Grace — Luxury Salon & Spa Sanctuary')

@section('content')
{{-- Tenant Hero --}}
<section class="relative bg-stone-900 text-white py-24 lg:py-32 overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=1920&q=80');"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-block text-xs uppercase tracking-widest text-rose-300 font-bold mb-4">
            Beverly Hills &bull; Malibu Coastal Retreat
        </span>
        <h1 class="text-4xl sm:text-6xl font-serif font-bold tracking-tight leading-tight max-w-4xl mx-auto">
            Where Timeless Elegance Meets Holistic Sanctuary
        </h1>
        <p class="mt-6 text-sm sm:text-base text-stone-300 max-w-2xl mx-auto font-light leading-relaxed">
            Immerse yourself in world-class hair couture, medical aesthetic skin therapies, and bespoke wellness bodywork crafted by master artisans.
        </p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="/salon/booking" class="px-8 py-3.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-lg transition">
                Reserve Your Appointment
            </a>
            <a href="/salon/services" class="px-8 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-white font-bold text-sm backdrop-blur border border-white/20 transition">
                Explore Treatment Menu
            </a>
        </div>
    </div>
</section>

{{-- Signature Services Highlights --}}
<section class="py-20 bg-stone-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-widest text-rose-800">Curated Experiences</span>
            <h2 class="text-3xl font-serif font-bold text-stone-900 mt-2">Signature Treatment Highlights</h2>
            <p class="text-xs text-stone-600 mt-2">Performed with rare organic botanicals, peptide serums, and high-precision aesthetic artistry.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach(\App\Models\Service::where('is_featured', true)->limit(6)->get() as $svc)
                <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-stone-500 mb-3">
                            <span class="font-bold text-rose-700 uppercase tracking-wider">{{ $svc->category?->name }}</span>
                            <span>{{ $svc->duration_minutes }} Mins</span>
                        </div>
                        <h3 class="font-serif text-lg font-bold text-stone-900">{{ $svc->name }}</h3>
                        <p class="text-xs text-stone-600 mt-2 leading-relaxed">{{ $svc->description }}</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-stone-400">Treatment Rate</span>
                            <span class="block font-serif text-xl font-bold text-stone-900">${{ number_format($svc->effective_price, 2) }}</span>
                        </div>
                        <a href="/salon/booking?service={{ $svc->id }}" class="px-4 py-2 rounded-full bg-stone-900 hover:bg-rose-900 text-white text-xs font-bold transition">
                            Book Now
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="/salon/services" class="inline-flex items-center gap-2 text-xs font-bold text-rose-800 hover:text-rose-950 uppercase tracking-wider">
                View All 30 Treatments &rarr;
            </a>
        </div>
    </div>
</section>

{{-- Prepaid Packages & Memberships Showcase --}}
<section class="py-20 bg-white border-y border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <span class="text-xs font-bold uppercase tracking-widest text-rose-800">Prepaid Wellness Packages</span>
                <h2 class="text-3xl font-serif font-bold text-stone-900">Indulgent Bundles & Curated Spa Days</h2>
                <p class="text-xs text-stone-600 leading-relaxed">
                    Treat yourself or gift an extraordinary experience with our signature prepaid service bundles. Each package includes complimentary credits, private room access, and extended validity.
                </p>

                <div class="space-y-4">
                    @foreach(\App\Models\ServicePackage::where('is_active', true)->limit(3)->get() as $pkg)
                        <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 flex items-center justify-between">
                            <div>
                                <h4 class="font-serif font-bold text-sm text-stone-900">{{ $pkg->name }}</h4>
                                <span class="text-xs text-stone-500">{{ $pkg->validity_days }} Days Validity &bull; {{ $pkg->items->sum('quantity') }} Treatments Included</span>
                            </div>
                            <span class="font-serif text-lg font-bold text-rose-800">${{ number_format($pkg->price, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Memberships --}}
            <div class="bg-stone-900 text-white rounded-3xl p-8 lg:p-10 space-y-6">
                <span class="text-xs font-bold uppercase tracking-widest text-rose-400">VIP Sanctuary Memberships</span>
                <h3 class="text-2xl font-serif font-bold">Priority Reservations & Exclusive Savings</h3>
                <p class="text-xs text-stone-300 leading-relaxed">
                    Join our privileged circle of members and unlock monthly complimentary treatments, up to 25% savings on all services & retail, and concierge booking privileges.
                </p>

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-xs text-rose-300 font-bold block">Gold Sanctuary</span>
                        <span class="font-serif text-2xl font-bold mt-1 block">$99 / mo</span>
                        <span class="text-[11px] text-stone-400 mt-1 block">15% off + 1 monthly facial credit</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-xs text-rose-300 font-bold block">Platinum Royal</span>
                        <span class="font-serif text-2xl font-bold mt-1 block">$199 / mo</span>
                        <span class="text-[11px] text-stone-400 mt-1 block">20% off + 2 monthly spa sessions</span>
                    </div>
                </div>

                <a href="/salon/booking" class="block text-center py-3 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition">
                    Inquire About Memberships
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Two Locations --}}
<section class="py-20 bg-stone-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-widest text-rose-800">Our Sanctuaries</span>
            <h2 class="text-3xl font-serif font-bold text-stone-900 mt-2">Visit Our Locations</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach(\App\Models\Branch::where('is_active', true)->get() as $branch)
                <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">
                    <span class="text-xs font-bold uppercase tracking-widest text-rose-700">{{ $branch->code }} &bull; {{ $branch->is_main ? 'Flagship' : 'Retreat' }}</span>
                    <h3 class="font-serif text-xl font-bold text-stone-900 mt-2">{{ $branch->name }}</h3>
                    <p class="text-xs text-stone-600 mt-2">{{ $branch->address }}, {{ $branch->city }}, {{ $branch->state }} {{ $branch->postal_code }}</p>
                    <p class="text-xs text-stone-600 mt-1">Telephone: <strong>{{ $branch->phone }}</strong></p>

                    <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                        <span class="text-xs text-emerald-600 font-semibold">● Open Today 09:00 - 19:00</span>
                        <a href="/salon/booking?branch={{ $branch->id }}" class="text-xs font-bold text-stone-900 hover:text-rose-800">
                            Book at this Branch &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
