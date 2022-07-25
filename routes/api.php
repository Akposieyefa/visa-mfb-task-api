<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TransactionController;

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

Route::group(['middleware' => 'api', 'prefix' => 'v1'], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('create-account', [UserController::class, 'createAccount']);

    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::controller(AuthController::class)->group(function () { // Logout and view user profile
            Route::post('logout',  'logout');
            Route::get('user-profile', 'userProfile');
        });

        Route::controller(TransactionController::class)->group(function () { // Transaction routes
            Route::post('credit-wallet',  'makePayment');
            Route::get('verify-payment/{refrence}', 'verify');
        });


        Route::group(['middleware' => ['is_admin']], function () {  //only admin have access to this routes

            Route::controller(UserController::class)->group(function () { // User routes
                Route::get('customers',  'getAllCustomers');
                Route::get('customers/{id}', 'getSingleCustomer');
            });
        });

    });
    
});
