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

    public function groupedExpenses(string $from, string $to, string $type = 'daily'): array
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
        $records = $this->recordRepository->dailyForPeriod($from, $to);
        $byDate = $labels = [];
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
            //echo $categoryString . "<br/>";

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
        }
        $dataSets = [];
        foreach ($allCategoriesInSet as $dataSetKey => $dataSetOptions) {
            $dataSet = [
                'label' => $dataSetOptions['label'] ?? $dataSetKey,
                'data' => [],
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
            }

            $dataSets[] = $dataSet;
        }
//        dump($byDate);
//        dd($dataSets);
        //dump($allCategoriesInSet);dd($byDate);

//
//        $dataSets[0] = [
//            'label' => 'Total spent',
//            'backgroundColor' => 'rgb(255, 99, 132)',
//            'borderColor' => 'rgb(255, 99, 132)',
//            'data' => $totalSpent,
//        ];
        //dump($labels);
        //dd($dataSets);


        return [
            'labels' => $labels,
            'datasets' => $dataSets,
        ];
    }
}
