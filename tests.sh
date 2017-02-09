#!/bin/sh

# Make sure we have the correct credentials on the WordPress config file
configure-tests

# Wait for MySQL to be ready
until mysqladmin -h db -u wordpress -pwordpress ping | grep "mysqld is alive" -C 99999; do echo '.'; sleep 10; done

# Try to create the database
mysqladmin -h db -u root -pwordpress create wordpress || true

# Start the tests
bash -c "phpunit-php-${PHP_VERSION:-7.0}"
