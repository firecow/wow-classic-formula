<?php

declare(strict_types=1);

namespace App;

class ItemsStatRegex
{
    public static $array = [
        "stamina" => [
            "regex" => "([\d|\.]*) Stamina",
        ],
        "strength" => [
            "regex" => "([\d|\.]*) Strength",
        ],
        "agility" => [
            "regex" => "([\d|\.]*) Agility",
        ],
        "spirit" => [
            "regex" => "([\d|\.]*) Spirit",
        ],
        "intellect" => [
            "regex" => "([\d|\.]*) Intellect",
        ],
        "armor" => [
            "regex" => "([\d|\.]*) Armor",
        ],
        "attackPower" => [
            "regex" => "([\d|\.]*) Attack Power\.",
        ],
        "rangedAttackPower" => [
            "regex" => "\+([\d|\.]*) ranged Attack Power\.",
        ],
        "minDmg" => [
            "regex" => "([\d|\.]*) - [\d|\.]*"
        ],
        "maxDmg" => [
            "regex" => "[\d|\.]* - ([\d|\.]*)"
        ],
        "speed" => [
            "regex" => "Speed ([\d|\.]*)"
        ],
        "dps" => [
            "regex" => "([\d|\.]*) damage per second",
        ],
        "physicalCrit" => [
            "regex" => "Improves your chance to get a critical strike by ([\d|\.]*)%",
        ],
        "physicalHit" => [
            "regex" => "Improves your chance to hit by ([\d|\.]*)%"
        ],
        "spellCrit" => [
            "regex" => "Improves your chance to get a critical strike with spells by ([\d|\.]*)%"
        ],
        "holyCrit" => [
            "regex" => "Increases the critical effect chance of your Holy spells by ([\d|\.]*)%"
        ],
        "spellHit" => [
            "regex" => "Improves your chance to hit with spells by ([\d|\.]*)%"
        ],
        "spellDmg" => [
            "regex" => "Increases damage and healing done by magical spells and effects by up to ([\d|\.]*)\."
        ],
        "spellDmgShadow" => [
            "regex" => "Increases damage done by Shadow spells and effects by up to ([\d|\.]*)\."
        ],
        "spellDmgFire" => [
            "regex" => "Increases damage done by Fire spells and effects by up to ([\d|\.]*)\."
        ],
        "spellDmgFrost" => [
            "regex" => "Increases damage done by Frost spells and effects by up to ([\d|\.]*)\."
        ],
        "spellDmgArcane" => [
            "regex" => "Increases damage done by Arcane spells and effects by up to ([\d|\.]*)\."
        ],
        "spellDmgNature" => [
            "regex" => "Increases damage done by Nature spells and effects by up to ([\d|\.]*)\."
        ],
        "healing" => [
            "regex" => "Increases healing done by spells and effects by up to ([\d|\.]*)\."
        ],
        "mana5" => [
            "regex" => "Restores ([\d|\.]*) mana per 5 sec\."
        ],
        "defense" => [
            "regex" => "Increased Defense \+([\d|\.]*)\."
        ],
        "parry" => [
            "regex" => "Increases your chance to parry an attack by ([\d|\.]*)%"
        ],
        "blockValue" => [
            "regex" => "Increases the block value of your shield by ([\d|\.]*)\.",
        ],
        "blockPct" => [
            "regex" => "Increases your chance to block attacks with a shield by ([\d|\.]*)%",
        ],
        "dodge" => [
            "regex" => "Increases your chance to dodge an attack by ([\d|\.]*)%"
        ],
        "frostRes" => [
            "regex" => "([\d|\.]*) Frost Resistance"
        ],
        "fireRes" => [
            "regex" => "([\d|\.]*) Fire Resistance"
        ],
        "shadowRes" => [
            "regex" => "([\d|\.]*) Shadow Resistance"
        ],
        "natureRes" => [
            "regex" => "([\d|\.]*) Nature Resistance"
        ]

    ];
}
