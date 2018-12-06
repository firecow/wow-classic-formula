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

require 'vendor/autoload.php';
require 'error.php';

$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());

// TODO: Icon name
// TODO: Fishing, Mining, Attack Power against (xxx)
// TODO: Buttons to filter items for specific patches.
// TODO: Quest/Drop/PVP... More granulated locations.

libxml_use_internal_errors(true);

$items = $sql->raw("SELECT itemId, itemName FROM item_stats")->fetchAll();
/*$items = [
    ["itemId" => 21563],
    ["itemId" => 1009],
    ["itemId" => 23319]
];*/

$climate = new CLImate();

$parseAndStoreData = function($contents, $itemId) use ($sql, $climate) {
    $document = new DOMDocument();
    $document->loadHTML($contents);
    $element = $document->getElementById("tooltip$itemId-generic");

    $itemsStatRegex = ItemsStatRegex::$array;

    if (!preg_match('/\<b.*class="\S\d"\>(.*)\<\/b\>/', $contents, $matches)) {
        $climate->red("No itemName found. ($itemId)\n");
        return;
    }
    $itemName = $matches[1];

    if ($element == null) {
        $climate->red("No dom element found $itemName ($itemId)\n");
        return;
    }

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
        $typeName = $slotName;
    }

    if ($slotName == null) {
        if (!preg_match('/<a class="q1" href=".*?">This Item Begins a Quest<\/a>/', $contents, $matches)) {
            $climate->yellow("Skipping $itemName ($itemId) no matched slot");
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
    $statsParsed['slotName'] = $slotName;
    $statsParsed['typeName'] = $typeName;
    $statsParsed['uniqueItem'] = preg_match('/Unique/', $strippedContents) ? 1 : 0;

    // Parse item level.
    if (preg_match('/Level: (\d*)/', $contents, $matches)) {
        $statsParsed['itemLevel'] = $matches[1];
    }

    // Parse required level.
    if (preg_match('/Requires Level (\d*)/', $contents, $matches)) {
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

    $keys = implode(",", array_keys($statsParsed));
    $keysColon = implode(",:", array_keys($statsParsed));
    $updateKeys = [];
    foreach (array_keys($statsParsed) as $key) {
        $updateKeys[] = "$key=VALUES($key)";
    }
    $updateKeys = implode(",", $updateKeys);
    $query = "INSERT INTO item_stats ($keys) VALUES (:$keysColon) ON DUPLICATE KEY UPDATE $updateKeys";
    $sql->execute($query, $statsParsed);

    // Insert, update, rmote classes for item.
    if (preg_match("/Classes: (.*?)(?:Requires|Equip|$)/", $strippedContents, $matches)) {
        foreach(explode(",", $matches[1]) as $className) {
            $query = "INSERT INTO item_classes (itemId, itemName, className) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE itemId=VALUES(itemId), className=VALUES(className), itemName=VALUES(itemName)";
            $sql->execute($query, [$itemId, $itemName, trim($className)]);
        }
    }
};

$promises = [];

// Initiate http requests.
foreach ($items as $item) {
    $itemId = $item['itemId'];

    $path = "../data/$itemId.html";
    if (file_exists($path)) {
        $parseAndStoreData(file_get_contents($path), $itemId);
        continue;
    }

    $client = new Client();
    $promise = $client->requestAsync('GET', "http://classicdb.ch/?item=$itemId");
    $promise->then(function(ResponseInterface $response) use ($parseAndStoreData, $itemId){
        $contents = $response->getBody()->getContents();
        $path = "../data/$itemId.html";
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
$p = $each->promise();
$p->wait();
$climate->blue("All done!!!");

