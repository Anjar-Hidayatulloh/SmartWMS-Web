<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="flex flex-col gap-6">

        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-[#212529] dark:text-white tracking-tight">Dashboard</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('operations.goods-in') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#1c7ed6] dark:bg-[#1971c2] hover:bg-[#1971c2] dark:hover:bg-[#1864ab] text-white text-xs font-semibold shadow-sm transition-colors">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Barang Masuk
                </a>
                <a href="{{ route('operations.goods-out') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#212529] dark:bg-[#373a40] hover:bg-[#343a40] dark:hover:bg-[#2c2e33] text-white text-xs font-semibold shadow-sm transition-colors">
                    <i data-lucide="minus" class="h-4 w-4"></i>
                    Barang Keluar
                </a>
            </div>
        </div>

        @if($nearExpiredCount > 0 || $quarantinedCount > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($nearExpiredCount > 0)
                    <div class="flex items-start gap-3 p-4 rounded-lg bg-[#fff9db] dark:bg-[#2d2812] border border-[#ffe066] dark:border-[#94720e] text-[#92780a] dark:text-[#ffd43b]">
                        <i data-lucide="alert-triangle" class="h-5 w-5 text-[#f59f00] dark:text-[#ffd43b] shrink-0 mt-0.5 animate-pulse"></i>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider">Kematangan Inventaris (Aging Stock)</p>
                            <p class="text-xs mt-0.5">Ada {{ $nearExpiredCount }} batch stok barang mendekati tanggal kedaluwarsa dalam 30 hari.</p>
                        </div>
                    </div>
                @endif

                @if($quarantinedCount > 0)
                    <div class="flex items-start gap-3 p-4 rounded-lg bg-[#fff5f5] dark:bg-[#2b1818] border border-[#ffc9c9] dark:border-[#9c2424] text-[#b91c1c] dark:text-[#ff8787]">
                        <i data-lucide="shield-alert" class="h-5 w-5 text-[#fa5252] shrink-0 mt-0.5"></i>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider">Stok Karantina (Quarantined)</p>
                            <p class="text-xs mt-0.5">Ada {{ number_format($quarantinedCount) }} item stok dalam status karantina/ditahan.</p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-6 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-3xl font-light text-[#212529] dark:text-white tracking-tight">{{ number_format($totalProducts) }}</div>
                    <div class="text-xs text-[#868e96] dark:text-[#909296] mt-1">Total Jenis Barang (SKU)</div>
                </div>
                <div class="text-[#40c057] text-3xl font-light select-none">↑</div>
            </div>

            <div class="bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-6 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-3xl font-light text-[#212529] dark:text-white tracking-tight">{{ number_format($totalStock) }}</div>
                    <div class="text-xs text-[#868e96] dark:text-[#909296] mt-1">Total Stok Fisik (Pcs)</div>
                </div>
                <div class="text-[#fa5252] text-3xl font-light select-none">↓</div>
            </div>

            <div class="bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-6 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-3xl font-light text-[#212529] dark:text-white tracking-tight">+{{ number_format($inboundToday) }}</div>
                    <div class="text-xs text-[#868e96] dark:text-[#909296] mt-1">Inbound Hari Ini (Pcs)</div>
                </div>
                <div class="text-[#40c057] text-3xl font-light select-none">↑</div>
            </div>

            <div class="bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-6 flex items-center justify-between shadow-sm">
                <div>
                    <div class="text-3xl font-light text-[#212529] dark:text-white tracking-tight">+{{ number_format($outboundToday) }}</div>
                    <div class="text-xs text-[#868e96] dark:text-[#909296] mt-1">Outbound Hari Ini (Pcs)</div>
                </div>
                <div class="text-[#40c057] text-3xl font-light select-none">↑</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-6 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4 border-b border-[#f1f3f5] dark:border-[#2c2e33] pb-4">
                    <h4 class="text-sm font-semibold text-[#495057] dark:text-[#c1c2c5] uppercase tracking-wider">Pergerakan Barang</h4>
                    <span class="text-xs text-[#868e96] dark:text-[#909296]">6 Bulan Terakhir</span>
                </div>
                <div class="flex-1 min-h-[300px] flex items-center justify-center">
                    <canvas id="movementChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-6 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4 border-b border-[#f1f3f5] dark:border-[#2c2e33] pb-4">
                    <h4 class="text-sm font-semibold text-[#495057] dark:text-[#c1c2c5] uppercase tracking-wider">Server & Space Status</h4>
                    <span class="text-xs text-[#868e96] dark:text-[#909296]">Live Indicators</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center mb-6">
                    <div>
                        <div class="text-sm font-bold text-[#212529] dark:text-white">{{ $utilizationPercent }}%</div>
                        <div class="text-[10px] text-[#868e96] dark:text-[#909296] mt-0.5">Rack Usage</div>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-[#212529] dark:text-white">{{ $totalLocations }}</div>
                        <div class="text-[10px] text-[#868e96] dark:text-[#909296] mt-0.5">Bins Space</div>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Active</div>
                        <div class="text-[10px] text-[#868e96] dark:text-[#909296] mt-0.5">AI API status</div>
                    </div>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <canvas id="utilityChart" class="w-full max-h-[200px]"></canvas>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg p-6 shadow-sm flex flex-col">
                <div class="border-b border-[#f1f3f5] dark:border-[#2c2e33] pb-3 mb-4">
                    <h4 class="text-sm font-semibold text-[#495057] dark:text-[#c1c2c5] uppercase tracking-wider">Tugas Operasional</h4>
                    <p class="text-[10px] text-[#868e96] dark:text-[#909296] mt-0.5">Indikator penugasan operator saat ini</p>
                </div>
                <div class="flex-1 flex flex-col justify-center gap-5">

                    <div>
                        <div class="flex items-center justify-between text-xs font-medium text-[#495057] dark:text-[#c1c2c5] mb-1.5">
                            <span>Karantina Kualitas (QC)</span>
                            <span class="text-red-500 font-bold">{{ number_format($quarantinedCount) }} Pcs</span>
                        </div>
                        <div class="w-full bg-[#f1f3f5] dark:bg-[#2c2e33] rounded-full h-2">
                            @php
                                $quarantinePercent = $totalStock > 0 ? min(100, ($quarantinedCount / $totalStock) * 100) : 0;
                            @endphp
                            <div class="bg-[#fa5252] h-2 rounded-full transition-all duration-500" style="width: {{ $quarantinePercent }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs font-medium text-[#495057] dark:text-[#c1c2c5] mb-1.5">
                            <span>Aging Stock (Kedaluwarsa < 30 Hari)</span>
                            <span class="text-amber-500 font-bold">{{ $nearExpiredCount }} Batch</span>
                        </div>
                        <div class="w-full bg-[#f1f3f5] dark:bg-[#2c2e33] rounded-full h-2">
                            @php
                                $expiredPercent = $totalProducts > 0 ? min(100, ($nearExpiredCount / $totalProducts) * 100) : 0;
                            @endphp
                            <div class="bg-[#f59f00] h-2 rounded-full transition-all duration-500" style="width: {{ $expiredPercent }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs font-medium text-[#495057] dark:text-[#c1c2c5] mb-1.5">
                            <span>Ketersediaan Bins Terpakai</span>
                            <span class="text-blue-500 font-bold">{{ $utilizationPercent }}%</span>
                        </div>
                        <div class="w-full bg-[#f1f3f5] dark:bg-[#2c2e33] rounded-full h-2">
                            <div class="bg-[#1c7ed6] h-2 rounded-full transition-all duration-500" style="width: {{ $utilizationPercent }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white dark:bg-[#1a1b1e] border border-[#e9ecef] dark:border-[#2c2e33] rounded-lg shadow-sm overflow-hidden flex flex-col">
                <div class="flex items-center justify-between border-b border-[#f1f3f5] dark:border-[#2c2e33] p-6">
                    <div>
                        <h4 class="text-sm font-semibold text-[#495057] dark:text-[#c1c2c5] uppercase tracking-wider">Aktivitas Terkini</h4>
                        <p class="text-[10px] text-[#868e96] dark:text-[#909296] mt-0.5">5 transaksi terakhir yang diproses</p>
                    </div>
                    <a href="{{ route('logs.index') }}" class="text-xs font-semibold text-[#1c7ed6] dark:text-[#4dabf7] hover:text-[#1971c2] dark:hover:text-[#74c0fc] flex items-center gap-1">
                        Lihat Semua
                        <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </a>
                </div>
                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-xs text-[#495057] dark:text-[#c1c2c5]">
                        <thead class="bg-[#f8f9fa] dark:bg-[#25262b] font-bold text-[#868e96] dark:text-[#909296] border-b border-[#e9ecef] dark:border-[#2c2e33]">
                            <tr>
                                <th class="px-6 py-3.5">Kode Transaksi</th>
                                <th class="px-6 py-3.5">Operator</th>
                                <th class="px-6 py-3.5">Jenis Aktivitas</th>
                                <th class="px-6 py-3.5 text-center">Status</th>
                                <th class="px-6 py-3.5 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e9ecef] dark:divide-[#2c2e33]">
                            @forelse($recentTransactions as $tx)
                                <tr class="hover:bg-[#f8f9fa] dark:hover:bg-[#25262b] transition-colors">
                                    <td class="px-6 py-4 font-semibold text-[#212529] dark:text-white">
                                        {{ $tx->transaction_code }}
                                        <div class="text-[9px] text-[#adb5bd] dark:text-[#5c5f66] font-normal mt-0.5">{{ $tx->transaction_date->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4">{{ $tx->user->name ?? 'System' }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-[#212529] dark:text-white max-w-[180px] truncate">{{ $tx->item->name }}</div>
                                        <div class="text-[9px] text-[#adb5bd] dark:text-[#5c5f66] mt-0.5">{{ $tx->item->sku }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($tx->type === 'goods_in')
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#ebfbee] dark:bg-[#123f22] text-[#2b8a3e] dark:text-[#8ce99a]">
                                                Finished
                                            </span>
                                        @elseif($tx->type === 'goods_out')
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#f3f0ff] dark:bg-[#2c1d4d] text-[#6f2dbd] dark:text-[#d0bfff]">
                                                Finished
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-[#e8f4fd] dark:bg-[#102a45] text-[#1c7ed6] dark:text-[#a5d8ff]">
                                                Mutation
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-[#212529] dark:text-white whitespace-nowrap">
                                        {{ number_format($tx->qty) }} <span class="text-[10px] font-normal text-[#868e96] dark:text-[#909296] capitalize">{{ $tx->item->unit }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-[#adb5bd]">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <i data-lucide="clipboard-list" class="h-8 w-8 text-[#dee2e6] dark:text-[#2c2e33]"></i>
                                            <span>Belum ada aktivitas transaksi</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Check current theme
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? '#2c2e33' : '#f1f3f5';
            const textColor = isDark ? '#c1c2c5' : '#868e96';

            // 1. Movement Chart (Bar Chart: Inbound vs Outbound)
            const ctx1 = document.getElementById('movementChart').getContext('2d');
            const movementChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($monthlyMovement, 'month')) !!},
                    datasets: [
                        {
                            label: 'Product Inbound (Masuk)',
                            data: {!! json_encode(array_column($monthlyMovement, 'in')) !!},
                            backgroundColor: '#1c7ed6', // Blue matches Product1 style
                            borderWidth: 0,
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.7
                        },
                        {
                            label: 'Product Outbound (Keluar)',
                            data: {!! json_encode(array_column($monthlyMovement, 'out')) !!},
                            backgroundColor: '#fcc419', // Yellow matches Product3 style
                            borderWidth: 0,
                            borderRadius: 4,
                            barPercentage: 0.6,
                            categoryPercentage: 0.7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                font: {
                                    size: 11,
                                    family: "'Instrument Sans', sans-serif"
                                },
                                color: isDark ? '#c1c2c5' : '#495057'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    family: "'Instrument Sans', sans-serif"
                                },
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                    family: "'Instrument Sans', sans-serif"
                                },
                                color: textColor
                            }
                        }
                    }
                }
            });

            // 2. Utility Line Chart (mimicking Server Status)
            const ctx2 = document.getElementById('utilityChart').getContext('2d');
            const utilityChart = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: ['0', '10', '20', '30', '40', '50'],
                    datasets: [{
                        label: 'Transaction Load',
                        data: [45, 52, 49, 63, 58, 42],
                        borderColor: '#1c7ed6',
                        backgroundColor: isDark ? 'rgba(28, 126, 214, 0.08)' : 'rgba(28, 126, 214, 0.04)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                stepSize: 20,
                                font: {
                                    size: 9,
                                    family: "'Instrument Sans', sans-serif"
                                },
                                color: textColor
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 9,
                                    family: "'Instrument Sans', sans-serif"
                                },
                                color: textColor
                            }
                        }
                    }
                }
            });

            // Watch dark mode changes and update chart colors
            const observer = new MutationObserver(() => {
                const isDarkActive = document.documentElement.classList.contains('dark');
                const nextGridColor = isDarkActive ? '#2c2e33' : '#f1f3f5';
                const nextTextColor = isDarkActive ? '#c1c2c5' : '#868e96';

                // Update Movement Chart
                movementChart.options.plugins.legend.labels.color = isDarkActive ? '#c1c2c5' : '#495057';
                movementChart.options.scales.y.grid.color = nextGridColor;
                movementChart.options.scales.y.ticks.color = nextTextColor;
                movementChart.options.scales.x.ticks.color = nextTextColor;
                movementChart.update();

                // Update Utility Chart
                utilityChart.options.scales.y.grid.color = nextGridColor;
                utilityChart.options.scales.y.ticks.color = nextTextColor;
                utilityChart.options.scales.x.ticks.color = nextTextColor;
                utilityChart.update();
            });

            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    </script>
</x-app-layout>
