@extends('layouts.public')

@section('title', 'Treatment Menu & Services — Glow & Grace')

@section('content')
<section class="py-16 bg-stone-100 border-b border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="text-xs font-bold uppercase tracking-widest text-rose-800">The Glow & Grace Treatment Menu</span>
        <h1 class="text-4xl font-serif font-bold text-stone-900 mt-2">Services & Wellness Rituals</h1>
        <p class="mt-3 text-xs sm:text-sm text-stone-600 max-w-2xl mx-auto">
            Explore our curated menu of hair couture, bespoke coloring, high-performance clinical facials, restorative massages, and grooming ceremonies.
        </p>
    </div>
</section>

<section class="py-16 bg-stone-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
        @foreach(\App\Models\ServiceCategory::with(['services' => fn($q) => $q->where('is_active', true)])->get() as $category)
            @if($category->services->isNotEmpty())
                <div>
                    <div class="border-b-2 border-stone-200 pb-3 mb-8 flex items-baseline justify-between">
                        <div>
                            <h2 class="font-serif text-2xl font-bold text-stone-900">{{ $category->name }}</h2>
                            <p class="text-xs text-stone-500 mt-1">{{ $category->description }}</p>
                        </div>
                        <span class="text-xs font-semibold text-rose-800">{{ $category->services->count() }} Rituals</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($category->services as $svc)
                            <div class="bg-white rounded-2xl border border-stone-200 p-6 shadow-sm hover:border-rose-400 transition flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-4">
                                        <h3 class="font-serif font-bold text-base text-stone-900">{{ $svc->name }}</h3>
                                        <span class="font-serif font-bold text-lg text-rose-900 flex-shrink-0">
                                            @if($svc->sale_price)
                                                <span class="text-xs text-stone-400 line-through mr-1">${{ number_format($svc->price, 2) }}</span>
                                                ${{ number_format($svc->sale_price, 2) }}
                                            @else
                                                ${{ number_format($svc->price, 2) }}
                                            @endif
                                        </span>
                                    </div>
                                    <p class="text-xs text-stone-600 mt-2 leading-relaxed">{{ $svc->description }}</p>

                                    <div class="mt-4 flex flex-wrap gap-2 text-[11px] text-stone-500">
                                        <span class="px-2 py-0.5 rounded bg-stone-100">⏱ {{ $svc->duration_minutes }} Mins Duration</span>
                                        @if($svc->deposit_required)
                                            <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-800">Deposit: ${{ number_format($svc->deposit_amount, 2) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-end">
                                    <a href="/salon/booking?service={{ $svc->id }}" class="px-5 py-2 rounded-full bg-stone-900 hover:bg-rose-900 text-white text-xs font-bold transition">
                                        Select & Book &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</section>
@endsection
