#!/bin/bash
(export SYMFONY_ENV="prod" && git pull && composer install && bin/console assetic:dump)
