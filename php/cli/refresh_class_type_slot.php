<?php


declare(strict_types=1);

use App\Config;
use App\SQL;

require '../vendor/autoload.php';
require '../error.php';


$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());
$sql->execute("DELETE FROM class_type_slot WHERE className IS NOT NULL", []);

$slots = ["Chest", "Feet", "Hands", "Head", "Legs", "Shoulder", "Waist", "Wrist"];
$classMap = [
    "Druid" => ["Cloth", "Leather"],
    "Hunter" => ["Cloth", "Leather", "Mail"],
    "Mage" => ["Cloth"],
    "Paladin" => ["Cloth", "Leather", "Mail", "Plate"],
    "Priest" => ["Cloth"],
    "Rogue" => ["Cloth", "Leather"],
    "Shaman" => ["Cloth", "Leather", "Mail"],
    "Warlock" => ["Cloth"],
    "Warrior" => ["Cloth", "Leather", "Mail", "Plate"]
];

// Item sets
foreach ($classMap as $className => $types) {
    foreach ($types as $typeName) {
        foreach ($slots as $slotName) {
            $sql->execute("REPLACE INTO class_type_slot VALUES(?, ?, ?)", [$className, $typeName, $slotName]);
        }
    }
}

// Trinket, Ring, Neck, Back.
foreach ($classMap as $className => $types) {
    $sql->execute("REPLACE INTO class_type_slot VALUES(?, ?, ?)", [$className, "Cloth", "Back"]);
    $sql->execute("REPLACE INTO class_type_slot VALUES(?, ?, ?)", [$className, "Finger", "Finger"]);
    $sql->execute("REPLACE INTO class_type_slot VALUES(?, ?, ?)", [$className, "Neck", "Neck"]);
    $sql->execute("REPLACE INTO class_type_slot VALUES(?, ?, ?)", [$className, "Trinket", "Trinket"]);
}

