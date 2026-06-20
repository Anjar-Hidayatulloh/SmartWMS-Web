<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'location_id',
        'qty',
        'batch_no',
        'expired_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'expired_at' => 'date',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->where('qty', '<', $threshold)->where('qty', '>', 0);
    }
}
