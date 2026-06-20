<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'type',
        'item_id',
        'qty',
        'batch_no',
        'expired_at',
        'user_id',
        'origin_location_id',
        'destination_location_id',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'expired_at' => 'date',
            'transaction_date' => 'datetime',
        ];
    }

    /* ──────────── Relationships ──────────── */

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(InventoryHistory::class);
    }

    /* ──────────── Scopes ──────────── */

    public function scopeGoodsIn($query)
    {
        return $query->where('type', 'goods_in');
    }

    public function scopeGoodsOut($query)
    {
        return $query->where('type', 'goods_out');
    }

    public function scopeMutation($query)
    {
        return $query->where('type', 'mutation');
    }
}
