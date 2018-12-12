#!/bin/sh
docker run --rm -t \
    --volume "/${PWD}/php/vendor:/app/vendor" \
    --volume "/${PWD}/php/composer.lock:/app/composer.lock" \
    --volume "/${PWD}/php/composer.json:/app/composer.json:ro" \
    --user $(id -u):$(id -g) \
    composer:1.7 update \
