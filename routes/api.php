<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'active.user', 'active.notary'])->group(function () {
    Route::get('/me', fn (Request $request) => $request->user());
});
