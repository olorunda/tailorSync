<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Offline page for PWA
Route::view('offline', 'offline')->name('offline');

// Public order viewing route
Route::get('orders/public/{hash}', [\App\Http\Controllers\PublicOrderController::class, 'show'])->name('orders.public');

// Public appointment booking routes
Route::get('appointments/public/{slug}', [\App\Http\Controllers\PublicAppointmentController::class, 'show'])->name('appointments.public.booking');
Route::post('appointments/public/{slug}', [\App\Http\Controllers\PublicAppointmentController::class, 'store'])->name('appointments.public.store');
Route::get('appointments/public/{slug}/confirmation/{appointment}', [\App\Http\Controllers\PublicAppointmentController::class, 'confirmation'])->name('appointments.public.confirmation');
Route::get('appointments/public/{slug}/time-slots', [\App\Http\Controllers\PublicAppointmentController::class, 'getAvailableTimeSlots'])->name('appointments.public.time-slots');

// Public business profile route
Route::get('business/public/{slug}', \App\Livewire\PublicBusinessProfile::class)->name('business.public');

// Store routes
Route::prefix('store')->name('store.')->middleware(['auth', \App\Http\Middleware\CheckOnboardingStatus::class])->group(function () {
    Route::middleware(['permission:manage_store'])->group(function () {
        Route::get('settings', [\App\Http\Controllers\StoreSettingsController::class, 'index'])->name('settings');
        Route::post('settings', [\App\Http\Controllers\StoreSettingsController::class, 'update'])->name('settings.update');
        Route::get('preview', [\App\Http\Controllers\StoreSettingsController::class, 'preview'])->name('preview');
    });

    // Product management routes
    Route::middleware(['permission:view_store_products'])->group(function () {
        Route::get('products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products.index');
    });

    Route::middleware(['permission:create_store_products'])->group(function () {
        Route::get('products/create', [\App\Http\Controllers\ProductController::class, 'create'])->name('products.create');
        Route::post('products', [\App\Http\Controllers\ProductController::class, 'store'])->name('products.store');
        Volt::route('products/import', 'store.products.import')->name('products.import');
    });

    Route::middleware(['permission:view_store_products'])->group(function () {
        Route::get('products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])->name('products.show');
    });

    Route::middleware(['permission:edit_store_products'])->group(function () {
        Route::get('products/{product}/edit', [\App\Http\Controllers\ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
        Route::patch('products/{product}', [\App\Http\Controllers\ProductController::class, 'update']);
    });

    Route::middleware(['permission:delete_store_products'])->group(function () {
        Route::delete('products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
    });

    // Store orders management
    Route::middleware(['permission:view_store_orders'])->group(function () {
        Route::get('orders', [\App\Http\Controllers\StoreOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [\App\Http\Controllers\StoreOrderController::class, 'show'])->name('orders.show');
    });

    Route::middleware(['permission:create_store_orders'])->group(function () {
        // Add routes for creating store orders if needed
    });

    Route::middleware(['permission:edit_store_orders|manage_store_orders'])->group(function () {
        Route::get('orders/{order}/edit', [\App\Http\Controllers\StoreOrderController::class, 'edit'])->name('orders.edit');
        Route::put('orders/{order}', [\App\Http\Controllers\StoreOrderController::class, 'update'])->name('orders.update');
        Route::post('orders/{order}/mark-as-paid', [\App\Http\Controllers\StoreOrderController::class, 'markAsPaid'])->name('orders.mark-as-paid');
        Route::post('orders/{order}/cancel', [\App\Http\Controllers\StoreOrderController::class, 'cancelOrder'])->name('orders.cancel');
    });

    Route::middleware(['permission:delete_store_orders|manage_store_orders'])->group(function () {
        // Add routes for deleting store orders if needed
    });

    // Store purchases management
    Route::middleware(['permission:view_store_purchases'])->group(function () {
        Route::get('purchases', [\App\Http\Controllers\StorePurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/{purchase}', [\App\Http\Controllers\StorePurchaseController::class, 'show'])->name('purchases.show');
    });

    Route::middleware(['permission:create_store_purchases'])->group(function () {
        // Add routes for creating store purchases if needed
    });

    Route::middleware(['permission:edit_store_purchases|manage_store_purchases'])->group(function () {
        Route::get('purchases/{purchase}/edit', [\App\Http\Controllers\StorePurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('purchases/{purchase}', [\App\Http\Controllers\StorePurchaseController::class, 'update'])->name('purchases.update');
    });

    Route::middleware(['permission:delete_store_purchases|manage_store_purchases'])->group(function () {
        // Add routes for deleting store purchases if needed
    });
});

// Public storefront routes
Route::prefix('shop/{slug}')->name('storefront.')->group(function () {
    Route::get('/', [\App\Http\Controllers\StorefrontController::class, 'index'])->name('index');
    Route::get('/products', [\App\Http\Controllers\StorefrontController::class, 'products'])->name('products');
    Route::get('/product/{product}', [\App\Http\Controllers\StorefrontController::class, 'product'])->name('product');
    Route::get('/cart', [\App\Http\Controllers\StorefrontController::class, 'cart'])->name('cart');
    Route::post('/cart/add', [\App\Http\Controllers\StorefrontController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update', [\App\Http\Controllers\StorefrontController::class, 'updateCart'])->name('cart.update');
    Route::get('/cart/remove/{cartItem}', [\App\Http\Controllers\StorefrontController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/checkout', [\App\Http\Controllers\StorefrontController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [\App\Http\Controllers\StorefrontController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/order/confirmation/{order}', [\App\Http\Controllers\StorefrontController::class, 'orderConfirmation'])->name('order.confirmation');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckOnboardingStatus::class])
    ->name('dashboard');

// Onboarding Routes
Route::middleware(['auth'])->group(function () {
    Volt::route('onboarding', 'onboarding.wizard')->name('onboarding.wizard');
});

Route::middleware(['auth', \App\Http\Middleware\CheckOnboardingStatus::class])->group(function () {
    // Notifications Routes
    Volt::route('notifications', 'notifications.index')->name('notifications.index');

    // Settings Routes
    Route::redirect('settings', 'settings/profile');

    // Profile settings
    Route::middleware(['permission:view_profile'])->group(function () {
        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    });

    // Password settings
    Route::middleware(['permission:change_password'])->group(function () {
        Volt::route('settings/password', 'settings.password')->name('settings.password');
    });

    // Appearance settings
    Route::middleware(['permission:manage_appearance'])->group(function () {
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    });

    // Roles & Permissions settings
    Route::middleware(['permission:manage_roles_permissions'])->group(function () {
        Volt::route('settings/roles', 'settings.roles')->name('settings.roles');
        Volt::route('settings/permissions', 'settings.permissions')->name('settings.permissions');
    });

    // Business settings (only for parent accounts)
    Volt::route('settings/business', 'settings.business')->name('settings.business');

    // Business profile page (only for parent accounts)
    Route::get('settings/business-profile', \App\Livewire\Settings\BusinessProfile::class)->name('settings.business-profile');

    // Public booking settings (only for parent accounts)
    Volt::route('settings/public-booking', 'settings.public-booking')->name('settings.public-booking');

    // Store settings (only for parent accounts)
    Route::middleware(['permission:manage_store'])->group(function () {
        Route::get('settings/store', \App\Livewire\Settings\Store::class)->name('settings.store');
        Route::get('settings/store/preview', \App\Livewire\Settings\StorePreview::class)->name('settings.store.preview');
    });

    // Measurement settings
    Route::middleware(['permission:manage_measurements'])->group(function () {
        Route::get('settings/measurements', \App\Livewire\Settings\Measurements::class)->name('settings.measurements');
    });

    // Client Routes
    Route::middleware(['permission:view_clients'])->group(function () {
        Volt::route('clients', 'clients.index')->name('clients.index');
    });

    Route::middleware(['permission:create_clients'])->group(function () {
        Volt::route('clients/create', 'clients.create')->name('clients.create');
        Volt::route('clients/import', 'clients.import')->name('clients.import');
    });

    Route::middleware(['permission:view_clients'])->group(function () {
        Volt::route('clients/{client}', 'clients.show')->name('clients.show');
    });

    Route::middleware(['permission:edit_clients'])->group(function () {
        Volt::route('clients/{client}/edit', 'clients.edit')->name('clients.edit');
    });

    // Measurement Routes
    Route::middleware(['permission:create_measurements'])->group(function () {
        Volt::route('clients/{client}/measurements/create', 'measurements.create')->name('measurements.create');
    });

    Route::middleware(['permission:view_measurements'])->group(function () {
        Route::get('clients/{client}/measurements/{measurement}', \App\Livewire\Measurements\View::class)->name('measurements.view');
    });

    Route::middleware(['permission:edit_measurements'])->group(function () {
        Volt::route('clients/{client}/measurements/{measurement}/edit', 'measurements.edit')->name('measurements.edit');
    });

    // Order Routes
    Route::middleware(['permission:view_orders'])->group(function () {
        Volt::route('orders', 'orders.index')->name('orders.index');
    });

    Route::middleware(['permission:create_orders'])->group(function () {
        Volt::route('orders/create', 'orders.create')->name('orders.create');
    });

    Route::middleware(['permission:view_orders'])->group(function () {
        Volt::route('orders/{order}', 'orders.show')->name('orders.show');
    });

    Route::middleware(['permission:edit_orders'])->group(function () {
        Volt::route('orders/{order}/edit', 'orders.edit')->name('orders.edit');
    });

    // Design Routes
    Route::middleware(['permission:view_designs'])->group(function () {
        Volt::route('designs', 'designs.index')->name('designs.index');
    });

    Route::middleware(['permission:create_designs'])->group(function () {
        Volt::route('designs/create', 'designs.create')->name('designs.create');
    });

    Route::middleware(['permission:view_designs'])->group(function () {
        Volt::route('designs/{design}', 'designs.show')->name('designs.show');
    });

    Route::middleware(['permission:edit_designs'])->group(function () {
        Volt::route('designs/{design}/edit', 'designs.edit')->name('designs.edit');
    });

    // Inventory Routes
    Route::middleware(['permission:view_inventory'])->group(function () {
        Volt::route('inventory', 'inventory.index')->name('inventory.index');
    });

    Route::middleware(['permission:create_inventory'])->group(function () {
        Volt::route('inventory/create', 'inventory.create')->name('inventory.create');
        Volt::route('inventory/import', 'inventory.import')->name('inventory.import');
    });

    Route::middleware(['permission:view_inventory'])->group(function () {
        Volt::route('inventory/{inventoryItem}', 'inventory.show')->name('inventory.show');
    });

    Route::middleware(['permission:edit_inventory'])->group(function () {
        Volt::route('inventory/{inventoryItem}/edit', 'inventory.edit')->name('inventory.edit');
    });

    // Appointment Routes
    Route::middleware(['permission:view_appointments'])->group(function () {
        Volt::route('appointments', 'appointments.index')->name('appointments.index');
    });

    Route::middleware(['permission:create_appointments'])->group(function () {
        Volt::route('appointments/create', 'appointments.create')->name('appointments.create');
    });

    Route::middleware(['permission:view_appointments'])->group(function () {
        Volt::route('appointments/{appointment}', 'appointments.show')->name('appointments.show');
    });

    Route::middleware(['permission:edit_appointments'])->group(function () {
        Volt::route('appointments/{appointment}/edit', 'appointments.edit')->name('appointments.edit');
    });

    // Message Routes
    Route::middleware(['permission:view_messages'])->group(function () {
        Volt::route('messages', 'messages.index')->name('messages.index');
    });

    Route::middleware(['permission:send_messages'])->group(function () {
        Volt::route('messages/create', 'messages.create')->name('messages.create');
    });

    // Finance Routes - Invoices
    Route::middleware(['permission:view_invoices'])->group(function () {
        Volt::route('invoices', 'invoices.index')->name('invoices.index');
    });

    Route::middleware(['permission:create_invoices'])->group(function () {
        Volt::route('invoices/create', 'invoices.create')->name('invoices.create');
    });

    Route::middleware(['permission:view_invoices'])->group(function () {
        Volt::route('invoices/{invoice}', 'invoices.show')->name('invoices.show');
    });

    Route::middleware(['permission:edit_invoices'])->group(function () {
        Volt::route('invoices/{invoice}/edit', 'invoices.edit')->name('invoices.edit');
    });

    // Finance Routes - Payments
    Route::middleware(['permission:view_payments'])->group(function () {
        Volt::route('payments', 'payments.index')->name('payments.index');
    });

    Route::middleware(['permission:create_payments'])->group(function () {
        Volt::route('payments/create', 'payments.create')->name('payments.create');
    });

    Route::middleware(['permission:view_payments'])->group(function () {
        Volt::route('payments/{payment}', 'payments.show')->name('payments.show');
    });

    Route::middleware(['permission:edit_payments'])->group(function () {
        Volt::route('payments/{payment}/edit', 'payments.edit')->name('payments.edit');
    });

    // Finance Routes - Expenses
    Route::middleware(['permission:view_expenses'])->group(function () {
        Volt::route('expenses', 'expenses.index')->name('expenses.index');
    });

    Route::middleware(['permission:create_expenses'])->group(function () {
        Volt::route('expenses/create', 'expenses.create')->name('expenses.create');
    });

    Route::middleware(['permission:view_expenses'])->group(function () {
        Volt::route('expenses/{expense}', 'expenses.show')->name('expenses.show');
    });

    Route::middleware(['permission:edit_expenses'])->group(function () {
        Volt::route('expenses/{expense}/edit', 'expenses.edit')->name('expenses.edit');
    });

    // Team Routes
    Route::middleware(['permission:view_team'])->group(function () {
        Volt::route('team', 'team.index')->name('team.index');
    });

    Route::middleware(['permission:create_team'])->group(function () {
        Volt::route('team/create', 'team.create')->name('team.create');
    });

    Route::middleware(['permission:view_team'])->group(function () {
        Volt::route('team/{teamMember}', 'team.show')->name('team.show');
    });

    Route::middleware(['permission:edit_team'])->group(function () {
        Volt::route('team/{teamMember}/edit', 'team.edit')->name('team.edit');
    });

    // Task Routes
    Route::middleware(['permission:view_tasks'])->group(function () {
        Volt::route('tasks', 'tasks.index')->name('tasks.index');
    });

    Route::middleware(['permission:create_tasks'])->group(function () {
        Volt::route('tasks/create', 'tasks.create')->name('tasks.create');
    });

    Route::middleware(['permission:view_tasks'])->group(function () {
        Volt::route('tasks/{task}', 'tasks.show')->name('tasks.show');
    });

    Route::middleware(['permission:edit_tasks'])->group(function () {
        Volt::route('tasks/{task}/edit', 'tasks.edit')->name('tasks.edit');
    });
});

require __DIR__.'/auth.php';
