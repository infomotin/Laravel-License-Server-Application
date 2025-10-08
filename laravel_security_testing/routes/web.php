<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['middleware' => ['auth']], function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index')->name('users.index');
        // Route::get('/users/create', 'create')->name('users.create');
        // Route::post('/users', 'store')->name('users.store');
        // Route::get('/users/{user}', 'show')->name('users.show');
        // Route::get('/users/{user}/edit', 'edit')->name('users.edit');
        // Route::put('/users/{user}', 'update')->name('users.update');
        // Route::delete('/users/{user}', 'destroy')->name('users.destroy');
        //Add Search Route for users
        Route::get('/users/search', 'search')->name('users.search');
    });
});

require __DIR__.'/auth.php';
