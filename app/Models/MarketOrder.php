<?php

namespace App\Models;

use App\Models\Scopes\ExpiredScope;
use App\TT\Factories\ItemFactory;
use App\TT\Items\Item;
use App\TT\StorageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'expires' => 'date',
        'price_each' => 'integer'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ExpiredScope);
    }

    public function scopeBuyOrders(Builder $query): Builder
    {
        return $query->where('type', 'buy');
    }

    public function scopeSellOrders(Builder $query): Builder
    {
        return $query->where('type', 'sell');
    }

    public function scopeMoveOrders(Builder $query): Builder
    {
        return $query->where('type', 'move');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getItemAttribute(): Item
    {
        return ItemFactory::make($this->item_name);
    }

    public function getTotalCostAttribute(): int
    {
        if ($this->type == 'move') {
            return $this->price_each * $this->count * $this->item->weight;
        }
        return $this->price_each * $this->count;
    }

    public function getStorageNameAttribute(): string
    {
        return StorageFactory::getPrettyName($this->storage);
    }

    public function getAltStorageNameAttribute(): string
    {
        return $this->storage_additional
            ? StorageFactory::getPrettyName($this->storage_additional)
            : '';
    }

    public function findInverseOrders(): EloquentCollection
    {
        if ($this->type == 'move') return new EloquentCollection();

        $lookupType = $this->type == 'sell'
            ? 'buy'
            : 'sell';

        return self::whereType($lookupType)->whereItemName($this->item_name)->get();
    }
}
