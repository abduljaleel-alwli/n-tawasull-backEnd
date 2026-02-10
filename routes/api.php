<?php

use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ContactMessageController;


// --> Analytics tracking endpoint
Route::post('/analytics/track', function (Request $request) {
    app(\App\Services\Analytics\AnalyticsService::class)->track(
        $request->input('event'),
        $request->all()
    );

    return response()->noContent();
})
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('analytics.track');


// ---> Newsletter Routes <---  Documented 👌
Route::post('/subscribe', [NewsletterController::class, 'subscribe']);


// ---> Settings Routes <--- Documented 👌
// Route to get all settings or filter by group
Route::get('/settings', [SettingsController::class, 'getSettings']);
// Route to get a specific setting by its key
Route::get('/settings/{key}', [SettingsController::class, 'getSettingByKey']);


// ---> Services Routes <---  Documented 👌
// Route to get all services with optional filters (e.g., category, is_active)
Route::get('/services', [ServiceController::class, 'index']);
// Route to get a specific service by its ID
Route::get('/services/{id}', [ServiceController::class, 'show']);


// ---> Projects Routes <--- Documented 👌
// Route to get all projects with optional filters (e.g., category, is_active)
Route::get('projects', [ProjectController::class, 'index']);
// Route to get a specific project by its ID
Route::get('projects/{id}', [ProjectController::class, 'show']);


// ---> Contact Messages Routes <--- Documented 👌
Route::post('contact-messages', [ContactMessageController::class, 'store']);