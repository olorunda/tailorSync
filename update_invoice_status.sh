#!/bin/bash

# Run the migration to add 'pending' to the enum
php artisan migrate

# Run the command to update the invoice with ID 3
php artisan invoice:update-status

echo "Invoice status update completed."
