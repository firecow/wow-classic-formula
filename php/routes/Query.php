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
        $locations = explode(",", $parsedBody['location']);

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

        $items = array_filter($items, function($item) use ($locations) {
            return in_array($item['location'], $locations);
        });

        usort($items, function($a, $b) {
            if ($a['position'] !== $b['position']) {
                return $a['position'] - $b['position'];
            }
            return ($b["gearpoint"] <=> $a["gearpoint"]);
        });

        $slots = [];
        foreach ($items as $item) {
            if ($item['gearpoint'] == 0) {
                continue;
            }

            $slotName = $item['slotName'];
            if (!isset($slots[$slotName])) {
                $slots[$slotName] = ["items" => []];
            }

            $slots[$slotName]["items"][] = $item;
        }

        // Recalculate weapons
        $ignoreDmgOnWeapons = ["Hunter"];
        $fieldsToIgnore = ['minDmg', 'maxDmg','speed','Dps'];
        $slotsToIgnore = ['Main Hand', 'One-hand', 'Off Hand', 'Two-hand'];
        if (in_array($class, $ignoreDmgOnWeapons)) {
            foreach ($slotsToIgnore as $slotToIgnore) {
                foreach ($slots[$slotToIgnore]['items'] as &$item) {
                    $item['gearpoint'] = 0;
                    foreach ($attributes as $name => $value) {
                        if (!in_array($name, $fieldsToIgnore)) {
                            $item['gearpoint'] += $item[$name] * $value;
                        }
                    }
                }
                usort($slots[$slotToIgnore]["items"], function($a, $b) {
                    return ($b["gearpoint"] <=> $a["gearpoint"]);
                });
            }
        }

        // Recalculate ranged
        $ignoreDmgOnWeapons = ["Warrior", "Rogue"];
        $fieldsToIgnore = ['minDmg', 'maxDmg','speed','Dps'];
        $slotsToIgnore = ['Ranged'];
        if (in_array($class, $ignoreDmgOnWeapons)) {
            foreach ($slotsToIgnore as $slotToIgnore) {
                if (empty($slots[$slotToIgnore])) {
                    continue;
                }

                foreach ($slots[$slotToIgnore]['items'] as &$item) {
                    $item['gearpoint'] = 0;
                    foreach ($attributes as $name => $value) {
                        if (!in_array($name, $fieldsToIgnore)) {
                            $item['gearpoint'] += $item[$name] * $value;
                        }
                    }
                }
                usort($slots[$slotToIgnore]["items"], function($a, $b) {
                    return ($b["gearpoint"] <=> $a["gearpoint"]);
                });
            }
        }

        // Merge One-hands into Main Hand.
        $mergeOneToMain = ["Hunter", "Warrior", "Rogue", "Mage", "Priest", "Warlock", "Paladin", "Druid"];
        if (in_array($class, $mergeOneToMain)) {
            $mainHandItems = isset($slots['Main Hand']) ? $slots['Main Hand']["items"] : [];
            $offHandItems = isset($slots['One-hand']) ? $slots['One-hand']["items"] : [];

            $slots['Main Hand']["items"] = array_merge($mainHandItems, $offHandItems);
            usort($slots['Main Hand']["items"], function($a, $b) {
                return ($b["gearpoint"] <=> $a["gearpoint"]);
            });
        }

        // Merge One-hands into Offhands.
        $mergeOneToOff = ["Hunter", "Warrior", "Rogue"];
        if (in_array($class, $mergeOneToOff)) {
            $mainHandItems = isset($slots['Off Hand']) ? $slots['Off Hand']["items"] : [];
            $offHandItems = isset($slots['One-hand']) ? $slots['One-hand']["items"] : [];

            $slots['Off Hand']["items"] = array_merge($mainHandItems, $offHandItems);
            usort($slots['Off Hand']["items"], function($a, $b) {
                return ($b["gearpoint"] <=> $a["gearpoint"]);
            });
        }

        if (in_array($class, $mergeOneToOff) || in_array($class, $mergeOneToMain)) {
            unset($slots['One-hand']);
        }

        foreach (array_keys($slots) as $slotName) {
            $slots[$slotName]["items"] = array_slice($slots[$slotName]["items"], 0, 20);
        }

        $html = $ctx->render("routes/Query.twig", [
            'slots' => $slots
        ]);
        return new HtmlTextResponse($html);
    }
}
