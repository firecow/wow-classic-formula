<?php
declare(strict_types=1);

namespace App\Responses;

class PlainTextResponse extends CommonResponse
{
    public function __construct(string $responseBody, array $headers = [])
    {
        $headers["Content-Type"] = ContentType::PLAIN_TEXT;
        parent::__construct(StatusCode::OK, $responseBody, $headers);
    }
}
