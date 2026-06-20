<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart WMS</title>
    
    <!-- Theme Detection Script (Prevents FOUC) -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    <!-- Tailwind & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- AlpineJS for UI interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full font-sans antialiased text-[#495057] dark:text-[#c1c2c5] bg-[#f8f9fa] dark:bg-[#101113] flex flex-col items-center justify-center px-6 relative transition-colors duration-200">
    
    <!-- Theme Toggle Button in corner -->
    <div class="absolute top-6 right-6">
        <button onclick="toggleTheme()" class="p-2.5 text-[#868e96] hover:text-[#212529] dark:hover:text-white hover:bg-white dark:hover:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg shadow-sm transition-all" title="Toggle Theme">
            <i data-lucide="sun" class="h-5 w-5 hidden dark:block"></i>
            <i data-lucide="moon" class="h-5 w-5 block dark:hidden"></i>
        </button>
    </div>

    <div class="w-full max-w-md" x-data="{ email: '', password: '' }">
        <!-- Brand Header matching template style -->
        <div class="flex flex-col items-center justify-center mb-8">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-3xl font-black tracking-widest text-[#1c7ed6] dark:text-[#4dabf7]" style="font-family: 'Instrument Sans', sans-serif;">SMART WMS</span>
                <span class="h-2.5 w-2.5 rounded-full bg-[#1c7ed6] dark:bg-[#4dabf7] mt-1.5 animate-pulse"></span>
            </div>
            <p class="text-xs text-[#868e96] dark:text-[#5c5f66] font-medium uppercase tracking-widest">Warehouse & Inventory Management</p>
        </div>

        <!-- Clean Card matching template -->
        <div class="bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-8 shadow-sm transition-colors">
            
            <!-- Notification states -->
            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2 shadow-sm">
                    <i data-lucide="check-circle" class="h-4 w-4 shrink-0 text-emerald-600"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-lg bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-300 text-xs font-semibold flex items-center gap-2 shadow-sm">
                    <i data-lucide="alert-circle" class="h-4 w-4 shrink-0 text-rose-600"></i>
                    <span>Email atau password salah. Silakan coba lagi.</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-[10px] font-bold text-[#868e96] dark:text-[#5c5f66] uppercase tracking-wider mb-2">Alamat Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#adb5bd] dark:text-[#5c5f66]">
                            <i data-lucide="mail" class="h-4 w-4"></i>
                        </span>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               required 
                               x-model="email"
                               class="w-full bg-[#f8f9fa] dark:bg-[#25262b] border border-[#e9ecef] dark:border-[#373a40] rounded-lg py-2.5 pl-10 pr-4 text-xs text-[#212529] dark:text-white placeholder-[#adb5bd] focus:outline-none focus:border-[#1c7ed6] focus:bg-white dark:focus:bg-[#1a1b1e] transition-all" 
                               placeholder="nama@perusahaan.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-bold text-[#868e96] dark:text-[#5c5f66] uppercase tracking-wider mb-2">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#adb5bd] dark:text-[#5c5f66]">
                            <i data-lucide="lock" class="h-4 w-4"></i>
                        </span>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               x-model="password"
                               class="w-full bg-[#f8f9fa] dark:bg-[#25262b] border border-[#e9ecef] dark:border-[#373a40] rounded-lg py-2.5 pl-10 pr-4 text-xs text-[#212529] dark:text-white placeholder-[#adb5bd] focus:outline-none focus:border-[#1c7ed6] focus:bg-white dark:focus:bg-[#1a1b1e] transition-all" 
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-[#868e96] dark:text-[#5c5f66] select-none cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-[#e9ecef] dark:border-[#373a40] bg-[#f8f9fa] dark:bg-[#25262b] text-[#1c7ed6] focus:ring-0 focus:ring-offset-0">
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-[#1c7ed6] dark:bg-[#1971c2] hover:bg-[#1971c2] dark:hover:bg-[#1864ab] text-white font-semibold py-2.5 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5 text-xs">
                    Masuk Ke Workspace
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </button>
            </form>

            <!-- Pre-seeded accounts quick-fill helper -->
            <div class="mt-8 border-t border-[#f1f3f5] dark:border-[#2c2e33] pt-6">
                <p class="text-[10px] font-bold text-[#868e96] dark:text-[#5c5f66] uppercase tracking-widest mb-3 text-center">Akun Demo Cepat</p>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="email='admin@wms.com'; password='password'" 
                            class="flex flex-col items-center justify-center p-2.5 rounded-lg border border-[#e9ecef] dark:border-[#2c2e33] hover:border-[#1c7ed6] dark:hover:border-[#4dabf7] bg-[#f8f9fa] dark:bg-[#25262b] hover:bg-white dark:hover:bg-[#1a1b1e]/50 transition-colors group">
                        <span class="text-xs font-bold text-[#495057] dark:text-[#c1c2c5] group-hover:text-[#1c7ed6] dark:group-hover:text-[#4dabf7] transition-colors">Admin Demo</span>
                        <span class="text-[9px] text-[#adb5bd] dark:text-[#5c5f66] mt-0.5">Full Access</span>
                    </button>
                    <button @click="email='operator@wms.com'; password='password'" 
                            class="flex flex-col items-center justify-center p-2.5 rounded-lg border border-[#e9ecef] dark:border-[#2c2e33] hover:border-[#1c7ed6] dark:hover:border-[#4dabf7] bg-[#f8f9fa] dark:bg-[#25262b] hover:bg-white dark:hover:bg-[#1a1b1e]/50 transition-colors group">
                        <span class="text-xs font-bold text-[#495057] dark:text-[#c1c2c5] group-hover:text-[#1c7ed6] dark:group-hover:text-[#4dabf7] transition-colors">Operator Demo</span>
                        <span class="text-[9px] text-[#adb5bd] dark:text-[#5c5f66] mt-0.5">Staff Operasional</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Theme Toggling and Lucide script -->
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
