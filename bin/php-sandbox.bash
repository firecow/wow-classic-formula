#!/usr/bin/env bash
docker run --rm -t \
    --volume "/${PWD}/php/php.ini:/usr/local/etc/php/conf.d/php.ini" \
    --volume "/${PWD}/php:/php" \
    --network wow-classic-formula_default \
    -w "//php/" \
    nanoninja/php-fpm:7.2 php sandbox.php \
