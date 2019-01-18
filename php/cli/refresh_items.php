<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

ini_set('memory_limit', '-1');

use App\Config;
use App\SQL;
use App\ItemsStatRegex;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\EachPromise;
use League\CLImate\CLImate;
use Psr\Http\Message\ResponseInterface;

require '/php/vendor/autoload.php';
require '/php/error.php';

$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());

// TODO: Dmg on Weapons for Hunter, Dmg on Ranged for Rogues/Warriors (Stat sticking)
// TODO: Merge Main Hand / One-hand, One-hand / Offhand
// TODO: Tier ½ item_classes
// TODO: White Bone Shredder
// TODO: The Shadowfoot Stabber (24222)
// TODO: Signet Ring of the Bronze Dragonflight
// TODO: Moonshadow Stave
// TODO: Set bonus's
// TODO: Rhok'delar, Longbow of the Ancient Keepers DEP
// TODO: Thorium Rifle
// TODO: Collapse All
// TODO: Bloodvine Vest
// TODO: Arnaments of War item locations
// TODO: Weapon Skill attributes
// TODO: Hover tooltip
// TODO: Random Bonus items
// TODO: Manually add averages over items USE effects.
// TODO: Fishing, Mining, Attack Power against (xxx)
// TODO: Buttons to filter items for specific patches/timeline
// TODO: Fails on comma sepator

libxml_use_internal_errors(true);

$files = scandir("../data/classicdbscrabes/");
if ($files === false) {
    throw new Exception("Dir not found");
}

$itemIdsToCrawl = [];
foreach ($files as $file) {
    $itemIdsToCrawl[] = str_replace(".html", "", $file);
}

function getPatchByItemId(int $itemId) {
    // 1.3.0    Dire Maul              18022 - 18538
    // 1.4.0    Elemental              18538 - 18879
    // 1.6.0    BWL                    18879 - 19509
    // 1.7.0    ZG                     19510 - 20266
    // 1.8.0    Nightmare, Silithid    20267 - 20698
    // 1.9.0    Gates of AQ            20699 - 21891
    // 1.10.0   Dungeon Set 2          21994 - 22412
    // 1.11.0   Naxx                   22416 - 23243
    $patchNames = ["1.0.0", "1.3.0", "1.4.0", "1.6.0", "1.7.0", "1.8.0", "1.9.0", "1.10.0", "1.11.0"];
    $patchRanges = [18021, 18538, 18879, 19509, 20266, 20698, 21891, 22412, 24222];
    $i = 0;
    foreach ($patchRanges as $patch) {
        if ($itemId <= $patch) {
            break;
        }
        $i++;
    }
    return $patchNames[$i];
}

$climate = new CLImate();

