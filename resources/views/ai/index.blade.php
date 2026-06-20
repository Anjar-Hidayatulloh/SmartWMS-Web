<x-app-layout>
    <x-slot name="title">Kecerdasan Buatan (AI Analytics)</x-slot>

    <div class="flex flex-col gap-6" x-data="{ activeTab: 'forecast' }">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Analisis Kecerdasan Buatan (AI Engine)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Integrasi server Python FastAPI untuk analisis prediktif dan optimasi spasial</p>
            </div>
            <div class="flex bg-slate-200/60 p-1 rounded-xl self-start">
                <button @click="activeTab = 'forecast'"
                        :class="activeTab === 'forecast' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-2 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5">
                    <i data-lucide="trending-up" class="h-4 w-4"></i>
                    Peramalan Permintaan (Forecasting)
                </button>
                <button @click="activeTab = 'zoning'"
                        :class="activeTab === 'zoning' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                        class="px-4 py-2 text-xs font-semibold rounded-lg transition-all flex items-center gap-1.5">
                    <i data-lucide="map" class="h-4 w-4"></i>
                    Optimasi Zona (Smart Zoning)
                </button>
            </div>
        </div>

        @if($aiOffline)
            <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 animate-pulse">
                <i data-lucide="shield-alert" class="h-5 w-5 text-rose-600 shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-semibold">Status AI Engine: OFFLINE (Port 8001)</p>
                    <p class="text-xs text-rose-700 mt-0.5">Layanan Python FastAPI tidak terdeteksi. Sistem secara otomatis beralih menggunakan simulasi lokal (Fallback Mode). Untuk mengaktifkan prediksi AI sesungguhnya, silakan jalankan server di latar belakang:</p>
                    <code class="block bg-slate-900 text-slate-100 rounded-lg p-2.5 mt-2 font-mono text-[10px] select-all max-w-fit">
                        cd app/smart-wms-ai && python3 -m venv venv && source venv/bin/activate && pip install -r requirements.txt && python3 main.py
                    </code>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900">
                <i data-lucide="check-circle" class="h-5 w-5 text-emerald-600 shrink-0"></i>
                <div>
                    <p class="text-sm font-semibold">Status AI Engine: TERHUBUNG (Port 8001)</p>
                    <p class="text-xs text-emerald-700 mt-0.5">Server Python FastAPI aktif. Model regresi linear dan kalkulasi Pareto ABC berjalan secara waktu-nyata (real-time) langsung dari data transaksi pergudangan Anda.</p>
                </div>
            </div>
        @endif

        <div x-show="activeTab === 'forecast'" class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-900 text-sm">Hasil Peramalan Permintaan Stok Bulanan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Proyeksi kuantitas barang keluar untuk bulan depan berdasarkan data historis pengeluaran</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Nama Barang & SKU</th>
                                <th class="px-6 py-4 text-center">Data 6 Bulan Terakhir</th>
                                <th class="px-6 py-4 text-right">Rata-rata Keluar</th>
                                <th class="px-6 py-4 text-right">Proyeksi Bulan Depan</th>
                                <th class="px-6 py-4 text-center">Skor Akurasi (Confidence)</th>
                                <th class="px-6 py-4 text-center">Tren Kebutuhan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($forecastList as $fc)
                                <tr class="hover:bg-slate-50/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-900">{{ $fc['name'] }}</div>
                                        <div class="text-xs text-slate-400 mt-0.5">{{ $fc['sku'] }}</div>
                                    </td>
                                    <td class="px-6 py-4">

                                        <div class="flex items-center justify-center gap-1.5">
                                            @foreach($fc['history'] as $hist)
                                                <div class="flex flex-col items-center">
                                                    <span class="text-[9px] text-slate-400 font-semibold mb-0.5">{{ number_format($hist['qty_out']) }}</span>
                                                    <div class="w-4 bg-blue-100 rounded-sm hover:bg-blue-300 transition-colors" style="height: {{ min(20, max(4, $hist['qty_out']/5)) }}px" title="Bulan: {{ Carbon\Carbon::parse($hist['date'])->format('M') }}"></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-500">
                                        @php
                                            $avg = array_sum(array_column($fc['history'], 'qty_out')) / 6;
                                        @endphp
                                        {{ number_format($avg, 1) }} <span class="text-[10px] text-slate-400 font-normal uppercase">{{ $fc['unit'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-extrabold text-slate-900 text-sm">
                                        {{ number_format($fc['forecast']) }} <span class="text-[10px] font-semibold text-blue-600 uppercase">{{ $fc['unit'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @php
                                            $scorePercent = $fc['confidence'] * 100;
                                        @endphp
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-slate-100 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $scorePercent }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold text-slate-800">{{ $scorePercent }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($fc['trend'] === 'upward')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 border border-emerald-100">
                                                <i data-lucide="trending-up" class="h-3 w-3"></i>
                                                Meningkat (Up)
                                            </span>
                                        @elseif($fc['trend'] === 'downward')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-800 border border-rose-100">
                                                <i data-lucide="trending-down" class="h-3 w-3"></i>
                                                Menurun (Down)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-800 border border-slate-100">
                                                <i data-lucide="minus" class="h-3 w-3"></i>
                                                Stabil
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'zoning'" class="space-y-6" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm border-l-4 border-l-emerald-500">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded bg-emerald-100 text-emerald-800 font-bold text-xs">A</span>
                        <h4 class="font-bold text-slate-900 text-sm">Kelas A (Fast Moving)</h4>
                    </div>
                    <p class="text-xs text-slate-500">Kategori produk dengan tingkat transaksi paling tinggi (frekuensi penarikan sering). Direkomendasikan diletakkan di zona dekat pintu gerbang utama / dispatch area untuk meminimalkan waktu penyiapan barang.</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm border-l-4 border-l-blue-500">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded bg-blue-100 text-blue-800 font-bold text-xs">B</span>
                        <h4 class="font-bold text-slate-900 text-sm">Kelas B (Medium Moving)</h4>
                    </div>
                    <p class="text-xs text-slate-500">Kategori produk dengan frekuensi transaksi menengah. Direkomendasikan ditempatkan pada zona rak reguler di area tengah gudang untuk menghemat kapasitas penempatan kritis pintu masuk.</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm border-l-4 border-l-slate-400">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded bg-slate-100 text-slate-800 font-bold text-xs">C</span>
                        <h4 class="font-bold text-slate-900 text-sm">Kelas C (Slow Moving)</h4>
                    </div>
                    <p class="text-xs text-slate-500">Kategori produk dengan tingkat perputaran lambat (sangat jarang dikeluarkan). Direkomendasikan diletakkan di area belakang gudang (bulk storage zone) agar tidak menghambat aliran barang.</p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-900 text-sm">Rekomendasi Pemetaan Spasial Barang (Velocity Suggestions)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Penetapan kelas penempatan barang berdasarkan Pareto Principle frekuensi transaksi keluar/mutasi</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4">Kode SKU</th>
                                <th class="px-6 py-4">Nama Barang</th>
                                <th class="px-6 py-4 text-center">Kelas Perputaran</th>
                                <th class="px-6 py-4">Rekomendasi Lokasi / Zona</th>
                                <th class="px-6 py-4 text-right">Frekuensi Mutasi (6 Bln)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($zoningSuggestions as $suggest)
                                <tr class="hover:bg-slate-50/40 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900">
                                        {{ $suggest['sku'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-slate-800">{{ $suggest['item_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($suggest['class_label'] === 'A')
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-800 font-bold border border-emerald-200" title="Fast Moving">
                                                A
                                            </span>
                                        @elseif($suggest['class_label'] === 'B')
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-blue-800 font-bold border border-blue-200" title="Medium Moving">
                                                B
                                            </span>
                                        @else
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-slate-800 font-bold border border-slate-200" title="Slow Moving">
                                                C
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="map-pin" class="h-4 w-4 text-slate-400"></i>
                                            <span class="font-medium text-slate-900 text-xs">{{ $suggest['suggested_zone'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-800">
                                        {{ number_format(App\Models\Transaction::where('item_id', $suggest['item_id'])->whereIn('type', ['goods_out', 'mutation'])->count()) }}x
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
