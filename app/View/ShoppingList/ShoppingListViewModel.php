<?php

namespace App\View\ShoppingList;

use App\TT\Factories\ItemFactory;
use App\TT\RecipeShoppingListDecorator;
use Illuminate\Support\Collection;

class ShoppingListViewModel
{
    protected Collection $totalNeededList;

    protected Collection $stillNeededList;

    protected array $flatStillNeededCounts;

    protected array $neededRunCalculations;

    public function __construct(Collection $totalNeededList, Collection $stillNeededList)
    {
        $this->stillNeededList = $stillNeededList;
        $this->totalNeededList = $totalNeededList;
        $this->neededRunCalculations = $stillNeededList['pickupCalculator']->getRunCalculations()->toArray();

        $stillNeededCounts = $stillNeededList->only(['crafted', 'refined', 'scrap'])
            ->flatten()
            ->mapWithKeys(function (RecipeShoppingListDecorator $item) {
                return [$item->recipeName => $item->count];
            })
            ->toArray();

        $this->flatStillNeededCounts = $totalNeededList->only(['crafted', 'refined', 'scrap'])
            ->flatten()
            ->mapWithKeys(function (RecipeShoppingListDecorator $item) use ($stillNeededCounts) {
                $count = array_key_exists($item->recipeName, $stillNeededCounts)
                    ? $stillNeededCounts[$item->recipeName]
                    : 0;

                return [$item->recipeName => $count];
            })
            ->merge($this->stillNeededCountsForPickups())
            ->toArray();
    }

    protected function stillNeededCountsForPickups(): Collection
    {
        return collect($this->totalNeededList['pickupCalculator']->baseItemsCounts)
            ->map(function ($count, $internalName) {
                return (int) max(
                    $this->stillNeededList['pickupCalculator']->baseItemsCounts[$internalName],
                    0);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Yes
    |--------------------------------------------------------------------------
    */

    public function totalCraftingCost(): string
    {
        return '~$' . number_format($this->totalNeededList['totalCost']);
    }

    public function remainingCraftingCost(): string
    {
        return '~$' . number_format($this->stillNeededList['totalCost']);
    }

    public function showType(string $type): bool
    {
        if ($type == 'pickup') return true;
        return (bool) $this->totalNeededList[$type]->count();
    }

    public function getStillNeededCount(string $internalName): int
    {
        return $this->flatStillNeededCounts[$internalName];
    }

    public function getTotalNeededCount(string $internalName): int
    {
        return $this->totalNeededList
                ->only(['scrap', 'crafted', 'refined'])
                ->flatten()
                ->firstWhere('recipeName', $internalName)
                ->count ?? 0;
    }

    public function getDisplayItems(string $type): Collection
    {
        if ($type == 'pickup') {
            return collect($this->totalNeededList['pickupCalculator']->baseItemsCounts)
                ->map(function ($totalNeeded, $internalName) {
                    return new ShoppingListDisplayItem(
                        ItemFactory::make($internalName),
                        $totalNeeded ?? 0,
                        $this->getStillNeededCount($internalName),
                        $this->neededRunCalculations[$internalName] ?? 0
                    );
                })
                ->filter(function (ShoppingListDisplayItem $displayItem) {
                    return $displayItem->totalNeeded;
                });
        }

        return $this->totalNeededList[$type]->map(function (RecipeShoppingListDecorator $recipeListItem) {
            $runCount = 0;

            if(collect(['refined_planks', 'tcargodust'])->contains($recipeListItem->recipeName)) {
                $runCount = $this->neededRunCalculations[$recipeListItem->recipeName] ?? 0;
            }

            return new ShoppingListDisplayItem(
                ItemFactory::make($recipeListItem->recipeName),
                $this->getTotalNeededCount($recipeListItem->recipeName),
                $this->getStillNeededCount($recipeListItem->recipeName),
                $runCount
            );
        });
    }
}
