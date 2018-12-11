#!/bin/bash
docker run --rm -t \
    --volume "/${PWD}/php/:/app/" \
    -w "//app/" \
    php:7.2-cli \
    bash -c "./vendor/bin/phpstan analyse --no-progress -c vendor/phpstan/phpstan-strict-rules/rules.neon -l max src/ routes/ cli/" \
