<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DocumentController;

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
// Route::post('/roles', [RoleController::class, 'createRole']);

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
        Route::post('', [UserController::class, 'editUser']);

        // Route::get('', [UserController::class, 'getUser'])->where('user_id', '[0-9]+');
    });

});

Route::group([
    'prefix' => '/settings'
], function () {
    Route::group([
        'prefix' => '/roles'
    ], function () {
        Route::post('', [RoleController::class, 'addRole']);
        Route::get('', [RoleController::class, 'getRoles']);

        Route::group([
            'prefix' => '/{role_id}',
            'where' => ['role_id' => '[0-9]+']
        ], function () {
            Route::get('', [RoleController::class, 'getRole']);
            Route::post('', [RoleController::class, 'editRole']);
            Route::delete('', [RoleController::class, 'deleteRole']);
        });
    });

Route::group([
     'prefix' => '/document-types'
], function () {
    Route::post('', [DocumentController::class, 'addDocumentType']);
    Route::get('', [DocumentController::class, 'getDocumentTypes']);


    Route::group([
            'prefix' => '/{document_id}',
            'where' => ['document_id' => '[0-9]+']
        ], function () {
            Route::get('', [DocumentController::class, 'getDocumentType']);
            Route::post('', [DocumentController::class, 'editDocumentType']);
            Route::delete('', [DocumentController::class, 'deleteDocumentType']);
        });
    });





    
});
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
