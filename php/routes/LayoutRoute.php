<?
declare(strict_types=1);

namespace Routes;

use App\Context;
use App\Responses\HtmlTextResponse;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class LayoutRoute
{

    public function executeRoute(Context $ctx, ServerRequest $request, array $args): ResponseInterface
    {
        $data = array(
            "classes" => ["Priest", "Rogue", "Warrior", "Druid", "Mage", "Warlock", "Paladin", "Shaman"]
        );
        $html = $ctx->render("routes/LayoutRoute.twig", $data);
        return new HtmlTextResponse($html);
    }
}
