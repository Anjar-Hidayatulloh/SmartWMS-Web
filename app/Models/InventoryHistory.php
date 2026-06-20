<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'item_id',
        'location_id',
        'batch_no',
        'qty_before',
        'qty_change',
        'qty_after',
    ];

    protected function casts(): array
    {
        return [
            'qty_before' => 'integer',
            'qty_change' => 'integer',
            'qty_after' => 'integer',
        ];
    }

    /* ──────────── Relationships ──────────── */

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
