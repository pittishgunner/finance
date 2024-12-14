<?php

namespace App\Service;

use App\Controller\Admin\RecordCrudController;
use App\Entity\Record;
use App\Repository\RecordRepository;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use eduMedia\TagBundle\Service\TagService;

class ChartDataService
{
    public function __construct(
        private RecordRepository       $recordRepository,
        private TagService             $tagService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    )
    {
    }

    public function groupedRecords(string $from, string $to, string $type = 'expenses', string $graphType = 'daily', array $accountIds = []): array
    {
        $format = match ($graphType) {
            'weekly' => 'W Y',
            'monthly' => 'M Y',
            'yearly' => 'Y',
            default => 'j M Y',
        };
        $records = $this->recordRepository->dailyForPeriod($from, $to, $accountIds, $type);
        $byDate = $labels = $datePeriods = $catSubCat = $tagsByCategoryString = [];
        $allCategoriesInSet = [
            'total' => [
                'label' => 'Total ' . ($type === 'expenses' ? 'spent' : 'income'),
                'backgroundColor' => 'rgb(255, 99, 132)',
                'borderColor' => 'rgb(255, 99, 132)',
                'color' => 'rgb(0, 0, 0)',
            ]
        ];
        $catSubCat['total'] = 'all all';
        foreach ($records as $record) {
            if (!empty($record->getSubCategory())) {
                $categoryString = $record->getCategory()->getName() . ' - ' . $record->getSubCategory()->getName();
                $catSubCat[$categoryString] = $record->getCategory()->getId() . ' ' . $record->getSubCategory()->getId();
            } else {
                $categoryString = 'N/A';
                $catSubCat[$categoryString] = 'null null';
            }
            if (!isset($allCategoriesInSet[$categoryString])) {
                $allCategoriesInSet[$categoryString] = [];
            }


            $date = $record->getDate()->format($format);
            if (!isset($byDate[$date]['total'])) {
                $byDate[$date]['total'] = 0;
                $labels[] = $date;
                switch ($graphType) {
                    case 'weekly':
                        $dto = new DateTime();
                        $dto->setISODate($record->getDate()->format('Y'), $record->getDate()->format('W'));
                        $period = $dto->format('Y-m-d') . ' ';
                        $dto->modify('+6 days');
                        $period .= $dto->format('Y-m-d');
                        break;
                    case 'monthly':
                        $period = $record->getDate()->modify('first day of this month')->format('Y-m-d') . ' ' .
                            $record->getDate()->modify('last day of this month')->format('Y-m-d');
                        break;
                    case 'yearly':
                        $period = $record->getDate()->modify('first day of january')->format('Y-m-d') . ' ' .
                            $record->getDate()->modify('last day of december')->format('Y-m-d');
                        break;
                    default:
                        $period = $record->getDate()->format('Y-m-d') . ' ' .
                            $record->getDate()->format('Y-m-d');
                        break;
                }
                $datePeriods[] = $period;
            }
            $byDate[$date]['total'] = $byDate[$date]['total'] + ($type === 'expenses' ? $record->getDebit() : $record->getCredit());

            if (!isset($byDate[$date][$categoryString])) {
                $byDate[$date][$categoryString] = 0;
            }
            $byDate[$date][$categoryString] = $byDate[$date][$categoryString] + ($type === 'expenses' ? $record->getDebit() : $record->getCredit());


            $tags = $this->tagService->getTagNames($record, true);
            foreach ($tags as $tag) {
                if (!isset($tagsByCategoryString[$date][$categoryString][$tag])) {
                    $tagsByCategoryString[$date][$categoryString][$tag] = 0;
                }
                $tagsByCategoryString[$date][$categoryString][$tag] += ($type === 'expenses' ? $record->getDebit() : $record->getCredit());
            }
        }

        //dd($tagsByCategoryString);


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

            $setCounter = 0;
            foreach ($byDate as $date => $categories) {
                if (isset($categories[$dataSetKey])) {
                    $dataSet['data'][$setCounter] = $categories[$dataSetKey];
                } else {
                    $dataSet['data'][$setCounter] = 0;
                }

                $tagData = [];
                if (!empty($tagsByCategoryString[$date][$dataSetKey])) {
                    foreach ($tagsByCategoryString[$date][$dataSetKey] as $tagKey => $tagValue) {
                        $tagData[$tagKey] = +$tagValue;
                    }
                }

                $dataSet['tagData'][$setCounter] = $tagData;

                $filters = [];
                $filters['date'] = [
                    'comparison' => 'between',
                    'value' => explode(' ', $datePeriods[$setCounter])[0],
                    'value2' => explode(' ', $datePeriods[$setCounter])[1],
                ];
                if ($catSubCat[$dataSetKey] !== 'all all') {
                    if ($catSubCat[$dataSetKey] !== 'null null') {
                        $filters['category'] = [
                            'comparison' => '=',
                            'value' => [explode(' ', $catSubCat[$dataSetKey])[0]],
                        ];
                        $filters['subCategory'] = [
                            'comparison' => '=',
                            'value' => [explode(' ', $catSubCat[$dataSetKey])[1]],
                        ];
                    } else {
                        $filters['category'] = [
                            'comparison' => '=',
                            'value' => [],
                        ];
                        $filters['subCategory'] = [
                            'comparison' => '=',
                            'value' => [],
                        ];
                    }
                }

                $urlGenerator = $this->adminUrlGenerator->setController(RecordCrudController::class)
                    ->setAction(Action::INDEX)
                    ->set('filters', $filters)
                    ->set('skipSettingSession', true)
                ;
                //$dataSet['urlx'][$setCounter] = 'URL for ' . $dataSetKey . ' - ' . $datePeriods[$setCounter] . ' ' . $catSubCat[$dataSetKey];
                $dataSet['url'][$setCounter] = $urlGenerator->generateUrl();

                $setCounter++;
            }

            $dataSets[] = $dataSet;
        }

        return [
            'labels' => $labels,
            'datasets' => $dataSets,
        ];
    }

    public function tagCount(string $from, string $to, string $type = 'expenses', string $graphType = 'daily', array $accountIds = []): array
    {
        $allTags = $this->tagService->getTagsWithCountArray(Record::class);
//        dump($allTags);
//        $allTags = $this->recordRepository->getTagsWithCountArray($from, $to, $accountIds, $type);
//        dd($allTags);

        unset($allTags['Round Up']);
//        dd($allTags);
        $dataSet = ['label' => 'DS', 'data' => []];
        $weighedTags = [];
        $min = end($allTags);
        $max = reset($allTags);
        $range = max(.01, $max - $min) * 1.0001;
        $steps = 100;

        foreach ($allTags as $tag => $tagCount) {
            $weighedTags[$tag] = 1 + floor($steps * ($tagCount - $min) / $range);
        }

        foreach ($weighedTags as $tag => $tagWeight) {
            $labels[] = $tag;
            $dataSet['data'][] = $tagWeight;

        }
        $dataSets[] = $dataSet;

        return [
            'labels' => $labels,
            'datasets' => $dataSets,
        ];
    }
}
