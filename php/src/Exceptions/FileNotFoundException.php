<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class FileNotFoundException extends Exception
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
