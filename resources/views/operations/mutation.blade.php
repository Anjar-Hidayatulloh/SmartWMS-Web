<x-app-layout>
    <x-slot name="title">Mutasi Lokasi Barang (Transfer)</x-slot>

    <div class="max-w-3xl mx-auto flex flex-col gap-6"
         x-data="{
            itemId: '',
            locations: [],
            originLocationId: '',
            batches: [],
            batchNo: '',
            availableQty: 0,
            loadingLocations: false,
            loadingBatches: false,
            
            fetchLocations() {
                if (!this.itemId) {
                    this.locations = [];
                    this.originLocationId = '';
                    this.batches = [];
                    this.batchNo = '';
                    this.availableQty = 0;
                    return;
                }
                this.loadingLocations = true;
                fetch(`/operations/api/get-locations?item_id=${this.itemId}`)
                    .then(res => res.json())
                    .then(data => {
                        this.locations = data;
                        this.originLocationId = '';
                        this.batches = [];
                        this.batchNo = '';
                        this.availableQty = 0;
                        this.loadingLocations = false;
                    });
            },

            fetchBatches() {
                if (!this.originLocationId || !this.itemId) {
                    this.batches = [];
                    this.batchNo = '';
                    this.availableQty = 0;
                    return;
                }
                this.loadingBatches = true;
                fetch(`/operations/api/get-batches?item_id=${this.itemId}&location_id=${this.originLocationId}`)
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
            }
         }">
        
        <!-- Header -->
        <div>
            <h2 class="text-xl font-bold text-slate-900">Mutasi Lokasi Barang (Relocation)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pindahkan stok barang dari satu koordinat bin ke koordinat bin lainnya secara real-time</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
            <form action="{{ route('operations.mutation.post') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Item / Product selection -->
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

                    <!-- Origin Location selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Pilih Bin Lokasi Asal
                            <span x-show="loadingLocations" class="text-xs text-blue-600 ml-1 italic">Memuat...</span>
                        </label>
                        <select name="origin_location_id" x-model="originLocationId" @change="fetchBatches" :disabled="!itemId" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors disabled:opacity-50">
                            <option value="">-- Pilih Lokasi Asal --</option>
                            <template x-for="loc in locations" :key="loc.id">
                                <option :value="loc.id" x-text="`${loc.bin_code} (Stok: ${loc.qty})`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Batch selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Pilih Batch
                            <span x-show="loadingBatches" class="text-xs text-blue-600 ml-1 italic">Memuat...</span>
                        </label>
                        <select name="batch_no" x-model="batchNo" @change="updateAvailableQty" :disabled="!originLocationId" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors disabled:opacity-50">
                            <option value="">-- Pilih Batch --</option>
                            <template x-for="b in batches" :key="b.batch_no">
                                <option :value="b.batch_no" x-text="`${b.batch_no} (Stok: ${b.qty})`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Destination Location selection -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Bin Lokasi Tujuan</label>
                        <select name="destination_location_id" :disabled="!batchNo" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-3 text-sm text-slate-700 focus:outline-none focus:border-blue-500 transition-colors disabled:opacity-50">
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" x-show="originLocationId != {{ $loc->id }}">
                                    {{ $loc->bin_code }} ({{ $loc->zone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Quantity input -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                            Jumlah Mutasi (Qty)
                            <span x-show="availableQty > 0" class="text-xs text-emerald-600 ml-1 font-bold">
                                (Tersedia: <span x-text="availableQty"></span>)
                            </span>
                        </label>
                        <input type="number" name="qty" required min="1" :max="availableQty" :disabled="!batchNo" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm text-slate-800 focus:outline-none focus:border-blue-500 disabled:opacity-50" placeholder="Masukkan jumlah barang...">
                    </div>
                </div>

                <!-- Submit buttons -->
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('dashboard') }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center justify-center">
                        Kembali
                    </a>
                    <button type="submit" :disabled="!batchNo" class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 transition-all flex items-center justify-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="git-compare" class="h-4.5 w-4.5"></i>
                        Proses Mutasi Lokasi
                    </button>
                </div>

            </form>
        </div>

    </div>
</x-app-layout>
