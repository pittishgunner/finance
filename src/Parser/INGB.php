<?php

namespace App\Parser;

use App\Entity\Record;
use DateTime;
use DateTimeImmutable;
use SplFileObject;

class INGB extends BaseParser
{
    public function getName(): string
    {
        return 'ING';
    }

    public function predictRecord(string $text): array
    {
        $ignored = [
            'tocmai ai incercat sa accesezi home',
            'Apasa aici pentru a aproba sau anula',
        ];
        foreach ($ignored as $case) {
            if (strstr(strtolower($text), strtolower($case))) {
                return ['ignored' => $text];
            }
        }

        $cases = [
            'plati POS' => '/Ai autorizat tranzactia de (?<debit>(.*)) (?<currency>(\w{3})) la (?<description>(.*)) din contul (?<account>(\d+)) in (?<date>(\d{1,4}-\d{1,2}-\d{2,4})). Sold: (?<balance>(.*))./mi',
            'intrari diverse' => '/Suma (?<credit>(.*)) (?<currency>(\w{3})) a fost creditata in (?<date>(\d{1,4}-\d{1,2}-\d{2,4})) in contul (?<account>(\d+)) - (?<description>(.*)). Sold: (?<balance>(.*))./mi',
            'intrari diverse2' => '/Suma (?<credit>(.*)) (?<currency>(\w{3})) a fost creditata in (?<date>(\d{1,4}-\d{1,2}-\d{2,4})) in contul (?<account>(\d+)) de catre (?<description>(.*)). Sold: (?<balance>(.*))./mi',
            'transferuri' => '/Suma (?<debit>(.*)) (?<currency>(\w{3})) a fost debitata in (?<date>(\d{1,4}-\d{1,2}-\d{2,4})) din contul (?<account>(\d+)) - (?<description>(.*)). Sold: (?<balance>(.*))./mi',
            'round-up' => '/Ai economisit (?<debit>(.*)) (?<currency>(\w{3})) prin (?<description>(.*))./mi',
            'retrageri ATM' => '/Ai retras (?<debit>(.*)) (?<currency>(\w{3})) la (?<description>(.*)) din contul (?<account>(\d+)) in (?<date>(\d{1,4}-\d{1,2}-\d{2,4})). Sold: (?<balance>(.*))./mi',
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
                    if ($matchKey === 'balance' && !empty($matches['currency'])) {
                        $matches[$matchKey] = $this->getPredictionFloatValue(
                            str_replace(' ' . $matches['currency'] , '', $matches[$matchKey])
                        );
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

    public function parseFile(?SplFileObject $fileData): array
    {
        $fileData->seek($fileData->getSize());

        $index = 0;
        $lastHeader = 0;
        $subData = [];
        $columns = 0;

        foreach ($fileData as $lineIndex => $line) {
            $index++;
            if ($index === 1) {
                continue;
            }
            if (!empty($line[0])) {
                $lastHeader = $lineIndex;
            }

            $subData[$lastHeader][] = $line;
            $columns = count($line);
        }

        $subDataMethod = 'getSubDataFrom' . $columns . 'ColumnsFormat';

        return $this->$subDataMethod($subData);
    }

    private function getSubDataFrom8ColumnsFormat(array $subData): array
    {
        $data = [];

        foreach ($subData as $lines) {
            $description = '';
            $debit = $credit = $balance = 0;
            $date = false;

            $index = 0;
            $details = [];
            foreach ($lines as $subLine) {
                $index++;
                if ($index === 1) {
                    $date = self::parseDate($subLine[0]);
                    if ($date === false) {
                        continue 2;
                    }
                    if (!empty($subLine[4])) {
                        $debit = self::getFloatValue($subLine[4]);
                        $credit = 0;
                    }
                    if (!empty($subLine[6])) {
                        $debit = 0;
                        $credit = self::getFloatValue($subLine[6]);
                    }
                    if (!empty($subLine[7])) {
                        $balance = self::getFloatValue($subLine[7]);
                    }
                }
                if (!empty($subLine[3])) {
                    $exploded = array_map('trim', explode(':', $subLine[3]));
                    // export will change its details after a transaction has been finished
                    // by adding a line and spaces on each line
                    if (!empty($exploded[0]) && $exploded[0] === 'Data finalizării(decontării)') {
                        continue;
                    }

                    $description .= implode(':', $exploded) . "\n";
                    $details[$exploded[0]] = $exploded[1] ?? '';
                }
            }

            // Get actual authorization date
            if (!empty($details['Data']) && strstr($details['Data'], ' Autorizare')) {
                try {
                    $dateTimeImmutable = DateTimeImmutable::createFromFormat('d-m-Y', str_replace(' Autorizare', '', $details['Data']));
                    if ($date !== false) {
                        $date = new DateTime();
                        $date->setTimestamp($dateTimeImmutable->getTimestamp());
                    }
                } catch (\Exception) {

                }
            }

            if ($credit === 0 && $debit === 0) {
                dd($subData);
            }
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
                'description' => trim($description),
                'details' => $details,
            ];
        }

        return $data;
    }

    private function getSubDataFrom7ColumnsFormat(array $subData): array
    {
        $data = [];

        foreach ($subData as $lines) {
            $description = '';
            $debit = $credit = $balance = 0;
            $date = false;

            $index = 0;
            $details = [];
            foreach ($lines as $subLine) {
                $index++;
                if ($index === 1) {
                    $date = self::parseDate($subLine[0]);
                    if ($date === false) {
                        continue 2;
                    }
                    if (!empty($subLine[5])) {
                        $debit = self::getFloatValue($subLine[5]);
                        $credit = 0;
                    }
                    if (!empty($subLine[6])) {
                        $debit = 0;
                        $credit = self::getFloatValue($subLine[6]);
                    }
                }
                if (!empty($subLine[3])) {
                    $exploded = array_map('trim', explode(':', $subLine[3]));
                    // export will change its details after a transaction has been finished
                    // by adding a line and spaces on each line
                    if (!empty($exploded[0]) && $exploded[0] === 'Data finalizării(decontării)') {
                        continue;
                    }

                    $description .= implode(':', $exploded) . "\n";
                    $details[$exploded[0]] = $exploded[1] ?? '';
                }
            }
            if ($credit === 0 && $debit === 0) {
                dd($subData);
            }
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
                'description' => trim($description),
                'details' => $details,
            ];
        }

        return $data;
    }

    private function getSubDataFrom4ColumnsFormat(array $subData): array
    {
        $data = [];

        foreach ($subData as $lines) {
            $description = '';
            $debit = $credit = $balance = 0;
            $date = false;

            $index = 0;
            $details = [];
            foreach ($lines as $subLine) {
                $index++;
                if ($index === 1) {
                    $date = self::parseDate($subLine[0]);
                    if ($date === false) {
                        continue 2;
                    }
                    if (!empty($subLine[2])) {
                        $debit = self::getFloatValue($subLine[2]);
                        $credit = 0;
                    }
                    if (!empty($subLine[3])) {
                        $debit = 0;
                        $credit = self::getFloatValue($subLine[3]);
                    }
                }
                if (!empty($subLine[1])) {
                    $exploded = array_map('trim', explode(':', $subLine[1]));
                    // export will change its details after a transaction has been finished
                    // by adding a line and spaces on each line
                    if (!empty($exploded[0]) && $exploded[0] === 'Data finalizării(decontării)') {
                        continue;
                    }

                    $description .= implode(':', $exploded) . "\n";
                    $details[$exploded[0]] = $exploded[1] ?? '';
                }
            }

            if ($credit === 0 && $debit === 0) {
                dd($subData);
            }

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
                'description' => trim($description),
                'details' => $details,
            ];
        }

        return $data;
    }

