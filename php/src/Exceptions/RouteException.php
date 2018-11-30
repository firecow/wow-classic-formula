<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

class RouteException extends Exception
{
    private $statusCode;
    private $contentType;

    /**
     * RouteException constructor.
     * @param string $message
     * @param int $statusCode
     * @param string $contentType
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $statusCode, string $contentType, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->contentType = $contentType;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }
}
