<?php

namespace App\Parser;

use App\Entity\Record;
use DateTime;
use SplFileObject;

class BTRL extends BaseParser
{
    public function getName(): string
    {
        return 'Banca Transilvania';
    }

    public function parseFile(?SplFileObject $fileData): array
    {
        $data = [];

        if (!empty($fileData)) {
            $index = 0;
            foreach ($fileData as $line) {
                $index++;
                if ($index === 1) {
                    continue;
                }
                $date = DateTime::createFromFormat('Y-m-d', $line[0]);
                $data[] = [
                    'date' => $date,
                    'debit' => abs(self::getFloatValue($line[4])),
                    'credit' => abs(self::getFloatValue($line[5])),
                    'balance' => abs(self::getFloatValue($line[6])),
                    'description' => $line[2],
                    'details' => $line,
                ];
            }
        }

        return $data;
    }

    private static function getFloatValue(string $value): float
    {
        return !empty($value) ? floatval(str_replace(',', '', $value)) : 0;
    }

    public function getUnmatchedData(Record $record): array
    {

        $details = json_decode($record->getDetails(), true);
        $key = '';
        if ($record->getDescription() === 'Abonament BT 24') {
            $key = 'Abonament BT 24';
        }
        if (empty($key)) {
            $key = $details[2];
        }

        if (empty($key)) {
            dd($record->getDescription(), $details);
        }

        return [
            'key' => $key,
            'value' => $key,
            'description' => $record->getDescription(),
            'details' => $details,
            'debit' => $record->getDebit(),
            'credit' => $record->getCredit(),
        ];

    }
}
