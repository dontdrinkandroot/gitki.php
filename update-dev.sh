#!/bin/bash
git pull && composer update && bin/console assetic:dump
