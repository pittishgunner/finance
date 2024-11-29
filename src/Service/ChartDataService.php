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

    public function dailyExpenses(string $from, string $to): array
    {

        $records = $this->recordRepository->dailyForPeriod($from, $to);
        $byDate = [];
        foreach ($records as $record) {
            $tags = $this->tagService->getTags($record, true);
            if (!empty($tags->getValues())) {
                //dd($tags->getValues());
            }
            $date = $record->getDate()->format('j M Y');
            if (!isset($byDate[$date])) {
                $byDate[$date] = 0;
            }
            $byDate[$date] = $byDate[$date] + $record->getDebit();
        }
        //ksort($byDate);

        $labels = $data = [];
        foreach ($byDate as $day => $value) {
            $labels[] = $day;
            $data[] = $value;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total spent',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $data,
                ],
            ],
        ];
    }
}
