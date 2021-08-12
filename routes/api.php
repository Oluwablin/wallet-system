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

    Route::group(['middleware' => 'auth:api', 'namespace' => 'App\Http\Controllers\v1\Auth','prefix' => 'auth'], function ($router) {
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');

    });

    Route::fallback(function () {
        return response()->json([
            'error' => true,
            'message' => 'Route don\'t exist',
            'data' => null,
        ], 404);
    });

});
