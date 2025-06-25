<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Resources\LivestockResource\Pages\ViewBatch;

Route::get('/', function () {
    return view('welcome');
});