// Weapons, Shields, Held in offhand
$classMap = [
    "Paladin" => [
        ["typeName" => "Mace", "slotName" => "Main Hand"],
        ["typeName" => "Mace", "slotName" => "One-hand"],
        ["typeName" => "Mace", "slotName" => "Off Hand"],
        ["typeName" => "Mace", "slotName" => "Two-hand"],

        ["typeName" => "Sword", "slotName" => "Main Hand"],
        ["typeName" => "Sword", "slotName" => "One-hand"],
        ["typeName" => "Sword", "slotName" => "Off Hand"],
        ["typeName" => "Sword", "slotName" => "Two-hand"],

        ["typeName" => "Axe", "slotName" => "Main Hand"],
        ["typeName" => "Axe", "slotName" => "One-hand"],
        ["typeName" => "Axe", "slotName" => "Off Hand"],
        ["typeName" => "Axe", "slotName" => "Two-hand"],

        ["typeName" => "Polearm", "slotName" => "Two-hand"],

        ["typeName" => "Libram", "slotName" => "Relic"],
        ["typeName" => "Shield", "slotName" => "Off Hand"],
    ],
    "Shaman" => [
        ["typeName" => "Fist Weapon", "slotName" => "Main Hand"],
        ["typeName" => "Fist Weapon", "slotName" => "One-hand"],
        ["typeName" => "Fist Weapon", "slotName" => "Off Hand"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"],
        ["typeName" => "Dagger", "slotName" => "Off Hand"],

        ["typeName" => "Mace", "slotName" => "Main Hand"],
        ["typeName" => "Mace", "slotName" => "One-hand"],
        ["typeName" => "Mace", "slotName" => "Off Hand"],
        ["typeName" => "Mace", "slotName" => "Two-hand"],

        ["typeName" => "Axe", "slotName" => "Main Hand"],
        ["typeName" => "Axe", "slotName" => "One-hand"],
        ["typeName" => "Axe", "slotName" => "Off Hand"],
        ["typeName" => "Axe", "slotName" => "Two-hand"],

        ["typeName" => "Staff", "slotName" => "Two-hand"],

        ["typeName" => "Shield", "slotName" => "Off Hand"],
    ],
    "Druid" => [
        ["typeName" => "Fist Weapon", "slotName" => "Main Hand"],
        ["typeName" => "Fist Weapon", "slotName" => "One-hand"],
        ["typeName" => "Fist Weapon", "slotName" => "Off Hand"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"],
        ["typeName" => "Dagger", "slotName" => "Off Hand"],

        ["typeName" => "Mace", "slotName" => "Main Hand"],
        ["typeName" => "Mace", "slotName" => "One-hand"],
        ["typeName" => "Mace", "slotName" => "Off Hand"],
        ["typeName" => "Mace", "slotName" => "Two-hand"],

        ["typeName" => "Polearm", "slotName" => "Two-hand"],
        ["typeName" => "Staff", "slotName" => "Two-hand"],

        ["typeName" => "Idol", "slotName" => "Relic"],
    ],
    "Hunter" => [
        ["typeName" => "Fist Weapon", "slotName" => "Main Hand"],
        ["typeName" => "Fist Weapon", "slotName" => "One-hand"],
        ["typeName" => "Fist Weapon", "slotName" => "Off Hand"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"],
        ["typeName" => "Dagger", "slotName" => "Off Hand"],

        ["typeName" => "Sword", "slotName" => "Main Hand"],
        ["typeName" => "Sword", "slotName" => "One-hand"],
        ["typeName" => "Sword", "slotName" => "Off Hand"],
        ["typeName" => "Sword", "slotName" => "Two-hand"],

        ["typeName" => "Axe", "slotName" => "Main Hand"],
        ["typeName" => "Axe", "slotName" => "One-hand"],
        ["typeName" => "Axe", "slotName" => "Off Hand"],
        ["typeName" => "Axe", "slotName" => "Two-hand"],

        ["typeName" => "Staff", "slotName" => "Two-hand"],
        ["typeName" => "Polearm", "slotName" => "Two-hand"],

        ["typeName" => "Bow", "slotName" => "Ranged"],
        ["typeName" => "Gun", "slotName" => "Ranged"],
        ["typeName" => "Crossbow", "slotName" => "Ranged"],
    ],
    "Warrior" => [
        ["typeName" => "Fist Weapon", "slotName" => "Main Hand"],
        ["typeName" => "Fist Weapon", "slotName" => "One-hand"],
        ["typeName" => "Fist Weapon", "slotName" => "Off Hand"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"],
        ["typeName" => "Dagger", "slotName" => "Off Hand"],

        ["typeName" => "Mace", "slotName" => "Main Hand"],
        ["typeName" => "Mace", "slotName" => "One-hand"],
        ["typeName" => "Mace", "slotName" => "Off Hand"],
        ["typeName" => "Mace", "slotName" => "Two-hand"],

        ["typeName" => "Sword", "slotName" => "Main Hand"],
        ["typeName" => "Sword", "slotName" => "One-hand"],
        ["typeName" => "Sword", "slotName" => "Off Hand"],
        ["typeName" => "Sword", "slotName" => "Two-hand"],

        ["typeName" => "Axe", "slotName" => "Main Hand"],
        ["typeName" => "Axe", "slotName" => "One-hand"],
        ["typeName" => "Axe", "slotName" => "Off Hand"],
        ["typeName" => "Axe", "slotName" => "Two-hand"],

        ["typeName" => "Polearm", "slotName" => "Two-hand"],
        ["typeName" => "Staff", "slotName" => "Two-hand"],

        ["typeName" => "Bow", "slotName" => "Ranged"],
        ["typeName" => "Gun", "slotName" => "Ranged"],
        ["typeName" => "Crossbow", "slotName" => "Ranged"],

        ["typeName" => "Shield", "slotName" => "Off Hand"],
    ],
    "Rogue" => [
        ["typeName" => "Fist Weapon", "slotName" => "Main Hand"],
        ["typeName" => "Fist Weapon", "slotName" => "One-hand"],
        ["typeName" => "Fist Weapon", "slotName" => "Off Hand"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"],
        ["typeName" => "Dagger", "slotName" => "Off Hand"],

        ["typeName" => "Mace", "slotName" => "Main Hand"],
        ["typeName" => "Mace", "slotName" => "One-hand"],
        ["typeName" => "Mace", "slotName" => "Off Hand"],

        ["typeName" => "Sword", "slotName" => "Main Hand"],
        ["typeName" => "Sword", "slotName" => "One-hand"],
        ["typeName" => "Sword", "slotName" => "Off Hand"],

        ["typeName" => "Bow", "slotName" => "Ranged"],
        ["typeName" => "Gun", "slotName" => "Ranged"],
        ["typeName" => "Crossbow", "slotName" => "Ranged"],
    ],
    "Mage" => [
        ["typeName" => "Staff", "slotName" => "Two-hand"],
        ["typeName" => "Held In Off-Hand", "slotName" => "Held In Off-Hand"],
        ["typeName" => "Wand", "slotName" => "Ranged"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"],

        ["typeName" => "Sword", "slotName" => "Main Hand"],
        ["typeName" => "Sword", "slotName" => "One-hand"]
    ],
    "Warlock" => [
        ["typeName" => "Staff", "slotName" => "Two-hand"],
        ["typeName" => "Held In Off-Hand", "slotName" => "Held In Off-Hand"],
        ["typeName" => "Wand", "slotName" => "Ranged"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"],

        ["typeName" => "Sword", "slotName" => "Main Hand"],
        ["typeName" => "Sword", "slotName" => "One-hand"]
    ],
    "Priest" => [
        ["typeName" => "Staff", "slotName" => "Two-hand"],
        ["typeName" => "Held In Off-Hand", "slotName" => "Held In Off-Hand"],
        ["typeName" => "Wand", "slotName" => "Ranged"],

        ["typeName" => "Mace", "slotName" => "Main Hand"],
        ["typeName" => "Mace", "slotName" => "One-hand"],

        ["typeName" => "Dagger", "slotName" => "Main Hand"],
        ["typeName" => "Dagger", "slotName" => "One-hand"]
    ],
];

// Weapons
foreach ($classMap as $className => $datas) {
    foreach ($datas as $data) {
        $sql->execute("REPLACE INTO class_type_slot VALUES(?, ?, ?)", [$className, $data["typeName"], $data["slotName"]]);
    }
}
