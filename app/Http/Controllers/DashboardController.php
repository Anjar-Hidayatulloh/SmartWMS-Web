<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalProducts = Item::count();
        $totalStock = Stock::sum('qty');

        $inboundToday = Transaction::goodsIn()
            ->whereDate('transaction_date', $today)
            ->sum('qty');

        $outboundToday = Transaction::goodsOut()
            ->whereDate('transaction_date', $today)
            ->sum('qty');

        $nearExpiredCount = Stock::where('qty', '>', 0)
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [Carbon::now(), Carbon::now()->addDays(30)])
            ->count();

        $quarantinedCount = Stock::where('status', 'quarantined')
            ->sum('qty');

        $zoneStockData = Location::join('stocks', 'locations.id', '=', 'stocks.location_id')
            ->select('locations.zone')
            ->selectRaw('SUM(stocks.qty) as total_qty')
            ->groupBy('locations.zone')
            ->get();

        $recentTransactions = Transaction::with(['item', 'user', 'originLocation', 'destinationLocation'])
            ->orderBy('transaction_date', 'desc')
            ->take(5)
            ->get();

        $monthlyMovement = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthName = $month->translatedFormat('F');

            $in = Transaction::goodsIn()
                ->whereMonth('transaction_date', $month->month)
                ->whereYear('transaction_date', $month->year)
                ->sum('qty');

            $out = Transaction::goodsOut()
                ->whereMonth('transaction_date', $month->month)
                ->whereYear('transaction_date', $month->year)
                ->sum('qty');

            $monthlyMovement[] = [
                'month' => $monthName,
                'in' => (int)$in,
                'out' => (int)$out,
            ];
        }

        $totalLocations = Location::count();
        $occupiedLocations = Stock::where('qty', '>', 0)->select('location_id')->distinct()->count();
        $utilizationPercent = $totalLocations > 0 ? round(($occupiedLocations / $totalLocations) * 100, 1) : 0;

        return view('dashboard', compact(
            'totalProducts',
            'totalStock',
            'inboundToday',
            'outboundToday',
            'nearExpiredCount',
            'quarantinedCount',
            'zoneStockData',
            'recentTransactions',
            'monthlyMovement',
            'totalLocations',
            'utilizationPercent'
        ));
    }
}
