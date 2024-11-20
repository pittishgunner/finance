<?php

namespace App\Command;

use App\Entity\Account;
use App\Entity\CommandResult;
use App\Entity\ImportedFile;
use App\Entity\Record;
use App\Helpers\Parser;
use App\Parser\BaseParser;
use DateTimeImmutable;
use Exception;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
                $records = $csvFile['parser']->parseFile($csvFile['file']);
                if (empty($records)) {
                    $this->loggableOutput->writeln(' - No records parsed. Skipping');
                    $this->loggableOutput->writeln(' ');
                    continue;
                }

                $this->loggableOutput->writeln(' - Crunched CSV file: ' . $csvFile['file']->getRealPath() . ' ' . count($records) . ' Records parsed. Importing');
                $new = $updated = 0;

                foreach ($records as $record) {
                    $hash = hash('sha256', json_encode($record));
                    $Record = $this->recordRepository->findOneBy(['hash' => $hash]);
                    if (empty($Record)) {
                        $Record = new Record();
                        $Record->setCreatedAt(new DateTimeImmutable());
                        $new++;
                    } else {
                        $Record->setUpdatedAt(new DateTimeImmutable());
                        $updated++;
                    }
                    $Record->setAccount($csvFile['account']);
                    $Record->setDate($record['date']);
                    $Record->setDebit($record['debit']);
                    $Record->setCredit($record['credit']);
                    $Record->setBalance($record['balance']);
                    $Record->setDescription($record['description']);
                    $Record->setDetails(json_encode($record['details']));
                    $Record->setHash($hash);

                    $this->entityManager->persist($Record);
                }

                $this->entityManager->flush();
                $this->loggableOutput->writeln(' - - Imported ' . $new . '/' . $updated . ' new/updated records.');

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
