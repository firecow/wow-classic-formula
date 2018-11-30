<?php

declare(strict_types=1);

namespace App\Responses;

use GuzzleHttp\Psr7\Response;

class CommonResponse extends Response
{
    public function __construct(int $statusCode, string $body, array $headers = [])
    {
        parent::__construct($statusCode, $headers, $body);
    }

    public function withAddedCookie($key, $value, int $secondsToLive)
    {
        return parent::withAddedHeader("Set-Cookie", "$key=$value; Max-Age=$secondsToLive; path=/; HttpOnly");
    }
}
