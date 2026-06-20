<x-app-layout>
    <x-slot name="title">Audit Trail & Log Transaksi</x-slot>

    <div class="flex flex-col gap-6">

        <!-- Top Header -->
        <div>
            <h2 class="text-xl font-bold text-slate-900">Audit Trail (Log Pergudangan)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Riwayat lengkap mutasi barang, penerimaan, dan pengeluaran secara kronologis</p>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <form action="{{ route('logs.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pencarian Kode Transaksi / SKU / Nama</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="Cari berdasarkan kode TRX, SKU barang, nama...">
                    </div>
                </div>

                <div class="w-full md:w-44">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter Jenis</label>
                    <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Semua Jenis</option>
                        <option value="goods_in" {{ $type === 'goods_in' ? 'selected' : '' }}>Masuk (Goods In)</option>
                        <option value="goods_out" {{ $type === 'goods_out' ? 'selected' : '' }}>Keluar (Goods Out)</option>
                        <option value="mutation" {{ $type === 'mutation' ? 'selected' : '' }}>Mutasi (Mutation)</option>
                    </select>
                </div>

                <div class="w-full md:w-36">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Mulai Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-750 focus:outline-none focus:border-blue-500">
                </div>

                <div class="w-full md:w-36">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-750 focus:outline-none focus:border-blue-500">
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        Filter
                    </button>
                    @if($search || $type || $startDate || $endDate)
                        <a href="{{ route('logs.index') }}" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Log Table Card -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Kode TRX / Tanggal</th>
                            <th class="px-6 py-4">Jenis</th>
                            <th class="px-6 py-4">Item & SKU</th>
                            <th class="px-6 py-4 text-center">Batch No.</th>
                            <th class="px-6 py-4 text-right">Jumlah</th>
                            <th class="px-6 py-4">Alur Lokasi (Bin)</th>
                            <th class="px-6 py-4">Rincian Perubahan Stok (Audit)</th>
                            <th class="px-6 py-4">Operator</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-950">{{ $log->transaction_code }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $log->transaction_date->format('d M Y H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->type === 'goods_in')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-100">
                                            Masuk
                                        </span>
                                    @elseif($log->type === 'goods_out')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-800 border border-violet-100">
                                            Keluar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800 border border-blue-100">
                                            Mutasi
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900 max-w-[180px] truncate">{{ $log->item->name }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $log->item->sku }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-medium text-slate-700">
                                    {{ $log->batch_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-slate-950">
                                    @if($log->type === 'goods_in') +@elseif($log->type === 'goods_out')-@endif{{ number_format($log->qty) }} 
                                    <span class="text-xs font-normal text-slate-400 capitalize">{{ $log->item->unit }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    @if($log->type === 'goods_in')
                                        <span class="text-slate-400">&mdash;</span> &rarr; <span class="font-semibold text-slate-800">{{ $log->destinationLocation->bin_code }}</span>
                                    @elseif($log->type === 'goods_out')
                                        <span class="font-semibold text-slate-800">{{ $log->originLocation->bin_code }}</span> &rarr; <span class="text-slate-400">&mdash;</span>
                                    @else
                                        <span class="font-semibold text-slate-850">{{ $log->originLocation->bin_code }}</span> &rarr; <span class="font-semibold text-slate-850">{{ $log->destinationLocation->bin_code }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @forelse($log->inventoryHistories as $history)
                                        <div class="flex items-center gap-1.5 text-slate-700 font-medium my-0.5">
                                            <span class="text-slate-400 font-semibold">{{ $history->location->bin_code }}:</span>
                                            <span>{{ number_format($history->qty_before) }}</span>
                                            <span class="{{ $history->qty_change >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold">
                                                ({{ $history->qty_change >= 0 ? '+' : '' }}{{ number_format($history->qty_change) }})
                                            </span>
                                            <span>&rarr;</span>
                                            <span class="font-bold text-slate-900">{{ number_format($history->qty_after) }}</span>
                                        </div>
                                    @empty
                                        <span class="text-slate-400 italic">No balance logs</span>
                                    @endforelse
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-slate-700">
                                    {{ $log->user->name }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="clipboard-list" class="h-10 w-10 text-slate-300"></i>
                                        <span class="text-sm">Tidak ada transaksi terekam</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
