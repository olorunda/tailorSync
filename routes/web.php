<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
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
