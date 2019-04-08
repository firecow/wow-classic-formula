<?php

declare(strict_types=1);

namespace Routes;

use App\Context;
use App\Encoding\JSON;
use App\Exceptions\DAOException;
use App\File;
use App\Responses\HtmlTextResponse;
use App\Responses\JsonResponse;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class ItemLog
{
    public function executeRoute(Context $ctx, ServerRequest $request, array $args): ResponseInterface
    {
        $sql = $ctx->createSQL();
        $parsedBody = JSON::decode(File::loadFileTextContent("/php/routes/ItemLogTestDataPjuske.json"));

        $response = [];
        foreach ($parsedBody as $charName => $arguments) {
            $items = $arguments['items'];
            $attributes = $arguments['attributes'];

            $response[$charName] = [];

            usort($items, function($a, $b) {
                return ($a['timestamp'] <=> $b['timestamp']);
            });

            foreach ($items as $itemArguments) {
                $itemId = $itemArguments['itemId'];
                $timestamp = $itemArguments["timestamp"];

                try {
                    $item = $sql->fetchAssoc("SELECT * FROM item_stats WHERE itemId = ?", [$itemId]);
                    $itemLocation = $sql->fetchAssoc("SELECT * FROM item_locations WHERE itemId = ?", [$itemId]);
                } catch(DAOException $exception) {
                    continue;
                }

                $gearPoint = 0;
                $itemName = $item['itemName'];
                $slotName = $item['slotName'];
                $rarity = $item['rarity'];
                if (in_array($slotName, ['Shirt', 'Tabard'])) {
                    continue;
                }

                foreach ($attributes as $name => $value) {
                    $gearPoint += $item[$name] * $value;
                }

                $location = $itemLocation['location'];
                $itemData = [
                    "location" => $location,
                    "gearpoint" => $gearPoint,
                    "slotName" => $slotName,
                    "timestamp" => $timestamp,
                    "itemName" => $itemName,
                    "rarity" => $rarity,
                    "delta" => ""
                ];

                $raids = ["ZG", "MC", "ONY", "BWL", "AQ20", "AQ40", "NAX"];

                $currentSlotBest = $response[$charName]['currentSlotBest'][$slotName] ?? null;
                if (empty($currentSlotBest) || $gearPoint > $currentSlotBest['gearpoint']) {
                    $delta = $gearPoint - $currentSlotBest['gearpoint'];
                    $itemData["delta"] = $delta > 0 ? $delta : "";
                    $response[$charName]['currentSlotBest'][$slotName] = ["gearpoint" => $gearPoint, "itemName" => $itemName];

                    if (in_array($location, $raids)) {
                        $response[$charName]['history'][] = $itemData;
                    } else if ($gearPoint >= 5) {
                        $response[$charName]['offspec'][] = $itemData;
                    }

                } else if ($gearPoint >= 5) {
                    $response[$charName]['offspec'][] = $itemData;
                }
            }

            usort($response[$charName]['history'], function($a, $b) {
                return ($b['timestamp'] <=> $a['timestamp']);
            });

            usort($response[$charName]['offspec'], function($a, $b) {
                return ($b['gearpoint'] <=> $a['gearpoint']);
            });
        }

        $data = [
            "chars" => $response
        ];
        $html = $ctx->render("routes/ItemLog.twig", $data);
        return new HtmlTextResponse($html);
    }
}
