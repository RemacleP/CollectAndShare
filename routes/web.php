<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\IdentityController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CollectController;
use App\Models\Club;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| 1. ROUTES PUBLIQUES (Accessibles à tous, même non-connectés)
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

// Consultation (Lecture seule pour le public)
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club:slug}', [ClubController::class, 'show'])->name('clubs.show');

// Pages Légales (Consultation)
Route::get('/mentions-legales', [LegalController::class, 'showLegal'])->name('legals.mentionsLegales');
Route::get('/contact', [LegalController::class, 'showContact'])->name('legals.contacts');
Route::get('/liens-utiles', [LegalController::class, 'showLiens'])->name('liensUtiles.index');

Route::get('/home', fn() => redirect('/'))->name('home');

/*
|--------------------------------------------------------------------------
| 2. ZONE MEMBRES (Utilisateurs authentifiés et vérifiés)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard principal
    Route::get('/dashboard', function () {
        $user = auth()->user()->load(['address', 'clubs']);
        return Inertia::render('Dashboard', [
            'user'   => $user,
            'status' => session('status'),
        ]);
    })->name('dashboard');

    // Inscriptions aux événements
    Route::post('/events/{event:slug}/join', [EventController::class, 'join'])->name('events.join');
    Route::delete('/events/{event:slug}/leave', [EventController::class, 'leave'])->name('events.leave');

    // Gestion de l'identité (e-ID) et du profil
    Route::post('/identity/verify', [IdentityController::class, 'verify'])->name('identity.verify');
    Route::post('/identity/upload', [IdentityController::class, 'upload'])->name('identity.upload');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 3. ZONE STAFF (Admins et Club Managers)
|--------------------------------------------------------------------------
| Actions de modification, création et suppression
*/

Route::middleware(['auth'])->group(function () {

    // CRUD Événements (On exclut index/show car ils sont publics plus haut)
    Route::resource('events', EventController::class)
        ->except(['index', 'show'])
        ->parameters(['events' => 'event:slug']);



    // CRUD Clubs (Idem)
    Route::resource('clubs', ClubController::class)
        ->except(['index', 'show'])
        ->parameters(['clubs' => 'club:slug']);

    // Administration Légale
    Route::match(['post', 'put'], '/mentions-legales', [LegalController::class, 'updateLegal'])->name('legals.update');
    Route::post('/mentions-legales/restore', [LegalController::class, 'restoreLegal'])->name('legals.restore');

    // Liens Utiles (Gestion)
    Route::prefix('liens-utiles')->group(function () {
        Route::get('/create', [LegalController::class, 'createLiens'])->name('liensUtiles.create');
        Route::post('/', [LegalController::class, 'storeLiens'])->name('liensUtiles.store');
        Route::get('/{lienUtile}/edit', [LegalController::class, 'editLiens'])->name('liensUtiles.edit');
        Route::patch('/{lienUtile}', [LegalController::class, 'updateLiens'])->name('liensUtiles.update');
        Route::delete('/{lienUtile}', [LegalController::class, 'deleteLiens'])->name('liensUtiles.delete');
    });
});

/*
|--------------------------------------------------------------------------
| 4. GESTION DES COLLECTIONS ET ÉLÉMENTS
|--------------------------------------------------------------------------
*/

// --- Consultation Publique (ou Membres seulement selon ton choix) ---
// Si tu veux que ce soit public, mets-le hors du middleware auth
Route::get('/collections', [CollectController::class, 'listeCollec'])->name('collections.listeCollec');
Route::get('/collections/{currentCollect}/elements', [CollectController::class, 'listeElem'])->name('elements.listeElem');

Route::middleware(['auth', 'verified'])->group(function () {

    // --- Actions sur les Collections ---
    Route::prefix('collections')->group(function () {
        Route::get('/create', [CollectController::class, 'createCollec'])->name('collections.createCollec');
        Route::post('/', [CollectController::class, 'storeCollec'])->name('collections.storeCollec');
        Route::get('/{currentCollect}/edit', [CollectController::class, 'editCollec'])->name('collections.editCollec');
        Route::post('/{currentCollect}', [CollectController::class, 'updateCollec'])->name('collections.updateCollec'); // POST car gestion d'image
        Route::delete('/{currentCollect}', [CollectController::class, 'deleteCollec'])->name('collections.deleteCollec');
    });

    // --- Actions sur les Éléments (liés à une collection) ---
    Route::prefix('collections/{currentCollect}/elements')->group(function () {
        Route::get('/create', [CollectController::class, 'createElem'])->name('elements.createElem');
        Route::post('/', [CollectController::class, 'storeElem'])->name('elements.storeElem');
        Route::get('/{currentElem}/edit', [CollectController::class, 'editElem'])->name('elements.editElem');
        Route::post('/{currentElem}', [CollectController::class, 'updateElem'])->name('elements.updateElem'); // POST pour les images
        Route::delete('/{currentElem}', [CollectController::class, 'deleteElem'])->name('elements.deleteElem');
    });
});

/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION DE BASE (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
