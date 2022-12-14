<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\TT\Inventories;
use App\TT\TTApi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int|null truckCapacity
 * @property int|null pocketCapacity
 * @property int|null trainYardCapacity
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $casts = [
        'full_trailer_alerts' => 'collection',
        'hidden_exportable_items' => 'collection',
        'custom_combined_storage' => 'collection',
    ];

    public function marketOrders(): HasMany
    {
        return $this->hasMany(MarketOrder::class);
    }

    public function buyOrders(): HasMany
    {
        return $this->hasMany(MarketOrder::class)->buyOrders();
    }

    public function sellOrders(): HasMany
    {
        return $this->hasMany(MarketOrder::class)->sellOrders();
    }

    public function setTTIdFromApi(): bool
    {
        // Theoretically, this should never be hit because all user records are created from a Discord login.
        if (! $this->discord_snowflake) {
            return false;
        }

        $apiResponse = TTApi::ttIdFromDiscordSnowflake($this->discord_snowflake);
        if (property_exists($apiResponse, 'user_id')) {
            $this->update(['tt_id' => $apiResponse->user_id]);
            Cache::forget($this->id . 'apiIdAttempts');
            return true;
        } else {
            Log::debug(json_encode($apiResponse) . ' - User: ' . $this->id);
            Cache::decrement($this->id . 'apiIdAttempts');
            Session::flash('cantGetTTApiAlert');
            return false;
        }
    }

    public function giveAttemptToGetTTId($count = 1): self
    {
        Cache::put($this->id . 'apiIdAttempts', $count);
        return $this;
    }

    public function usesPublicKey()
    {
        return ! empty($this->api_public_key);
    }

    public function canMakeApiCall(): bool
    {
        return ! empty($this->tt_id);
    }

    public function canCalculate(): bool
    {
        return $this->truckCapacity && $this->pocketCapacity;
    }

    public function hasTrainYard(): bool
    {
        return (bool) $this->trainYardCapacity;
    }

    public function hasSecondTrailer(): bool
    {
        return (bool) $this->truckCapacityTwo;
    }

    public function addItemToFullTrailerAlerts(string $itemName)
    {
        if ($this->full_trailer_alerts->contains($itemName)) return;

        $this->full_trailer_alerts = $this->full_trailer_alerts->push($itemName);
        $this->save();
    }

    public function removeItemFromFullTrailerAlerts(string $itemNameToRemove)
    {
        if (! $this->full_trailer_alerts->contains($itemNameToRemove)) return;

        $this->full_trailer_alerts = $this->full_trailer_alerts->reject(function ($itemName) use ($itemNameToRemove) {
            return $itemName == $itemNameToRemove;
        })->sort()->values();
        $this->save();
    }

    public function getGamePlan(): Collection
    {
        return Cache::get($this->id . 'gamePlan', collect());
    }

    public function updateGamePlan(Collection $gamePlan): void
    {
        Cache::put($this->id.'gamePlan', $gamePlan, $thirtyHours = 108000);
    }

    public function clearGamePlan(): void
    {
        Cache::forget($this->id.'gamePlan');
    }

    public function hasCraftingGoal(): bool
    {
        return (bool) $this->getCraftingGoal()['count'];
    }

    public function setCraftingGoal(int $count, string $recipe): self
    {
        $goal = [
            'count' => $count,
            'recipe' => $recipe
        ];

        if ($goal['count']) {
            Session::put('craftingGoal', $goal);
        } else {
            Session::forget('craftingGoal');
        }

        return $this;
    }

    #[ArrayShape(['count' => 'int', 'recipe' => 'string'])] // @phpstan-ignore-line
    public function getCraftingGoal(): array
    {
        return Session::get('craftingGoal', [
            'count' => 0,
            'recipe' => $this->default_crafting_recipe
        ]);
    }

    public function getDiscordProfileLinkAttribute(): string
    {
        return 'https://discordapp.com/users/' . $this->discord_snowflake . '/';
    }

    public function makeInventories(): Inventories
    {
        return $this->makeTruckingInventories()
            ->createTrunk('pocket', $this->pocketCapacity)
            ->createTrunk('trainYard', $this->trainYardCapacity);
    }

    public function makeTruckingInventories(): Inventories
    {
        $inventories = (new Inventories())->createTrunk($this->trailer_name, $this->truckCapacity);

        if ($this->hasSecondTrailer()) {
            $inventories->createTrunk($this->trailer_two_name, $this->truckCapacityTwo);
        }

        return $inventories;
    }

}
