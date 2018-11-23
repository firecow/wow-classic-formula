<?
declare(strict_types=1);

namespace App;

use App\Exceptions\FileErrorException;
use App\Exceptions\FileNotFoundException;

class File
{

    public static function loadFileTextContent(string $filePath): string
    {
        if (!self::fileExists($filePath)) {
            throw new FileNotFoundException("File not found $filePath");
        }
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new FileErrorException("File error. Could not get content $filePath");
        }
        return $content;
    }

    private static function fileExists($filePath): bool
    {
        return file_exists($filePath);
    }

}