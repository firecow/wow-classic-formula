<?
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

use App\Context;
use App\Exceptions\RouteException;
use GuzzleHttp\Psr7\ServerRequest;
use League\Route\Http\Exception\NotFoundException;

require 'error.php';
require 'vendor/autoload.php';


$ctx = new Context();

$request = ServerRequest::fromGlobals();

require 'routes.php';

$headers = [];
$protocol = "1.1";
$statusCode = 404;
try {
    $response = $router->dispatch($request);
    $statusCode = $response->getStatusCode();
    $body = $response->getBody();
    $protocol = $response->getProtocolVersion();
    $headers = $response->getHeaders();
}
    /** @noinspection PhpRedundantCatchClauseInspection */
catch (NotFoundException $ex) {
    $body = $ctx->render("fragments/NotFound.twig");
}
/** @noinspection PhpRedundantCatchClauseInspection */
catch (RouteException $ex) {
    $statusCode = $ex->getStatusCode();
    $protocol = "1.1";
    $body = $ex->getMessage();
}

header("HTTP/$protocol $statusCode");
foreach ($headers as $headerKey => $values) {
    $implodedValues = implode(",", $values);
    header("$headerKey: $implodedValues");
}
echo "$body\n";