<?php

namespace App\Parser;

use App\Entity\Record;
use DateTime;
use Exception;
use SplFileObject;

class REVO extends BaseParser
{
    public function getName(): string
    {
        return 'Revolut';
    }

    public function predictRecord(string $text): array
    {
        $ignored = [
            'replace this text with parts of the notification to be ignored',
            'replace this text with parts of the notification to be ignored',
        ];
        foreach ($ignored as $case) {
            if (strstr(strtolower($text), strtolower($case))) {
                return ['ignored' => $text];
            }
        }

        $cases = [
            'transferuri intrate' => '/(?<description>(.*)) Has sent you (?<currency>(\w{3}))(?<credit>(.*)). Tap to say thank you(.*)/mi',
        ];

        foreach ($cases as $key => $case) {
            preg_match($case, $text, $matches);
            if (!empty($matches)) {
                $matches['matched'] = $key;
                $matches['string'] = $text;
                foreach ($matches as $matchKey => $match) {
                    if (is_integer($matchKey)) {
                        unset($matches[$matchKey]);
                    }
                    if (!empty($matches['currency'])) {
                        $matches['account'] = 'REVOR' . $matches['currency'];
                    }
                    if ($matchKey === 'debit' || $matchKey === 'credit') {
                        $matches[$matchKey] = $this->getPredictionFloatValue($matches[$matchKey]);
                    }
                }
                ksort($matches);

                return $matches;
            }
        }

        return ['unmatched' => $text];
    }

    /**
     * @throws Exception
     */
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
                if (!$date) {
                    throw new Exception('Could not parse a date for ' . $line[2]);
                }
                $amount = self::getFloatValue($line[5]);

                $data[] = [
                    'date' => $date->format('Y-m-d'),
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

    private static function getPredictionFloatValue(string $value): float
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
