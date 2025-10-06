<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ServiceAreaController;
use App\Http\Controllers\Admin\PotentialCustomerController;
use App\Http\Controllers\Admin\FlagTypeController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\FlagProductController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\PlacementController;

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
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/metrics', [AdminDashboardController::class, 'getMetrics'])->name('metrics');
    Route::get('/calendar-data', [AdminDashboardController::class, 'getCalendarData'])->name('calendar-data');
    Route::post('/quick-action', [AdminDashboardController::class, 'quickAction'])->name('quick-action');

    // Service Areas
    Route::resource('service-areas', ServiceAreaController::class);

    // Flag Management
    Route::resource('flag-types', FlagTypeController::class);
    Route::post('/flag-types/{flagType}/toggle-active', [FlagTypeController::class, 'toggleActive'])->name('flag-types.toggle-active');
    Route::post('/flag-types/{flagType}/duplicate', [FlagTypeController::class, 'duplicate'])->name('flag-types.duplicate');

    // Flag Products
    Route::resource('flag-products', FlagProductController::class);
    Route::get('/flag-products/export', [FlagProductController::class, 'export'])->name('flag-products.export');
    Route::post('/flag-products/bulk-update-inventory', [FlagProductController::class, 'bulkUpdateInventory'])->name('flag-products.bulk-update-inventory');
    Route::post('/flag-products/{flagProduct}/adjust-inventory', [FlagProductController::class, 'adjustInventory'])->name('flag-products.adjust-inventory');
    Route::post('/flag-products/{flagProduct}/toggle-active', [FlagProductController::class, 'toggleActive'])->name('flag-products.toggle-active');
    Route::get('/flag-products/pricing-suggestions', [FlagProductController::class, 'getPricingSuggestions'])->name('flag-products.pricing-suggestions');
    Route::get('/flag-products/{flagProduct}/inventory-history', [FlagProductController::class, 'inventoryHistory'])->name('flag-products.inventory-history');
    Route::post('/flag-products/{flagProduct}/duplicate', [FlagProductController::class, 'duplicate'])->name('flag-products.duplicate');

    // Flag Placements
Route::get('/placements', [PlacementController::class, 'index'])->name('placements.index');
    Route::get('/placements/create', [App\Http\Controllers\Admin\PlacementController::class, 'create'])->name('placements.create');
Route::get('/placements/calendar', [PlacementController::class, 'calendar'])->name('placements.calendar');
Route::get('/placements/calendar-data', [PlacementController::class, 'getCalendarData'])->name('placements.calendar-data');
Route::get('/placements/export', [PlacementController::class, 'export'])->name('placements.export');
    Route::post('/placements', [App\Http\Controllers\Admin\PlacementController::class, 'store'])->name('placements.store');
