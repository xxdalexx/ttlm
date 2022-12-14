<?php

namespace App\View\Components;

use App\TT\Items\ItemData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class ItemSelect extends Component
{
    public function __construct(public ?string $changeWireModel = null)
    {
    }

    public function getItemNames(): array|Collection
    {
        return ItemData::getInternalNameDisplayNamePairs();
    }

    public function render(): View
    {
        return view('components.item-select');
    }
}
