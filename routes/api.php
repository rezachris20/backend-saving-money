<?php

use App\Http\Controllers\API\PortofolioController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserFamilyController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {
    // USER
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user/generate-kode/{family_id}', [UserController::class, 'generateKodeFamily']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('user/photo',[UserController::class, 'uploadPhoto']);
    Route::post('logout',[UserController::class, 'logout']);

    // USER FAMILY
    Route::get('user-family', [UserFamilyController::class, 'fetch']);
    Route::get('list-family/{family_id}', [UserFamilyController::class, 'getData']);

    // TRANSACTIONS
    Route::prefix('transactions')->group(function(){
        Route::get('get', [TransactionController::class, 'getData']);
        Route::get('detail/{tansactions_id}/{family_id}', [TransactionController::class, 'detailTransactions']);
        Route::post('insert', [TransactionController::class, 'insertData']);
    });

    // PORTOFOLIO
    Route::prefix('portofolio')->group(function(){
        Route::post('/insert',[PortofolioController::class, 'addPortofolio']);
        Route::get('/edit/{id}',[PortofolioController::class, 'editPortofolio']);
        Route::post('/edit/{id}',[PortofolioController::class, 'updatePortofolio']);
        Route::get('/get/{family_id}',[PortofolioController::class, 'getData']);
        Route::get('/saving-target/{family_id}',[PortofolioController::class, 'savingTarget']);
        Route::get('/dropdown/{family_id}',[PortofolioController::class, 'getDropdown']);
        Route::post('/add-target/{family_id}',[PortofolioController::class, 'addTarget']);
    });
});

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
