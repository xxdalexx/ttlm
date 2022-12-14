<?php

namespace App\View\Components;

use App\TT\Items\ItemData;
use App\TT\StorageFactory;
use Illuminate\Support\Collection;

class ItemSelectOnlyInStorage extends RecipeSelect
{
    public function getItemNames(): array|Collection
    {
        return StorageFactory::get('combined')->pluck('name')->mapWithKeys(function ($internalName) {
            return [$internalName => ItemData::getName($internalName)];
        })->sort();
    }

}
