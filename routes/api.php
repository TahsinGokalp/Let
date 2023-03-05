<?php

use Illuminate\Support\Facades\Route;
use TahsinGokalp\Lett\Http\Controllers\LettReportController;

Route::post('javascript-report', [LettReportController::class, 'report']);
