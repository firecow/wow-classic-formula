<?php


declare(strict_types=1);

use App\Config;
use App\SQL;

require '/php/vendor/autoload.php';
require '/php/error.php';


$config = new Config();
$sql = new SQL($config->getPDODataSourceName(), $config->getPDOUsername(), $config->getPDOPassword());

$files = ["/dumps/crafting.en.lua"];

echo "Converting\n";

foreach ($files as $fileName) {
    $contents = file_get_contents($fileName);

    $contents = preg_replace("/--.*--/", "", $contents);
    $contents = preg_replace("/[\t ]*(.*) = {/", "'$1' => {", $contents);
    $contents = preg_replace("/'AtlasLoot_Data[\"AtlasLootCrafting\"]' =>/", '$map =', $contents);
    $contents = preg_replace("/{/", "[", $contents);
    $contents = preg_replace("/}/", "]", $contents);
    $contents = preg_replace("/];/", "],", $contents);


    $php = "<?php\n";
    $php .= $contents . ";";

    $phpFileName = str_replace(".lua", ".php", $fileName);
    echo "$phpFileName\n";
    file_put_contents($phpFileName, $php);
}

echo "Success\n";