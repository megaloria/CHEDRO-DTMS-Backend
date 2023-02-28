<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\HEISController;
use App\Http\Controllers\NGASController;
use App\Http\Controllers\ChedOfficesController;
use App\Http\Controllers\CategoriesController;

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
        'prefix' => '/user'
    ], function () {
        Route::get('', [UserController::class, 'getCurrentUser']);
        Route::delete('', [UserController::class, 'logout']);
    });

    Route::middleware(['admin'])->group(function () {
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
                // Route::post('', [UserController::class, 'editPass']);
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
                
                Route::group([
                    'prefix' => '/heis'
                ], function () {
                    Route::post('', [HEISController::class, 'addHEI']);
                    Route::get('', [HEISController::class, 'getHEIS']);
            
                    Route::group([
                            'prefix' => '/{hei_id}',
                            'where' => ['hei_id' => '[0-9]+']
                        ], function () {
                            Route::get('', [HEISController::class, 'getHEI']);
                            Route::post('', [HEISController::class, 'editHEI']);
                            Route::delete('', [HEISController::class, 'deleteHEI']);
                        });
                    }); 

                    Route::group([
                        'prefix' => '/ngas'
                    ], function () {
                        Route::post('', [NGASController::class, 'addNGA']);
                        Route::get('', [NGASController::class, 'getNGAS']);
                
                        Route::group([
                                'prefix' => '/{nga_id}',
                                'where' => ['nga_id' => '[0-9]+']
                            ], function () {
                                Route::get('', [NGASController::class, 'getNGA']);
                                Route::post('', [NGASController::class, 'editNGA']);
                                Route::delete('', [NGASController::class, 'deleteNGA']);
                            });
                        }); 

                        Route::group([
                            'prefix' => '/ched-offices'
                        ], function () {
                            Route::post('', [ChedOfficesController::class, 'addChedOffice']);
                            Route::get('', [ChedOfficesController::class, 'getChedOffices']);
                    
                            Route::group([
                                    'prefix' => '/{ched_id}',
                                    'where' => ['ched_id' => '[0-9]+']
                                ], function () {
                                    Route::get('', [ChedOfficesController::class, 'geChedOffice']);
                                    Route::post('', [ChedOfficesController::class, 'editChedOffice']);
                                    Route::delete('', [ChedOfficesController::class, 'deleteChedOffice']);
                                });
                            }); 
        });
    });
    
    Route::group([
        'prefix' => '/document'
    ], function () {
        Route::post('', [DocumentController::class, 'addDocument']);
        Route::get('', [DocumentController::class, 'getDocuments']);
        Route::get('', [CategoryController::class, 'getCategories']);

        Route::group([
                'prefix' => '/{document_id}',
                'where' => ['document_id' => '[0-9]+']
            ], function () {
                Route::get('', [DocumentController::class, 'getDocument']);
                Route::post('', [DocumentController::class, 'editDocument']);
                Route::delete('', [DocumentController::class, 'deleteDocument']);
                Route::get('', [CategoryController::class, 'getCategory']);
            });
    });
});
