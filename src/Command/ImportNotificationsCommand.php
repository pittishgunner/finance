<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'import-notifications',
    description: 'Imports notifications',
)]
class ImportNotificationsCommand extends LoggableCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loggableOutput->output = $output;
        try {
            $this->loggableOutput->writeln('Starting import notifications for folder: ' . $this->notificationsPath);
            $finder = new Finder();
            $finder->files()->in($this->notificationsPath);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $absoluteFilePath = $file->getRealPath();
                    $json = file_get_contents($absoluteFilePath);
                    $decoded = json_decode($json, true);
                    $response = $this->recordsService->captureNotification(
                        $decoded['packageName'] ?? '',
                        $decoded['text'] ?? '',
                        $json
                    );
                    $this->created ++;
                }
            }

            $this->saveOutput();
        } catch (Exception $exception) {
            $this->saveException($exception);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
