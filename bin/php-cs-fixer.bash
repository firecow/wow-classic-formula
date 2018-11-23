#!/bin/bash
docker run --rm -t \
    --volume "/${PWD}/php/src:/app/src" \
    --volume "/${PWD}/php/vendor:/app/vendor" \
    -w "//app/" \
    php:7.2-cli \
    ./vendor/bin/php-cs-fixer fix --using-cache=no src/ \

docker run --rm -t \
    --volume "/${PWD}/php/fragments:/app/routes" \
    --volume "/${PWD}/php/vendor:/app/vendor" \
    -w "//app/" \
    php:7.2-cli \
    ./vendor/bin/php-cs-fixer fix --using-cache=no routes/ \
