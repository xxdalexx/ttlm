<?php

namespace App\TT;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RecipeShoppingListDecorator
{
    public Recipe $recipe;

    /**
     * @var Collection<RecipeShoppingListDecorator>
     */
    public Collection $componentRecipes;

    public int $count;

    public string $recipeName;

    public function __construct(Recipe $recipe, int $count)
    {
        $this->recipe = $recipe;
        $this->recipeName = $recipe->internalName();
        $this->count = $count;
        $this->componentRecipes = collect();
        $this->checkStorage();
        if ($this->count) {
            $this->loadComponentRecipes();
        }
    }

    protected function loadComponentRecipes(): void
    {
        foreach ($this->recipe->components as $craftingMaterial)
        {
            // Account for recipes that yield more than one item. Round up .5s
            $count = (int) ceil($this->count / $this->recipe->makes);

            $recipe = RecipeFactory::get($craftingMaterial);
            $decoratedRecipe = new self($recipe, $craftingMaterial->recipeCount * $count);

            $this->componentRecipes->push($decoratedRecipe);
            ShoppingListBuilder::$allComponents->push($decoratedRecipe);
        }
    }

    protected function checkStorage(): void
    {
        if (!array_key_exists($this->recipeName, ShoppingListBuilder::$diminishingStorage)) return;

        $haveEnough = ShoppingListBuilder::$diminishingStorage[$this->recipeName] > $this->count;

        if ($haveEnough) {
            ShoppingListBuilder::$diminishingStorage[$this->recipeName] -= $this->count;
            $this->count = 0;
        } else {
            $this->count -= ShoppingListBuilder::$diminishingStorage[$this->recipeName];
            unset(ShoppingListBuilder::$diminishingStorage[$this->recipeName]);
        }
    }

    public function getType(): string
    {
        $name = Str::of($this->recipe->internalName());

        if (ShoppingListBuilder::$scrapOverrides->contains($name)) {
            return 'scrap';
        }

        if (ShoppingListBuilder::$refinedOverrides->contains($name)) {
            return 'refined';
        }

        if ($name->contains('_')) {
            return $name->before('_')->toString();
        }

        return 'unhandled';
    }

    public function getTotalCraftingCost(): int
    {
        return $this->recipe->costPerItem() * $this->count;
    }

}
