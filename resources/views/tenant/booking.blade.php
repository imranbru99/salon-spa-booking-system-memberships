@extends('layouts.public')

@section('title', 'Reserve Your Appointment — Glow & Grace')

@section('content')
<section class="py-12 bg-stone-100 border-b border-stone-200">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <span class="text-xs font-bold uppercase tracking-widest text-rose-800">Direct Reservation</span>
        <h1 class="text-3xl sm:text-4xl font-serif font-bold text-stone-900 mt-2">Book Your Salon & Spa Experience</h1>
        <p class="mt-2 text-xs sm:text-sm text-stone-600">Select your preferred branch, treatment, specialist, and time. Instant confirmation guaranteed.</p>
    </div>
</section>

<section class="py-12 bg-stone-50">
    <div class="max-w-4xl mx-auto px-4">
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('booking.store') }}" method="POST" class="bg-white rounded-3xl border border-stone-200 shadow-sm p-6 sm:p-10 space-y-8" id="bookingForm">
            @csrf

            {{-- Step 1: Branch & Service --}}
            <div>
                <h3 class="font-serif text-lg font-bold text-stone-900 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-rose-900 text-white text-xs flex items-center justify-center font-sans">1</span>
                    Location & Treatment
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Select Sanctuary Branch</label>
                        <select name="branch_id" id="branchSelect" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3 focus:ring-rose-500 focus:border-rose-500" required>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Select Service or Treatment</label>
                        <select name="service_id" id="serviceSelect" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3 focus:ring-rose-500 focus:border-rose-500" required>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}" data-price="{{ $svc->effective_price }}" data-dur="{{ $svc->duration_minutes }}" {{ $selectedServiceId == $svc->id ? 'selected' : '' }}>
                                    {{ $svc->name }} (${{ number_format($svc->effective_price, 2) }} • {{ $svc->duration_minutes }}m)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Step 2: Specialist & Date --}}
            <div>
                <h3 class="font-serif text-lg font-bold text-stone-900 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-rose-900 text-white text-xs flex items-center justify-center font-sans">2</span>
                    Specialist & Date
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Preferred Specialist</label>
                        <select name="staff_id" id="staffSelect" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3 focus:ring-rose-500 focus:border-rose-500">
                            <option value="">Any Available Specialist</option>
                            @foreach($staffList as $st)
                                <option value="{{ $st->id }}">{{ $st->user?->name }} ({{ $st->job_title }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">Appointment Date</label>
                        <input
                            type="date"
                            name="appointment_date"
                            id="dateInput"
                            min="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d') }}"
                            class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3 focus:ring-rose-500 focus:border-rose-500"
                            required
                        />
                    </div>
                </div>
            </div>

            {{-- Step 3: Available Time Slots --}}
            <div>
                <h3 class="font-serif text-lg font-bold text-stone-900 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-rose-900 text-white text-xs flex items-center justify-center font-sans">3</span>
                    Available Time Slot
                </h3>

                <div id="slotsContainer" class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    @php
                        $sampleSlots = ['09:00:00', '10:00:00', '11:30:00', '13:00:00', '14:30:00', '16:00:00', '17:30:00'];
                    @endphp
                    @foreach($sampleSlots as $idx => $sTime)
                        <label class="cursor-pointer">
                            <input type="radio" name="start_time" value="{{ $sTime }}" class="peer sr-only" {{ $idx === 1 ? 'checked' : '' }} required />
                            <div class="p-2.5 text-center rounded-xl border border-stone-200 peer-checked:border-rose-900 peer-checked:bg-rose-900 peer-checked:text-white hover:bg-stone-50 transition text-xs font-semibold">
                                {{ date('g:i A', strtotime($sTime)) }}
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="text-[11px] text-stone-500 mt-2">Slots computed according to branch hours and certified specialist schedules.</p>
            </div>

            {{-- Step 4: Client Contact Details --}}
            <div>
                <h3 class="font-serif text-lg font-bold text-stone-900 mb-4 pb-2 border-b border-stone-100 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-rose-900 text-white text-xs flex items-center justify-center font-sans">4</span>
                    Client Details
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">First Name *</label>
                        <input type="text" name="first_name" value="Victoria" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Last Name</label>
                        <input type="text" name="last_name" value="Sterling" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Email Address *</label>
                        <input type="email" name="email" value="customer@example.test" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Phone Number *</label>
                        <input type="tel" name="phone" value="+1 (555) 400-0001" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3" required />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Promo Code (Try WELCOME15)</label>
                        <input type="text" name="coupon_code" placeholder="WELCOME15" class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3 uppercase" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Special Requests or Treatment Notes</label>
                        <textarea name="customer_notes" rows="2" placeholder="Any preferences, allergies, or pressure requests..." class="w-full text-sm rounded-xl border-stone-300 bg-stone-50 p-3"></textarea>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="pt-4 border-t border-stone-100 flex items-center justify-between">
                <span class="text-xs text-stone-500">Instant reservation &bull; Free cancellation up to 24h prior</span>
                <button type="submit" class="px-8 py-3.5 rounded-full bg-rose-900 hover:bg-rose-950 text-white font-bold text-sm shadow-md transition">
                    Confirm & Reserve Appointment &rarr;
                </button>
            </div>
        </form>
    </div>
</section>
@endsection
