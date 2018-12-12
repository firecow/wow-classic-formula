<?php


declare(strict_types=1);

use App\Config;
use App\Encoding\JSON;
use App\SQL;

require '../vendor/autoload.php';
require '../error.php';


$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());

$json = JSON::decode(file_get_contents("../dumps/instances.json"));

$instances = [
    "Mara",
    "ST",
    "BRD",
    "DME",
    "DMN",
    "DMW",
    "UBRS",
    "STRAT",
    "LBRS",
    "SCHOLO",
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

$raidRegExp = "/^(";
$raidRegExp .= implode("|", $raids);
$raidRegExp .= ")(.*)/";

$instanceRegExp = "/^(";
$instanceRegExp .= implode("|", $instances);
$instanceRegExp .= ")(.*)/";

$sql->execute("TRUNCATE item_locations", []);


foreach ($json as $atlasKey => $list) {
    if (preg_match($instanceRegExp, $atlasKey, $matches)) {
        $instanceName = $matches[1];
        $bossName = $matches[2];

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

        foreach ($list as $itemData) {
            $itemId = $itemData[0];
            if ($itemId != 0) {
                $itemName = preg_replace("/=>.*=>/", "", $itemData[2]);
                $sql->execute("REPLACE INTO item_locations VALUES (?, ?, ?, ?, ?)", [$itemId, $itemName, $raidName, $bossName, 1]);
            }
        }

    } else {
        echo "Skipping: $atlasKey\n";
    }
}