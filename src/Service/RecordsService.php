<?php

namespace App\Service;

use App\Helpers\Parser;
use App\Parser\BaseParser;
use App\Repository\CategoryRuleRepository;
use App\Repository\RecordRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

class RecordsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordRepository $recordRepository,
        private CategoryRuleRepository $categoryRuleRepository,
    ) {}


    public function getUnmatchedRecords(): array
    {
        $prepared = [];
        $unmatchedRecords = $this->recordRepository->findBy(['category' => null]);
        foreach ($unmatchedRecords as $record) {
            $parser = Parser::getBankCsvParser($record->getAccount()->getIban());
            $parsedData = $parser->getUnmatchedData($record);
            if (empty($parsedData)) {
                throw new \Exception('Parse error. Empty data for ' . $parser::class);
            }
            if (empty($parsedData['key'])) {
                throw new \Exception('Parse error. Key missing');
            }
            if (isset($prepared[$parsedData['key']]['count'])) {
                $prepared[$parsedData['key']]['count']++;
            } else {
                $parsedData['count'] = 1;
                $parsedData['account'] = $record->getAccount()->getCurrency() . ' - ' . $record->getAccount()->getBank() . ' - ' . $record->getAccount()->getAlias();
                $prepared[$parsedData['key']] = $parsedData;
            }
        }
        asort($prepared);

        return $prepared;
    }

    public function getRulesTree(): array
    {
        $categoryRules = $this->categoryRuleRepository->findBy([], ['category' => 'ASC', 'subCategory' => 'ASC']);
        foreach ($categoryRules as $categoryRule) {
            $c = $categoryRule->getCategory();
            $rules[$c->getId()]['s'] = $categoryRule->getCategory()->getName();
            $rules[$c->getId()]['e'][] = [
                'id' => $categoryRule->getId(),
                'name' => $categoryRule->getName(),
                'subCategoryName' =>  $categoryRule->getSubCategory()->getName(),
                'credit' => $categoryRule->getCredit(),
                'debit' => $categoryRule->getDebit(),
                'account' => $categoryRule->getAccount()?->getAlias(),
                'matches' => $categoryRule->getMatches(),
                'matchesJson' => json_encode($categoryRule->getMatches()),
            ];
        }

        return $rules;
    }
}
