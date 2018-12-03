<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

require "error.php";
require "luaparser.php";

ini_set('memory_limit', '-1');

$luaParser = new Lua();

$luaStr = file_get_contents("/dumps/instances.en.lua");

$luaStr = preg_replace("/= \{/m", ":", $luaStr);

file_put_contents("/dumps/instances.json", $luaStr);
echo "Success\n";