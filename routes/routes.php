<?php

use Illuminate\Support\Facades\Route;
use Statamic\Http\Middleware\API\SwapExceptionHandler as SwapAPIExceptionHandler;
use Statamic\Http\Middleware\CP\SwapExceptionHandler as SwapCpExceptionHandler;

if (config('statamic.api.enabled')) {
    Route::middleware(SwapApiExceptionHandler::class)->group(function () {
        Route::middleware(config('statamic.api.middleware'))
            ->name('statamic.api.')
            ->prefix(config('statamic.api.route'))
            ->namespace('Statamic\Http\Controllers\API')
            ->group(__DIR__.'/api.php');
    });
}

if (config('statamic.cp.enabled')) {
    Route::middleware(SwapCpExceptionHandler::class)->group(function () {
        Route::middleware('web')
            ->name('statamic.cp.')
            ->prefix(config('statamic.cp.route'))
            ->namespace('Statamic\Http\Controllers\CP')
            ->group(__DIR__.'/cp.php');
    });
}

if (config('statamic.routes.enabled')) {
    Route::middleware('web')
        ->namespace('Statamic\Http\Controllers')
        ->group(__DIR__.'/web.php');
}
