#!/bin/bash
(export SYMFONY_ENV="prod" && git pull && composer install --no-dev --optimize-autoloader && bin/console assetic:dump)
lessc -x src/Net/Dontdrinkandroot/Gitki/BaseBundle/Resources/public/css/style.less > src/Net/Dontdrinkandroot/Gitki/BaseBundle/Resources/public/css/style.css

