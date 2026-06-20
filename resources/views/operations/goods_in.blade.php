<x-app-layout>
    <x-slot name="title">Barang Masuk (Goods In)</x-slot>

    <div class="max-w-3xl mx-auto flex flex-col gap-6">

        <div>
            <h2 class="text-xl font-bold text-slate-900">Barang Masuk (Goods In)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catat penerimaan stok barang baru ke dalam lokasi penyimpanan (bin)</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
            <form action="{{ route('operations.goods-in.post') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih Barang</label>
                        <select name="item_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">-- Pilih Barang / SKU --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->sku }} - {{ $item->name }} ({{ $item->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih Bin Lokasi Tujuan</label>
                        <select name="location_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">-- Pilih Lokasi Target --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->bin_code }} ({{ $loc->zone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Jumlah (Qty)</label>
                        <input type="number" name="qty" required min="1" value="{{ old('qty', 1) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-800 focus:outline-none focus:border-blue-500" placeholder="Masukkan jumlah barang...">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nomor Batch (Batch No.)</label>
                        <input type="text" name="batch_no" required value="{{ old('batch_no') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-800 focus:outline-none focus:border-blue-500" placeholder="Contoh: BCH-2026-001">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tanggal Kedaluwarsa (Optional)</label>
                        <input type="date" name="expired_at" value="{{ old('expired_at') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-800 focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('dashboard') }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center justify-center">
                        Kembali
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 transition-all flex items-center justify-center gap-1.5">
                        <i data-lucide="plus" class="h-4.5 w-4.5"></i>
                        Simpan Penerimaan (Goods In)
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-app-layout>
