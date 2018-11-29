#!/bin/bash
docker run --rm -t \
    --volume "/${PWD}/php/src:/app/src" \
    --volume "/${PWD}/php/fragments:/app/fragments" \
    --volume "/${PWD}/php/vendor:/app/vendor" \
    -w "//app/" \
    php:7.2-cli \
    ./vendor/bin/phpcs --report-width=auto --standard=PSR1,PSR2 src/ fragments/ \