$parseAndStoreData = function($contents, $itemId) use ($sql, $climate) {
    $document = new DOMDocument();
    $document->loadHTML($contents);
    $element = $document->getElementById("tooltip$itemId-generic");

    $itemsStatRegex = ItemsStatRegex::$array;

    if (preg_match('/\<b.*class="\S\d"\>(.*)\<\/b\>/', $contents, $matches) === 0) {
        unlink("../data/classicdbscrabes/$itemId.html");
        $climate->red("No itemName found. ($itemId)");
        return;
    }

    if (strpos($matches[0], "Pattern:")) {
        //$climate->yellow("This is a Pattern");
        return;
    }

    if (strpos($matches[0], "Schematic:")) {
        //$climate->yellow("This is a Schematic");
        return;
    }

    if (strpos($matches[0], "Plans:")) {
        //$climate->yellow("This is a Plan");
        return;
    }

    $itemName = $matches[1];

    if ($element == null) {
        $climate->red("No dom element found $itemName ($itemId)");
        return;
    }

    $iconName = null;
    if (preg_match("/ShowIconName\('(.*)'\)/", $contents, $matches) === 0) {
        $climate->red("No icon name found $itemName ($itemId)");
        return;
    }
    $iconName = $matches[1];

    // Remove Set:
    $strippedContents = $element->textContent;
    $strippedContents = preg_replace("/Set: [\s\S]*/", "", $strippedContents);

    // Remove Use:
    $strippedContents = preg_replace("/Use: [\s\S]*/", "", $strippedContents);

    // Remove Equip
    $strippedContents = preg_replace("/Equip: [\s\S]*/", "", $strippedContents);

    // Remove item set
    $strippedContents = preg_replace("/\(\d\/\d\)[\s\S]*/", "", $strippedContents);

    // Readd equip effects
    if (preg_match_all("/Equip: .*?\./", $element->textContent, $matches)) {
        foreach ($matches[0] as $match) {
            $strippedContents .= $match;
        }
    }

    $slotName = null;
    $typeName = null;
    if (preg_match('/<table width="100%"><tr><td>([\D]*?)<\/td><th>([\D]*?)<\/th>/', $contents, $matches)) {
        $slotName = $matches[1];
        $typeName = $matches[2];
    }

    if (empty($slotName) && preg_match('/<table width="100%"><tr><td>(Trinket)<\/td>/', $contents, $matches)) {
        $slotName = $matches[1];
    }

    if (empty($typeName)) {
        if ($slotName === 'Held In Off-Hand') {
            $typeName = 'Held In Off-Hand';
            $slotName = 'Off Hand';
        } else {
            $typeName = $slotName;
        }
    }

    if ($slotName == null) {
        if (!preg_match('/<a class="q1" href=".*?">This Item Begins a Quest<\/a>/', $contents, $matches)) {
            //$climate->yellow("Skipping $itemName ($itemId) no matched slot");
        }
        return;
    }

    $statsParsed = [];

    // Match stats regex.
    foreach($itemsStatRegex as $key => $value) {
        $regEx = $itemsStatRegex[$key]['regex'];
        $statsParsed[$key] = 0;
        if (preg_match_all("/$regEx/m", $strippedContents, $matches)) {
            foreach ($matches[1] as $match) {
                $floatValue = floatval($match);
                $statsParsed[$key] += $floatValue;
            }
        }
    }


    $statsParsed['itemId'] = $itemId;
    $statsParsed['itemName'] = $itemName;
    $statsParsed['iconName'] = $iconName;
    $statsParsed['slotName'] = $slotName;
    $statsParsed['typeName'] = $typeName;
    $statsParsed['uniqueItem'] = preg_match('/Unique/', $strippedContents) ? 1 : 0;

    // Parse item level.
    if (preg_match('/Level: (\d*)/', $contents, $matches)) {
        $statsParsed['itemLevel'] = $matches[1];
    }

    // Parse required level.
    if (preg_match('/>Requires Level (\d*)</', $contents, $matches)) {
        $statsParsed['requiresLevel'] = $matches[1];
    }

    if (preg_match('/<\/b><br \/>(Binds when equipped|Binds when picked up+?)/', $contents, $matches)) {
        $map = [
            "Binds when equipped" => "equipped",
            "Binds when picked up" => "pickup"
        ];
        if (isset($map[$matches[1]])) {
            $statsParsed['bindOn'] = $map[$matches[1]];
        } else {
            $m = $matches[1];
            $climate->yellow("Unknown bind on $itemId $m");
        }
    }

    // Parse item rarity
    if (preg_match('/\<b.*class="(\S\d)".*\/b\>/', $contents, $matches)) {
        $map = [
            'q0' => "poor",
            'q1' => "common",
            'q2' => "uncommon",
            'q3' => "rare",
            'q4' => "epic",
            "q5" => "legendary"
        ];
        if (isset($map[$matches[1]])) {
            $statsParsed['rarity'] = $map[$matches[1]];
        } else {
            $q = $matches[1];
            $climate->yellow("Unknown rarity $itemId $q");
        }
    }

    $statsParsed['patch'] = getPatchByItemId((int)$itemId);

    $keys = implode(",", array_keys($statsParsed));
    $keysColon = implode(",:", array_keys($statsParsed));
    $updateKeys = [];
    foreach (array_keys($statsParsed) as $key) {
        $updateKeys[] = "$key=VALUES($key)";
    }
    $updateKeys = implode(",", $updateKeys);
    $query = "INSERT INTO item_stats ($keys) VALUES (:$keysColon) ON DUPLICATE KEY UPDATE $updateKeys";

    try {
        $sql->execute($query, $statsParsed);
    } catch (Throwable $ex) {
        $climate->red("---\nItemId: '$itemId'\n$ex");
        return;
    }

    // Insert, update, rmote classes for item.
    if (preg_match("/Classes: (.*?)(?:Requires|Equip|$)/", $strippedContents, $matches)) {
        foreach(explode(",", $matches[1]) as $className) {
            $query = "INSERT INTO item_classes (itemId, itemName, className) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE itemId=VALUES(itemId), className=VALUES(className), itemName=VALUES(itemName)";
            $sql->execute($query, [$itemId, $itemName, trim($className)]);
        }
    }
};

