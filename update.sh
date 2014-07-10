#!/bin/bash
git pull && composer install && bin/console cache:clear --env=prod && bin/console assetic:dump && bin/console assetic:dump --env=prod
