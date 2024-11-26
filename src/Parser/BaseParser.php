<?php

namespace App\Parser;

use App\Entity\Record;
use SplFileObject;

class BaseParser
{
    public function __construct(private $attemptedClassName)
    {

    }

    public function getName(): string
    {
        return 'UNKNOWN PARSER FOR BANK: ' . $this->attemptedClassName;
    }

    public function parseFile(?SplFileObject $fileData): array
    {
        return [];
    }

    public function getUnmatchedData(Record $record): array
    {
        return [];
    }
}
