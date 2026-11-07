<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left: Item Selection (Catalog) --}}
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Branch</label>
                        <select wire:model.live="selectedBranchId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-white">
                            @foreach(\App\Models\Branch::all() as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Client (Optional)</label>
                        <select wire:model.live="selectedCustomerId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-white">
                            <option value="">Walk-in Guest</option>
                            @foreach(\App\Models\Customer::limit(30)->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->segment }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Attending Staff</label>
                        <select wire:model.live="selectedStaffId" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-white">
                            <option value="">No Staff Assigned</option>
                            @foreach(\App\Models\StaffProfile::with('user')->get() as $sp)
                                <option value="{{ $sp->id }}">{{ $sp->user?->name }} ({{ $sp->job_title }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Search & Category Tabs --}}
                <div class="flex items-center gap-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Search treatments or products..."
                        class="flex-1 text-sm rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 dark:text-white px-3 py-2"
                    />
                    <div class="flex rounded-lg bg-gray-100 dark:bg-gray-800 p-0.5 text-xs font-medium">
                        <button wire:click="$set('activeCategory', 'all')" type="button" class="px-3 py-1.5 rounded-md {{ $activeCategory === 'all' ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white font-bold' : 'text-gray-600 dark:text-gray-400' }}">All</button>
                        <button wire:click="$set('activeCategory', 'services')" type="button" class="px-3 py-1.5 rounded-md {{ $activeCategory === 'services' ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white font-bold' : 'text-gray-600 dark:text-gray-400' }}">Services</button>
                        <button wire:click="$set('activeCategory', 'products')" type="button" class="px-3 py-1.5 rounded-md {{ $activeCategory === 'products' ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-white font-bold' : 'text-gray-600 dark:text-gray-400' }}">Retail</button>
                    </div>
                </div>
            </div>

            {{-- Grid of Services & Products --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-[550px] overflow-y-auto pr-1">
                @if($activeCategory === 'all' || $activeCategory === 'services')
                    @php
                        $servicesQ = \App\Models\Service::where('is_active', true);
                        if (!empty($searchQuery)) {
                            $servicesQ->where('name', 'like', "%{$searchQuery}%");
                        }
                    @endphp
                    @foreach($servicesQ->limit(18)->get() as $svc)
                        <button
                            type="button"
                            wire:click="addToCart('service', {{ $svc->id }}, '{{ addslashes($svc->name) }}', {{ $svc->effective_price }})"
                            class="text-left rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 shadow-sm hover:border-rose-400 dark:hover:border-rose-500 hover:shadow transition group flex flex-col justify-between"
                        >
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-rose-600 dark:text-rose-400">Service • {{ $svc->duration_minutes }}m</span>
                                <h5 class="font-bold text-xs text-gray-900 dark:text-white mt-1 group-hover:text-rose-600 line-clamp-2">{{ $svc->name }}</h5>
                            </div>
                            <div class="mt-3 font-extrabold text-sm text-gray-900 dark:text-white">
                                ${{ number_format($svc->effective_price, 2) }}
                            </div>
                        </button>
                    @endforeach
                @endif

                @if($activeCategory === 'all' || $activeCategory === 'products')
                    @php
                        $productsQ = \App\Models\Product::where('is_active', true);
                        if (!empty($searchQuery)) {
                            $productsQ->where('name', 'like', "%{$searchQuery}%");
                        }
                    @endphp
                    @foreach($productsQ->limit(18)->get() as $prd)
                        <button
                            type="button"
                            wire:click="addToCart('product', {{ $prd->id }}, '{{ addslashes($prd->name) }}', {{ $prd->selling_price }})"
                            class="text-left rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-3 shadow-sm hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow transition group flex flex-col justify-between"
                        >
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-wider text-indigo-600 dark:text-indigo-400">Retail • {{ $prd->brand }}</span>
                                <h5 class="font-bold text-xs text-gray-900 dark:text-white mt-1 group-hover:text-indigo-600 line-clamp-2">{{ $prd->name }}</h5>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="font-extrabold text-sm text-gray-900 dark:text-white">${{ number_format($prd->selling_price, 2) }}</span>
                                <span class="text-[11px] text-gray-500">Stock: {{ $prd->stock_quantity }}</span>
                            </div>
                        </button>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Right: Cart & Checkout Summary --}}
        <div class="lg:col-span-5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                <h3 class="font-bold text-base text-gray-900 dark:text-white">Current Order</h3>
                <span class="text-xs bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-bold px-2 py-0.5 rounded-full">
                    {{ count($cart) }} Items
                </span>
            </div>

            {{-- Cart Item List --}}
            <div class="space-y-2 max-h-[260px] overflow-y-auto pr-1">
                @forelse($cart as $key => $item)
                    <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800 text-xs">
                        <div class="flex-1 pr-2">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                            <span class="text-[11px] text-gray-500">${{ number_format($item['price'], 2) }} each</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded">
                                <button type="button" wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" class="px-2 py-0.5 hover:bg-gray-200 dark:hover:bg-gray-700">-</button>
                                <span class="px-2 font-bold">{{ $item['quantity'] }}</span>
                                <button type="button" wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" class="px-2 py-0.5 hover:bg-gray-200 dark:hover:bg-gray-700">+</button>
                            </div>
                            <span class="font-bold w-14 text-right">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                            <button type="button" wire:click="removeItem('{{ $key }}')" class="text-red-500 hover:text-red-700 ml-1">✕</button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-gray-400 text-xs">
                        Cart is empty. Tap items on the left to add.
                    </div>
                @endforelse
            </div>

            {{-- Discount, Tip & Payment Method --}}
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-2 text-xs">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-600 dark:text-gray-400 mb-1">Discount ($)</label>
                        <input type="number" step="0.5" wire:model.live="discount" class="w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-2 py-1" />
                    </div>
                    <div>
                        <label class="block text-gray-600 dark:text-gray-400 mb-1">Tip ($)</label>
                        <input type="number" step="1" wire:model.live="tip" class="w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-2 py-1" />
                    </div>
                </div>

                <div>
                    <label class="block text-gray-600 dark:text-gray-400 mb-1">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="$set('paymentMethod', 'card')" class="p-2 text-center rounded border text-xs font-semibold {{ $paymentMethod === 'card' ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-300 font-bold' : 'border-gray-200 dark:border-gray-700' }}">Card</button>
                        <button type="button" wire:click="$set('paymentMethod', 'cash')" class="p-2 text-center rounded border text-xs font-semibold {{ $paymentMethod === 'cash' ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-300 font-bold' : 'border-gray-200 dark:border-gray-700' }}">Cash</button>
                        <button type="button" wire:click="$set('paymentMethod', 'digital')" class="p-2 text-center rounded border text-xs font-semibold {{ $paymentMethod === 'digital' ? 'border-rose-500 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-300 font-bold' : 'border-gray-200 dark:border-gray-700' }}">Digital</button>
                    </div>
                </div>
            </div>

            {{-- Financial Summary --}}
            <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-1.5 text-xs">
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    <span>${{ number_format($this->subtotal, 2) }}</span>
                </div>
                @if($discount > 0)
                <div class="flex justify-between text-red-600">
                    <span>Discount:</span>
                    <span>-${{ number_format($discount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Tax (8.5%):</span>
                    <span>+${{ number_format($this->taxAmount, 2) }}</span>
                </div>
                @if($tip > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>Tip:</span>
                    <span>+${{ number_format($tip, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between font-extrabold text-base text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                    <span>Total Due:</span>
                    <span class="text-rose-600 dark:text-rose-400">${{ number_format($this->total, 2) }}</span>
                </div>
            </div>

            <button
                type="button"
                wire:click="processCheckout"
                class="w-full py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-md transition flex items-center justify-center gap-2"
            >
                <span>Charge ${{ number_format($this->total, 2) }}</span>
            </button>
        </div>
    </div>
</x-filament-panels::page>
