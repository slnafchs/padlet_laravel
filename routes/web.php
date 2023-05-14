<?php

use App\Models\Padlet;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PadletController;


Route::get('/', [PadletController::class,'index']);
Route::get('/padlets', [PadletController::class,'index']);
Route::get('/padlets/{id}',[PadletController::class,'show']);
