<?php

use App\Models\MarketOrder;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);


test('relationships and casts', function () {

    $order = MarketOrder::factory()->buyOrder()->create();

    expect($order->user)->toBeInstanceOf(User::class);

});

it('has an item attribute', function () {

    $order = MarketOrder::factory()->sellOrder()->create();

    expect($order->item)->toBeInstanceOf(\App\TT\Items\Item::class)
        ->and($order->item->name)->toBe($order->item_name);

});

it('has a total cost attribute', function () {

    $order = MarketOrder::factory()->sellOrder()->create();

    expect($order->totalCost)->toBe($order->price_each * $order->count);
});

it('has a storage name attribute', function () {

    $order = MarketOrder::factory()->sellOrder()->create(['storage' => 'bhsl']);

    expect($order->storageName)->toBe('Big House Storage LSIA');

});

// Example: Order is a buy order for an item, find sell orders for the same item.
it('finds the inverse of buy and sell orders', function () {

    $user = User::factory()->create();
    $shouldFind = MarketOrder::factory()->sellOrder()->create(['item_name' => 'biz_token']);

    $order = MarketOrder::make([
        'user_id' => $user->id,
        'item_name' => 'biz_token',
        'type' => 'buy'
    ]);

    $results = $order->findInverseOrders();

    expect($results->count())->toBe(1)
        ->and($results->first()->is($shouldFind))->toBeTrue();
});

test('findInverseOrders returns a blank eloquent collection when the type is move', function () {

    $order = MarketOrder::factory()->moveOrder()->create();

    expect($order->findInverseOrders())->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class)
        ->and($order->findInverseOrders()->count())->toBe(0);

});
