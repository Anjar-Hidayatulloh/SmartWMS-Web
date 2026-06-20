<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:quarantine-expired', function () {
    $today = \Carbon\Carbon::today();
    
    $expiredStocks = \App\Models\Stock::where('status', 'available')
        ->whereNotNull('expired_at')
        ->whereDate('expired_at', '<', $today)
        ->where('qty', '>', 0)
        ->get();

    $count = $expiredStocks->count();

    if ($count > 0) {
        foreach ($expiredStocks as $stock) {
            $stock->update(['status' => 'quarantined']); // Or pending_quarantine
        }
        $this->info("Berhasil mengkarantina {$count} batch stok yang telah kedaluwarsa.");
    } else {
        $this->comment("Tidak ada stok kedaluwarsa baru yang ditemukan.");
    }
})->purpose('Karantina otomatis semua stok barang yang telah kedaluwarsa');
