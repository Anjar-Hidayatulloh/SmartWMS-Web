<x-app-layout>
    <x-slot name="title">Barang Keluar (Goods Out)</x-slot>

    <div class="max-w-3xl mx-auto flex flex-col gap-6"
         x-data="{
            itemId: '',
            locations: [],
            locationId: '',
            batches: [],
            batchNo: '',
            availableQty: 0,
            loadingLocations: false,
            loadingBatches: false,

            // FEFO Suggestion States
            fefoQty: 1,
            fefoAllocations: [],
            fefoFullyAllocated: false,
            loadingFefo: false,

            init() {
                // Initialize Lucide icons on Alpine changes
                this.$watch('itemId', () => {
                    this.$nextTick(() => lucide.createIcons());
                });
                this.$watch('fefoAllocations', () => {
                    this.$nextTick(() => lucide.createIcons());
                });
            },

            fetchLocations() {
                if (!this.itemId) {
                    this.locations = [];
                    this.locationId = '';
                    this.batches = [];
                    this.batchNo = '';
                    this.availableQty = 0;
                    this.fefoAllocations = [];
                    return;
                }
                this.loadingLocations = true;
                fetch(`/operations/api/get-locations?item_id=${this.itemId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.locations = data;
                        this.locationId = '';
                        this.batches = [];
                        this.batchNo = '';
                        this.availableQty = 0;
                        this.loadingLocations = false;
                    });

                this.fetchFefoSuggestions();
            },

            fetchBatches() {
                if (!this.locationId || !this.itemId) {
                    this.batches = [];
                    this.batchNo = '';
                    this.availableQty = 0;
                    return;
                }
                this.loadingBatches = true;
                fetch(`/operations/api/get-batches?item_id=${this.itemId}&location_id=${this.locationId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.batches = data;
                        this.batchNo = '';
                        this.availableQty = 0;
                        this.loadingBatches = false;
                    });
            },

            updateAvailableQty() {
                const selectedBatch = this.batches.find(b => b.batch_no === this.batchNo);
                this.availableQty = selectedBatch ? selectedBatch.qty : 0;
            },

            fetchFefoSuggestions() {
                if (!this.itemId || this.fefoQty < 1) {
                    this.fefoAllocations = [];
                    return;
                }
                this.loadingFefo = true;
                fetch(`/operations/api/fefo-suggest?item_id=${this.itemId}&qty=${this.fefoQty}`)
                    .then(res => res.json())
                    .then(data => {
                        this.fefoAllocations = data.allocated;
                        this.fefoFullyAllocated = data.fully_allocated;
                        this.loadingFefo = false;
                    });
            }
         }">

        <div>
            <h2 class="text-xl font-bold text-slate-900">Barang Keluar (Goods Out)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catat pengeluaran stok barang untuk pengiriman atau keperluan operasional</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
            <form action="{{ route('operations.goods-out.post') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih Barang</label>
                        <select name="item_id" x-model="itemId" @change="fetchLocations" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="">-- Pilih Barang / SKU --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->sku }} - {{ $item->name }} ({{ $item->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Pilih Bin Lokasi Asal
                            <span x-show="loadingLocations" class="text-xs text-blue-600 ml-1 italic">Memuat...</span>
                        </label>
                        <select name="location_id" x-model="locationId" @change="fetchBatches" :disabled="!itemId" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors disabled:opacity-50">
                            <option value="">-- Pilih Lokasi Asal --</option>
                            <template x-for="loc in locations" :key="loc.id">
                                <option :value="loc.id" x-text="`${loc.bin_code} (Stok: ${loc.qty})`"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Pilih Batch
                            <span x-show="loadingBatches" class="text-xs text-blue-600 ml-1 italic">Memuat...</span>
                        </label>
                        <select name="batch_no" x-model="batchNo" @change="updateAvailableQty" :disabled="!locationId" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors disabled:opacity-50">
                            <option value="">-- Pilih Batch --</option>
                            <template x-for="b in batches" :key="b.batch_no">
                                <option :value="b.batch_no" x-text="`${b.batch_no} (Stok: ${b.qty})`"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Jumlah Pengeluaran (Qty)
                            <span x-show="availableQty > 0" class="text-xs text-emerald-600 ml-1 font-bold">
                                (Tersedia: <span x-text="availableQty"></span>)
                            </span>
                        </label>
                        <input type="number" name="qty" required min="1" x-model.number="fefoQty" @input="fetchFefoSuggestions" :max="availableQty" :disabled="!batchNo" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-800 focus:outline-none focus:border-blue-500 disabled:opacity-50" placeholder="Masukkan jumlah barang...">
                    </div>
                </div>

                <div x-show="itemId && fefoAllocations.length > 0" class="p-5 rounded-2xl bg-blue-50/50 border border-blue-100 animate-in fade-in slide-in-from-top-4 duration-200">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="sparkles" class="h-4.5 w-4.5 text-blue-600 animate-pulse"></i>
                        <h4 class="text-xs font-bold text-blue-800 uppercase tracking-wider">Saran Alokasi Pengambilan FEFO (First Expired First Out)</h4>
                    </div>
                    <div class="space-y-2">
                        <template x-for="alloc in fefoAllocations" :key="alloc.batch_no">
                            <div class="flex items-center justify-between text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-100 shadow-sm">
                                <div>
                                    <span class="font-bold text-slate-800" x-text="`Lokasi Bin: ${alloc.bin_code}`"></span>
                                    <span class="text-slate-300 mx-1.5">&middot;</span>
                                    <span class="text-slate-500 font-semibold" x-text="`Batch: ${alloc.batch_no}`"></span>
                                    <span class="text-slate-300 mx-1.5">&middot;</span>
                                    <span class="text-[10px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-100" x-text="`Exp: ${alloc.expired_at}`"></span>
                                </div>
                                <div class="font-bold text-blue-700 text-xs">
                                    Kuantitas: <span class="text-sm font-extrabold text-blue-900" x-text="alloc.qty_allocated"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="!fefoFullyAllocated" class="text-[10px] text-rose-600 font-semibold mt-2 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="h-3.5 w-3.5"></i>
                            Peringatan: Total stok layak jual tidak mencukupi jumlah kebutuhan yang diinput!
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('dashboard') }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center justify-center">
                        Kembali
                    </a>
                    <button type="submit" :disabled="!batchNo" class="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-lg shadow-slate-900/10 hover:shadow-slate-900/20 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="minus" class="h-4.5 w-4.5"></i>
                        Simpan Pengeluaran (Goods Out)
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-app-layout>
