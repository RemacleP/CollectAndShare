<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IdentityController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CollectController;
use App\Http\Controllers\CategoryController;
use App\Models\Club;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| 1. ROUTES PUBLIQUES
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
        'topClubs' => Club::withCount(['users as users_count' => function($query) {
            $query->select(DB::raw('count(distinct(user_id))'));
        }])->orderBy('users_count', 'desc')->take(3)->get(),
    ]);
})->name('welcome');

Route::get('/home', fn() => redirect('/'))->name('home');

// Consultation publique (Événements, Clubs, Infos)
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club:slug}', [ClubController::class, 'show'])->name('clubs.show');
Route::get('/mentions-legales', [LegalController::class, 'showLegal'])->name('legals.mentionsLegales');
Route::get('/contact', [LegalController::class, 'showContact'])->name('legals.contacts');
Route::get('/liens-utiles', [LegalController::class, 'showLiens'])->name('liensUtiles.index');

// --- NOUVEAU : Accès public aux collections ---
Route::get('/collections', [CollectController::class, 'listeCollec'])->name('collections.listeCollec');
Route::get('/collections/{currentCollect:slug}/elements', [CollectController::class, 'listeElem'])->name('elements.listeElem');

/*
|--------------------------------------------------------------------------
| 2. ZONE MEMBRES (Auth & Verified)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user()->load(['address', 'clubs']);
        return Inertia::render('Dashboard', [
            'user'   => $user,
            'status' => session('status'),
        ]);
    })->name('dashboard');

    // Profil & Identité
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/identity/verify', [IdentityController::class, 'verify'])->name('identity.verify');
    Route::post('/identity/upload', [IdentityController::class, 'upload'])->name('identity.upload');

    // Événements (Participer)
    Route::post('/events/{event:slug}/join', [EventController::class, 'join'])->name('events.join');
    Route::delete('/events/{event:slug}/leave', [EventController::class, 'leave'])->name('events.leave');

    /* --- GESTION PRIVÉE DES COLLECTIONS & ÉLÉMENTS --- */
    Route::prefix('collections')->group(function () {
        // Actions de création / modification
        Route::get('/create', [CollectController::class, 'createCollec'])->name('collections.createCollec');
        Route::post('/', [CollectController::class, 'storeCollec'])->name('collections.storeCollec');
        Route::get('/{currentCollect}/edit', [CollectController::class, 'editCollec'])->name('collections.editCollec');
        Route::post('/{currentCollect}', [CollectController::class, 'updateCollec'])->name('collections.updateCollec');
        Route::delete('/{currentCollect}', [CollectController::class, 'deleteCollec'])->name('collections.deleteCollec');

        // Actions sur les éléments
        Route::get('/{currentCollect}/elements/create', [CollectController::class, 'createElem'])->name('elements.createElem');
        Route::post('/{currentCollect}/elements', [CollectController::class, 'storeElem'])->name('elements.storeElem');
        Route::get('/{currentCollect}/elements/{currentElem}/edit', [CollectController::class, 'editElem'])->name('elements.editElem');
        Route::post('/{currentCollect}/elements/{currentElem}', [CollectController::class, 'updateElem'])->name('elements.updateElem');
        Route::delete('/{currentCollect}/elements/{currentElem}', [CollectController::class, 'deleteElem'])->name('elements.deleteElem');
    });
});

/*
|--------------------------------------------------------------------------
| 3. ZONE STAFF & ADMIN (Auth)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Gestion des Catégories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/quick', [CategoryController::class, 'storeQuick'])->name('categories.storeQuick');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // CRUD Clubs & Events (Admin)
    Route::resource('events', EventController::class)->except(['index', 'show'])->parameters(['events' => 'event:slug']);
    Route::resource('clubs', ClubController::class)->except(['index', 'show'])->parameters(['clubs' => 'club:slug']);

    // Administration Légale & Liens
    Route::match(['post', 'put'], '/mentions-legales', [LegalController::class, 'updateLegal'])->name('legals.update');
    Route::post('/mentions-legales/restore', [LegalController::class, 'restoreLegal'])->name('legals.restore');

    Route::prefix('liens-utiles')->group(function () {
        Route::get('/create', [LegalController::class, 'createLiens'])->name('liensUtiles.create');
        Route::post('/', [LegalController::class, 'storeLiens'])->name('liensUtiles.store');
        Route::get('/{lienUtile}/edit', [LegalController::class, 'editLiens'])->name('liensUtiles.edit');
        Route::patch('/{lienUtile}', [LegalController::class, 'updateLiens'])->name('liensUtiles.update');
        Route::delete('/{lienUtile}', [LegalController::class, 'deleteLiens'])->name('liensUtiles.delete');
    });
});

require __DIR__.'/auth.php';
