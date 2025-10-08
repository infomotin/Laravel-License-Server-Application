<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LicenseController;

Route::post('/license/activate', [LicenseController::class,'activate']);
Route::post('/license/validate',  [LicenseController::class,'validateLicense']);
Route::post('/license/revoke',    [LicenseController::class,'revoke']);
