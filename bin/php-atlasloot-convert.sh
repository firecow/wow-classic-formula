#!/usr/bin/env bash
docker run --rm -t \
    --volume "/${PWD}/php/php.ini:/usr/local/etc/php/conf.d/php.ini" \
    --volume "/${PWD}/php:/php" \
    --volume "/${PWD}/dumps:/dumps" \
    --network wow-classic-formula_default \
    --user $(id -u):$(id -g) \
    -w "//php/" \
    nanoninja/php-fpm:7.2 php cli/atlasloot_convert.php \
