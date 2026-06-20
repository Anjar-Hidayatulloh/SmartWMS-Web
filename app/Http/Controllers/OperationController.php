<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\InventoryHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OperationController extends Controller
{
    /* ──────────── GOODS IN ──────────── */

    public function showGoodsIn()
    {
        $items = Item::all();
        $locations = Location::all();
        return view('operations.goods_in', compact('items', 'locations'));
    }

    public function processGoodsIn(Request $request)
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

            // 1. Find or create stock entry
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

            // 2. Generate transaction code
            $txCode = 'TRX-IN-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 3. Log transaction
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

            // 4. Log history audit trail
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
            return redirect()->route('operations.goods-in')
                ->with('success', "Goods In Berhasil! Kode transaksi: {$txCode}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /* ──────────── GOODS OUT ──────────── */

    public function showGoodsOut()
    {
        $items = Item::whereHas('stocks', function($q) {
            $q->where('qty', '>', 0);
        })->get();
        
        $locations = Location::all();
        return view('operations.goods_out', compact('items', 'locations'));
    }

    public function processGoodsOut(Request $request)
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

            // 1. Locate stock and validate
            $stock = Stock::where('item_id', $itemId)
                ->where('location_id', $locationId)
                ->where('batch_no', $batchNo)
                ->first();

            if (!$stock || $stock->qty < $qty) {
                $available = $stock ? $stock->qty : 0;
                throw new \Exception("Stok tidak mencukupi di lokasi tersebut. Jumlah tersedia: {$available}");
            }

            if ($stock->status === 'quarantined') {
                throw new \Exception("Gagal melakukan penarikan. Stok di lokasi ini sedang dikarantina.");
            }

            $qtyBefore = $stock->qty;
            $qtyAfter = $qtyBefore - $qty;

            // Update or remove stock
            if ($qtyAfter == 0) {
                $stock->delete();
            } else {
                $stock->qty = $qtyAfter;
                $stock->save();
            }

            // 2. Generate transaction code
            $txCode = 'TRX-OUT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 3. Log transaction
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

            // 4. Log history audit trail
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
            return redirect()->route('operations.goods-out')
                ->with('success', "Goods Out Berhasil! Kode transaksi: {$txCode}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ──────────── MUTATION (RELOCATION) ──────────── */

    public function showMutation()
    {
        $items = Item::whereHas('stocks', function($q) {
            $q->where('qty', '>', 0);
        })->get();
        
        $locations = Location::all();
        return view('operations.mutation', compact('items', 'locations'));
    }

    public function processMutation(Request $request)
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

            // 1. Validate Origin Stock
            $originStock = Stock::where('item_id', $itemId)
                ->where('location_id', $originLocId)
                ->where('batch_no', $batchNo)
                ->first();

            if (!$originStock || $originStock->qty < $qty) {
                $available = $originStock ? $originStock->qty : 0;
                throw new \Exception("Stok asal tidak mencukupi. Tersedia: {$available}");
            }

            if ($originStock->status === 'quarantined') {
                throw new \Exception("Stok di lokasi asal sedang dikarantina, tidak dapat dimutasikan.");
            }

            $originBefore = $originStock->qty;
            $originAfter = $originBefore - $qty;

            // Update or remove origin stock
            if ($originAfter == 0) {
                $originStock->delete();
            } else {
                $originStock->qty = $originAfter;
                $originStock->save();
            }

            // 2. Update or create destination stock
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

            // 3. Generate transaction code
            $txCode = 'TRX-MUT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // 4. Log transaction
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

            // 5. Log history audit trail (Origin & Destination)
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
            return redirect()->route('operations.mutation')
                ->with('success', "Mutasi Stok Berhasil! Kode transaksi: {$txCode}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /* ──────────── AJAX API FOR CLIENT INTERACTION ──────────── */

    public function getBatchesByItem(Request $request)
    {
        $itemId = $request->input('item_id');
        $locationId = $request->input('location_id');

        $query = Stock::where('item_id', $itemId)->where('qty', '>', 0);
        
        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $batches = $query->get(['batch_no', 'qty', 'expired_at', 'location_id']);

        return response()->json($batches);
    }

    public function getLocationsByItem(Request $request)
    {
        $itemId = $request->input('item_id');

        $locations = Location::join('stocks', 'locations.id', '=', 'stocks.location_id')
            ->where('stocks.item_id', $itemId)
            ->where('stocks.qty', '>', 0)
            ->select('locations.id', 'locations.bin_code', 'stocks.qty')
            ->distinct()
            ->get();

        return response()->json($locations);
    }

    public function fefoSuggest(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $itemId = $request->input('item_id');
        $qtyNeeded = $request->input('qty');

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
                'expired_at' => $stock->expired_at ? $stock->expired_at->format('Y-m-d') : 'Tidak Ada',
                'qty_allocated' => $take,
            ];
            $remaining -= $take;
        }

        return response()->json([
            'allocated' => $allocated,
            'remaining_qty' => $remaining,
            'fully_allocated' => ($remaining === 0),
        ]);
    }
}
