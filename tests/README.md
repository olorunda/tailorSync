# Tests for TailorFit

This directory contains tests for the TailorFit application. The tests are organized into two main categories:

1. **Feature Tests**: These test the application's features and functionality from an end-user perspective.
2. **Unit Tests**: These test individual components of the application in isolation.

## Running Tests

To run all tests, use the following command:

```bash
php artisan test
```

To run a specific test file, use:

```bash
php artisan test --filter=ClientTest
```

To run tests in a specific directory, use:

```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## Test Coverage

The following modules have been tested:

### Feature Tests

1. **Client Module**
   - Display clients page
   - Create clients
   - View client details
   - Update clients
   - Validate client data
   - Prevent unauthorized access

2. **Task Module**
   - Display tasks page
   - Create tasks
   - Update tasks
   - Validate task data
   - Assign tasks to team members
   - Link tasks to orders
   - Prevent unauthorized access

3. **Appointment Module**
   - Display appointments page
   - Create appointments
   - Update appointments
   - Validate appointment data
   - Link appointments to clients
   - Handle past appointments
   - Prevent unauthorized access

4. **Expense Module**
   - Display expenses page
   - Create expenses
   - Update expenses
   - Validate expense data
   - Validate expense categories
   - Prevent unauthorized access

5. **Payment Module**
   - Display payments page
   - Create payments
   - Validate payment data
   - Link payments to clients
   - Link payments to invoices
   - Validate payment methods
   - Validate payment statuses

6. **Team Module**
   - Display team page
   - Create team members
   - View team member details
   - Update team members
   - Validate team member data
   - Update passwords
   - Validate team member roles
   - Enforce role-based permissions

### Unit Tests

1. **Client Model**
   - Belongs to user relationship
   - Has many measurements relationship
   - Has latest measurement relationship
   - Has many orders relationship
   - Has many appointments relationship
   - Has many invoices relationship
   - Has many payments relationship
   - Has many messages relationship

2. **Task Model**
   - Belongs to user relationship
   - Belongs to team member relationship
   - Belongs to order relationship
   - Status scope
   - Priority scope
   - Overdue scope
   - isOverdue method
   - complete method

## Adding New Tests

When adding new tests, follow these guidelines:

1. **Feature Tests**: Place in the `Feature` directory and name with the suffix `Test.php`.
2. **Unit Tests**: Place in the `Unit` directory and name with the suffix `Test.php`.
3. Use the `RefreshDatabase` trait for tests that modify the database.
4. Test both happy paths and edge cases.
5. For Livewire components, use `Volt::test()` to test component functionality.

## Test Data

Tests use factories to generate test data. The factories are defined in the `database/factories` directory.
