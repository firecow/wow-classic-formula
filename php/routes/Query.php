<?php

declare(strict_types=1);

namespace Routes;

use App\Context;
use App\Responses\HtmlTextResponse;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class Query
{
    public function executeRoute(Context $ctx, ServerRequest $request, array $args): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $attributes = [];
        foreach ($parsedBody as $name => $value) {
            if (!empty($value) && strpos($name, "attr_") > -1) {
                $attributes[str_replace("attr_", "", $name)] = $value;
            }
        }

        $class = $parsedBody['class'];

        // Generate formula
        $formula = [];
        foreach ($attributes as $name => $value) {
            $formula[] = "$name * $value";
        }
        $formula = join(" + ", $formula);

        error_log(json_encode($formula));

        $sql = $ctx->createSQL();

        $statement = "
          SELECT item_stats.*, ($formula) as gearpoint, location
            FROM item_stats
              JOIN item_slots
                ON item_slots.slotName = item_stats.slotName
              JOIN class_type_slot ct 
                ON item_stats.slotName = ct.slotName AND item_stats.typeName = ct.typeName
              LEFT JOIN item_classes
                ON item_classes.itemId = item_stats.itemId
              LEFT JOIN item_locations
                ON item_locations.itemId = item_stats.itemId
          WHERE 
            (item_classes.className = '$class' OR item_classes.className IS NULL)
          ORDER BY item_slots.position ASC, gearpoint DESC 
        ";
        $iter = $sql->execute($statement, []);
        $slots = [];
        foreach ($iter as $row) {
            if ($row['gearpoint'] == 0) {
                continue;
            }

            $slotName = $row['slotName'];
            if (!isset($slots[$slotName])) {
                $slots[$slotName] = [];
            }
            $slots[$slotName][] = $row;
        }

        $html = $ctx->render("routes/Query.twig", [
            'slots' => $slots
        ]);
        return new HtmlTextResponse($html);
    }
}
