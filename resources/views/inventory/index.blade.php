<x-app-layout>
    <x-slot name="title">Monitor Stok & Karantina</x-slot>

    <div class="flex flex-col gap-6">

        <!-- Top Header & Export Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Monitor Stok Pergudangan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau persebaran stok barang, tanggal kedaluwarsa, dan status karantina</p>
            </div>
            <a href="{{ route('inventory.export') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/10 transition-colors self-start sm:self-auto">
                <i data-lucide="file-spreadsheet" class="h-4.5 w-4.5"></i>
                Ekspor Stok (.xlsx)
            </a>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <form action="{{ route('inventory.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pencarian Barang / SKU / Batch</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="Cari berdasarkan SKU, nama barang, nomor batch...">
                    </div>
                </div>

                <div class="w-full md:w-48">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter Zona</label>
                    <select name="zone" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Semua Zona</option>
                        @foreach($zones as $z)
                            <option value="{{ $z }}" {{ $zone == $z ? 'selected' : '' }}>{{ $z }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-48">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter Status</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Semua Status</option>
                        <option value="available" {{ $status == 'available' ? 'selected' : '' }}>Tersedia (Available)</option>
                        <option value="quarantined" {{ $status == 'quarantined' ? 'selected' : '' }}>Dikarantina (Quarantined)</option>
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        Filter
                    </button>
                    @if($search || $zone || $status)
                        <a href="{{ route('inventory.index') }}" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Stock Table -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Item & SKU</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4">Lokasi Bin (Zona)</th>
                            <th class="px-6 py-4">Batch No.</th>
                            <th class="px-6 py-4">Kedaluwarsa</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Jumlah Stok</th>
                            @if(auth()->user()->isAdmin())
                                <th class="px-6 py-4 text-center">Tindakan</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($stocks as $stock)
                            @php
                                $isNearExpired = $stock->expired_at && $stock->expired_at->isBetween(Carbon\Carbon::now(), Carbon\Carbon::now()->addDays(30));
                                $isExpired = $stock->expired_at && $stock->expired_at->isBefore(Carbon\Carbon::now());
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $stock->status === 'quarantined' ? 'bg-red-50/30' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">{{ $stock->item->name }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $stock->item->sku }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 font-medium text-slate-800 border border-slate-200">
                                        {{ $stock->item->category->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-850">{{ $stock->location->bin_code }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $stock->location->zone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-xs text-slate-700">
                                    {{ $stock->batch_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($stock->expired_at)
                                        @if($isExpired)
                                            <span class="inline-flex items-center gap-1 rounded bg-red-100 px-2 py-0.5 font-semibold text-red-800 border border-red-200">
                                                Expired ({{ $stock->expired_at->format('d/m/Y') }})
                                            </span>
                                        @elseif($isNearExpired)
                                            <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-2 py-0.5 font-semibold text-amber-800 border border-amber-200 animate-pulse">
                                                Mendekati ({{ $stock->expired_at->format('d/m/Y') }})
                                            </span>
                                        @else
                                            <span class="text-slate-600 font-medium">{{ $stock->expired_at->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($stock->status === 'available')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-100">
                                            Tersedia
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-800 border border-red-100">
                                            Dikarantina
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-slate-900">
                                    {{ number_format($stock->qty) }} <span class="text-xs font-normal text-slate-400 capitalize">{{ $stock->item->unit }}</span>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-xs">
                                        @if($stock->status === 'available')
                                            <form action="{{ route('inventory.quarantine', $stock->id) }}" method="POST" onsubmit="return confirm('Karantina batch ini? Stok tidak akan bisa ditarik keluar/mutasi.');" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-semibold transition-colors flex items-center gap-1 mx-auto">
                                                    <i data-lucide="shield-alert" class="h-3.5 w-3.5"></i>
                                                    Karantina
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('inventory.release', $stock->id) }}" method="POST" onsubmit="return confirm('Lepaskan karantina batch ini? Stok kembali dapat digunakan.');" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50 font-semibold transition-colors flex items-center gap-1 mx-auto">
                                                    <i data-lucide="shield-check" class="h-3.5 w-3.5"></i>
                                                    Bebaskan
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="inbox" class="h-10 w-10 text-slate-300"></i>
                                        <span class="text-sm">Tidak ada stok yang terdaftar</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($stocks->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $stocks->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
