#!/bin/bash
git pull && composer install --no-dev && bin/console cache:clear --env=prod && bin/console assetic:dump --env=prod
