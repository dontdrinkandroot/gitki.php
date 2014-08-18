#!/bin/bash
git pull && composer update && bin/console assetic:dump
lessc -x src/Net/Dontdrinkandroot/Gitki/BaseBundle/Resources/public/css/style.less > src/Net/Dontdrinkandroot/Gitki/BaseBundle/Resources/public/css/style.css

