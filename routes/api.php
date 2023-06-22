<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\EntrieController;
use App\Http\Controllers\InvitesController;
use App\Http\Controllers\PadletController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserrightsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
Definiert die API-Routen für eine Laravel-Anwendung.

Die aufgeführten Routen verknüpfen verschiedene Endpunkte mit den entsprechenden Controllern und Aktionen, um
die entsprechenden Funktionalitäten bereitzustellen. Jede Route hat eine spezifische URL, einen zugehörigen Controller
und eine Aktion, die ausgeführt wird, wenn der Endpunkt aufgerufen wird. Diese Routen ermöglichen den Zugriff auf
verschiedene Ressourcen und Operationen wie das Abrufen von Daten, das Speichern, Aktualisieren und Löschen von Daten,
die Verwaltung von Benutzerberechtigungen und mehr.
*/

// Middleware
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();});
//registrieren der Middleware und den Controller in Route
Route::post('auth/login', [AuthController::class,'login']);

//Route::group(['middleware' => ['api','auth.jwt']], function(){});

//Padlet
Route::get('padlets', [PadletController::class,'index']);
Route::get('padlets/{id}', [PadletController::class,'findById']);
Route::get('padlets/checkid/{id}', [PadletController::class,'checkID']);
Route::get('padlets/search/{searchTerm}', [PadletController::class,'findBySearchTerm']);
Route::get('public', [PadletController::class,'getPublic']);
Route::get('mypadlets/{user_id}', [PadletController::class,'getPadletsOfUser']);
Route::post('padlets', [PadletController::class,'save']);
Route::put('padlets/{id}', [PadletController::class,'update']);
Route::delete('padlets/{id}', [PadletController::class, 'delete']);

//Entries
Route::get('entries', [EntrieController::class,'index']);
Route::get('entries/{id}', [EntrieController::class,'findById']);
Route::get('padlets/{padlet_id}/entries', [EntrieController::class,'findByPadletID']);
Route::post('padlets/{padlet_id}/entries', [EntrieController::class, 'save']);
Route::put('entries/{id}', [EntrieController::class,'update']);
Route::delete('entries/{id}', [EntrieController::class, 'delete']);

//Comments
Route::get('comments', [CommentController::class,'index']);
Route::post('entries/{entrie_id}/comments', [CommentController::class, 'saveComment']);
Route::put('entries/{entrie_id}/comments/{id}', [CommentController::class,'update']);
Route::delete('entries/{entrie_id}/comments/{id}', [CommentController::class,'delete']);
Route::get('entries/{entrie_id}/comments', [CommentController::class,'findByEntryID']);

//Ratings
Route::post('entries/{entrie_id}/ratings', [RatingController::class, 'saveRating']);
Route::put('entries/{entrie_id}/ratings/{user_id}', [RatingController::class,'update']);
Route::delete('entries/{entrie_id}/ratings/{user_id}', [RatingController::class,'delete']);
Route::get('entries/{entrie_id}/ratings', [RatingController::class,'findByEntryID']);

//User
Route::get('users/{id}', [UserController::class, 'findById']);
Route::get('users/mail/{mail}', [UserController::class, 'findByEmail']);
Route::post('users', [UserController::class, 'save']);
Route::put('users/{user_id}', [UserController::class,'update']);
Route::delete('users/{user_id}', [UserController::class,'delete']);

//Userrights
Route::get('userrights', [UserrightsController::class,'index']);
Route::post('userrights', [UserrightsController::class,'save']);
Route::get('userrights/{padlet_id}/{user_id}', [UserrightsController::class,'findById']);
Route::put('userrights/{padlet_id}/{user_id}', [UserrightsController::class,'update']);
Route::delete('userrights/{padlet_id}/{user_id}', [UserrightsController::class, 'delete']);
Route::get('userrights/{padlet_id}', [UserrightsController::class,'findByPadletId']);
Route::get('userrightsuser/{user_id}', [UserrightsController::class,'findByUserId']);

//Invites
Route::get('invites', [InvitesController::class,'index']);
Route::get('invites/{user_id}', [InvitesController::class,'findByUserId']);
Route::get('invites/{padlet_id}/{user_id}', [InvitesController::class,'findIfExists']);
Route::post('invites', [InvitesController::class,'save']);
Route::put('invites/{id}', [InvitesController::class,'update']);
Route::delete('invites/{id}', [InvitesController::class, 'delete']);



