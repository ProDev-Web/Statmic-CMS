<?php

Route::middleware('web')
    ->prefix(config('cp.route', 'cp'))
    ->namespace('Statamic\Http\Controllers\CP')
    ->group(__DIR__.'/cp.php');

Route::middleware('web')
     ->namespace('Statamic\Http\Controllers')
     ->group(__DIR__.'/web.php');