#!/bin/sh

configure-tests

mysql -e "CREATE DATABASE wordpress_tests;" -uroot

php-$PHP_VERSION phpunit
