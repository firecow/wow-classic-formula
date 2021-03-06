<?php

declare(strict_types=1);

namespace Routes;

use App\Context;
use App\PredefinedSpecs;
use App\Responses\HtmlTextResponse;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;

class LayoutRoute
{
    public function executeRoute(Context $ctx, ServerRequest $request, array $args): ResponseInterface
    {
//        $queryParams = $request->getQueryParams();
//        error_log(json_encode($queryParams));

        $sql = $ctx->createSQL();
        $data = [
            "classes" => ["Priest", "Rogue", "Warrior", "Hunter", "Druid", "Mage", "Warlock", "Paladin", "Shaman"],

            "attributes" => [
                "Core" => ["stamina", "strength", "agility", "spirit", "intellect"],
                "Physical" => ["attackPower", "rangedPower", "druidAttackPower", "crit", "hit"],
                "Weapons" => ["minDmg", "maxDmg", "speed", "dps"],
                "Tank" => ["armor", "defense", "dodge", "parry", "blockPct", "blockValue"],
                "Caster" => ["spellDmg", "spellCrit", "spellHit", "shadowDmg", "fireDmg", "frostDmg", "arcaneDmg", "natureDmg"],
                "Healers" => ["healing", "mana5", "holyCrit"],
                "Resistance" => ["frostRes", "fireRes", "shadowRes", "natureRes"],
            ],
            "predefinedSpecs" => PredefinedSpecs::$array,
            'patches' => $sql->fetchAll("SELECT patch FROM item_stats GROUP BY patch", []),
            'locations' => array_map(function($row) { return $row['location']; }, $sql->fetchAll("SELECT location FROM item_locations GROUP BY location", []))
        ];
        $html = $ctx->render("routes/LayoutRoute.twig", $data);
        return new HtmlTextResponse($html);
    }
}
