<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\TunnelController;
use App\Http\Controllers\Api\ChambreFroideController;
use App\Http\Controllers\Api\TrajetController;
use App\Http\Controllers\Api\ServiceCongelationController;
use App\Http\Controllers\Api\ServiceStockageController;
use App\Http\Controllers\Api\ServiceTransportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::apiResource('clients', ClientController::class)->names('api.clients');
Route::apiResource('produits', ProduitController::class)->names('api.produits');
Route::apiResource('lots', LotController::class)->names('api.lots');
Route::apiResource('tunnels', TunnelController::class)->names('api.tunnels');
Route::apiResource('chambre-froides', ChambreFroideController::class)->names('api.chambre-froides');
Route::apiResource('service-congelations', ServiceCongelationController::class)->names('api.service-congelations');
Route::apiResource('service-stockages', ServiceStockageController::class)->names('api.service-stockages');

// Nouvelles routes speciales pour le magasinier
Route::patch('lots/{lot}/liberer',
    [LotController::class, 'liberer']);

Route::get('service-congelations/{serviceCongelation}/cout',
    [ServiceCongelationController::class, 'calculerCout']);

Route::get('service-stockages/{serviceStockage}/cout',
    [ServiceStockageController::class, 'calculerCout']);
