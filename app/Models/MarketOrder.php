<?php

namespace App\Models;

use App\TT\Items\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsStringable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketOrder extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => AsStringable::class
    ];

    public function scopeBuyOrders(Builder $query): Builder
    {
        return $query->where('type', 'buy');
    }

    public function scopeSellOrders(Builder $query): Builder
    {
        return $query->where('type', 'sell');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getItemAttribute(): Item
    {
        return new Item($this->item_name);
    }

    public function getTotalCostAttribute(): int
    {
        return $this->price_each * $this->count;
    }
}