<?php

declare(strict_types=1);

namespace App;

use App\Exceptions\BadRequestRouteException;
use GuzzleHttp\Psr7\ServerRequest;

class ServerRequestUtil
{
    public static function getBodyOrFail(ServerRequest $request): array
    {
        $parsedBody = $request->getParsedBody();
        if ($parsedBody === null || !is_array($parsedBody)) {
            throw new BadRequestRouteException("No parsed body array found");
        }
        return $parsedBody;
    }

    public static function getPostOrFail(ServerRequest $request, string $postKey): string
    {
        $body = self::getBodyOrFail($request);
        if (!isset($body[$postKey])) {
            throw new BadRequestRouteException("Post key '$postKey' not found in body");
        }
        return $body[$postKey];
    }
}
