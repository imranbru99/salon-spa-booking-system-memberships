<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Salon & Spa SaaS — Business Management Platform')</title>
    <meta name="description" content="@yield('meta_description', 'The premier all-in-one business management SaaS for salons, luxury spas, barbershops, and wellness chains.')">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|playfair-display:400,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            500: '#ec4899',
                            700: '#be185d',
                            800: '#9d174d',
                            900: '#831843',
                            950: '#500724',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-stone-50 text-stone-900 antialiased selection:bg-rose-500 selection:text-white">
    {{-- Top Demo Bar --}}
    <div class="bg-gradient-to-r from-stone-900 via-rose-950 to-stone-900 text-stone-200 text-xs py-2 px-4 shadow-sm border-b border-rose-900/40">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">DEMO MODE ACTIVE</span>
                <span class="hidden sm:inline text-stone-300">Glow & Grace Salon and Spa (Beverly Hills & Malibu)</span>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="text-stone-400">Quick Access:</span>
                <a href="/admin" class="hover:text-white underline decoration-rose-500 underline-offset-2">Platform Admin</a>
                <a href="/app" class="hover:text-white underline decoration-rose-500 underline-offset-2">Salon Owner</a>
                <a href="/reception" class="hover:text-white underline decoration-rose-500 underline-offset-2">Reception</a>
                <a href="/staff" class="hover:text-white underline decoration-rose-500 underline-offset-2">Staff Portal</a>
                <a href="/portal" class="hover:text-white underline decoration-rose-500 underline-offset-2">Client Portal</a>
            </div>
        </div>
    </div>

    {{-- Main Navbar --}}
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-stone-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                {{-- Logo --}}
                <div class="flex items-center gap-3">
                    <a href="/" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-900 via-rose-700 to-rose-500 flex items-center justify-center text-white font-serif font-bold text-xl shadow-md group-hover:scale-105 transition">
                            G
                        </div>
                        <div>
                            <span class="font-serif font-bold text-xl tracking-wide text-stone-900 group-hover:text-brand-900 transition">GLOW & GRACE</span>
                            <span class="block text-[10px] font-medium tracking-widest uppercase text-stone-500">Salon & Spa Platform</span>
                        </div>
                    </a>
                </div>

                {{-- Navigation links --}}
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-stone-700">
                    <a href="/" class="hover:text-brand-900 transition">SaaS Platform</a>
                    <a href="/features" class="hover:text-brand-900 transition">Features</a>
                    <a href="/pricing" class="hover:text-brand-900 transition">Pricing Plans</a>
                    <a href="/salon" class="hover:text-brand-900 transition">Live Salon</a>
                    <a href="/salon/services" class="hover:text-brand-900 transition">Services Menu</a>
                    <a href="/salon/booking" class="text-brand-700 font-semibold hover:text-brand-900 transition">Book Appointment</a>
                </nav>

                {{-- Right CTAs --}}
                <div class="flex items-center gap-3">
                    <a href="/app/login" class="text-xs font-semibold px-4 py-2 text-stone-700 hover:text-brand-900 transition">
                        Sign In
                    </a>
                    <a href="/salon/booking" class="text-xs font-bold px-5 py-2.5 rounded-full bg-gradient-to-r from-brand-900 to-brand-700 text-white shadow-sm hover:shadow hover:brightness-110 transition">
                        Book Online
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-stone-900 text-stone-300 pt-16 pb-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-stone-800">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-rose-700 text-white flex items-center justify-center font-serif font-bold text-lg">G</div>
                        <span class="font-serif font-bold text-lg tracking-wide text-white">GLOW & GRACE</span>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed">
                        Complete enterprise business management platform engineered for luxury beauty salons, day spas, barbershops, and international wellness chains.
                    </p>
                    <div class="text-xs text-stone-400">
                        Official Developer: <a href="https://imrandev.bd" target="_blank" class="text-rose-400 hover:underline">Imran Ahmed</a>
                    </div>
                </div>

                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Core Modules</h5>
                    <ul class="space-y-2 text-xs text-stone-400">
                        <li><a href="/features#booking" class="hover:text-white transition">Online Appointment Engine</a></li>
                        <li><a href="/features#pos" class="hover:text-white transition">Point of Sale (POS)</a></li>
                        <li><a href="/features#packages" class="hover:text-white transition">Prepaid Service Packages</a></li>
                        <li><a href="/features#memberships" class="hover:text-white transition">Recurring Memberships</a></li>
                        <li><a href="/features#commissions" class="hover:text-white transition">Staff Commissions & Payroll</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Interactive Demos</h5>
                    <ul class="space-y-2 text-xs text-stone-400">
                        <li><a href="/admin" class="hover:text-white transition">Platform Super Admin Panel</a></li>
                        <li><a href="/app" class="hover:text-white transition">Business Owner Dashboard</a></li>
                        <li><a href="/reception" class="hover:text-white transition">Front Desk Reception Terminal</a></li>
                        <li><a href="/staff" class="hover:text-white transition">Staff & Stylist Portal</a></li>
                        <li><a href="/portal" class="hover:text-white transition">Client Self-Service Portal</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-white mb-4">Demo Credentials</h5>
                    <p class="text-xs text-stone-400 mb-2">Default Password for all accounts:</p>
                    <code class="block bg-stone-800 p-2 rounded text-rose-300 font-mono text-xs mb-3">Password123!</code>
                    <p class="text-[11px] text-stone-500">
                        Configured with realistic multi-branch operations, 30 services, 15 staff members, and real transaction ledgers.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-between text-xs text-stone-500">
                <p>&copy; {{ date('Y') }} Salon & Spa Business Management SaaS. Built by <a href="https://imrandev.bd" target="_blank" class="text-stone-400 hover:underline">Imran Ahmed</a>.</p>
                <div class="flex gap-6 mt-4 sm:mt-0">
                    <a href="/terms" class="hover:text-stone-300">Terms of Service</a>
                    <a href="/privacy" class="hover:text-stone-300">Privacy Policy</a>
                    <a href="/docs" class="hover:text-stone-300">API Documentation</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
