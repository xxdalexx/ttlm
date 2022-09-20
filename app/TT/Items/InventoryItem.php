<?php

namespace App\TT\Items;

class InventoryItem extends Item
{
    public int $count;

    public function __construct(string $name, int $count)
    {
        parent::__construct($name);
        $this->count = $count;
    }

    public function getTotalWeight(): int
    {
        return $this->weight * $this->count;
    }
}
