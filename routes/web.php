<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest'], static function (): void {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::view('login', 'app.auth.login')->name('login');

    Route::get('login/{email}', LoginController::class)->middleware('signed')->name('login:store');
    Route::view('register', 'app.auth.register')->name('register');
});

Route::group(['middleware' => 'auth'], static function (): void {
    Route::view('dashboard', 'app.dashboard.show')->name('dashboard:show');
    Route::post('logout', LogoutController::class)->name('logout');
});
