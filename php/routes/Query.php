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

        $sql = $ctx->createSQL();

        $statement = "
          SELECT item_stats.*, ($formula) as gearpoint, COALESCE(location, 'Quest') as location, cts.slotName, cts.typeName
            FROM item_stats
              JOIN item_slots slots
                ON slots.slotName = item_stats.slotName
              JOIN class_type_slot cts 
                ON item_stats.slotName = cts.slotName AND item_stats.typeName = cts.typeName
              LEFT JOIN item_classes classes
                ON classes.itemId = item_stats.itemId
              LEFT JOIN item_locations locations
                ON locations.itemId = item_stats.itemId
          WHERE 
            item_stats.rarity IN ('uncommon', 'rare', 'epic', 'legendary') AND
            cts.className = ? AND
            (classes.className = ? OR classes.className IS NULL)
          ORDER BY 
            slots.position ASC, 
            gearpoint DESC, 
            FIELD(`rarity`, 'legendary', 'epic', 'rare', 'uncommon', 'common', 'poor'), 
            itemLevel DESC,
            itemName ASC
        ";
        $iter = $sql->execute($statement, [$class, $class]);
        $slots = [];
        foreach ($iter as $row) {
            if ($row['gearpoint'] == 0) {
                continue;
            }

            $slotName = $row['slotName'];
            if (!isset($slots[$slotName])) {
                $slots[$slotName] = [];
            }
            if (count($slots[$slotName]) > 25) {
                continue;
            }

            //if (!in_array($row['location'], ["Quest", "BWL", "MC", "ONY", "BOE", "BRD", "DM", "DME", "DMN", "DMW", "Dukes", "Elemental", "Crafted", "LBRS", "Mara", "UBRS", "STRAT", "ST", "SCHOLO"])) {
            //    continue;
            //}

            $slots[$slotName][] = $row;
        }

        $html = $ctx->render("routes/Query.twig", [
            'slots' => $slots
        ]);
        return new HtmlTextResponse($html);
    }
}
