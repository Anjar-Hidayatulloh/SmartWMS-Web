<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MobileOperationController extends Controller
{
    public function getItems()
    {
        $items = Item::with('category')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'sku' => $item->sku,
                'name' => $item->name,
                'description' => $item->description,
                'unit' => $item->unit,
                'category' => $item->category->name,
                'total_stock' => $item->total_stock,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items
        ]);
    }

    public function getLocations()
    {
        $locations = Location::all();
        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    public function getLocationsByItem(Request $request)
    {
        $request->validate(['item_id' => ['required', 'exists:items,id']]);
        $itemId = $request->item_id;

        $locations = Location::join('stocks', 'locations.id', '=', 'stocks.location_id')
            ->where('stocks.item_id', $itemId)
            ->where('stocks.qty', '>', 0)
            ->select('locations.id', 'locations.bin_code', 'stocks.qty')
            ->distinct()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    public function getBatchesByItemLocation(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'location_id' => ['required', 'exists:locations,id'],
        ]);

        $batches = Stock::where('item_id', $request->item_id)
            ->where('location_id', $request->location_id)
            ->where('qty', '>', 0)
            ->get(['batch_no', 'qty', 'expired_at']);

        return response()->json([
            'status' => 'success',
            'data' => $batches
        ]);
    }

    public function goodsIn(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'batch_no' => ['required', 'string', 'max:100'],
            'expired_at' => ['nullable', 'date'],
        ]);

        DB::beginTransaction();
        try {
            $itemId = $validated['item_id'];
            $locationId = $validated['location_id'];
            $qty = $validated['qty'];
            $batchNo = $validated['batch_no'];
            $expiredAt = $validated['expired_at'];

            $stock = Stock::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('batch_no', $batchNo)
                ->first();

            $qtyBefore = $stock ? $stock->qty : 0;
            $qtyAfter = $qtyBefore + $qty;

            if ($stock) {
                $stock->qty = $qtyAfter;
                if ($expiredAt) {
                    $stock->expired_at = $expiredAt;
                }
                $stock->save();
            } else {
                $stock = Stock::create([
                    'item_id' => $itemId,
                    'location_id' => $locationId,
                    'qty' => $qty,
                    'batch_no' => $batchNo,
                    'expired_at' => $expiredAt,
                    'status' => 'available',
                ]);
            }

            $txCode = 'TRX-IN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $tx = Transaction::create([
                'transaction_code' => $txCode,
                'type' => 'goods_in',
                'item_id' => $itemId,
                'qty' => $qty,
                'batch_no' => $batchNo,
                'expired_at' => $expiredAt,
                'user_id' => Auth::id(),
                'origin_location_id' => null,
                'destination_location_id' => $locationId,
                'transaction_date' => Carbon::now(),
            ]);

            InventoryHistory::create([
                'transaction_id' => $tx->id,
                'item_id' => $itemId,
                'location_id' => $locationId,
                'batch_no' => $batchNo,
                'qty_before' => $qtyBefore,
                'qty_change' => $qty,
                'qty_after' => $qtyAfter,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Goods In Berhasil dicatat. Kode: {$txCode}",
                'data' => [
                    'transaction_code' => $txCode,
                    'qty_after' => $qtyAfter
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencatat barang masuk: ' . $e->getMessage()
            ], 500);
        }
    }

    public function goodsOut(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'batch_no' => ['required', 'string'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            $itemId = $validated['item_id'];
            $locationId = $validated['location_id'];
            $batchNo = $validated['batch_no'];
            $qty = $validated['qty'];

            $stock = Stock::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('batch_no', $batchNo)
                ->first();

            if (!$stock || $stock->qty < $qty) {
                $available = $stock ? $stock->qty : 0;
                return response()->json([
                    'status' => 'error',
                    'message' => "Stok di lokasi asal tidak mencukupi. Tersedia: {$available}"
                ], 400);
            }

            if ($stock->status === 'quarantined') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stok di lokasi asal sedang dikarantina dan tidak dapat dikeluarkan."
                ], 400);
            }

            $qtyBefore = $stock->qty;
            $qtyAfter = $qtyBefore - $qty;

            if ($qtyAfter == 0) {
                $stock->delete();
            } else {
                $stock->qty = $qtyAfter;
                $stock->save();
            }

            $txCode = 'TRX-OUT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $tx = Transaction::create([
                'transaction_code' => $txCode,
                'type' => 'goods_out',
                'item_id' => $itemId,
                'qty' => $qty,
                'batch_no' => $batchNo,
                'expired_at' => $stock->expired_at,
                'user_id' => Auth::id(),
                'origin_location_id' => $locationId,
                'destination_location_id' => null,
                'transaction_date' => Carbon::now(),
            ]);

            InventoryHistory::create([
                'transaction_id' => $tx->id,
                'item_id' => $itemId,
                'location_id' => $locationId,
                'batch_no' => $batchNo,
                'qty_before' => $qtyBefore,
                'qty_change' => -$qty,
                'qty_after' => $qtyAfter,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Goods Out Berhasil dicatat. Kode: {$txCode}",
                'data' => [
                    'transaction_code' => $txCode,
                    'qty_after' => $qtyAfter
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencatat barang keluar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mutation(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id', 'different:origin_location_id'],
            'batch_no' => ['required', 'string'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            $itemId = $validated['item_id'];
            $originLocId = $validated['origin_location_id'];
            $destLocId = $validated['destination_location_id'];
            $batchNo = $validated['batch_no'];
            $qty = $validated['qty'];

            $originStock = Stock::where('item_id', $itemId)
                ->where('location_id', $originLocId)
                ->where('batch_no', $batchNo)
                ->first();

            if (!$originStock || $originStock->qty < $qty) {
                $available = $originStock ? $originStock->qty : 0;
                return response()->json([
                    'status' => 'error',
                    'message' => "Stok asal tidak mencukupi. Tersedia: {$available}"
                ], 400);
            }

            if ($originStock->status === 'quarantined') {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stok di lokasi asal sedang dikarantina dan tidak dapat dipindahkan."
                ], 400);
            }

            $originBefore = $originStock->qty;
            $originAfter = $originBefore - $qty;

            if ($originAfter == 0) {
                $originStock->delete();
            } else {
                $originStock->qty = $originAfter;
                $originStock->save();
            }

            $destStock = Stock::where('item_id', $itemId)
                ->where('location_id', $destLocId)
                ->where('batch_no', $batchNo)
                ->first();

            $destBefore = $destStock ? $destStock->qty : 0;
            $destAfter = $destBefore + $qty;

            if ($destStock) {
                $destStock->qty = $destAfter;
                $destStock->save();
            } else {
                Stock::create([
                    'item_id' => $itemId,
                    'location_id' => $destLocId,
                    'qty' => $qty,
                    'batch_no' => $batchNo,
                    'expired_at' => $originStock->expired_at,
                    'status' => 'available',
                ]);
            }

            $txCode = 'TRX-MUT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $tx = Transaction::create([
                'transaction_code' => $txCode,
                'type' => 'mutation',
                'item_id' => $itemId,
                'qty' => $qty,
                'batch_no' => $batchNo,
                'expired_at' => $originStock->expired_at,
                'user_id' => Auth::id(),
                'origin_location_id' => $originLocId,
                'destination_location_id' => $destLocId,
                'transaction_date' => Carbon::now(),
            ]);

            InventoryHistory::create([
                'transaction_id' => $tx->id,
                'item_id' => $itemId,
                'location_id' => $originLocId,
                'batch_no' => $batchNo,
                'qty_before' => $originBefore,
                'qty_change' => -$qty,
                'qty_after' => $originAfter,
            ]);

            InventoryHistory::create([
                'transaction_id' => $tx->id,
                'item_id' => $itemId,
                'location_id' => $destLocId,
                'batch_no' => $batchNo,
                'qty_before' => $destBefore,
                'qty_change' => $qty,
                'qty_after' => $destAfter,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Mutasi Lokasi Berhasil dicatat. Kode: {$txCode}",
                'data' => [
                    'transaction_code' => $txCode,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memutasi lokasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOperatorLogs()
    {
        $logs = Transaction::with(['item', 'originLocation', 'destinationLocation'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'transaction_code' => $tx->transaction_code,
                    'type' => $tx->type,
                    'item' => $tx->item->name,
                    'sku' => $tx->item->sku,
                    'qty' => $tx->qty,
                    'unit' => $tx->item->unit,
                    'batch_no' => $tx->batch_no,
                    'origin_bin' => $tx->originLocation ? $tx->originLocation->bin_code : null,
                    'destination_bin' => $tx->destinationLocation ? $tx->destinationLocation->bin_code : null,
                    'date' => $tx->transaction_date->format('d-m-Y H:i'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    public function fefoSuggest(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $itemId = $request->item_id;
        $qtyNeeded = $request->qty;

        $stocks = Stock::with('location')
            ->where('item_id', $itemId)
            ->where('status', 'available')
            ->where('qty', '>', 0)
            ->where(function($q) {
                $q->whereNull('expired_at')
                  ->orWhereDate('expired_at', '>=', Carbon::today());
            })
            ->orderByRaw('expired_at IS NULL ASC, expired_at ASC, qty DESC')
            ->get();

        $allocated = [];
        $remaining = $qtyNeeded;

        foreach ($stocks as $stock) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($stock->qty, $remaining);
            $allocated[] = [
                'bin_code' => $stock->location->bin_code,
                'location_id' => $stock->location_id,
                'batch_no' => $stock->batch_no,
                'expired_at' => $stock->expired_at ? $stock->expired_at->format('Y-m-d') : null,
                'qty_allocated' => $take,
            ];
            $remaining -= $take;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'allocated' => $allocated,
                'remaining_qty' => $remaining,
                'fully_allocated' => ($remaining === 0),
            ]
        ]);
    }
}