    private static function getFloatValue(string $value): float
    {
        return !empty($value) ? floatval(str_replace(['.', ','], ['', '.'], $value)) : 0;
    }

    private static function getPredictionFloatValue(string $value): float
    {
        return !empty($value) ? floatval(str_replace([','], [''], $value)) : 0;
    }

    private static function parseDate($d): bool|DateTime
    {
        $m = ['ianuarie' => '01', 'februarie' => '02', 'martie' => '03', 'aprilie' => '04', 'mai' => '05', 'iunie' => '06', 'iulie' => '07', 'august' => '08', 'septembrie' => '09', 'octombrie' => '10', 'noiembrie' => '11', 'decembrie' => '12'];
        $t = explode(' ', $d);
        if (empty($t[1]) || empty($m[$t[1]])) {
            return false;
        }

        return DateTime::createFromFormat('Y-m-d H:i:s', $t[2] . '-' . $m[$t[1]] . '-' . $t[0] . ' 00:00:00');
    }

    public function getUnmatchedData(Record $record): array
    {
        $details = json_decode($record->getDetails(), true);
        $key = '';

        $mapping = [
            'Cumparare POS' => fn($details) => 'Cumparare POS - ' . $details['Terminal'],
            'Transfer Home\'Bank' => fn($details) => 'Transfer Home\'Bank - ' . $details['Beneficiar'],
            'Plata online' => fn($details) => 'Plata online - ' . $details['Beneficiar'],
            'Incasare' => fn($details) => 'Incasare - ' . ($details['Ordonator'] ?? $details['Detalii']),
            'Cumparare POS - stornare' => fn($details) => 'Cumparare POS - stornare - ' . $details['Terminal'],
            'Plata debit direct' => fn($details) => 'Plata online - ' . $details['Beneficiar'],
            'Incasare via card' => fn($details) => 'Incasare via card - ' . $details['Terminal'],
        ];
        $mappedKeys = [];

        foreach ($mapping as $keyType => $callback) {
            $mappedKeys[] = $keyType . ' - ';
            if (isset($details[$keyType])) {
                $key = $callback($details);
                break;
            }
        }

        if (empty($key)) {
            $key = array_key_first($details);
            if ($key === 'notifiedAt') {
                $key = $details['notification'];
            }
        }

        return [
            'key' => $key,
            'value' => str_replace($mappedKeys, '', $key),
            'description' => $record->getDescription(),
            'details' => $details,
            'debit' => $record->getDebit(),
            'credit' => $record->getCredit(),
        ];

    }
}
