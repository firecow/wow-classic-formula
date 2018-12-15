<?php


declare(strict_types=1);

use App\Config;
use App\Encoding\JSON;
use App\SQL;

require '/php/vendor/autoload.php';
require '/php/error.php';


$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());

$json = JSON::decode(file_get_contents("/dumps/instances.json"));

$instances = [
    "Mara",
    "BRD",
    "DME",
    "DMN",
    "DMW",
    "DMTome",
    "UBRS",
    "STRAT",
    "ST",
    "LBRS",
    "SCHOLO",
    "UldTrash",
    "AQOpening",
];

$raids = [
    "BWL",
    "MC",
    "Onyxia",
    "ZG",
    "AQ20",
    "AQ40",
    "NAX",
];

$renameMap = [
    "Mara" => "Mara",
    "DMTome" => "DM",
    "UldTrash" => "BOE",
    "AQOpening" => "AQO",
    "Onyxia" => "ONY",
];

$raidRegExp = "/^(";
$raidRegExp .= implode("|", $raids);
$raidRegExp .= ")(.*)/";

$instanceRegExp = "/^(";
$instanceRegExp .= implode("|", $instances);
$instanceRegExp .= ")(.*)/";

$sql->execute("TRUNCATE item_locations", []);

// Raid and dungeon items.
foreach ($json as $atlasKey => $list) {
    if (preg_match($instanceRegExp, $atlasKey, $matches)) {
        $instanceName = $matches[1];
        $bossName = $matches[2];

        if (isset($renameMap[$instanceName])) {
            $instanceName = $renameMap[$instanceName];
        }

        foreach ($list as $itemData) {
            $itemId = $itemData[0];
            if ($itemId != 0) {
                $itemName = preg_replace("/=>.*=>/", "", $itemData[2]);
                $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $instanceName, $bossName, 0]);
            }
        }

    } else if (preg_match($raidRegExp, $atlasKey, $matches)) {
        $raidName = $matches[1];
        $bossName = $matches[2];

        if (isset($renameMap[$raidName])) {
            $raidName = $renameMap[$raidName];
        }

        foreach ($list as $itemData) {
            $itemId = $itemData[0];
            if ($itemId != 0) {
                $itemName = preg_replace("/=>.*=>/", "", $itemData[2]);
                $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $raidName, $bossName, 1]);
            }
        }

    } else {
        //echo "Skipping: $atlasKey\n";
    }
}

// Crafted items.
$json = JSON::decode(file_get_contents("/dumps/crafting.en.json"));
foreach ($json as $atlasKey => $list) {
    $instanceName = "Crafted";

    foreach ($list as $itemData) {
        $itemName = preg_replace("/=.*=/", "", $itemData[2]);

        if (!empty($itemName)) {
            try {
                $itemId = $sql->fetchColumnInt("SELECT itemId FROM item_stats WHERE itemName = ?", [$itemName]);
                $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $instanceName, '', 0]);
            } catch (\App\Exceptions\DAOException $ex) {
                //echo "Exception : ($itemName)\n";
            }
        } else {
            //echo "Skipped : ($itemName)\n";
        }
    }
}

// World bosses
$json = JSON::decode(file_get_contents("/dumps/worldbosses.json"));
foreach ($json as $atlasKey => $list) {

    foreach ($list as $itemData) {
        $itemId = $itemData[0];
        $itemName = preg_replace("/=.*=/", "", $itemData[2]);
        if ($itemId > 0) {
            $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $atlasKey, '', 0]);
        }
    }
}

// World Events
$json = JSON::decode(file_get_contents("/dumps/worldevents.json"));
foreach ($json as $atlasKey => $list) {

    foreach ($list as $itemData) {
        $itemId = $itemData[0];
        $itemName = preg_replace("/=.*=/", "", $itemData[2]);

        if ($itemId > 0) {
            $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $atlasKey, '', 0]);
        }
    }
}

