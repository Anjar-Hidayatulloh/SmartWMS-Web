<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AiController extends Controller
{
    public function index()
    {
        $items = Item::all();
        $aiOffline = false;

        $productsPayload = [];
        foreach ($items as $item) {
            $count = Transaction::where('item_id', $item->id)
                ->whereIn('type', ['goods_out', 'mutation'])
                ->count();

            $productsPayload[] = [
                'item_id' => (int)$item->id,
                'sku' => $item->sku,
                'transaction_count' => (int)$count
            ];
        }

        $zoningSuggestions = [];
        try {
            $zoningResponse = Http::timeout(3)->post('http://127.0.0.1:8001/api/ai/suggest-zoning', [
                'products' => $productsPayload
            ]);

            if ($zoningResponse->successful()) {
                $zoningSuggestions = $zoningResponse->json()['zoning_suggestions'] ?? [];
            } else {
                $aiOffline = true;
            }
        } catch (\Exception $e) {
            $aiOffline = true;
            Log::warning('AI Service offline for suggest-zoning: ' . $e->getMessage());
        }

        if ($aiOffline || empty($zoningSuggestions)) {

            $zoningSuggestions = [];
            foreach ($productsPayload as $p) {

                if ($p['transaction_count'] > 10) {
                    $class = 'A';
                    $zone = 'Picking Zone (Near Dispatch) - Fallback';
                } elseif ($p['transaction_count'] > 2) {
                    $class = 'B';
                    $zone = 'Storage Zone (Regular) - Fallback';
                } else {
                    $class = 'C';
                    $zone = 'Bulk Storage Zone (Rear) - Fallback';
                }

                $zoningSuggestions[] = [
                    'item_id' => $p['item_id'],
                    'sku' => $p['sku'],
                    'class_label' => $class,
                    'suggested_zone' => $zone
                ];
            }
        }

        $itemMap = $items->pluck('name', 'id')->toArray();
        foreach ($zoningSuggestions as &$suggest) {
            $suggest['item_name'] = $itemMap[$suggest['item_id']] ?? 'Unknown Item';
        }

        $forecastList = [];
        foreach ($items as $item) {

            $historicalData = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
                $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();

                $qtyOut = Transaction::where('item_id', $item->id)
                    ->goodsOut()
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->sum('qty');

                $historicalData[] = [
                    'date' => $monthStart->format('Y-m-d'),
                    'qty_out' => (int)$qtyOut
                ];
            }

            $forecastVal = 0;
            $confidence = 0.0;
            $trend = 'stable';

            try {
                if (!$aiOffline) {
                    $forecastResponse = Http::timeout(3)->post('http://127.0.0.1:8001/api/ai/forecast', [
                        'item_id' => (int)$item->id,
                        'historical_data' => $historicalData
                    ]);

                    if ($forecastResponse->successful()) {
                        $resData = $forecastResponse->json();
                        $forecastVal = $resData['forecast_next_month'];
                        $confidence = $resData['confidence_score'];
                        $trend = $resData['trend'];
                    }
                }
            } catch (\Exception $e) {
                $aiOffline = true;
            }

            if ($aiOffline || $forecastVal == 0) {

                $totalOut = array_sum(array_column($historicalData, 'qty_out'));
                $forecastVal = (int)round($totalOut / 6);
                $confidence = 0.70;
                $trend = $forecastVal > 5 ? 'upward' : 'stable';
            }

            $forecastList[] = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'unit' => $item->unit,
                'history' => $historicalData,
                'forecast' => $forecastVal,
                'confidence' => $confidence,
                'trend' => $trend
            ];
        }

        return view('ai.index', compact('zoningSuggestions', 'forecastList', 'aiOffline'));
    }
}
