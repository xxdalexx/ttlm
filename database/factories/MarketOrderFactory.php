<?php

namespace Database\Factories;

use App\Models\User;
use App\TT\Items\Weights;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarketOrder>
 */
class MarketOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_name' =>$this->fakeItem(),
            'expires' => Carbon::now()->addWeek(),
            'count' => fake()->numberBetween(100,1000),
            'price_each' => fake()->numberBetween(15000,800000)
        ];
    }

    public function buyOrder()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'buy',
            ];
        });
    }

    public function sellOrder()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'sell',
            ];
        });
    }

    protected function fakeItem()
    {
        return collect(Weights::$weights)->keys()->random();
    }
}