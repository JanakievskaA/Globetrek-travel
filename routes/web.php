<?php

use App\Http\Controllers\Account\AccountBookingController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/tours', [TourController::class, 'index'])->name('tours.index');
Route::get('/tours/{tour}', [TourController::class, 'show'])->name('tours.show');

Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination}', [DestinationController::class, 'show'])->name('destinations.show');

Route::post('/tours/{tour}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::post('/wishlist/sync', [WishlistController::class, 'sync'])->name('wishlist.sync');

Route::get('/tours/{tour}/book', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/tours/{tour}/book', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'show'])->name('bookings.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.store');
Route::post('/newsletter', [PageController::class, 'newsletter'])->name('newsletter.store');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/account/bookings', [AccountBookingController::class, 'index'])->name('account.bookings');

    // The notification bell, shared by the site header and the admin topbar.
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

/*
 * Managers run the catalogue and the day-to-day desk work. Three things stay
 * with admins: staff accounts (or a manager could promote themselves out of
 * every other restriction), the homepage, and deletes — an edit can be edited
 * back, a deleted tour takes its bookings and reviews with it.
 */
Route::middleware(['auth', 'staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Deleting is destructive, so it is lifted out of every resource below.
        Route::resource('tours', Admin\TourController::class)->except('show', 'destroy');
        Route::resource('destinations', Admin\DestinationController::class)->except('show', 'destroy');
        Route::resource('categories', Admin\CategoryController::class)->except('show', 'destroy');
        Route::resource('bookings', Admin\BookingController::class)->except('create', 'store', 'destroy');
        Route::resource('reviews', Admin\ReviewController::class)->except('create', 'store', 'destroy');

        // Quick status toggles used by the table row actions.
        Route::patch('bookings/{booking}/status', [Admin\BookingController::class, 'updateStatus'])
            ->name('bookings.status');
        Route::patch('reviews/{review}/status', [Admin\ReviewController::class, 'updateStatus'])
            ->name('reviews.status');

        // Image picker (JSON). Managers upload photos for their own tours.
        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');

        /*
         * Admins only.
         */
        Route::middleware('admin')->group(function () {
            // Page content: a tab per page, one editable screen per section of it.
            Route::redirect('home', 'admin/pages/home');
            // {page?} so the sidebar can link to the screen without naming a tab.
            Route::get('pages/{page?}', [Admin\PageSectionController::class, 'index'])->name('pages.index');
            Route::get('pages/{page}/{key}', [Admin\PageSectionController::class, 'edit'])->name('pages.edit');
            Route::put('pages/{page}/{key}', [Admin\PageSectionController::class, 'update'])->name('pages.update');
            Route::patch('pages/{page}/{key}/toggle', [Admin\PageSectionController::class, 'toggle'])
                ->name('pages.toggle');

            Route::resource('users', Admin\UserController::class);

            Route::delete('media/{medium}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
            Route::delete('tours/{tour}', [Admin\TourController::class, 'destroy'])->name('tours.destroy');
            Route::delete('destinations/{destination}', [Admin\DestinationController::class, 'destroy'])
                ->name('destinations.destroy');
            Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])
                ->name('categories.destroy');
            Route::delete('bookings/{booking}', [Admin\BookingController::class, 'destroy'])->name('bookings.destroy');
            Route::delete('reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
        });
    });
