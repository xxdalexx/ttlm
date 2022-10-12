<?php

namespace App\Http\Livewire;

use App\TT\Items\CraftingMaterial;
use App\TT\Items\Item;
use App\TT\PickupRun;
use App\TT\Recipe;
use App\TT\RecipeFactory;
use App\TT\ShoppingListBuilder;
use App\TT\Storage;
use App\TT\StorageFactory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NextGrindRevised extends Component
{
    protected $listeners = [
        'refresh' => '$refresh',
        'updateNextRecipeToGrind' => 'changeRecipe',
    ];

    protected Recipe $recipe;

    protected Recipe $parentRecipe;

    protected Storage $storage;

    public int $truckCapacity;

    public string $customCount = '100';

    public array $toHydrate = [];

    public array|string $storageName = '';

    // Properties for Goal Modal
    public string $goalCount;

    public array|string $goalRecipe;

    /*
    |--------------------------------------------------------------------------
    | Life Cycle Hooks and Listener Calls
    |--------------------------------------------------------------------------
    */

    public function mount(Recipe $parentRecipe)
    {
        $this->parentRecipe = $parentRecipe;
        $this->recipe = $parentRecipe->mostLimitedByAsRecipe();

        $this->toHydrate['parentRecipe'] = $parentRecipe->internalName();
        $this->toHydrate['recipe'] = $this->recipe->internalName();

        $this->setStorageOnRecipeAndThis();

        $this->mountGoal();
    }

    public function hydrate()
    {
        foreach ($this->toHydrate as $property => $recipeName) {
            $this->{$property} = RecipeFactory::get(new Item($recipeName));
        }

        $this->parentRecipe->autoSetStorageBasedOnLocationOfMostComponents();
        $this->setStorageOnRecipeAndThis();
    }

    protected function setStorageOnRecipeAndThis()
    {
        $this->storageName = $this->recipe->autoSetStorageBasedOnLocationOfMostComponents();
        $this->storage = StorageFactory::get($this->storageName);
    }

    public function updatedStorageName($storageName)
    {
        if (is_string($storageName)) {
            $this->storage = StorageFactory::get($storageName);
        }
    }

    public function changeRecipe(string $recipeName)
    {
        $this->toHydrate['recipe'] = $recipeName;
        $this->recipe = RecipeFactory::get(new Item($recipeName));
        $this->setStorageOnRecipeAndThis();
    }

    public function render()
    {
        return view('livewire.next-grind-revised');
    }

    /*
    |--------------------------------------------------------------------------
    | Protected Getters
    |--------------------------------------------------------------------------
    */

    public function getParentRecipe(): Recipe
    {
        return $this->parentRecipe;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /*
    |--------------------------------------------------------------------------
    | Goal Modal Methods
    |--------------------------------------------------------------------------
    */

    public function mountGoal()
    {
        $goal = Auth::user()->getCraftingGoal();
        $this->goalCount = $goal['count'];
        $this->goalRecipe = $goal['recipe'];
    }

    public function updateGoal()
    {
        Auth::user()->setCraftingGoal($this->goalCount, $this->goalRecipe);
        $this->emit('refreshParentRecipeTable');
    }

    public function usingGoal(): bool
    {
        return (bool) $this->goalCount;
    }

    /*
    |--------------------------------------------------------------------------
    | Business
    |--------------------------------------------------------------------------
    */

    public function getNeededForGoal()
    {
        $shoppingList = ShoppingListBuilder::build(
                RecipeFactory::get(new Item($this->goalRecipe)),
                $this->storage,
                (int) $this->goalCount,
                $this->truckCapacity
            )
            ->only(['crafted', 'refined', 'scrap'])
            ->flatten();

        return $this->recipe->components->map(function (CraftingMaterial $craftingMaterial) use ($shoppingList) {
            return $shoppingList->firstWhere('recipeName', $craftingMaterial->name)?->count ?? 0;
        });
    }

    public function getParentRecipeCountForFullTrailer()
    {
        return $this->parentRecipe->components->firstWhere('name', $this->recipe->internalName())->recipeCount
        * $this->parentRecipe->howManyRecipesCanFit($this->truckCapacity);
    }

    public function getNeededForParentTrailer()
    {
        return $this->recipe->components->map(function (CraftingMaterial $craftingMaterial) {
            return $this->getParentRecipeCountForFullTrailer() * $craftingMaterial->recipeCount / $craftingMaterial->recipe->makes;
        });
    }

    public function pickupRunYields(): array
    {
        return match ($this->getRecipe()->pickupRun) {
            'quarry' => PickupRun::quarry($this->truckCapacity),
            'logging camp' => PickupRun::logging($this->truckCapacity, $this->getRecipe()->internalName()),
            'trash' => PickupRun::trash($this->truckCapacity),
            'electronics' => PickupRun::electronics($this->truckCapacity),
            'toxic waste' => PickupRun::toxicWaste($this->truckCapacity),
            'crude oil' => PickupRun::crudeOil($this->truckCapacity, $this->getRecipe()->internalName()),
            'raw gas' => PickupRun::rawGas($this->truckCapacity),
            default => []
        };
    }


}
