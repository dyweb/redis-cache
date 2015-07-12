#!/usr/bin/env bash

# a shortcut for running all test commands

# TODO:this require phpunit install globaly, should use the vendor version instead
./vendor/bin/phpunit

# phpcs

./vendor/bin/phpcs --standard=PSR2 ./src