<?php
declare(strict_types=1);

namespace App;

class Time
{
    public static function nowInSeconds(): int
    {
        return intval(self::nowInMilliseconds() * 0.001);
    }

    public static function nowInMilliseconds(): int
    {
        return intval(microtime(true) * 1000);
    }
}
