<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\UserController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/account/verify/{token}', [AuthController::class, 'verifyAccount']);
Route::post('/activate-account', [UserController::class, 'activateAccount']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::patch('/user/{id}', [UserController::class, 'update']);

    Route::apiResource('/group', GroupController::class);
    Route::post('/group-detail', [GroupController::class, 'addDetail']);
    Route::patch('/group-detail/{id}', [GroupController::class, 'updateDetail']);
    Route::delete('/group-detail/{id}', [GroupController::class, 'destroyDetail']);
});

Route::get('middleware', function () {
    $collection = collect(Route::getRoutes())->map(function ($r) {
        if (isset($r->action['middleware']))
            return $r->action['middleware'];
    })->flatten();
    return array_unique($collection->toArray());
});
