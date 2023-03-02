<?php

use Illuminate\Support\Facades\Route;
use Lett\Http\Controllers\LettReportController;

Route::post('javascript-report', [LettReportController::class, 'report']);
