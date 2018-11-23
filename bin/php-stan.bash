#!/bin/bash
docker run --rm -t \
    --volume "/${PWD}/php/src:/app/src:ro" \
    --volume "/${PWD}/php/routes:/app/routes:ro" \
    --volume "/${PWD}/php/vendor:/app/vendor:ro" \
    -w "//app/" \
    php:7.2-cli \
    bash -c "./vendor/bin/phpstan analyse --no-progress -l max src/ routes/" \

# TODO: Add -c vendor/phpstan/phpstan-strict-rules/rules.neon