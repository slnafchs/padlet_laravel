<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\EntrieController;
use App\Http\Controllers\PadletController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserrightsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('padlets', [PadletController::class,'index']);
Route::get('padlets/{id}', [PadletController::class,'findById']);
Route::get('padlets/checkid/{id}', [PadletController::class,'checkID']);
Route::get('padlets/search/{searchTerm}', [PadletController::class,'findBySearchTerm']);
Route::get('public', [PadletController::class,'getPublic']);
Route::get('mypadlets/{user_id}', [PadletController::class,'getPadletsOfUser']);
Route::post('padlets', [PadletController::class,'save']);
Route::put('padlets/{id}', [PadletController::class,'update']);
Route::delete('padlets/{id}', [PadletController::class, 'delete']);


Route::get('entries', [EntrieController::class,'index']);
Route::get('entries/{id}', [EntrieController::class,'findById']);
Route::get('padlets/{padlet_id}/entries', [EntrieController::class,'findByPadletID']);
Route::post('padlets/{padlet_id}/entries', [EntrieController::class, 'save']);
Route::put('entries/{id}', [EntrieController::class,'update']);
Route::delete('entries/{id}', [EntrieController::class, 'delete']);

Route::post('entries/{entrie_id}/comments', [CommentController::class, 'saveComment']);
Route::post('entries/{entrie_id}/ratings', [RatingController::class, 'saveRating']);
Route::get('comments', [CommentController::class,'index']);

Route::get('entries/{entrie_id}/ratings', [RatingController::class,'findByEntryID']);
Route::get('entries/{entrie_id}/comments', [CommentController::class,'findByEntryID']);
Route::get('users/{id}', [UserController::class, 'findById']);

Route::get('userrights', [UserrightsController::class,'index']);
Route::post('userrights', [UserrightsController::class,'save']);
Route::get('userrights/{padlet_id}/{user_id}', [UserrightsController::class,'findById']);
Route::put('userrights/{padlet_id}/{user_id}', [UserrightsController::class,'update']);
Route::delete('userrights/{padlet_id}/{user_id}', [UserrightsController::class, 'delete']);

/* auth */
Route::middleware('cors')->group(function() {
    Route::post('auth/login', [AuthController::class,'login']);
});