// PVP Rep
$json = JSON::decode(file_get_contents("/dumps/pvp_rep.json"));
foreach ($json as $atlasKey => $list) {

    if (!preg_match("/(.*)Rep(.*?)(?:\d|$)/", $atlasKey, $matches)) {
        echo "No match $atlasKey\n";
        continue;
    }

    $battlegroundName = $matches[1];
    $repLevel = $matches[2];

    foreach ($list as $itemData) {
        $itemId = $itemData[0];
        $itemName = preg_replace("/=.*=/", "", $itemData[2]);

        if ($itemId > 0) {
            $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $battlegroundName, $repLevel, 0]);
        }
    }
}

// PVP Honor
$json = JSON::decode(file_get_contents("/dumps/pvp_honor.json"));
foreach ($json as $atlasKey => $list) {

    foreach ($list as $itemData) {
        $itemId = $itemData[0];
        $itemName = preg_replace("/=.*=/", "", $itemData[2]);

        if ($itemId > 0) {
            $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, 'PVP', '', 0]);
        }
    }
}

// Sets
$map = [
    "T3" => "NAX",
    "World" => "BOE",
    "AQ20" => "AQ20",
    "AQ40" => "AQ40",
    "ZG" => "ZG",
    "T0" => "T0",
];

$json = JSON::decode(file_get_contents("/dumps/sets.json"));
foreach ($json as $atlasKey => $list) {

    if (!preg_match("/(T3|World|AQ20|AQ40|ZG|T0)/", $atlasKey, $matches)) {
        echo "No match $atlasKey\n";
        continue;
    }

    $match = $matches[1];
    $location = $map[$match];

    //echo "$atlasKey $match $location\n";
    foreach ($list as $itemData) {
        $itemId = $itemData[0];
        $itemName = preg_replace("/=.*=/", "", $itemData[2]);

        if ($itemId > 0) {
            $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $location, '', 0]);
        }
    }
}

// Rep factions
$json = JSON::decode(file_get_contents("/dumps/factions.json"));
foreach ($json as $atlasKey => $list) {

    if (!preg_match("/(.*)(?:\d|$)/", $atlasKey, $matches)) {
        echo "No match $atlasKey\n";
        continue;
    }

    $location = "Rep";
    $locationComment = $matches[1];

    //echo "$atlasKey $match $location\n";
    foreach ($list as $itemData) {
        $itemId = $itemData[0];
        $itemName = preg_replace("/=.*=/", "", $itemData[2]);

        if ($itemId > 0) {
            $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $location, $locationComment, 0]);
        }
    }
}

// Items not included in json files. These are class specific.
// AQ Arnaments of War
// TODO: Add em all
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20717, "Desert Bloom Gloves", "AQ", "Quest - Armaments of War", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20716, "Sandworm Skin Gloves", "AQ", "Quest - Armaments of War", 0]);

// ZG Quests
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20217, "Belt of Tiny Heads", "ZG", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20216, "Belt of Preserved Heads", "ZG", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20215, "Belt of Shriveled Heads", "ZG", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20213, "Belt of Shrunken Heads", "ZG", "Quest", 0]);


// 1.10 The Perfect Poison AQ / ZG Queste
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [22378, "Ravenholdt Slicer", "AQ", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [22379, "Shivsprocket's Shiv", "AQ", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [22377, "The Thunderwood Poker", "AQ", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [22348, "Doomulus Prime", "AQ", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [22347, "Fahrad's Reloading Repeater", "AQ", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [22380, "Simone's Cultivating Hammer", "AQ", "Quest", 0]);

// Confront Yeh'kinya ZG
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20219, "Tattered Hakkari Cape", "ZG", "Quest", 0]);
$sql->execute("INSERT INTO item_locations VALUES (?, ?, ?, ?, ?)", [20218, "Faded Hakkari Cloak", "ZG", "Quest", 0]);