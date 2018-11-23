<?
declare(strict_types=1);

namespace App\Encoding;

use App\Exceptions\JsonException;

class JSON
{

    public static function encode(array $arr): string
    {
        $result = json_encode($arr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($error = json_last_error()) {
            throw new JsonException(json_last_error_msg(), $error);
        }
        if (!$result) {
            throw new JsonException("Result is false", 0);
        }
        return $result;
    }

    public static function decode(string $str): array
    {
        $arr = json_decode($str, true);
        if ($error = json_last_error()) {
            throw new JsonException(json_last_error_msg(), $error);
        }
        return $arr;
    }

}