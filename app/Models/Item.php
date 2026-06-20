<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'unit',
        'initial_stock',
    ];

    protected function casts(): array
    {
        return [
            'initial_stock' => 'integer',
        ];
    }

    /* ──────────── Relationships ──────────── */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function inventoryHistories(): HasMany
    {
        return $this->hasMany(InventoryHistory::class);
    }

    /* ──────────── Accessors ──────────── */

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->where('status', 'available')->sum('qty');
    }
}
