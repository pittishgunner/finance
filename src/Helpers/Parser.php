<?php

namespace App\Helpers;

use App\Parser\BaseParser;

class Parser
{
    public static function getBankCsvParser(string $iban): BaseParser
    {
        $substr = substr($iban, 4, 4);

        $className = '\\App\\Parser\\' . $substr;

        if (class_exists($className)) {
            return new $className($className);
        }

        return new BaseParser($className);
    }
}
