<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Helpers\UserSeeder;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function () {

    // Test route
    Route::get('/test', function () {
        return "Api route is working";
    });

     /** Cache */
     Route::get('/clear-cache', function() {
        Artisan::call('config:cache');
        Artisan::call('cache:clear');
        return "Cache is cleared";
    });

    /** SEED DB */
    Route::get('/seed-db', function() {
        UserSeeder::user();
        return "Database seeded successfully";
    });

    Route::group(['namespace' => 'App\Http\Controllers\v1\Auth','prefix' => 'auth'], function ($router) {
        Route::post('login', 'AuthController@login');
    });

    //Authentication
    Route::group(['middleware' => 'auth:api', 'namespace' => 'App\Http\Controllers\v1\Auth','prefix' => 'auth'], function ($router) {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');

    });

    //WalletTransaction
    Route::group(['middleware' => 'auth:api', 'namespace' => 'App\Http\Controllers\v1\WalletTransaction','prefix' => 'wallet/transaction'], function ($router) {
        Route::get('history', 'WalletTransactionController@transactionHistory');
    });

    //User
    Route::group(['middleware' => 'auth:api', 'namespace' => 'App\Http\Controllers\v1\User','prefix' => 'customer'], function ($router) {
        Route::get('/', 'UserController@index');
    });

    //Wallet
    Route::group(['middleware' => 'auth:api', 'namespace' => 'App\Http\Controllers\v1\Wallet','prefix' => 'wallet'], function ($router) {
        Route::post('fund', 'WalletController@fundWallet');
        Route::post('transfer', 'WalletController@transferFunds');
    });

    // Paystack Gateway
    Route::group(['middleware' => 'auth:api', 'namespace' => 'App\Http\Controllers\v1\PaystackTransaction','prefix' => 'transaction'], function ($router) {
        Route::get('payment/callback', 'PaystackTransactionController@handleGatewayCallback');
        //Route::post('pay','PaystackTransactionController@initialize');
        Route::get('verify/fund','PaystackTransactionController@verifyFundWallet');
        Route::get('verify/transfer','PaystackTransactionController@verifyTransferWallet');

    });


    Route::fallback(function () {
        return response()->json([
            'error' => true,
            'message' => 'Route don\'t exist',
            'data' => null,
        ], 404);
    });

});
