<?php

namespace App\Parser;

use App\Entity\Record;
use DateTime;
use SplFileObject;

class REVO extends BaseParser
{
    public function getName(): string
    {
        return 'Revolut';
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
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $line[2]);
                $amount = self::getFloatValue($line[5]);

                $data[] = [
                    'date' => $date,
                    'debit' => $amount < 0 ? abs($amount) : 0,
                    'credit' => $amount > 0 ? abs($amount) : 0,
                    'balance' => self::getFloatValue($line[9]),
                    'description' => $line[4],
                    'details' => ['type' => $line[0]],
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
        $key = $record->getDescription();

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
