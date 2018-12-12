#!/bin/sh
docker run --rm -t \
    --network wow-classic-formula_default \
    mariadb:10.3.8 mysqldump -h sql -proot \
    --default-character-set=utf8mb4 \
    --hex-blob \
    --skip-disable-keys \
    --skip-add-locks \
    --skip-comments \
    --skip-dump-date \
    --extended-insert=FALSE \
    wcf \
