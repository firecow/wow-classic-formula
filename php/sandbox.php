<?php
declare(strict_types=1);


$a = 55;

$func = function() {
    global $a;
    echo "Just Before Inside Func $a\n";
    $a = 100000000;
    echo "Just After Inside Func $a\n";
};

echo "Global before func $a\n";
$func();
echo "Global after func $a\n";

$teori = function() {
    global $a;
    echo "Inside Teori $a\n";
};

echo "Global before teori $a\n";
$teori();
echo "Global after teori $a\n";