#!/bin/bash
git pull && composer install && app/console cache:clear --env=prod && app/console assetic:dump && app/console assetic:dump --env=prod
