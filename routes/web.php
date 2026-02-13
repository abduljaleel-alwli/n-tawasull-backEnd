<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Storage;


// ---> Public Landing Page
Volt::route('/', 'app.home.index')->name('home');

// --> Download link for the attached file via contactUs
Route::get(
    '/contact-messages/{contactMessage}/attachment',
    function (\App\Models\ContactMessage $contactMessage) {

        abort_unless($contactMessage->attachment_path, 404);

        $disk = Storage::disk('public');

        abort_unless(
            $disk->exists($contactMessage->attachment_path),
            404
        );

        return $disk->download(
            $contactMessage->attachment_path,
            'contact-attachment-' . $contactMessage->id . '.' .
            pathinfo($contactMessage->attachment_path, PATHINFO_EXTENSION)
        );
    }
)->name('contact.attachments.download');


// ---> Super-Admin & Admin
Route::middleware(['auth', 'verified', 'role:admin|super-admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        // Dashboard
        Volt::route('/dashboard', 'admin.dashboard.dashboard')
            ->name('dashboard');

        // Services Management
        Volt::route('/services', 'admin.services.services-manager')
            ->name('services');

        // Projects Management
        Volt::route('/projects', 'admin.projects.projects-manager')
            ->name('projects');

        // Categories Management
        Volt::route('/categories', 'admin.categories.categories-manager')
            ->name('categories');

        // Hero Management
        Volt::route('/hero', 'admin.hero.hero-manager')->name('hero');

        // Partners Management
        Volt::route('/partners', 'admin.partners.partners-manager')->name('partners');

        // Reviews Management
        Volt::route('/reviews', 'admin.reviews.reviews-manager')->name('reviews');

        // FAQs Management
        Volt::route('/faqs', 'admin.faqs.faqs-manager')->name('faqs');

        // Features Management
        Volt::route('/features', 'admin.features.features-manager')->name('features');

        // Newsletter Management
        Volt::route('/newsletter', 'admin.newsletter.newsletter-manager')->name('newsletter');

        // Contact Management
        Volt::route('/contact', 'admin.contact.contact-manager')->name('contact');

        // Contact messages Management
        Volt::route('/contact-messages', 'admin.contact.contact-messages')
            ->name('contact-messages');

        // Notifications Management
        Volt::route('/notifications', 'admin.notifications.notifications-manager')
            ->name('notifications');


        // Settings Management (CMS Settings)
        Volt::route('/settings', 'admin.settings')
            ->name('settings');


    });

// ---> Super-Admin only
Route::middleware(['auth', 'verified', 'role:super-admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {

        Volt::route('/users', 'admin.users.users-manager')->name('users');
        Volt::route('/audit-logs', 'admin.logs.audit-logs')->name('audit-logs');
    });


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
