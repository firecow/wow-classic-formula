#!/bin/bash
docker run --rm -t \
    --volume "/${PWD}/php/src:/app/src:ro" \
    --volume "/${PWD}/php/cli:/app/cli:ro" \
    --volume "/${PWD}/php/routes:/app/routes:ro" \
    --volume "/${PWD}/php/vendor:/app/vendor:ro" \
    -w "//app/" \
    php:7.2-cli \
    bash -c "./vendor/bin/phpstan analyse --no-progress -c vendor/phpstan/phpstan-strict-rules/rules.neon -l max src/ routes/ cli/" \
