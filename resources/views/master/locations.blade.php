<x-app-layout>
    <x-slot name="title">Daftar Bin Lokasi (Master Locations)</x-slot>

    <div x-data="{ 
        createOpen: false, 
        editOpen: false, 
        editLoc: { id: '', bin_code: '', zone: '' },
        openEdit(loc) {
            this.editLoc = { ...loc };
            this.editOpen = true;
        }
    }" class="flex flex-col gap-6">

        <!-- Top Header & Add Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Master Bin Lokasi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola titik koordinat penyimpanan (Bin) di dalam area gudang</p>
            </div>
            <button @click="createOpen = true" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/10 transition-colors self-start sm:self-auto">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Tambah Bin Lokasi
            </button>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <form action="{{ route('master.locations.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pencarian Kode Bin</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="Cari berdasarkan kode bin (contoh: ZONE-A-S1-R1)...">
                    </div>
                </div>

                <div class="w-full md:w-64">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter Zona</label>
                    <select name="zone" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Semua Zona</option>
                        @foreach($zones as $z)
                            <option value="{{ $z }}" {{ $zone == $z ? 'selected' : '' }}>{{ $z }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        Filter
                    </button>
                    @if($search || $zone)
                        <a href="{{ route('master.locations.index') }}" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4">Kode Bin Lokasi</th>
                            <th class="px-6 py-4">Zona / Area</th>
                            <th class="px-6 py-4">Kapasitas Penggunaan</th>
                            <th class="px-6 py-4">Tanggal Dibuat</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($locations as $loc)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded bg-slate-100 border border-slate-200 text-slate-600">
                                            <i data-lucide="map-pin" class="h-4 w-4"></i>
                                        </div>
                                        <span class="font-bold text-slate-900">{{ $loc->bin_code }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-medium">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 border border-blue-100">
                                        {{ $loc->zone }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $uniqueProducts = $loc->stocks()->where('qty', '>', 0)->distinct('item_id')->count();
                                        $totalQty = $loc->stocks()->sum('qty');
                                    @endphp
                                    @if($totalQty > 0)
                                        <div class="text-xs font-medium text-slate-900">{{ $uniqueProducts }} Jenis Barang</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Total Qty: {{ number_format($totalQty) }}</div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Kosong</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $loc->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Edit trigger -->
                                        <button @click="openEdit({{ json_encode($loc) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Lokasi">
                                            <i data-lucide="edit-3" class="h-4.5 w-4.5"></i>
                                        </button>
                                        
                                        <!-- Delete form -->
                                        <form action="{{ route('master.locations.destroy', $loc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi bin ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus Lokasi">
                                                <i data-lucide="trash-2" class="h-4.5 w-4.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="map" class="h-10 w-10 text-slate-300"></i>
                                        <span class="text-sm">Tidak ada data lokasi bin yang ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination links -->
            @if($locations->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $locations->links() }}
                </div>
            @endif
        </div>

        <!-- CREATE MODAL -->
        <div x-show="createOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
            <div @click.away="createOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900">Tambah Bin Lokasi Baru</h3>
                    <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
                
                <form action="{{ route('master.locations.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Zona / Area Gudang</label>
                        <input type="text" name="zone" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500" placeholder="Contoh: ZONE-A, ZONE-B">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kode Bin Lokasi (Unik)</label>
                        <input type="text" name="bin_code" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500" placeholder="Contoh: ZONE-A-S1-R1">
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="createOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/10 transition-colors">
                            Simpan Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="editOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
            <div @click.away="editOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900">Ubah Data Bin Lokasi</h3>
                    <button @click="editOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
                
                <form :action="'{{ url('master/locations') }}/' + editLoc.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Zona / Area Gudang</label>
                        <input type="text" name="zone" x-model="editLoc.zone" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kode Bin Lokasi (Unik)</label>
                        <input type="text" name="bin_code" x-model="editLoc.bin_code" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="editOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/10 transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
