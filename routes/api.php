<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DivisionController;

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

Route::post('/login', [UserController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::group([
        'prefix' => '/users'
    ], function () {
        Route::post('', [UserController::class, 'createUser']);
        Route::get('', [UserController::class, 'getUsers']);
    
        Route::group([
            'prefix' => '/{user_id}',
            'where' => ['user_id' => '[0-9]+']
        ], function () {
            Route::delete('', [UserController::class, 'deleteUser']);
            Route::get('', [UserController::class, 'getUser']);
            Route::post('', [UserController::class, 'editUser']);
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
            'prefix' => '/divisions'
        ], function () {
            Route::post('', [DivisionController::class, 'addDivision']);
            Route::get('', [DivisionController::class, 'getDivisions']);
    
            Route::group([
                'prefix' => '/{division_id}',
                'where' => ['division_id' => '[0-9]+']
            ], function () {
                Route::get('', [DivisionController::class, 'getDivision']);
                Route::post('', [DivisionController::class, 'editDivision']);
                Route::delete('', [DivisionController::class, 'deleteDivision']);
            });  
        });
    
        Route::group([
            'prefix' => '/document-types'
        ], function () {
            Route::post('', [DocumentTypeController::class, 'addDocumentType']);
            Route::get('', [DocumentTypeController::class, 'getDocumentTypes']);
    
            Route::group([
                    'prefix' => '/{document_id}',
                    'where' => ['document_id' => '[0-9]+']
                ], function () {
                    Route::get('', [DocumentTypeController::class, 'getDocumentType']);
                    Route::post('', [DocumentTypeController::class, 'editDocumentType']);
                    Route::delete('', [DocumentTypeController::class, 'deleteDocumentType']);
                });
            });      
    });
    
    Route::group([
        'prefix' => '/documents'
    ], function () {
        Route::post('', [DocumentController::class, 'addDocument']);
        Route::get('', [DocumentController::class, 'getDocuments']);
    
        Route::group([
                'prefix' => '/{document_id}',
                'where' => ['document_id' => '[0-9]+']
            ], function () {
                Route::get('', [DocumentController::class, 'getDocument']);
                Route::post('', [DocumentController::class, 'editDocument']);
                Route::delete('', [DocumentController::class, 'deleteDocument']);
            });
    });
});
