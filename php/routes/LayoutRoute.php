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

        $data = [
            "classes" => ["Priest", "Rogue", "Warrior", "Druid", "Mage", "Warlock", "Paladin", "Shaman"],

            "attributes" => [
                "Core" => ["stamina", "strength", "agility", "spirit", "intellect"],
                "Physical" => ["attackPower", "rangedPower", "crit", "hit", "defense", "parry", "blockValue", "blockPct", "dodge"],
                "Weapons" => ["minDmg", "maxDmg", "speed", "dps"],
                "Tank" => ["armor", "defense", "dodge", "parry", "blockPct", "blockValue"],
                "Caster" => ["spellDmg", "spellCrit", "spellHit", "shadowDmg", "fireDmg", "frostDmg", "arcaneDmg", "natureDmg"],
                "Healers" => ["healing", "mana5", "holyCrit"],
                "Resistance" => ["frostRes", "fireRes", "shadowRes", "natureRes"],
            ],
            "predefinedSpecs" => PredefinedSpecs::$array
        ];
        $html = $ctx->render("routes/LayoutRoute.twig", $data);
        return new HtmlTextResponse($html);
    }
}
