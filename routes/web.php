<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Models\Product;
use App\Models\StockLog;

// ----------------------------------------
// Publieke routes (inloggen)
// ----------------------------------------

// Login pagina
Route::get('/', function () {
    return view('auth.login');
})->name('login');

// Login formulier verwerken
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors(['email' => 'Ongeldige inloggegevens'])->onlyInput('email');
})->name('login.post');


// ----------------------------------------
// Beveiligde routes (vereisen authenticatie)
// ----------------------------------------

Route::middleware('auth')->group(function () {
    // Dashboard overzicht
    Route::get('/dashboard', function () {
        $products = Product::with(['category', 'stock'])->orderBy('naam')->limit(10)->get();

        // Recente acties voor het dashboard
        $logs = StockLog::with(['user', 'product'])->orderBy('datumtijd', 'desc')->limit(5)->get();

        // Items met lage voorraad: strikt onder minimum
        $lowStock = Product::with('stock')->get()->filter(function ($p) {
            return (optional($p->stock)->aantal ?? 0) < ($p->minimale_voorraad ?? 0);
        })->take(5);

        return view('dashboard', compact('products', 'logs', 'lowStock'));
    })->name('dashboard');

    // Nieuw materiaal (blade-pagina)
    Route::get('/nieuw_materiaal', function () {
        return view('nieuwmaterialen');
    })->name('nieuw_materiaal');

    // Account instellingen
    Route::get('/account', function () {
        return view('account');
    })->name('account');

    Route::post('/account', [AccountController::class, 'update'])->name('account.update');

    // Uitloggen
    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Materialen resource + extra acties
    Route::resource('materialen', ProductController::class)
        ->except(['show'])
        ->names([
            'index' => 'materialen.index',
            'create' => 'materialen.create',
            'store' => 'materialen.store',
            'edit' => 'materialen.edit',
            'update' => 'materialen.update',
            'destroy' => 'materialen.destroy',
        ]);

    Route::post('materialen/{id}/pakken', [ProductController::class, 'pakken'])->name('materialen.pakken');
    Route::post('materialen/{id}/bijleggen', [ProductController::class, 'bijleggen'])->name('materialen.bijleggen');
    Route::post('materialen/bulk', [ProductController::class, 'bulk'])->name('materialen.bulk');

    // Categorieën (resource routes voor toevoegen/verwijderen gebruiken)
    Route::resource('categorieen', CategoryController::class);

    // Gebruikersbeheer (admin)
    // Voeg 'destroy' toe zodat gebruikers verwijderd kunnen worden via formulier
    Route::resource('gebruikers', UserController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::post('gebruikers/{id}/role', [UserController::class, 'changeRole'])->name('gebruikers.changeRole');
    Route::post('gebruikers/{id}/password', [UserController::class, 'updatePassword'])->name('gebruikers.password');

    // Logboek overzicht
    Route::get('logboek', [LogbookController::class, 'index'])->name('logboek.index');
});
