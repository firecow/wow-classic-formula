<?php
declare(strict_types=1);

namespace App\Encoding;

use Exception;

class Base64
{
    public static function encode(string $str): string
    {
        return base64_encode($str);
    }

    public static function decode(string $str): string
    {
        $result = base64_decode($str);
        if (!$result) {
            throw new Exception("Base64 decode failed");
        }
        return $result;
    }

    public static function urlEncode(string $str): string
    {
        $encoded = Base64::encode($str);
        return str_replace("=", "", strtr($encoded, "+/", "-_"));
    }

    public static function urlDecode(string $str): string
    {
        $remainder = strlen($str) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $str .= str_repeat("=", $padlen);
        }
        return Base64::decode(strtr($str, "-_", "+/"));
    }
}
