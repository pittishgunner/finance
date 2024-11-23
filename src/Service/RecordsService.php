<?php

namespace App\Service;

use App\Entity\CapturedRequest;
use App\Entity\Record;
use App\Helpers\Parser;
use App\Repository\AccountRepository;
use App\Repository\CategoryRuleRepository;
use App\Repository\RecordRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class RecordsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordRepository       $recordRepository,
        private CategoryRuleRepository $categoryRuleRepository,
        private AccountRepository      $accountRepository,
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

    /**
     * This method should return false to ignore and not save the CapturedRequest or
     * an empty array so CapturedRequest is still saved but without a Record connection or
     * a non-empty array for Records that were added
     *
     * @param CapturedRequest $capturedRequest
     * @return bool|Record[]
     */
    public function addRecordsByNotification(CapturedRequest $capturedRequest): bool|array
    {
        switch ($capturedRequest->getSource()) {
            case 'ro.ing.mobile.banking.android.activity':
                $parser = Parser::getBankCsvParser('----INGB');
                break;
            default:
                $parser = Parser::getBankCsvParser('');
                break;
        }
        $data = [];
        if (method_exists($parser, 'predictRecord')) {
            $decoded = json_decode($capturedRequest->getContent(), true);
            if (empty($decoded['text']) || empty($decoded['currentTime'])) {
                return false;
            }

            $predicted = $parser->predictRecord($decoded['text']);
            if (isset($predicted['ignored'])) {
                return false;
            }
            if (isset($predicted['unmatched'])) {
                return [];
            }

            $recordDate = DateTime::createFromFormat('Y-m-d H:i:s.u', $decoded['currentTime']);
            if (isset($predicted['matched']) && $predicted['matched'] === 'round-up')
            {
                return $this->processRoundUp($decoded, $predicted, $recordDate);
            }

            return $this->processRecordByNotification($decoded, $predicted, $recordDate);
        }

        return $data;
    }

    private function processRecordByNotification(array $decoded, array $predicted, DateTime $recordDate): array
    {
        $account = $this->accountRepository->findOneByIbanLike($predicted['account']);
        $findCriteria = [
            'account' => $account,
            'date' => $recordDate,
        ];
        if (!empty($predicted['debit'])) {
            $findCriteria['debit'] = $predicted['debit'];
        }
        if (!empty($predicted['credit'])) {
            $findCriteria['credit'] = $predicted['credit'];
        }
        if (!empty($predicted['balance'])) {
            $findCriteria['balance'] = $predicted['balance'];
        }
        $existingRecord = null;
        $records = $this->recordRepository->findBy($findCriteria, ['id' => 'DESC']);
        foreach ($records as $record) {
            if (strstr($record->getDescription(), $predicted['description'])) {
                $existingRecord = $record;
            }
        }

        if (null === $existingRecord) {
            foreach ($records as $record) {
                if ($record->getBalance() === $predicted['balance']) {
                    $existingRecord = $record;
                }
            }
        }

        if (null === $existingRecord) {
            $existingRecord = new Record();
            $existingRecord->setDescription($predicted['string']);
            $details = [];
        } else {
            $details = json_decode($existingRecord->getDetails(), true);
            $existingRecord->setDescription($existingRecord->getDescription() . "\n" . $predicted['string']);
        }

        $existingRecord->setAccount($account);
        $existingRecord->setDate($recordDate);
        $existingRecord->setDebit($predicted['debit'] ?? 0);
        $existingRecord->setCredit($predicted['credit'] ?? 0);
        $existingRecord->setBalance($predicted['balance'] ?? 0);


        $details['notifiedAt'] = $decoded['currentTime'];
        $details['notification'] = $decoded['text'];
        $details['prediction'] = $predicted;
        $existingRecord->setDetails(json_encode($details));
        $existingRecord->setCreatedAt(DateTimeImmutable::createFromMutable($recordDate));
        $existingRecord->setNotifiedAt(DateTimeImmutable::createFromMutable($recordDate));

        $this->entityManager->persist($existingRecord);
        $this->entityManager->flush();

        return [
            $existingRecord,
        ];
    }
    private function processRoundUp(array $decoded, array $predicted, DateTime $recordDate): array
    {
        $debitAccount = $this->accountRepository->findOneBy(['iban' => 'RO64INGB5649999901181524']);
        $debit = $this->recordRepository->findOneBy([
                'account' => $debitAccount,
                'date' => $recordDate,
                'debit' => $predicted['debit'],
            ], ['id' => 'DESC']
        );
        if ($debit === null) {
            $debit = new Record();
            $debit->setAccount($debitAccount);
            $debit->setDate($recordDate);
            $debit->setDebit($predicted['debit']);
            $debit->setCredit(0);
            $debit->setBalance(0);
            $details = [];
            $details['notifiedAt'] = $decoded['currentTime'];
            $details['notification'] = $decoded['text'];
            $details['prediction'] = $predicted;
            $debit->setCreatedAt(DateTimeImmutable::createFromMutable($recordDate));
            $debit->setDescription($decoded['text']);
        } else {
            $details = json_decode($debit->getDetails(), true);
            $details['notifiedAt'] = $decoded['currentTime'];
            $details['notification'] = $decoded['text'];
            $details['prediction'] = $predicted;
            $debit->setUpdatedAt(DateTimeImmutable::createFromMutable($recordDate));
        }
        $debit->setDetails(json_encode($details));
        $debit->setNotifiedAt(DateTimeImmutable::createFromMutable($recordDate));

        $creditAccount = $this->accountRepository->findOneBy(['iban' => 'RO95INGB0000999903001060']);
        $credit = $this->recordRepository->findOneBy([
                'account' => $creditAccount,
                'date' => $recordDate,
                'debit' => $predicted['debit'],
            ], ['id' => 'DESC']
        );
        if ($credit === null) {
            $credit = new Record();
            $credit->setAccount($creditAccount);
            $credit->setDate($recordDate);
            $credit->setCredit($predicted['debit']);
            $credit->setDebit(0);
            $credit->setBalance(0);
            $details = [];
            $details['notifiedAt'] = $decoded['currentTime'];
            $details['notification'] = $decoded['text'];
            $details['prediction'] = $predicted;
            $credit->setCreatedAt(DateTimeImmutable::createFromMutable($recordDate));
            $credit->setDescription($decoded['text']);
        } else {
            $details = json_decode($credit->getDetails(), true);
            $details['notifiedAt'] = $decoded['currentTime'];
            $details['notification'] = $decoded['text'];
            $details['prediction'] = $predicted;
            $credit->setUpdatedAt(DateTimeImmutable::createFromMutable($recordDate));
        }
        $credit->setDetails(json_encode($details));
        $credit->setNotifiedAt(DateTimeImmutable::createFromMutable($recordDate));

        $this->entityManager->persist($debit);
        $this->entityManager->persist($credit);
        $this->entityManager->flush();

        return  [
            $debit,
            $credit,
        ];
    }
}
