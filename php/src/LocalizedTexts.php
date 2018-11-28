<?php
declare(strict_types=1);

namespace App;

use App\Encoding\JSON;

class LocalizedTexts
{
    private $localizedTextsData;
    private $languageCode;

    public function __construct()
    {
        $fileContent = File::loadFileTextContent("localizedtexts.json");
        $this->localizedTextsData = JSON::decode($fileContent);
        $this->languageCode = 'eng';
    }

    public function getText(string $key, ...$args): string
    {
        $localizedTextsData = $this->localizedTextsData[$this->languageCode];
        if (isset($localizedTextsData[$key])) {
            return sprintf($localizedTextsData[$key], ...$args);
        }
        return "[[[[$key]]]]]";
    }
}
