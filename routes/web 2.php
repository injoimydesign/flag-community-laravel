<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('how-it-works');
Route::get('/service-areas', [HomeController::class, 'serviceAreas'])->name('service-areas');
Route::post('/contact', [HomeController::class, 'contact'])->name('contact');

// AJAX endpoints
Route::post('/check-service-area', [HomeController::class, 'checkServiceArea'])->name('check-service-area');
Route::get('/flag-products', [HomeController::class, 'getFlagProducts'])->name('flag-products');
Route::post('/calculate-pricing', [HomeController::class, 'calculatePricing'])->name('calculate-pricing');

// Authentication routes
Auth::routes();

// Checkout routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    Route::get('/success', [CheckoutController::class, 'success'])->name('success');
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
    Route::get('/customer-portal', [CheckoutController::class, 'customerPortal'])
        ->name('customer-portal')
        ->middleware('auth');
});

// Stripe webhooks (must be outside auth middleware)
Route::post('/stripe/webhook', [WebhookController::class, 'handleStripe'])->name('stripe.webhook');

// Customer dashboard routes
Route::middleware(['auth', 'customer'])->prefix('dashboard')->name('customer.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/subscription', [DashboardController::class, 'subscription'])->name('subscription');
    Route::get('/placements', [DashboardController::class, 'placements'])->name('placements');
    Route::get('/account', [DashboardController::class, 'account'])->name('account');
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');

    // AJAX routes
    Route::post('/update-instructions', [DashboardController::class, 'updateInstructions'])->name('update-instructions');
    Route::post('/cancel-subscription', [DashboardController::class, 'cancelSubscription'])->name('cancel-subscription');
    Route::post('/renew-subscription', [DashboardController::class, 'renewSubscription'])->name('renew-subscription');
    Route::post('/update-account', [DashboardController::class, 'updateAccount'])->name('update-account');
    Route::post('/change-password', [DashboardController::class, 'changePassword'])->name('change-password');
    Route::post('/update-notifications', [DashboardController::class, 'updateNotifications'])->name('update-notifications');
    Route::get('/calendar-data', [DashboardController::class, 'getCalendarData'])->name('calendar-data');
    Route::get('/invoice/{subscription}', [DashboardController::class, 'downloadInvoice'])->name('download-invoice');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/metrics', [AdminDashboardController::class, 'getMetrics'])->name('metrics');
    Route::get('/calendar-data', [AdminDashboardController::class, 'getCalendarData'])->name('calendar-data');
    Route::post('/quick-action', [AdminDashboardController::class, 'quickAction'])->name('quick-action');

    // Service Areas
    Route::resource('service-areas', 'Admin\ServiceAreaController');

    // Flag Management
    Route::resource('flag-types', 'Admin\FlagTypeController');
    Route::resource('flag-sizes', 'Admin\FlagSizeController');

    // Flag Products
    Route::resource('flag-products', 'Admin\FlagProductController');
    Route::get('/flag-products/export', 'Admin\FlagProductController@export')->name('flag-products.export');
    Route::post('/flag-products/bulk-update-inventory', 'Admin\FlagProductController@bulkUpdateInventory')->name('flag-products.bulk-update-inventory');
    Route::post('/flag-products/{flagProduct}/adjust-inventory', 'Admin\FlagProductController@adjustInventory')->name('flag-products.adjust-inventory');
    Route::post('/flag-products/{flagProduct}/toggle-active', 'Admin\FlagProductController@toggleActive')->name('flag-products.toggle-active');
    Route::get('/flag-products/pricing-suggestions', 'Admin\FlagProductController@getPricingSuggestions')->name('flag-products.pricing-suggestions');
    Route::get('/flag-products/{flagProduct}/inventory-history', 'Admin\FlagProductController@inventoryHistory')->name('flag-products.inventory-history');
    Route::post('/flag-products/{flagProduct}/duplicate', 'Admin\FlagProductController@duplicate')->name('flag-products.duplicate');

    // Holidays
    Route::resource('holidays', 'Admin\HolidayController');

    // Subscriptions
    Route::resource('subscriptions', 'Admin\SubscriptionController');
    Route::post('/subscriptions/{subscription}/cancel', 'Admin\SubscriptionController@cancel')->name('subscriptions.cancel');
    Route::post('/subscriptions/{subscription}/reactivate', 'Admin\SubscriptionController@reactivate')->name('subscriptions.reactivate');
    Route::get('/subscriptions/export', 'Admin\SubscriptionController@export')->name('subscriptions.export');

    // Flag Placements
    Route::get('/placements', 'Admin\PlacementController@index')->name('placements.index');
    Route::get('/placements/calendar', 'Admin\PlacementController@calendar')->name('placements.calendar');
    Route::get('/placements/export', 'Admin\PlacementController@export')->name('placements.export');
    Route::get('/placements/{placement}', 'Admin\PlacementController@show')->name('placements.show');
    Route::post('/placements/{placement}/place', 'Admin\PlacementController@place')->name('placements.place');
    Route::post('/placements/{placement}/remove', 'Admin\PlacementController@remove')->name('placements.remove');
    Route::post('/placements/{placement}/skip', 'Admin\PlacementController@skip')->name('placements.skip');
    Route::post('/placements/bulk-update', 'Admin\PlacementController@bulkUpdate')->name('placements.bulk-update');
    Route::post('/placements/optimize-routes', 'Admin\PlacementController@optimizeRoutes')->name('placements.optimize-routes');
    Route::post('/placements/send-reminders', 'Admin\PlacementController@sendReminders')->name('placements.send-reminders');

    // Routes
    Route::resource('routes', 'Admin\RouteController');
    Route::post('/routes/generate', 'Admin\RouteController@generate')->name('routes.generate');
    Route::post('/routes/{route}/start', 'Admin\RouteController@start')->name('routes.start');
    Route::post('/routes/{route}/complete', 'Admin\RouteController@complete')->name('routes.complete');
    Route::post('/routes/{route}/assign', 'Admin\RouteController@assign')->name('routes.assign');

    // Customers
    Route::resource('customers', 'Admin\CustomerController');
    Route::get('/customers/export', 'Admin\CustomerController@export')->name('customers.export');
    Route::post('/customers/{customer}/toggle-status', 'Admin\CustomerController@toggleStatus')->name('customers.toggle-status');

    // Potential Customers
    Route::resource('potential-customers', 'Admin\PotentialCustomerController');
    Route::post('/potential-customers/{potentialCustomer}/contact', 'Admin\PotentialCustomerController@contact')->name('potential-customers.contact');
    Route::post('/potential-customers/{potentialCustomer}/convert', 'Admin\PotentialCustomerController@convert')->name('potential-customers.convert');
    Route::post('/potential-customers/bulk-notify', 'Admin\PotentialCustomerController@bulkNotify')->name('potential-customers.bulk-notify');
    Route::get('/potential-customers/export', 'Admin\PotentialCustomerController@export')->name('potential-customers.export');

    // Notifications
    Route::get('/notifications', 'Admin\NotificationController@index')->name('notifications.index');
    Route::post('/notifications/send', 'Admin\NotificationController@send')->name('notifications.send');
    Route::get('/notifications/templates', 'Admin\NotificationController@templates')->name('notifications.templates');
    Route::post('/notifications/bulk-send', 'Admin\NotificationController@bulkSend')->name('notifications.bulk-send');

    // Reports
    Route::get('/reports', 'Admin\ReportController@index')->name('reports.index');
    Route::get('/reports/revenue', 'Admin\ReportController@revenue')->name('reports.revenue');
    Route::get('/reports/placements', 'Admin\ReportController@placements')->name('reports.placements');
    Route::get('/reports/customers', 'Admin\ReportController@customers')->name('reports.customers');
    Route::get('/reports/inventory', 'Admin\ReportController@inventory')->name('reports.inventory');
    Route::get('/reports/performance', 'Admin\ReportController@performance')->name('reports.performance');

    // Settings
    Route::get('/settings', 'Admin\SettingsController@index')->name('settings.index');
    Route::post('/settings', 'Admin\SettingsController@update')->name('settings.update');
    Route::get('/settings/stripe', 'Admin\SettingsController@stripe')->name('settings.stripe');
    Route::post('/settings/stripe/sync', 'Admin\SettingsController@syncStripe')->name('settings.stripe-sync');
});
