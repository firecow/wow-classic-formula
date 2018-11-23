<?
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class FileErrorException extends Exception
{

    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }

}