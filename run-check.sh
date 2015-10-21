#!/usr/bin/env bash

# a shortcut for running all test commands

# use the vendor version instead
./vendor/bin/phpunit

# use the vendored phpcs
./vendor/bin/phpcs --standard=PSR2 ./src
