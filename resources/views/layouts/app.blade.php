<!DOCTYPE html>
<html lang="id" class="h-full bg-[#f8f9fa]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Smart WMS' }} - Warehouse & Inventory Management</title>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="h-full font-sans antialiased text-[#495057] dark:text-[#c1c2c5] bg-[#f8f9fa] dark:bg-[#101113]" x-data="{ sidebarOpen: false }">

    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-[#212529]/40 dark:bg-black/60 lg:hidden"
         @click="sidebarOpen = false"></div>

    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
         class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-white dark:bg-[#1a1b1e] border-r border-[#e9ecef] dark:border-[#2c2e33] transition-transform duration-300 ease-in-out lg:z-30">

        <div class="flex h-16 shrink-0 items-center justify-between px-6 border-b border-[#f1f3f5] dark:border-[#2c2e33]">
            <div class="flex items-center gap-2">
                <span class="text-xl font-black tracking-widest text-[#1c7ed6] dark:text-[#4dabf7] select-none" style="font-family: 'Instrument Sans', sans-serif;">SMART WMS</span>
                <span class="h-2 w-2 rounded-full bg-[#1c7ed6] dark:bg-[#4dabf7] mt-1.5 animate-pulse"></span>
            </div>

            <button @click="sidebarOpen = false" class="lg:hidden text-[#868e96] dark:text-[#5c5f66] hover:text-[#212529] dark:hover:text-white p-1 rounded-lg">
                <i data-lucide="x" class="h-6 w-6"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto space-y-1.5 px-4 py-6">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                Dashboard
            </a>

            <div class="px-4 pt-4 pb-2 text-[10px] font-bold uppercase tracking-widest text-[#adb5bd] dark:text-[#5c5f66]">Warehouse Operations</div>
            <a href="{{ route('operations.goods-in') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('operations.goods-in*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="log-in" class="h-5 w-5"></i>
                Barang Masuk (Goods In)
            </a>
            <a href="{{ route('operations.goods-out') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('operations.goods-out*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="log-out" class="h-5 w-5"></i>
                Barang Keluar (Goods Out)
            </a>
            <a href="{{ route('operations.mutation') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('operations.mutation*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="git-compare" class="h-5 w-5"></i>
                Mutasi Lokasi (Transfer)
            </a>

            <div class="px-4 pt-4 pb-2 text-[10px] font-bold uppercase tracking-widest text-[#adb5bd] dark:text-[#5c5f66]">Inventory & Monitoring</div>
            <a href="{{ route('inventory.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('inventory.index*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="monitor" class="h-5 w-5"></i>
                Monitor Stok & Karantina
            </a>
            <a href="{{ route('logs.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('logs.index*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="history" class="h-5 w-5"></i>
                Log Transaksi (Audit Trail)
            </a>
            <a href="{{ route('ai.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('ai.index*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="brain-circuit" class="h-5 w-5"></i>
                Analisis AI (FastAPI)
            </a>

            @if(auth()->user()->isAdmin())
            <div class="px-4 pt-4 pb-2 text-[10px] font-bold uppercase tracking-widest text-[#adb5bd] dark:text-[#5c5f66]">Master Data (Admin)</div>
            <a href="{{ route('master.items.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('master.items*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="box" class="h-5 w-5"></i>
                Item / Barang
            </a>
            <a href="{{ route('master.locations.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('master.locations*') ? 'bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7]' : 'text-[#495057] dark:text-[#c1c2c5] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white' }}">
                <i data-lucide="map-pin" class="h-5 w-5"></i>
                Bin Lokasi
            </a>
            @endif

            <div class="border-t border-[#f1f3f5] dark:border-[#2c2e33] my-4 pt-4"></div>
            <a href="#" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-[#868e96] dark:text-[#5c5f66] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white transition-colors">
                <i data-lucide="help-circle" class="h-5 w-5"></i>
                Documentation
            </a>
            <a href="#" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-[#868e96] dark:text-[#5c5f66] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] hover:text-[#212529] dark:hover:text-white transition-colors">
                <span class="flex items-center gap-3">
                    <i data-lucide="file-text" class="h-5 w-5"></i>
                    Changelog
                </span>
                <span class="bg-[#ff8787] text-white text-[10px] font-bold px-2 py-0.5 rounded-md">1.0</span>
            </a>
        </nav>

        <div class="p-4 border-t border-[#f1f3f5] dark:border-[#2c2e33] bg-[#f8f9fa]/80 dark:bg-[#1a1b1e]/80">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7] font-bold border border-blue-200 dark:border-[#102a45]">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs font-semibold text-[#212529] dark:text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-[#868e96] dark:text-[#5c5f66] capitalize">{{ auth()->user()->role }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-50 dark:bg-red-950/20 hover:bg-red-100 dark:hover:bg-red-950/50 border border-red-200/50 dark:border-red-900/30 py-2 text-xs font-semibold text-red-600 dark:text-red-400 transition-colors">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    Keluar Sistem
                </button>
            </form>
        </div>
    </div>

    <div class="lg:pl-72 flex flex-col min-h-screen bg-[#f8f9fa] dark:bg-[#101113]">

        <header class="flex h-16 items-center justify-between border-b border-[#e9ecef] dark:border-[#2c2e33] bg-white dark:bg-[#1a1b1e] px-6 sticky top-0 z-20">
            <div class="flex items-center gap-4 flex-1">

                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-[#868e96] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b]">
                    <i data-lucide="menu" class="h-6 w-6"></i>
                </button>

                <div class="flex items-center gap-2 hidden sm:flex">
                    <button class="p-2 text-[#868e96] dark:text-[#5c5f66] hover:text-[#212529] dark:hover:text-white hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] rounded-lg transition-colors">
                        <i data-lucide="menu" class="h-5 w-5"></i>
                    </button>
                    <button onclick="document.documentElement.requestFullscreen()" class="p-2 text-[#868e96] dark:text-[#5c5f66] hover:text-[#212529] dark:hover:text-white hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] rounded-lg transition-colors">
                        <i data-lucide="maximize" class="h-5 w-5"></i>
                    </button>
                </div>

                <div class="relative w-64 max-w-xs ml-2">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#adb5bd] dark:text-[#5c5f66]">
                        <i data-lucide="search" class="h-4 w-4"></i>
                    </span>
                    <input type="text" placeholder="Search..." class="w-full bg-[#f8f9fa] dark:bg-[#25262b] border border-[#e9ecef] dark:border-[#373a40] rounded-lg pl-9 pr-4 py-1.5 text-xs text-[#495057] dark:text-[#c1c2c5] placeholder-[#adb5bd] focus:outline-none focus:border-[#1c7ed6] focus:bg-white dark:focus:bg-[#1a1b1e] transition-all" />
                </div>
            </div>

            <div class="flex items-center gap-3">

                <button onclick="toggleTheme()" class="p-2 text-[#868e96] hover:text-[#212529] dark:hover:text-white hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] rounded-lg transition-colors" title="Toggle Dark/Light Mode">
                    <i data-lucide="sun" class="h-5 w-5 hidden dark:block"></i>
                    <i data-lucide="moon" class="h-5 w-5 block dark:hidden"></i>
                </button>

                <button class="p-2 text-[#868e96] hover:text-[#212529] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] rounded-lg relative transition-colors">
                    <i data-lucide="mail" class="h-5 w-5"></i>
                </button>

                <button class="p-2 text-[#868e96] hover:text-[#212529] hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] rounded-lg relative transition-colors">
                    <i data-lucide="bell" class="h-5 w-5"></i>
                    @if(($nearExpiredCount ?? 0) > 0 || ($quarantinedCount ?? 0) > 0)
                        <span class="absolute top-1.5 right-1.5 h-2.5 w-2.5 rounded-full bg-[#f59f00] border-2 border-white dark:border-[#1a1b1e]"></span>
                    @endif
                </button>

                <div class="flex items-center gap-2 pl-3 border-l border-[#e9ecef] dark:border-[#2c2e33]">
                    <div class="h-8 w-8 rounded-full bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#4dabf7] flex items-center justify-center font-bold text-xs select-none border border-[#d0ebff] dark:border-[#102a45]">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <span class="text-xs font-semibold text-[#495057] dark:text-[#c1c2c5] hidden md:inline">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-8 bg-[#f8f9fa] dark:bg-[#101113]">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 flex items-center justify-between p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 text-emerald-800 dark:text-emerald-300 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-800">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 flex items-center justify-between p-4 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30 text-rose-800 dark:text-rose-300 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-triangle" class="h-5 w-5 text-rose-600"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-800">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30 text-rose-800 dark:text-rose-300 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-rose-600"></i>
                            <span class="text-sm font-semibold">Terdapat kesalahan input:</span>
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-800">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 pl-8">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="bg-white dark:bg-[#1a1b1e] border-t border-[#e9ecef] dark:border-[#2c2e33] py-4 px-6 text-center text-xs text-[#adb5bd] dark:text-[#5c5f66]">
            &copy; 2026 Smart Warehouse & Inventory Management System (Smart WMS). All rights reserved.
        </footer>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
        });

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</body>
</html>
