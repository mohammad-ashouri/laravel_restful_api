<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::get('test', function () {
    return response()->json([
        'id' => 1,
        'message' => 'fine!',
        'numbers' => [1, 2, 3]
    ]);
});
