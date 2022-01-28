<?php

use App\Http\Controllers\cartasycoleccionesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\userControlador;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::put('/create',[userControlador::class,'create']);
Route::put('/login',[userControlador::class,'login']);
Route::put('/recoveryPassword',[userControlador::class,'recoveryPassword']);

Route::middleware(['comprobarToken', 'comprobarAdmin'])->group(function () {

    Route::put('/registrarCartas', [cartasycoleccionesController::class, 'registrarCartas']);
    Route::put('/registrarColecciones', [cartasycoleccionesController::class, 'registrarColecciones']);
    Route::put('/subirCartasColecciones', [cartasycoleccionesController::class, 'subirCartasColecciones']);

});

Route::middleware(['comprobarToken', 'comprobarVendedor'])->group(function () {

    Route::put('/vender', [cartasycoleccionesController::class, 'vender']);
    Route::get('/busquedaVenta', [cartasycoleccionesController::class, 'busquedaVenta']);
    
});



Route::get('/busquedaCompra',[cartasycoleccionesController::class,'busquedaCompra']);
