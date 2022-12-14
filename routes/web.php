<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\SandboxController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return view('home');
    }
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'ttApi', 'canCalculate'])->group(function () {
    Route::get('/crafting/{name?}', [\App\Http\Controllers\CraftingController::class, 'index'])->name('craftingPage');
    Route::get('/shopping-list', [\App\Http\Controllers\ShoppingListController::class, 'index'])->name('shoppingList');
    Route::get('/storages/{name?}', [\App\Http\Controllers\StorageManagementController::class, 'index'])->name('storageManagement');
});
Route::get('/settings', [\App\Http\Controllers\UserSettingsController::class, 'index'])->name('userSettings')->middleware('auth');
Route::get('/market-orders', \App\Http\Livewire\MarketOrderIndex::class)->name('marketOrders');
Route::get('/market-orders/{user:tt_id}', \App\Http\Livewire\MarketOrderShow::class)->name('marketOrders.show');

Route::view('login', 'discord-login-cta')->name('login');
Route::get('/logout', function () {
    Auth::logout();
    return redirect()->route('home');
})->name('logout');

Route::get('/auth/redirect', [DiscordController::class, 'redirectToDiscord'])->name('discordSend');
Route::get('/auth/callback', [DiscordController::class, 'handleCallback'])->name('discordCallback');

Route::get('/sb', [SandboxController::class, 'index']);

Route::middleware(['auth', 'onlyUserOne'])->group(function () {
    Route::get('/dev/missing-items', \App\Http\Livewire\MissingItems::class)->name('admin.missingItems');
    Route::get('/dev/missing-items/{name}', [SandboxController::class, 'apiItemLookup'])->name('admin.itemLookup');
    Route::get('/dev/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/dev/users', [AdminController::class, 'users'])->name('admin.users');
});

Route::get('/dev/loginas/{id}', function (int $id) {
    if (Auth::id() != 1) abort(404);
    Auth::loginUsingId($id);
    return redirect()->back();
});
