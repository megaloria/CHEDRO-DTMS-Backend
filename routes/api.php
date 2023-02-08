<?php

use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
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
Route::post('/roles', [RoleController::class, 'createRole']);

Route::group([
    'prefix' => '/users'
], function () {
    Route::post('', [UserController::class, 'createUser']);
    // Route::get

    Route::group([
        'prefix' => '/{user_id}',
        'where' => ['user_id' => '[0-9]+']
    ], function () {
        // Route::patch
        Route::delete('', [UserController::class, 'deleteUser']);
        Route::get('', [UserController::class, 'getUser']);
        // Route::get('', [UserController::class, 'getUser'])->where('user_id', '[0-9]+');
    });

});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
