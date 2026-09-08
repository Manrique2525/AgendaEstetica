<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'app')->name('spa.home');

Route::view('/{pathMatch}', 'app')
    ->where('pathMatch', '^(?!(?:api|sanctum|build|storage)(?:/|$)).+');
