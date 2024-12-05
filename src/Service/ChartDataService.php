<?php

namespace App\Service;

use App\Repository\RecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use eduMedia\TagBundle\Service\TagService;

class ChartDataService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordRepository       $recordRepository,
        private TagService             $tagService,
    )
    {
    }

    public function groupedExpenses(string $from, string $to, string $type = 'daily', array $accountIds = []): array
    {
        switch ($type) {
            case 'weekly':
                $format = 'W Y';
                break;
            case 'monthly':
                $format = 'M Y';
                break;
            default:
                $format = 'j M Y';
                break;
        }
        $records = $this->recordRepository->dailyForPeriod($from, $to, $accountIds);
        $byDate = $labels = $tagsByCategoryString = [];
        $allCategoriesInSet = [
            'total' => [
                'label' => 'Total spent',
                'backgroundColor' => 'rgb(255, 99, 132)',
                'borderColor' => 'rgb(255, 99, 132)',
                'color' => 'rgb(0, 0, 0)',
            ]
        ];
        foreach ($records as $record) {
            if (!empty($record->getSubCategory())) {
                $categoryString = $record->getCategory()->getName() . ' - ' . $record->getSubCategory()->getName();
            } else {
                $categoryString = 'N/A';
            }
            if (!isset($allCategoriesInSet[$categoryString])) {
                $allCategoriesInSet[$categoryString] = [];
            }

            $date = $record->getDate()->format($format);
            if (!isset($byDate[$date]['total'])) {
                $byDate[$date]['total'] = 0;
                $labels[] = $date;
            }
            $byDate[$date]['total']= $byDate[$date]['total'] + $record->getDebit();

            if (!isset($byDate[$date][$categoryString])) {
                $byDate[$date][$categoryString] = 0;
            }
            $byDate[$date][$categoryString] = $byDate[$date][$categoryString] + $record->getDebit();


            $tags = $this->tagService->getTagNames($record, true);
            foreach ($tags as $tag) {
                if (!isset($tagsByCategoryString[$date][$categoryString][$tag])) {
                    $tagsByCategoryString[$date][$categoryString][$tag] = 0;
                }
                $tagsByCategoryString[$date][$categoryString][$tag] += $record->getDebit();
            }
        }

        $dataSets = [];
        foreach ($allCategoriesInSet as $dataSetKey => $dataSetOptions) {
            $dataSet = [
                'label' => $dataSetOptions['label'] ?? $dataSetKey,
                'data' => [],
                'tagData' => [],
            ];
            if (!empty($dataSetOptions['backgroundColor'])) {
                $dataSet['backgroundColor'] = $dataSetOptions['backgroundColor'];
            }
            if (!empty($dataSetOptions['borderColor'])) {
                $dataSet['borderColor'] = $dataSetOptions['borderColor'];
            }
            if (!empty($dataSetOptions['color'])) {
                $dataSet['color'] = $dataSetOptions['color'];
            }

            foreach ($byDate as $date => $categories) {
                if (isset($categories[$dataSetKey])) {
                    $dataSet['data'][] = $categories[$dataSetKey];
                } else {
                    $dataSet['data'][] = 0;
                }
               /* if ($dataSetKey !== 'total') {
                    dump($tagsByCategoryString);
                    dump($tagsByCategoryString[$date][$dataSetKey]);

                    dump($date);
                    dd($categories);
                }*/

                $tagData = [];
                if (!empty($tagsByCategoryString[$date][$dataSetKey])) {
                    foreach ($tagsByCategoryString[$date][$dataSetKey] as $tagKey => $tagValue) {
                        $tagData[$tagKey] = +$tagValue;
                    }
                }

                $dataSet['tagData'][] = $tagData;
            }

            $dataSets[] = $dataSet;
        }
        //dump($tagsByCategoryString);dd($dataSets);
        return [
            'labels' => $labels,
            'datasets' => $dataSets,
        ];
    }
}