Route::get('/placements/{placement}', [PlacementController::class, 'show'])->name('placements.show');
Route::post('/placements/{placement}/place', [PlacementController::class, 'place'])->name('placements.place');
Route::post('/placements/{placement}/remove', [PlacementController::class, 'remove'])->name('placements.remove');
Route::post('/placements/{placement}/skip', [PlacementController::class, 'skip'])->name('placements.skip');
Route::post('/placements/bulk-update', [PlacementController::class, 'bulkUpdate'])->name('placements.bulk-update');
Route::post('/placements/send-reminders', [PlacementController::class, 'sendReminders'])->name('placements.send-reminders');

    // Holidays
    Route::resource('holidays', HolidayController::class);
    Route::post('/holidays/{holiday}/toggle-active', [HolidayController::class, 'toggleActive'])->name('holidays.toggle-active');
    Route::post('/holidays/{holiday}/generate-placements', [HolidayController::class, 'generatePlacements'])->name('holidays.generate-placements');
    Route::get('/holidays/export', [HolidayController::class, 'export'])->name('holidays.export');

    // Subscriptions
    Route::resource('subscriptions', SubscriptionController::class);
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
    Route::get('/subscriptions/export', [SubscriptionController::class, 'export'])->name('subscriptions.export');
    Route::get('/subscriptions/metrics', [SubscriptionController::class, 'getMetrics'])->name('subscriptions.metrics');

    // Subscription routes
    Route::get('/subscriptions', [App\Http\Controllers\Admin\SubscriptionController::class, 'index'])
        ->name('subscriptions.index');

    Route::get('/subscriptions/create', [App\Http\Controllers\Admin\SubscriptionController::class, 'create'])
        ->name('subscriptions.create');

    Route::post('/subscriptions', [App\Http\Controllers\Admin\SubscriptionController::class, 'store'])
        ->name('subscriptions.store');

    Route::get('/subscriptions/{subscription}', [App\Http\Controllers\Admin\SubscriptionController::class, 'show'])
        ->name('subscriptions.show');

    Route::get('/subscriptions/{subscription}/edit', [App\Http\Controllers\Admin\SubscriptionController::class, 'edit'])
        ->name('subscriptions.edit');

    Route::put('/subscriptions/{subscription}', [App\Http\Controllers\Admin\SubscriptionController::class, 'update'])
        ->name('subscriptions.update');

    Route::delete('/subscriptions/{subscription}', [App\Http\Controllers\Admin\SubscriptionController::class, 'destroy'])
        ->name('subscriptions.destroy');

    Route::get('/subscriptions/export', [App\Http\Controllers\Admin\SubscriptionController::class, 'export'])
        ->name('subscriptions.export');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::post('/customers/{customer}/send-notification', [CustomerController::class, 'sendNotification'])->name('customers.send-notification');
    Route::get('/customers/metrics', [CustomerController::class, 'getMetrics'])->name('customers.metrics');

    // Potential Customers
    Route::resource('potential-customers', PotentialCustomerController::class);
    Route::post('/potential-customers/{potentialCustomer}/contact', [PotentialCustomerController::class, 'contact'])->name('potential-customers.contact');
    Route::post('/potential-customers/{potentialCustomer}/convert', [PotentialCustomerController::class, 'convert'])->name('potential-customers.convert');
    Route::post('/potential-customers/bulk-notify', [PotentialCustomerController::class, 'bulkNotify'])->name('potential-customers.bulk-notify');
    Route::get('/potential-customers/export', [PotentialCustomerController::class, 'export'])->name('potential-customers.export');

    // Routes
    Route::resource('routes', RouteController::class);
    Route::post('/routes/generate', [RouteController::class, 'generate'])->name('routes.generate');
    Route::post('/routes/{route}/start', [RouteController::class, 'start'])->name('routes.start');
    Route::post('/routes/{route}/complete', [RouteController::class, 'complete'])->name('routes.complete');
    Route::post('/routes/{route}/assign', [RouteController::class, 'assign'])->name('routes.assign');
    Route::get('/routes/export', [RouteController::class, 'export'])->name('routes.export');
    Route::get('/routes/metrics', [RouteController::class, 'getMetrics'])->name('routes.metrics');

    // Notifications
    Route::resource('notifications', NotificationController::class);
    Route::post('/notifications/{notification}/resend', [NotificationController::class, 'resend'])->name('notifications.resend');
    Route::post('/notifications/process-scheduled', [NotificationController::class, 'processScheduled'])->name('notifications.process-scheduled');
    Route::get('/notifications/export', [NotificationController::class, 'export'])->name('notifications.export');
    Route::get('/notifications/metrics', [NotificationController::class, 'getMetrics'])->name('notifications.metrics');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/revenue', [ReportsController::class, 'revenue'])->name('revenue');
        Route::get('/customers', [ReportsController::class, 'customers'])->name('customers');
        Route::get('/operations', [ReportsController::class, 'operations'])->name('operations');
        Route::get('/marketing', [ReportsController::class, 'marketing'])->name('marketing');
        Route::get('/export', [ReportsController::class, 'export'])->name('export');
        Route::get('/financial-metrics', [ReportsController::class, 'getFinancialMetrics'])->name('financial-metrics');
    });
});
