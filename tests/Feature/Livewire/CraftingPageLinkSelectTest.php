<?php

use App\Http\Livewire\CraftingPageLinkSelect;

it('sets a default item name on mount', function () {

    Livewire::test(CraftingPageLinkSelect::class)
        ->assertSet('itemName', 'house');

});

it('redirects', function () {

    Livewire::test(CraftingPageLinkSelect::class)
        ->set('itemName', 'crafted_computer')
        ->call('goToLink')
        ->assertRedirect(route('craftingPage', ['name' => 'crafted_computer']));

});
