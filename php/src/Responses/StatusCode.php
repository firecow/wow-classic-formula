<?php
declare(strict_types=1);

namespace App\Responses;

class StatusCode
{
    // Script executed as intended, and everything was ok.
    const OK = 200;

    // Input was malformed or missing.
    const BAD_REQUEST = 400;

    // Resource was not found. User, game or challenge wasn't found, or file was not found.
    const NOT_FOUND = 404;

    // Username, id or other resource was already taken.
    const CONFLICT = 409;

    // Trying to access without specifying access token.
    const UNAUTHORIZED = 401;

    // Input was wellformed and present, but input data could not be used to fullfill the use case.;
    const UNPROCESSABLE_ENTITY = 422;

    // An unhandled exception occured.
    const INTERNAL_SERVER_ERROR = 500;
}
