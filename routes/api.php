<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthTechnicianController;
use App\Http\Controllers\Api\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ClientController;
use App\Models\Project;
use Illuminate\Support\Facades\Mail;
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


//Technican

Route::group([
    ['middleware' => 'auth:Admin-api'],
    'prefix' => 'admin/auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

Route::group([
    ['middleware' => 'auth:technician-api'],
    'prefix' => 'tech/auth'
], function ($router) {
    Route::post('/technician-login', [AuthTechnicianController::class, 'login']);
    Route::post('/technician-register', [AuthTechnicianController::class, 'register']);
    Route::post('/technician-logout', [AuthTechnicianController::class, 'logout']);
    Route::post('/technician-refresh', [AuthTechnicianController::class, 'refresh']);
    Route::get('/technician-user-profile', [AuthTechnicianController::class, 'userProfile']);
});

Route::middleware(['jwt.verify', 'auth:technician-api'])->group(function () {
    // Projects
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    //comments
    Route::post('/comment', [CommentController::class, 'create']);
});

Route::middleware(['jwt.verify', 'auth:Admin-api'])->group(function () {
    //posts
    Route::post('/projects', [ProjectController::class, 'create']);
    //technicans
    Route::get('/technicans/{id}', [TechnicianController::class, 'show']);
    Route::get('/technicans', [TechnicianController::class, 'index']);
    Route::post('/technicans', [TechnicianController::class, 'create']);
    Route::delete('/technicans/{id}', [TechnicianController::class, 'destroy']);
    //clients
    Route::post('/clients', [ClientController::class, 'create']);
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::delete('/client/{id}', [ClientController::class, 'destroy']);
    Route::get('/client/search', [ClientController::class, 'search']);
});
//projects
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{id}', [ProjectController::class, 'show']);
Route::post('/projects/{id}', [ProjectController::class, 'update']);
Route::get('/project/search', [ProjectController::class, 'search']);
//mail view
// Route::view('/projects/{id}', '/resources/views/mail.text')->name('mail-text');