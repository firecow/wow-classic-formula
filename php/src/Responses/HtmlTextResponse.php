<?
declare(strict_types=1);

namespace App\Responses;

class HtmlTextResponse extends CommonResponse
{

    public function __construct(string $responseBody, array $headers = array())
    {
        $headers["Content-Type"] = ContentType::HTML_TEXT;
        parent::__construct(StatusCode::OK, $responseBody, $headers);
    }

}