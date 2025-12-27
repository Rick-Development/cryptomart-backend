<?php

use Illuminate\Support\Facades\Route;
use Modules\P2P\Http\Controllers\P2PController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('p2ps', P2PController::class)->names('p2p');
});
