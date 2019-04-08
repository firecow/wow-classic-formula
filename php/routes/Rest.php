<?php

declare(strict_types=1);

namespace Routes;

use App\Context;
use App\Encoding\JSON;
use App\Responses\JsonResponse;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class Rest
{
    public function executeRoute(Context $ctx, ServerRequest $request, array $args): ResponseInterface
    {
        $sql = $ctx->createSQL();
        $parsedBody = JSON::decode($request->getBody()->getContents());

        $response = [];
        foreach ($parsedBody as $charName => $arguments) {
            $items = $arguments['items'];
            $attributes = $arguments['attributes'];

            $response[$charName] = [];

            usort($items, function($a, $b) {
                return ($a['timestamp'] <=> $b['timestamp']);
            });

            foreach ($items as $itemArguments) {
                $itemName = $itemArguments['itemName'];
                $timestamp = $itemArguments["timestamp"];

                $item = $sql->fetchAssoc("SELECT * FROM item_stats WHERE itemName = ?", [$itemName]);
                $gearPoint = 0;
                $slotName = $item['slotName'];
                foreach ($attributes as $name => $value) {
                    $gearPoint += $item[$name] * $value;
                }

                $currentSlotBest = $response[$charName]['currentSlotBest'][$slotName] ?? null;
                if (empty($currentSlotBest) || $gearPoint > $currentSlotBest['gearpoint']) {
                    $response[$charName]['currentSlotBest'][$slotName] = ["gearpoint" => $gearPoint, "itemName" => $itemName];
                    $response[$charName]['history'][] = ["gearpoint" => $gearPoint, "slotName" => $slotName, "timestamp" => $timestamp, "itemName" => $itemName];
                } else {
                    $response[$charName]['offspec'][] = ["gearpoint" => $gearPoint, "slotName" => $slotName, "timestamp" => $timestamp, "itemName" => $itemName];
                }
            }
        }



        return new JsonResponse($response);
    }
}
