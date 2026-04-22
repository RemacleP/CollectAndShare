<?php

use App\Models\Collection;
use App\Models\Element;
use App\Models\User;
use App\Http\Controllers\{InternalMailController,
    ProfileController,
    IdentityController,
    ClubController,
    LegalController,
    EventController,
    CollectController,
    CategoryController,
    CartController,
    PaymentController,
    ConversationController,
    AdminUserController};
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\SettingController;
use App\Models\Club;
use App\Models\Order;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| 1. GÉNÉRAL & ACCUEIL
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,

        // --- DONNÉES POUR LE CARROUSEL DYNAMIQUE ---

        // Le dernier club créé
        'latestClub' => Club::latest()->first(),

        // Le dernier utilisateur inscrit (pour le mot de bienvenue)
        'latestUser' => User::select('id', 'firstname', 'lastname')->latest()->first(),

        // Le tout dernier objet ajouté
        'latestElement' => Element::with('collection')->latest()->first(),

        // --- DONNÉES POUR LES COLONNES ---

        // 1. Les 10 dernières collections
        'latestCollections' => Collection::with('club')
            ->latest()
            ->take(10)
            ->get(),

        // 2. Top Clubs (le plus populaire en premier)
        'topClubs' => Club::with(['address']) // Ajout de l'adresse pour le design responsive
        ->withCount(['users as users_count' => function($query) {
            $query->select(DB::raw('count(distinct(user_id))'));
        }])
            ->orderBy('users_count', 'desc')
            ->take(5) // On en prend 5 pour remplir un peu plus la colonne centrale
            ->get(),

        // 3. Les 10 derniers objets
        'latestElems' => Element::with(['collection', 'images'])
            ->latest()
            ->take(10)
            ->get(),
    ]);
})->name('welcome');

Route::get('/home', fn() => redirect('/'))->name('home');

Route::post('/register-request', [RegisteredUserController::class, 'store'])
    ->name('register.request');
