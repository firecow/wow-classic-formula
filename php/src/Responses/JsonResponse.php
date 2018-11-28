<?php
declare(strict_types=1);

namespace App\Responses;

use App\Encoding\JSON;

class JsonResponse extends CommonResponse
{
    public function __construct(array $arr, array $headers = [])
    {
        $responseBodyEncoded = JSON::encode($arr);
        $headers["Content-Type"] = ContentType::JSON;
        parent::__construct(StatusCode::OK, $responseBodyEncoded, $headers);
    }
}
