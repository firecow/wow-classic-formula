<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Responses\ContentType;
use App\Responses\StatusCode;
use Throwable;

class BadRequestRouteException extends RouteException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, StatusCode::BAD_REQUEST, ContentType::PLAIN_TEXT, $previous);
    }
}