/*
|--------------------------------------------------------------------------
| 2. MESSAGERIE & ADMINISTRATION (SYSTEME)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'user'   => auth()->user()->load(['address', 'clubs']),
            'orders' => Order::where('user_id', auth()->id())->with('items')->latest()->take(5)->get(),
            'status' => session('status'),
        ]);
    })->name('dashboard');

    // Messagerie Interne
    Route::get('/messages', [InternalMailController::class, 'index'])->name('messages.index');
    Route::patch('/messages/{mail}/read', [InternalMailController::class, 'markAsRead'])->name('messages.read');
    Route::post('/requests/{id}/approve', [RegisteredUserController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{id}/reject', [RegisteredUserController::class, 'reject'])->name('requests.reject');


    // Administration spécifique
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/requests', function () {
            return Inertia::render('admin/requests/index', ['requests' => []]);
        })->name('requests.index');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/verify', [AdminUserController::class, 'verify'])->name('users.verify');
        Route::patch('/users/{user}/ban', [AdminUserController::class, 'toggleBan'])->name('users.ban');

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/logo', [SettingController::class, 'updateLogo'])->name('settings.logo.update');
    });
});

/*
|--------------------------------------------------------------------------
| 3. CLUBS
|--------------------------------------------------------------------------
*/
Route::prefix('clubs')->name('clubs.')->group(function () {
    Route::get('/api/all', [ClubController::class, 'apiIndex'])->name('api.index');

    Route::get('/', [ClubController::class, 'index'])->name('index');

    Route::middleware(['auth'])->group(function () {
        Route::get('/create', [ClubController::class, 'create'])->name('create');
        Route::post('/', [ClubController::class, 'store'])->name('store');

        Route::prefix('{club:slug}')->group(function () {
            Route::get('/edit', [ClubController::class, 'edit'])->name('edit');
            Route::put('/', [ClubController::class, 'update'])->name('update');
            Route::delete('/', [ClubController::class, 'destroy'])->name('destroy');

            // Chat de club
            Route::get('/chat/{conversation:slug}', [ConversationController::class, 'show'])
                ->name('chat.show')
                ->scopeBindings();;
            Route::post('/conversations', [ConversationController::class, 'store'])->name('chat.conversations.store');
        });
    });

    Route::get('/{club:slug}', [ClubController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| 4. ÉVÉNEMENTS
|--------------------------------------------------------------------------
*/
Route::prefix('events')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');

    Route::middleware('auth')->group(function () {
        Route::get('/create', [EventController::class, 'create'])->name('create');
        Route::post('/', [EventController::class, 'store'])->name('store');

        Route::prefix('{event:slug}')->group(function () {
            Route::get('/edit', [EventController::class, 'edit'])->name('edit');
            Route::put('/', [EventController::class, 'update'])->name('update');
            Route::delete('/', [EventController::class, 'destroy'])->name('destroy');
            Route::post('/join', [EventController::class, 'join'])->name('join');
            Route::delete('/leave', [EventController::class, 'leave'])->name('leave');
        });
    });

    Route::get('/{event:slug}', [EventController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| 5. COLLECTIONS & ÉLÉMENTS
|--------------------------------------------------------------------------
*/
// 1. Collections elles-mêmes
Route::prefix('collections')->name('collections.')->group(function () {
    Route::get('/', [CollectController::class, 'listeCollec'])->name('listeCollec');

    Route::middleware('auth')->group(function () {
        Route::get('/create', [CollectController::class, 'createCollec'])->name('createCollec');
        Route::post('/', [CollectController::class, 'storeCollec'])->name('storeCollec');
        Route::get('/{collection:slug}/edit', [CollectController::class, 'editCollec'])->name('editCollec');
        Route::patch('/{collection:slug}', [CollectController::class, 'updateCollec'])->name('updateCollec');
        Route::delete('/{collection:slug}', [CollectController::class, 'deleteCollec'])->name('deleteCollec');
    });
});

// 2. Eléments

// --- ROUTES PUBLIQUES (Accessibles à tous) ---
Route::prefix('collections/{collection:slug}/elements')->name('elements.')->group(function () {
    Route::get('/', [CollectController::class, 'listeElem'])->name('listeElem');
    Route::get('/{element:slug}', [CollectController::class, 'showElem'])->name('show');
});

// --- ROUTES PRIVÉES (Connexion requise) ---
Route::middleware(['auth'])->prefix('collections/{collection:slug}/elements')->name('elements.')->group(function () {
    Route::get('/create', [CollectController::class, 'createElem'])->name('createElem');
    Route::post('/', [CollectController::class, 'storeElem'])->name('storeElem');
    Route::get('/{element:slug}/edit', [CollectController::class, 'editElem'])->name('editElem');
    Route::post('/{element:slug}', [CollectController::class, 'updateElem'])->name('updateElem');
    Route::delete('/{element:slug}', [CollectController::class, 'deleteElem'])->name('deleteElem');
});
/*
|--------------------------------------------------------------------------
| 6. CATÉGORIES
|--------------------------------------------------------------------------
*/
Route::prefix('categories')->name('categories.')->middleware('auth')->controller(CategoryController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::put('/{category}', 'update')->name('update');
    Route::delete('/{category}', 'destroy')->name('destroy');
});

/*
|--------------------------------------------------------------------------
| 7. LIENS UTILES & LÉGAL
|--------------------------------------------------------------------------
*/
Route::prefix('liens-utiles')->name('liensUtiles.')->group(function () {
    Route::get('/', [LegalController::class, 'showLiens'])->name('index');

    Route::middleware('auth')->group(function () {
        Route::get('/create', [LegalController::class, 'createLiens'])->name('create'); // AVANT le slug
        Route::post('/', [LegalController::class, 'storeLiens'])->name('store');
        Route::get('/{lienUtile}/edit', [LegalController::class, 'editLiens'])->name('edit');
        Route::patch('/{lienUtile}', [LegalController::class, 'updateLiens'])->name('update');
        Route::delete('/{lienUtile}', [LegalController::class, 'deleteLiens'])->name('delete');
    });
});

Route::get('/mentions-legales', [LegalController::class, 'showLegal'])->name('legals.mentionsLegales');
Route::get('/contact', [LegalController::class, 'showContact'])->name('legals.contacts');
Route::middleware('auth')->match(['post', 'put'], '/mentions-legales', [LegalController::class, 'updateLegal'])->name('legals.update');

/*
|--------------------------------------------------------------------------
| 8. BOUTIQUE (PANIER & PAIEMENT)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::controller(CartController::class)->prefix('panier')->name('cart.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/add/{element:id}', 'store')->name('add');
        Route::patch('/update/{cartItem}', 'update')->name('update');
        Route::delete('/remove/{cartItem}', 'destroy')->name('remove');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::post('/payment/checkout', 'checkout')->name('checkout');
        Route::get('/payment-success', 'success')->name('payment.success');
        Route::get('/mes-commandes', 'history')->name('orders.history');
    });
});

/*
|--------------------------------------------------------------------------
| 9. PROFIL & IDENTITÉ
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
        Route::patch('/profile/address', 'updateAddress')->name('address.update');
        Route::patch('/profile/socials', [ProfileController::class, 'updateSocials'])->name('profile.socials.update');
        Route::get('/profile/{id?}', 'show')->name('profile.show');
    });

    Route::post('/identity/verify', [IdentityController::class, 'verify'])->name('identity.verify');
    Route::post('/identity/upload', [IdentityController::class, 'upload'])->name('identity.upload');
    Route::get('/parametres/interface', fn() => Inertia::render('Settings/UISettings'))->name('ui.settings');

    Route::get('/parametres/apparence', function () {
        return Inertia::render('Settings/UISettings');
    })->name('settings.ui.index');
    Route::patch('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.update-theme');
});

require __DIR__.'/auth.php';
