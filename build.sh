#!/bin/bash

# Install dependencies
cd backend
composer install --no-dev --optimize-autoloader

# Set proper permissions
chmod -R 755 .

# Run database migrations if needed
# php migrate.php

echo "Build completed successfully!"
