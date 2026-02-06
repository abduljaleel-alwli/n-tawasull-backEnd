<?php

use App\Http\Controllers\NewsletterController;

Route::post('/subscribe', [NewsletterController::class, 'subscribe']);
