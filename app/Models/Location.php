<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bin_code',
        'zone',
    ];

    /* ──────────── Relationships ──────────── */

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'origin_location_id');
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'destination_location_id');
    }
}
