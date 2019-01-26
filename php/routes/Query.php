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
                $attributes[str_replace("attr_", "", $name)] = (float)$value;
            }
        }

        $class = $parsedBody['class'];
        $patches = implode("','", explode(",", $parsedBody['patch']));
        $locations = implode("','", explode(",", $parsedBody['location']));

        $sql = $ctx->createSQL();

//        $statement = "
//          SELECT item_stats.*, ($formula) as gearpoint, COALESCE(location, 'Quest') as location, cts.slotName, cts.typeName
//            FROM item_stats
//              JOIN item_slots slots
//                ON slots.slotName = item_stats.slotName
//              JOIN class_type_slot cts
//                ON item_stats.slotName = cts.slotName AND item_stats.typeName = cts.typeName
//              LEFT JOIN item_classes classes
//                ON classes.itemId = item_stats.itemId
//              LEFT JOIN item_locations locations
//                ON locations.itemId = item_stats.itemId
//          WHERE
//            item_stats.rarity IN ('uncommon', 'rare', 'epic', 'legendary') AND
//            cts.className = ? AND
//            item_stats.patch IN ('$patches') AND
//            (classes.className = ? OR classes.className IS NULL)
//          ORDER BY
//            slots.position ASC,
//            gearpoint DESC,
//            FIELD(`rarity`, 'legendary', 'epic', 'rare', 'uncommon', 'common', 'poor'),
//            itemLevel DESC,
//            itemName ASC
//        ";

        $statement = "
          SELECT item_stats.*, COALESCE(location, 'Quest') as location, cts.slotName, cts.typeName, slots.position
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
            item_stats.patch IN ('$patches') AND
            locations.location IN ('$locations') AND
            item_stats.rarity IN ('uncommon', 'rare', 'epic', 'legendary') AND
            cts.className = ? AND
            (classes.className = ? OR classes.className IS NULL)
        ";

        $items = [];
        $iter = $sql->execute($statement, [$class, $class]);
        foreach ($iter as $item) {
            $item['gearpoint'] = 0;
            foreach ($attributes as $name => $value) {
                $item['gearpoint'] += $item[$name] * $value;
            }
            $items[] = $item;
        }

        usort($items, function($a, $b) {
            if ($a['position'] !== $b['position']) {
                return $a['position'] - $b['position'];
            }
            return ($b["gearpoint"] <=> $a["gearpoint"]);
        });

        //echo json_encode($items);
        //exit;

        $instances = [
            "Mara",
            "BRD",
            "DME",
            "DMN",
            "DMW",
            "DM",
            "Quest",
            "UBRS",
            "STRAT",
            "ST",
            "Crafted",
            "BOE",
            "LBRS",
            "SCHOLO",
            "UldTrash",
            "AQOpening",
            "ZG"
        ];
        $slots = [];
        $mergeHands = ["Mage", "Priest", "Warlock", "Paladin", "Druid"];
        foreach ($items as $item) {
            if ($item['gearpoint'] == 0) {
                continue;
            }

            $slotName = $item['slotName'];
            if (!isset($slots[$slotName])) {
                $slots[$slotName] = ["items" => []];
            }

            if (count($slots[$slotName]["items"]) >= 15) {
                continue;
            }

            $slots[$slotName]["items"][] = $item;
        }

        if (in_array($class, $mergeHands)) {
            $mainHandItems = isset($slots['Main Hand']) ? $slots['Main Hand']["items"] : [];
            $offHandItems = isset($slots['One-hand']) ? $slots['One-hand']["items"] : [];

            $slots['Main Hand']["items"] = array_merge($mainHandItems, $offHandItems);
            usort($slots['Main Hand']["items"], function($a, $b) {
                return ($b["gearpoint"] <=> $a["gearpoint"]);
            });
            unset($slots['One-hand']);
            $slots['Main Hand']["items"] = array_slice($slots['Main Hand']["items"], 0, 15);
        }


        $html = $ctx->render("routes/Query.twig", [
            'slots' => $slots
        ]);
        return new HtmlTextResponse($html);
    }
}
