<?php

namespace App\Command;

use App\Entity\Account;
use App\Entity\ImportedFile;
use App\Entity\Record;
use App\Helpers\Parser;
use App\Parser\BaseParser;
use DateTime;
use DateTimeImmutable;
use Exception;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'import-csv',
    description: 'Imports raw CSV files',
)]
class ImportCsvCommand extends LoggableCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loggableOutput->output = $output;
        try {
            $this->loggableOutput->writeln('Starting import csv command for folder: ' . $this->csvPath);
            $csvFiles = $this->getCsvFiles();

            $this->loggableOutput->writeln('Got local csv data and existing DB data');
            foreach ($csvFiles as $csvFile) {
                if ($csvFile['fileImported']) {
                    $this->loggableOutput->writeln('Skipped  CSV file: ' . $csvFile['file']->getRealPath());
                    $this->loggableOutput->writeln(' ');
                    continue;
                }
                $this->loggableOutput->writeln('Crunching CSV file: ' . $csvFile['file']->getRealPath());
                $parsedRecords = $csvFile['parser']->parseFile($csvFile['file']);
                if (empty($parsedRecords)) {
                    $this->loggableOutput->writeln(' - No records parsed. Skipping');
                    $this->loggableOutput->writeln(' ');
                    continue;
                }
                $recordsByDate = [];
                $totalRecords = 0;
                foreach ($parsedRecords as $record) {
                    $hash = hash('sha256', json_encode($record));
                    $record['hash'] = $hash;
                    $recordsByDate[$record['date']][] = $record;
                    $totalRecords++;
                }
                ksort($recordsByDate);
                $this->loggableOutput->writeln(
                    ' - Crunched CSV file: ' . $csvFile['file']->getFilename() . ' ' . count($recordsByDate) . ' Days and ' . $totalRecords .
                    ' Records parsed. Checking for already notified records'
                );

                // If exactly one record is found, store the exact transaction date
                // and delete all notified records AND unreconciled for specific date
                // Exactly one record is needed to avoid double payments with exact same date, amount and description
                foreach ($recordsByDate as $date => $records) {
                    foreach ($records as $key => $record) {
                        $ExistingRecords = $this->recordRepository->findNotifiedAndUnreconciledRecords(
                            $csvFile['account'],
                            DateTime::createFromFormat('Y-m-d', $record['date']),
                            $record['debit'],
                            $record['credit']
                        );
                        $Record = null;
                        if (count($ExistingRecords) > 0) {
                            if (count($ExistingRecords) === 1) {
                                $Record = $ExistingRecords[0];
                            } else {
                                foreach ($ExistingRecords as $ExistingRecord) {
                                    if (strstr($ExistingRecord->getDescription(), $record['description'])) {
                                        $Record = $ExistingRecord;
                                    }
                                }
                                if (null === $Record) {
                                    foreach ($ExistingRecords as $ExistingRecord) {
                                        if ($ExistingRecord->getBalance() === $record['balance']) {
                                            $Record = $ExistingRecord;
                                        }
                                    }
                                }
                            }
                        }
                        if (null !== $Record) {
                            $recordsByDate[$date][$key]['notifiedAt'] = $Record->getNotifiedAt();
                        }
                    }

                    $this->recordRepository->deleteNotifiedAndUnreconciledRecordsByDateAndAccount($date, $csvFile['account']);
                }

                foreach ($recordsByDate as $date => $records) {
                    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
                    foreach ($records as $record) {
                        $hash = $record['hash'];
                        $Record = $this->recordRepository->findOneBy(['hash' => $hash]);
                        if (null === $Record) {
                            $Record = new Record();
                            $Record->setCreatedAt(new DateTimeImmutable());
                            $this->created++;
                        } else {
                            $Record->setUpdatedAt(new DateTimeImmutable());
                            $this->updated++;
                        }
                        $Record->setAccount($csvFile['account']);
                        $Record->setDate($dateTime);
                        $Record->setDebit($record['debit']);
                        $Record->setCredit($record['credit']);
                        $Record->setBalance($record['balance']);
                        $Record->setDescription($record['description']);
                        $Record->setDetails(json_encode($record['details']));
                        $Record->setHash($hash);
                        if (isset($record['notifiedAt'])) {
                            $Record->setNotifiedAt($record['notifiedAt']);
                            $Record->setReconciled(true);
                        }

                        $this->entityManager->persist($Record);
                    }
                }

                $this->entityManager->flush();

                $ImportedFile = new ImportedFile();
                $ImportedFile->setAccount($csvFile['account']);
                $ImportedFile->setFolder($csvFile['iban']);
                $ImportedFile->setFileName($csvFile['file']->getFilename());
                $ImportedFile->setImportedAt(new DateTimeImmutable());
                $ImportedFile->setForceReImport(false);
                $ImportedFile->setFileCreatedAt($csvFile['createdAt']);
                $ImportedFile->setParsedRecords(count($records));

                $this->entityManager->persist($ImportedFile);
                $this->loggableOutput->writeln(' ');
            }

            $this->entityManager->flush();

            $this->saveOutput();
        } catch (Exception $exception) {
            $this->saveException($exception);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string, Account, BaseParser, bool, SplFileInfo|SplFileObject, DateTimeImmutable>[]
     */
    private function getCsvFiles(): array
    {
        $Finder = new Finder();
        $Finder->files()->depth(1)->name('*.csv')->in($this->csvPath);
        $files = [];
        foreach ($Finder as $file) {
            $iban = substr($file->getRelativePath(), 0, 24);
            $parser = Parser::getBankCsvParser($iban);
            $Account = $this->accountRepository->findOneBy(['iban' => $iban]);
            if ($Account === null) {
                $Account = new Account();
                $Account->setBank($parser->getName());
                $Account->setCurrency(substr($file->getRelativePath(), 27, 3));
                $Account->setIban($iban);
                $Account->setAlias(substr($file->getRelativePath(), 33));
                $Account->setEnabled(false);
                $Account->setCreatedAt(new DateTimeImmutable());

                $this->entityManager->persist($Account);
            }

            $this->entityManager->flush();

            $ImportedFile = $this->importedFileRepository->findOneBy(['folder' => $iban, 'fileName' => $file->getFilename()], ['id' => 'DESC']);
            $fileImported = $ImportedFile !== null && !$ImportedFile->isForceReImport();
            $csvData = null;
            if (!$fileImported) {
                $csvFile = new SplFileObject($file->getRealPath());
                $csvFile->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE);
                $csvData = $csvFile;
            }

            $files[] = [
                'iban' => $iban,
                'account' => $Account,
                'parser' => $parser,
                'fileImported' => $fileImported,
                'file' => $csvData ?? $file,
                'createdAt' => (new DateTimeImmutable())->setTimestamp($file->getMTime()),
            ];
        }

        return $files;
    }
}
