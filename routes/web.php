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
use App\Http\Controllers\FactureWebController;

// ==========================================
// PUBLIC ROUTES
// ==========================================
// La racine du site mène toujours à la page de connexion : une session ouverte est fermée d'abord
Route::get('/', function (\Illuminate\Http\Request $request) {
    if (\Illuminate\Support\Facades\Auth::check()) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect('/admin/login');
});

Route::controller(AuthController::class)->group(function () {
    // l'ancienne page de connexion mène maintenant à celle de la nouvelle interface
    Route::redirect('/login', '/admin/login')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout')->middleware('auth');
});

// ==========================================
// PROTECTED ROUTES (authentification requise)
// ==========================================
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::redirect('/dashboard', '/admin')->name('dashboard');

    // ==========================================
    // 1. ENTREES / CONGELATION (Lots)
    // ==========================================
    Route::prefix('lots')->name('lots.')->group(function () {
        Route::redirect('/', '/admin/lots')->name('index');
        Route::get('/create', fn (\Illuminate\Http\Request $r) => redirect('/admin/lots/create' . ($r->getQueryString() ? '?' . $r->getQueryString() : '')))->name('create');
        Route::post('/', [LotWebController::class, 'store'])->name('store');
        Route::post('/{id}/maj-contre-pesee', [LotWebController::class, 'majContrePesee'])->name('majContrePesee');
        Route::delete('/{id}', [LotWebController::class, 'destroy'])->name('destroy');
        Route::get('/print-multiple', [LotWebController::class, 'printBons'])->name('printMultiple');
        Route::get('/{id}/print', [LotWebController::class, 'printBon'])->name('print');
        Route::get('/{id}/sortir-tunnel', [LotWebController::class, 'demanderSortieTunnel'])->name('demanderSortieTunnel');
        // Paiements (ajoutés)
        Route::get('/{id}/payer-tunnel', [LotWebController::class, 'payerTunnel'])->name('payerTunnel');
        Route::get('/{id}/payer-chambre', [LotWebController::class, 'payerChambre'])->name('payerChambre');
        Route::get('/{id}/sortir-chambre', [LotWebController::class, 'formSortieChambre'])->name('formSortieChambre');
        Route::post('/{id}/sortir-chambre', [LotWebController::class, 'demanderSortieChambre'])->name('demanderSortieChambre');
    });

    // Le menu "Entrées/Congélation" ouvre le tableau de suivi
    Route::redirect('/entrees', '/admin/lots')->name('entrees.index');


    // ==========================================
    // 2. TUNNELS
    // ==========================================
Route::prefix('tunnels')->name('tunnels.')->group(function () {
    Route::redirect('/', '/admin/tunnels')->name('index');
    Route::redirect('/{id}', '/admin/tunnels/{id}')->name('show');
    Route::redirect('/{id}/edit', '/admin/tunnels/{id}/edit')->name('edit');
    Route::delete('/{id}', [TunnelWebController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/print', [TunnelWebController::class, 'print'])->name('print');
});
// ==========================================
// CHAMBRES FROIDES
// ==========================================
Route::prefix('chambres')->name('chambres.')->group(function () {
    Route::redirect('/', '/admin/chambre-froides')->name('index');
    Route::redirect('/{id}', '/admin/chambre-froides/{id}')->name('show');
    Route::post('/{id}/assigner-lot', [ChambreWebController::class, 'assignerLot'])->name('assignerLot');
    Route::delete('/{id}', [ChambreWebController::class, 'destroy'])->name('destroy');
});
    // ==========================================
    // 4. STOCK
    // ==========================================

Route::prefix('stock')->name('stock.')->middleware('auth')->group(function () {
    Route::redirect('/', '/admin/stock')->name('index');
    Route::get('/{id}', [StockWebController::class, 'show'])->name('show');
});

// Route pour le bon de sortie (déjà existante dans le groupe 'lots')
Route::prefix('lots')->name('lots.')->group(function () {
    // ...
    // ...
});

    // ==========================================
    // 5. CLIENTS
    // ==========================================
    Route::redirect('/clients', '/admin/clients')->name('clients.index');
    Route::redirect('/clients/create', '/admin/clients/create')->name('clients.create');
    Route::redirect('/clients/{id}/edit', '/admin/clients/{id}/edit')->name('clients.edit');
    Route::post('/clients', [ClientWebController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientWebController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientWebController::class, 'destroy'])->name('clients.destroy');

    // ==========================================
    // 6. PRODUITS

Route::prefix('produits')->name('produits.')->group(function () {
    Route::redirect('/', '/admin/produits')->name('index');
    Route::redirect('/create', '/admin/produits/create')->name('create');
    Route::post('/', [ProduitWebController::class, 'store'])->name('store');
    Route::redirect('/{id}/edit', '/admin/produits/{id}/edit')->name('edit');
    Route::put('/{id}', [ProduitWebController::class, 'update'])->name('update');
    Route::delete('/{id}', [ProduitWebController::class, 'destroy'])->name('destroy');
});
    // ==========================================
    // 7. TARIFS
    // ==========================================
    Route::prefix('tarifs')->name('tarifs.')->group(function () {
        Route::redirect('/', '/admin/tarifs')->name('index');
        Route::redirect('/create', '/admin/tarifs')->name('create');
        Route::redirect('/{id}/edit', '/admin/tarifs')->name('edit');
    });

       Route::redirect('/factures', '/admin/factures-chambre')->name('factures.index');
       Route::get('/factures/groupee', [LotWebController::class, 'factureGroupee'])->name('factures.groupee');
       Route::get('/sorties/bon-groupe', [LotWebController::class, 'bonSortieGroupe'])->name('sorties.bonGroupe');
       Route::get('/archives/{id}', [LotWebController::class, 'voirArchive'])->name('archives.voir');
       Route::get('/sorties-chambre/{id}/facture', [LotWebController::class, 'factureSortieChambre'])->name('sortiesChambre.facture');
       Route::post('/sorties-chambre/{id}/payer', [LotWebController::class, 'payerSortieChambre'])->name('sortiesChambre.payer');
       Route::get('/sorties-chambre/{id}/confirmer-sortie', [LotWebController::class, 'confirmerSortieChambre'])->name('sortiesChambre.confirmer');
       Route::post('/sorties-tunnel/{id}/payer', [LotWebController::class, 'payerSortieTunnel'])->name('sortiesTunnel.payer');
       Route::get('/sorties-tunnel/{id}/confirmer-sortie', [LotWebController::class, 'confirmerSortieTunnel'])->name('sortiesTunnel.confirmer');
       Route::get('/sorties-tunnel/{id}/facture', [LotWebController::class, 'factureTunnel'])->name('sortiesTunnel.facture');


    // ==========================================

    // ==========================================
// RAPPORT – Suivi des entrées, sorties et stock
// ==========================================
Route::prefix('rapports')->name('rapports.')->middleware('auth')->group(function () {
    Route::redirect('/', '/admin/rapports')->name('index');
    Route::get('/imprimer', [RapportWebController::class, 'imprimer'])->name('imprimer');
    Route::get('/export-excel', [RapportWebController::class, 'exporterExcel'])->name('export-excel');
});

Route::get('/activite', [\App\Http\Controllers\ActiviteController::class, 'index'])->name('activite.index');

});

// ==========================================
// FALLBACK
// ==========================================
