<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LotWebController;
use App\Http\Controllers\ClientWebController;
use App\Http\Controllers\ProduitWebController;
use App\Http\Controllers\TunnelWebController;
use App\Http\Controllers\ChambreWebController;
use App\Http\Controllers\StockWebController;
use App\Http\Controllers\RapportWebController;
use App\Http\Controllers\TarifWebController;
use App\Http\Controllers\Api\LotController as ApiLotController;

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', fn() => view('welcome'));

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout')->middleware('auth');
});

// ==========================================
// API ROUTES
// ==========================================
Route::prefix('api')->as('api.')->group(function () {
    Route::post('/lots', [ApiLotController::class, 'store'])->name('lots.store');
});

// ==========================================
// PROTECTED ROUTES (authentification requise)
// ==========================================
Route::middleware('auth')->group(function () {   // ✅ Correction

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // 1. ENTREES / CONGELATION (Lots)
    // ==========================================
    Route::prefix('lots')->name('lots.')->group(function () {
        Route::get('/', [LotWebController::class, 'index'])->name('index');
        Route::get('/create', [LotWebController::class, 'create'])->name('create');
        Route::post('/', [LotWebController::class, 'store'])->name('store');
        Route::post('/transferer', [LotWebController::class, 'transferer'])->name('transferer');
        Route::post('/{id}/sortir', [LotWebController::class, 'sortirDirect'])->name('sortirDirect');
        Route::post('/{id}/maj-contre-pesee', [LotWebController::class, 'majContrePesee'])->name('majContrePesee');
        Route::get('/{id}/sortir-chambre', [LotWebController::class, 'sortirChambre'])->name('sortirChambre');
        Route::delete('/{id}', [LotWebController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [LotWebController::class, 'printBon'])->name('print');
        Route::get('/{id}/sortir-tunnel', [LotWebController::class, 'sortirTunnel'])->name('sortirTunnel');
        Route::get('/lots', [LotWebController::class, 'index'])->name('lots.index');
        Route::get('/{id}/facture-chambre', [LotWebController::class, 'factureChambre'])->name('factureChambre');
        // Paiements (ajoutés)
        Route::get('/{id}/payer-tunnel', [LotWebController::class, 'payerTunnel'])->name('payerTunnel');
        Route::get('/{id}/payer-chambre', [LotWebController::class, 'payerChambre'])->name('payerChambre');

    });

    Route::get('/entrees', [LotWebController::class, 'create'])->name('entrees.index');


    // ==========================================
    // 2. TUNNELS
    // ==========================================
Route::prefix('tunnels')->name('tunnels.')->group(function () {
    Route::get('/', [TunnelWebController::class, 'index'])->name('index');
    Route::get('/{id}', [TunnelWebController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TunnelWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TunnelWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [TunnelWebController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/print', [TunnelWebController::class, 'print'])->name('print');
});
// ==========================================
// CHAMBRES FROIDES
// ==========================================
Route::prefix('chambres')->name('chambres.')->group(function () {
    Route::get('/', [ChambreWebController::class, 'index'])->name('index');
    Route::get('/{id}', [ChambreWebController::class, 'show'])->name('show');
    Route::post('/chambres/{id}/assigner-lot', [ChambreWebController::class, 'assignerLot'])->name('assignerLot');
    Route::delete('/{id}', [ChambreWebController::class, 'destroy'])->name('destroy');
});
    // ==========================================
    // 4. STOCK
    // ==========================================

Route::prefix('stock')->name('stock.')->middleware('auth')->group(function () {
    Route::get('/', [StockWebController::class, 'index'])->name('index');
    Route::post('/', [StockWebController::class, 'store'])->name('store');
    Route::get('/{id}', [StockWebController::class, 'show'])->name('show');
});

// Route pour le bon de sortie (déjà existante dans le groupe 'lots')
Route::prefix('lots')->name('lots.')->group(function () {
    // ...
    Route::get('/{id}/bon-sortie', [LotWebController::class, 'bonSortie'])->name('bonSortie');
    // ...
});

    // ==========================================
    // 5. CLIENTS
    // ==========================================
    Route::resource('clients', ClientWebController::class)->except(['show']);

    // ==========================================
    // 6. PRODUITS

Route::prefix('produits')->name('produits.')->group(function () {
    Route::get('/', [ProduitWebController::class, 'index'])->name('index');
    Route::get('/create', [ProduitWebController::class, 'create'])->name('create');
    Route::post('/', [ProduitWebController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [ProduitWebController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ProduitWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProduitWebController::class, 'destroy'])->name('destroy');
});
    // ==========================================
    // 7. TARIFS
    // ==========================================
    Route::prefix('tarifs')->name('tarifs.')->group(function () {
        Route::get('/', [TarifWebController::class, 'index'])->name('index');
        Route::get('/create', [TarifWebController::class, 'create'])->name('create');
        Route::post('/', [TarifWebController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [TarifWebController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TarifWebController::class, 'update'])->name('update');
        Route::delete('/{id}', [TarifWebController::class, 'destroy'])->name('destroy');
    });

    // ==========================================

    // ==========================================
    // 9. RAPPORT (2 mois)
    // ==========================================
    Route::prefix('rapports')->name('rapports.')->group(function () {
        Route::get('/', [RapportWebController::class, 'index'])->name('index');

    });

}); // ✅ Fermeture du groupe auth

// ==========================================
// FALLBACK
// ==========================================

