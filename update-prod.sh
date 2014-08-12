#!/bin/bash
(export SYMFONY_ENV="prod" && git pull && composer install --no-dev --optimize-autoloader && bin/console assetic:dump)
