<x-app-layout>
    <x-slot name="title">Daftar Barang (Master Items)</x-slot>

    <div x-data="{ 
        createOpen: false, 
        editOpen: false, 
        editItem: { id: '', category_id: '', sku: '', name: '', description: '', unit: '', initial_stock: 0 },
        openEdit(item) {
            this.editItem = { ...item };
            this.editOpen = true;
        }
    }" class="flex flex-col gap-6">

        <!-- Top Header & Add Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Master Barang</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data produk/barang yang tersimpan di dalam sistem</p>
            </div>
            <button @click="createOpen = true" class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/10 transition-colors self-start sm:self-auto">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Tambah Barang
            </button>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <form action="{{ route('master.items.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pencarian SKU / Nama</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="search" class="h-4 w-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors" placeholder="Cari berdasarkan nama atau SKU...">
                    </div>
                </div>

                <div class="w-full md:w-64">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Filter Kategori</label>
                    <select name="category_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                        Filter
                    </button>
                    @if($search || $categoryId)
                        <a href="{{ route('master.items.index') }}" class="flex-1 md:flex-none justify-center px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5">
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
                            <th class="px-6 py-4">SKU / Kode</th>
                            <th class="px-6 py-4">Nama Barang</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4">Satuan</th>
                            <th class="px-6 py-4 text-right">Stok Awal</th>
                            <th class="px-6 py-4 text-right">Stok Fisik Aktif</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-900">
                                    {{ $item->sku }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900">{{ $item->name }}</div>
                                    @if($item->description)
                                        <div class="text-[10px] text-slate-400 mt-0.5 truncate max-w-xs">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 font-medium text-slate-800 border border-slate-200">
                                        {{ $item->category->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold capitalize text-slate-700">
                                    {{ $item->unit }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-slate-500">
                                    {{ number_format($item->initial_stock) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-slate-900">
                                    {{ number_format($item->total_stock) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Edit trigger -->
                                        <button @click="openEdit({{ json_encode($item) }})" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Barang">
                                            <i data-lucide="edit-3" class="h-4.5 w-4.5"></i>
                                        </button>
                                        
                                        <!-- Delete form -->
                                        <form action="{{ route('master.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus Barang">
                                                <i data-lucide="trash-2" class="h-4.5 w-4.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i data-lucide="inbox" class="h-10 w-10 text-slate-300"></i>
                                        <span class="text-sm">Tidak ada data barang yang ditemukan</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination links -->
            @if($items->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

        <!-- CREATE MODAL -->
        <div x-show="createOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
            <div @click.away="createOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900">Tambah Barang Baru</h3>
                    <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
                
                <form action="{{ route('master.items.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kategori</label>
                            <select name="category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">SKU Barang (Unik)</label>
                            <input type="text" name="sku" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500" placeholder="Contoh: SKU-ELEC-999">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Satuan</label>
                            <input type="text" name="unit" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500" placeholder="pcs, box, drum, dll.">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nama Barang</label>
                            <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500" placeholder="Masukkan nama barang lengkap...">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Stok Awal Sistem</label>
                            <input type="number" name="initial_stock" required min="0" value="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500" placeholder="0">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Deskripsi / Keterangan</label>
                            <textarea name="description" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500" placeholder="Tambahkan deskripsi singkat..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button" @click="createOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/10 transition-colors">
                            Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="editOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
            <div @click.away="editOpen = false" class="bg-white rounded-2xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-900">Ubah Data Barang</h3>
                    <button @click="editOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                </div>
                
                <form :action="'{{ url('master/items') }}/' + editItem.id" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Kategori</label>
                            <select name="category_id" x-model="editItem.category_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:outline-none focus:border-blue-500">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">SKU Barang (Unik)</label>
                            <input type="text" name="sku" x-model="editItem.sku" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Satuan</label>
                            <input type="text" name="unit" x-model="editItem.unit" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nama Barang</label>
                            <input type="text" name="name" x-model="editItem.name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Stok Awal Sistem</label>
                            <input type="number" name="initial_stock" x-model="editItem.initial_stock" required min="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Deskripsi / Keterangan</label>
                            <textarea name="description" x-model="editItem.description" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500"></textarea>
                        </div>
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
