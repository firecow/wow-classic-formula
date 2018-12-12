#!/bin/sh
docker run --rm -t \
    --volume "/${PWD}/php/:/app/" \
    -w "//app/" \
    php:7.2-cli \
    ./vendor/bin/ecs check --fix src/ routes/ \
