<?
declare(strict_types=1);

namespace App;

class Random
{

    public function getRandomMD5()
    {
        return md5(uniqid("", true));
    }

}