// Truncate tables
$sql->execute("TRUNCATE item_stats", []);
$sql->execute("TRUNCATE item_classes", []);

$promises = [];

// Initiate http requests.
foreach ($itemIdsToCrawl as $itemId) {

    $path = "../data/classicdbscrabes/$itemId.html";
    if (file_exists($path)) {
        $parseAndStoreData(file_get_contents($path), $itemId);
        continue;
    }

    $client = new Client();
    $promise = $client->requestAsync('GET', "http://classicdb.ch/?item=$itemId");
    $promise->then(function(ResponseInterface $response) use ($parseAndStoreData, $itemId){
        $contents = $response->getBody()->getContents();
        $path = "../data/classicdbscrabes/$itemId.html";
        file_put_contents($path, $contents);
        chown($path, 'www-data');
        chgrp($path, 'www-data');
        $parseAndStoreData($contents, $itemId);
    }, function(RequestException $ex) use ($itemId, $climate) {
        $message = $ex->getMessage();
        $climate->red("ItemId: '$itemId'\n$message");
    })->then(null, function(Throwable $ex) use ($itemId, $climate) {
        $climate->red("ItemId: '$itemId''\n$ex");
    });
    $promises[] = $promise;
}

$each = new EachPromise($promises, [
    'concurrency' => 100
]);

// Start items refreshing
$p = $each->promise();
$p->wait();

// Non parsable class specifics
$sql->execute("INSERT INTO item_classes VALUES (20037, 'Arcane Crystal Pendant', 'Mage')", []);

// Insert Atiesh, Greatstaff of the Guardian manually
$sql->execute("INSERT INTO item_classes VALUES (22632, 'Atiesh, Greatstaff of the Guardian', 'Druid')", []);
$sql->execute("INSERT INTO item_classes VALUES (22631, 'Atiesh, Greatstaff of the Guardian', 'Priest')", []);
$sql->execute("INSERT INTO item_classes VALUES (22630, 'Atiesh, Greatstaff of the Guardian', 'Warlock')", []);
$sql->execute("INSERT INTO item_classes VALUES (22589, 'Atiesh, Greatstaff of the Guardian', 'Mage')", []);
$sql->execute("UPDATE item_stats SET stamina = 28, intellect = 28, spirit = 27, healing = 362, spellDmg = 120 WHERE itemId = 22632", []);
$sql->execute("UPDATE item_stats SET stamina = 28, intellect = 28, spirit = 27, mana5 = 11, healing = 300, attackPower = 420 WHERE itemId = 22631", []);
$sql->execute("UPDATE item_stats SET stamina = 30, intellect = 29, spellCrit = 2, spellDmg = 183 WHERE itemId = 22630", []);
$sql->execute("UPDATE item_stats SET stamina = 31, intellect = 32, spirit = 24, spellHit = 2, spellCrit = 2, spellDmg = 150 WHERE itemId = 22589", []);

// Remove test items.
$sql->execute("DELETE FROM item_stats WHERE itemId = ?", [19879]); // Alex's Test Beatdown Staff
$sql->execute("DELETE FROM item_stats WHERE itemId = ?", [18970]); // Ring of Critical Testing 2
$sql->execute("DELETE FROM item_stats WHERE itemId = ?", [13262]); // Ashbringer
$sql->execute("DELETE FROM item_stats WHERE itemId = ?", [17782]); // Talisman of Binding Shard
$sql->execute("DELETE FROM item_stats WHERE itemId = ?", [23051]); // Glaive of the Defender
$sql->execute("DELETE FROM item_stats WHERE itemId = ?", [22736]); // Andonisus, Reaper of Souls

$climate->blue("All done!!!");

