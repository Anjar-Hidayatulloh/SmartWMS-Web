<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Transaction::with(['item', 'user', 'originLocation', 'destinationLocation', 'inventoryHistories'])->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('logs.index', compact('logs', 'search', 'type', 'startDate', 'endDate'));
    }
}
