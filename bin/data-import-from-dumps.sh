#!/bin/sh
docker run --rm -t \
    --network wow-classic-formula_default \
    mariadb:10.3.8 \
    bash -c "mysql -h sql -proot wcf < dumps/dump.sql" \
