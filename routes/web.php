<?php

use Illuminate\Support\Facades\Route;

Route::get('zoho/oauth2callback', [Asciisd\Zoho\Http\Controllers\ZohoController::class, 'oauth2callback'])->name('zoho.oauth2callback');
