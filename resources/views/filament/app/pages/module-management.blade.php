<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-5 dark:border-rose-900/50 dark:bg-rose-950/20">
            <h3 class="text-base font-semibold text-rose-950 dark:text-rose-200">Dynamic Modular Architecture</h3>
            <p class="mt-1 text-sm text-rose-800/80 dark:text-rose-300/80">
                Activate or deactivate business modules according to your operational model. Deactivating a module hides its menus, blocks direct routes and API access, and preserves all underlying historical data. Modules with active dependencies cannot be deactivated until dependent features are toggled off.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($modules as $key => $mod)
                <div class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="font-bold text-gray-950 dark:text-white">{{ $mod['name'] }}</h4>
                                <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full font-medium {{ ($mod['is_core'] ?? false) ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ ucfirst($mod['category']) }}
                                </span>
                            </div>
                            @if($mod['is_core'] ?? false)
                                <span class="text-xs font-semibold px-2 py-1 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Core</span>
                            @else
                                <span class="text-xs font-semibold px-2 py-1 rounded {{ $mod['is_enabled'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                    {{ $mod['is_enabled'] ? 'Active' : 'Disabled' }}
                                </span>
                            @endif
                        </div>

                        <p class="mt-3 text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ $mod['description'] }}
                        </p>

                        @if(!empty($mod['dependencies']))
                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-semibold">Requires:</span>
                                @foreach($mod['dependencies'] as $dep)
                                    <span class="inline-block bg-gray-50 dark:bg-gray-800 px-1.5 py-0.5 rounded text-[11px] font-mono border border-gray-200 dark:border-gray-700 mr-1">
                                        {{ $dep }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                        @if($mod['is_core'] ?? false)
                            <button disabled class="text-xs text-gray-400 dark:text-gray-600 font-medium cursor-not-allowed">
                                Permanently Enabled
                            </button>
                        @else
                            <button
                                wire:click="toggleModule('{{ $key }}')"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $mod['is_enabled'] ? 'bg-rose-50 text-rose-700 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300' : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm' }}"
                            >
                                @if($mod['is_enabled'])
                                    <span>Deactivate Module</span>
                                @else
                                    <span>Activate Module</span>
                                @endif
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
