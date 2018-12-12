#!/usr/bin/env bash
docker run --rm -t \
    --volume "/${PWD}/php/php.ini:/usr/local/etc/php/conf.d/php.ini" \
    --volume "/${PWD}/php:/php" \
    --volume "/${PWD}/data:/data" \
    --volume "/${PWD}/dumps:/dumps" \
    --network wow-classic-formula_default \
    -w "//php/" \
    nanoninja/php-fpm:7.2 php cli/refresh_locations.php \
