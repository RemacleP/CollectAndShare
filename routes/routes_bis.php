<?php

use App\Http\Controllers\{
    ProfileController, IdentityController, ClubController,
    LegalController, EventController, CollectController,
    CategoryController, CartController, PaymentController,
    ConversationController
};
use App\Models\{Club, Order};
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\{Route, DB};
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| CORE / DASHBOARD
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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user()->load(['address', 'clubs']);
        $orders = Order::where('user_id', auth()->id())->with('items')->orderBy('created_at', 'desc')->take(5)->get();
        return Inertia::render('Dashboard', ['user' => $user, 'orders' => $orders, 'status' => session('status')]);
    })->name('dashboard');

    Route::get('/parametres/interface', fn() => Inertia::render('Settings/UISettings'))->name('ui.settings');
});


/*
|--------------------------------------------------------------------------
| CLUBS
|--------------------------------------------------------------------------
*/
Route::prefix('clubs')->group(function () {
    // Public
    Route::get('/', [ClubController::class, 'index'])->name('clubs.index');

    // Auth requis
    Route::middleware('auth')->group(function () {
        Route::get('/create', [ClubController::class, 'create'])->name('clubs.create');
        Route::post('/', [ClubController::class, 'store'])->name('clubs.store');

        Route::prefix('{club:slug}')->group(function () {
            Route::get('/edit', [ClubController::class, 'edit'])->name('clubs.edit');
            Route::put('/', [ClubController::class, 'update'])->name('clubs.update');
            Route::delete('/', [ClubController::class, 'destroy'])->name('clubs.destroy');

            // Chat au sein du club
            Route::get('/chat/{conversation:slug?}', [ConversationController::class, 'show'])->name('chat.show');
            Route::post('/conversations', [ConversationController::class, 'store'])->name('chat.conversations.store');
        });
    });

    // Public
    Route::get('/{club:slug}', [ClubController::class, 'show'])->name('clubs.show');
});

/*
|--------------------------------------------------------------------------
| COLLECTIONS & ÉLÉMENTS
|--------------------------------------------------------------------------
*/
Route::prefix('collections')->group(function () {
    // Public
    Route::get('/', [CollectController::class, 'listeCollec'])->name('collections.listeCollec');
    Route::get('/{collection:slug}/elements', [CollectController::class, 'listeElem'])->name('elements.listeElem');
    Route::get('/{collection:slug}/elements/{element:slug}', [CollectController::class, 'showElem'])->name('elements.show');

    // Auth requis (Gestion)
    Route::middleware('auth')->group(function () {
        Route::get('/create', [CollectController::class, 'createCollec'])->name('collections.createCollec');
        Route::post('/', [CollectController::class, 'storeCollec'])->name('collections.storeCollec');
        Route::get('/{collection:slug}/edit', [CollectController::class, 'editCollec'])->name('collections.editCollec');
        Route::patch('/{collection:slug}', [CollectController::class, 'updateCollec'])->name('collections.updateCollec');
        Route::delete('/{collection:slug}', [CollectController::class, 'deleteCollec'])->name('collections.deleteCollec');

        // Éléments
        Route::prefix('{collection:slug}/elements')->group(function () {
            Route::get('/create', [CollectController::class, 'createElem'])->name('elements.createElem');
            Route::post('/', [CollectController::class, 'storeElem'])->name('elements.storeElem');
            Route::get('/{element:slug}/edit', [CollectController::class, 'editElem'])->name('elements.editElem');
            Route::post('/{element:slug}', [CollectController::class, 'updateElem'])->name('elements.updateElem');
            Route::delete('/{element:slug}', [CollectController::class, 'deleteElem'])->name('elements.deleteElem');
        });
    });
});

/*
|--------------------------------------------------------------------------
| BOUTIQUE (PANIER & PAIEMENT)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::controller(CartController::class)->prefix('panier')->group(function () {
        Route::get('/', 'index')->name('cart.index');
        Route::post('/add/{element:id}', 'store')->name('cart.add');
        Route::patch('/update/{cartItem}', 'update')->name('cart.update');
        Route::delete('/remove/{cartItem}', 'destroy')->name('cart.remove');
    });

    Route::controller(PaymentController::class)->group(function () {
        Route::post('/payment/checkout', 'checkout')->name('checkout');
        Route::get('/payment-success', 'success')->name('payment.success');
        Route::get('/mes-commandes', 'history')->name('orders.history');
        Route::get('/commande/{order}/facture', 'downloadInvoice')->name('order.invoice');
    });
});

/*
|--------------------------------------------------------------------------
| UTILISATEURS & PROFIL
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'edit')->name('profile.edit');
        Route::patch('/', 'update')->name('profile.update');
        Route::delete('/', 'destroy')->name('profile.destroy');
        Route::patch('/address', 'updateAddress')->name('address.update');
        Route::get('/{id?}', 'show')->name('profile.show');
    });

    Route::post('/identity/verify', [IdentityController::class, 'verify'])->name('identity.verify');
    Route::post('/identity/upload', [IdentityController::class, 'upload'])->name('identity.upload');
});

/*
|--------------------------------------------------------------------------
| ADMINISTRATION (CATÉGORIES, LÉGAL, CHAT MSG)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Catégories
    Route::prefix('categories')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index')->name('categories.index');
        Route::post('/', 'store')->name('categories.store');
        Route::post('/quick', 'storeQuick')->name('categories.storeQuick');
        Route::put('/{category}', 'update')->name('categories.update');
        Route::delete('/{category}', 'destroy')->name('categories.destroy');
    });

    // Légal & Liens
    Route::controller(LegalController::class)->group(function () {
        Route::match(['post', 'put'], '/mentions-legales', 'updateLegal')->name('legals.update');
        Route::post('/mentions-legales/restore', 'restoreLegal')->name('legals.restore');

        Route::prefix('liens-utiles')->group(function () {
            Route::get('/create', 'createLiens')->name('liensUtiles.create');
            Route::post('/', 'storeLiens')->name('liensUtiles.store');
            Route::get('/{lienUtile}/edit', 'editLiens')->name('liensUtiles.edit');
            Route::patch('/{lienUtile}', 'updateLiens')->name('liensUtiles.update');
            Route::delete('/{lienUtile}', 'deleteLiens')->name('liensUtiles.delete');
        });
    });

    // Messages directs (Conversation hors club context)
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage'])->name('chat.messages.store');
});

require __DIR__.'/auth.php';
