#!/bin/bash
docker run --rm -t \
    --volume "/${PWD}/php/easy-coding-standard.yml:/app/easy-coding-standard.yml" \
    --volume "/${PWD}/php/src:/app/src" \
    --volume "/${PWD}/php/routes:/app/routes" \
    --volume "/${PWD}/php/vendor:/app/vendor" \
    -w "//app/" \
    php:7.2-cli \
    ./vendor/bin/ecs check src/ routes/ \
