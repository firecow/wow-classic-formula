<?php

namespace App;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;

class AccessToken
{
    public static function isUserAuthenticated(ServerRequestInterface $request, string $jwtSecret): bool
    {
        $accessToken = self::getAccessToken($request);
        if ($accessToken == null) {
            return false;
        }

        $payload =  self::getDecodedPayload($accessToken, $jwtSecret);
        return $payload->sub != null;
    }

    public static function getAuthenticatedUserId(ServerRequestInterface $request, string $jwtSecret): string
    {
        $accessToken = self::getAccessToken($request);
        if (!$accessToken) {
            throw new Exception("AccessToken is false or null");
        }
        $payload = self::getDecodedPayload($accessToken, $jwtSecret);
        return $payload->sub;
    }

    public static function createAccessToken(int $duration, array $userData, string $jwtSecret)
    {
        $photoId = $userData["photoId"];
        $img = $photoId !== null ? "/photo/$photoId" : null;
        $timeInSeconds = Time::nowInSeconds();
        $payload = [
            "iss" => "simplephp.com",
            "iat" => $timeInSeconds,
            "exp" => $timeInSeconds + $duration,
            "sub" => $userData["userId"],
            "img" => $img,
            "una" => $userData["username"]
        ];
        return JWT::encode($payload, $jwtSecret, 'HS256');
    }

    private static function getDecodedPayload(string $jwt, string $jwtSecret)
    {
        $payload = JWT::decode($jwt, $jwtSecret, ["HS256"]);
        return $payload;
    }

    private static function getAccessToken(ServerRequestInterface $request): ?string
    {
        // Get access token from cookie
        $cookies = $request->getCookieParams();
        if (isset($cookies["accessToken"])) {
            return $cookies["accessToken"];
        }

        // Get access token from authorization bearer value
        $authorizations = $request->getHeader("Authorization");
        foreach ($authorizations as $authorizationValue) {
            if (preg_match('/Bearer\s(\S+)/', $authorizationValue, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
