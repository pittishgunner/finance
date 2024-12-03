<?php

namespace App\Service;

use App\Controller\Admin\RecordCrudController;
use App\Entity\Notification;
use App\Entity\Record;
use App\Helpers\Parser;
use App\Repository\AccountRepository;
use App\Repository\CategoryRuleRepository;
use App\Repository\RecordRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use WebPush\WebPush;

class RecordsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RecordRepository       $recordRepository,
        private CategoryRuleRepository $categoryRuleRepository,
        private AccountRepository      $accountRepository,
        private UserRepository         $userRepository,
        private KernelInterface        $kernel,
        private WebPush                $webPush,
        private AdminUrlGenerator      $adminUrlGenerator, private readonly NotificationsService $notificationsService,
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

        uasort($prepared, fn($a, $b) => $b['count'] <=> $a['count']);
        //asort($prepared);

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
     * This method should return false to ignore and not save the Notification or
     * a string to only set the result or
     * a non-empty array for Records that were added
     *
     * @return bool|string|Record[]
     */
    public function captureNotification(string $source = '', string $message = '', string $content = '', string $ip = '', array $headers = []): bool|string|array
    {
        $Notification = new Notification();
        $Notification->setCreatedAt(new DateTimeImmutable());
        $decoded = json_decode($content, true);
        if (!empty($decoded['currentTime'])) {
            $Notification->setOriginalTime( DateTime::createFromFormat('Y-m-d H:i:s.u', $decoded['currentTime']));
        }
        $Notification->setSource($source);
        $Notification->setMessage($message);
        $Notification->setContent($content);
        $Notification->setIp($ip);
        $Notification->setHeaders(json_encode($headers));

        $Records = $this->addRecordsByNotification($Notification);

        if ($Records !== false) {
            $result = '';
            if (is_string($Records)) {
                $result = $Records;
            } else {
                if (count($Records) > 0) {
                    set_time_limit(0);
                    $application = new Application($this->kernel);
                    $application->setAutoExit(false);

                    $input = new ArrayInput([
                        'command' => 'assign-categories',
                        '--force' => 'false',
                    ]);

                    $output = new NullOutput();
                    $application->run($input, $output);
                    $result = 'Added ';
                    foreach ($Records as $Record) {
                        $url = str_replace('http://localhost/', '/', $this->adminUrlGenerator
                            ->unsetAll()
                            ->setController(RecordCrudController::class)
                            ->setAction(Action::DETAIL)
                            ->setEntityId($Record->getId())
                            ->generateUrl());

                        $result .= '<a title="' . $Record->getDescription() . '" href="' . $url . '">' . $Record->getId() . '</a> ';
                    }
                }
            }
            $Notification->setResult($result);

            $this->entityManager->persist($Notification);
            $this->entityManager->flush();

            //TODO - move this only for count($Records) > 0
            $this->notificationsService->notifySubscribedUsers($message);
        }

        return $Records;
    }

    /**
     * This method should return false to ignore and not save the Notification or
     * a string to only set the result or
     * a non-empty array for Records that were added
     *
     * @param Notification $notification
     * @return bool|string|Record[]
     */
    public function addRecordsByNotification(Notification $notification): bool|string|array
    {
        $decoded = json_decode($notification->getContent(), true);
        if (empty($decoded['currentTime'])) {
            return false;
        }

        switch ($notification->getSource()) {
            case 'ro.ing.mobile.banking.android.activity':
                $parser = Parser::getBankCsvParser('----INGB');
                break;
            case 'com.revolut.revolut':
                $parser = Parser::getBankCsvParser('----REVO');
                break;
            default:
                $parser = Parser::getBankCsvParser('');
                break;
        }
        $data = [];
        if (method_exists($parser, 'predictRecord')) {
            $predicted = $parser->predictRecord($notification->getMessage());
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

    private function processRecordByNotification(array $decoded, array $predicted, DateTime $recordDate): string|array
    {
        $account = $this->accountRepository->findOneByIbanLike($predicted['account']);
        if (null === $account) {
            return 'WARNING! Could not match account';
        }
        if (!empty($predicted['currency']) && $predicted['currency'] !== $account->getCurrency()) {
            return 'WARNING! Could not match currency';
        }

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

        if (null !== $existingRecord) {
            return 'WARNING! Duplicated record.';
        }
        $details = [];
        $details['notifiedAt'] = $decoded['currentTime'];
        $details['notification'] = $decoded['text'];
        $details['prediction'] = $predicted;

        $existingRecord = new Record();
        $existingRecord->setDescription($predicted['string']);
        $existingRecord->setAccount($account);
        $existingRecord->setDate($recordDate);
        $existingRecord->setDebit($predicted['debit'] ?? 0);
        $existingRecord->setCredit($predicted['credit'] ?? 0);
        $existingRecord->setBalance($predicted['balance'] ?? 0);
        $existingRecord->setDetails(json_encode($details));
        $existingRecord->setNotifiedAt(DateTimeImmutable::createFromMutable($recordDate));

        $this->entityManager->persist($existingRecord);
        $this->entityManager->flush();

        return [
            $existingRecord,
        ];
    }
    private function processRoundUp(array $decoded, array $predicted, DateTime $recordDate): string|array
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
            return 'WARNING! Duplicated Round Up Debit';
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
            return 'WARNING! Duplicated Round Up Credit';
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
