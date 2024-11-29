<?php

namespace App\Command;

use App\Constant\Source;
use App\Entity\CommandResult;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\AccountRepository;
use App\Repository\ImportedFileRepository;
use App\Repository\RecordRepository;
use App\Service\ChartDataService;
use App\Service\RecordsService;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use eduMedia\TagBundle\Service\TagService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

abstract class LoggableCommand extends Command
{
    protected float $startingAt;

    protected int $created = 0;

    protected int $updated = 0;

    protected int $ignored = 0;

    protected $loggableOutput;

    protected ?User $User = null;

    protected string $csvPath;

    protected string $notificationsPath;

    protected readonly ExpressionLanguage $expressionLanguage;

    /**
     * @throws Exception
     */
    public function __construct(
        protected readonly HttpClientInterface    $httpClient,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly UserRepository         $userRepository,
        protected readonly TagService             $tagService,
        protected readonly ParameterBagInterface  $params,
        protected readonly AccountRepository      $accountRepository,
        protected readonly RecordRepository       $recordRepository,
        protected readonly ImportedFileRepository $importedFileRepository,
        protected readonly RecordsService       $recordsService,
    )
    {
        parent::__construct();
        $this->startingAt = microtime(true);
        $this->loggableOutput = self::getLoggableOutput();
        $this->projectDir = $params->get('projectDir');
        $this->csvPath = realpath($this->projectDir) . DIRECTORY_SEPARATOR . $params->get('storagePath') . DIRECTORY_SEPARATOR . 'csv';
        $this->notificationsPath = realpath($this->projectDir) . DIRECTORY_SEPARATOR . $params->get('storagePath') . DIRECTORY_SEPARATOR . 'notifications';
        $this->expressionLanguage = new ExpressionLanguage();

        $this->User = $this->userRepository->find(Source::IMPORTER_USER_ID);
        if (null === $this->User) {
            throw new Exception(Source::IMPORTER_USER_ID . ' User not found');
        }
    }

    protected function saveOutput($extraName = ''): void
    {
        $this->loggableOutput->writeln('Added ' . $this->created . ' records. ' .
            'Updated ' . $this->updated . ' records. ' .
            'Ignored ' . $this->ignored . ' records.'
        );

        $duration = round((microtime(true) - $this->startingAt), 4);
        $this->loggableOutput->writeln('Saving output. Done in ' . $duration . ' seconds.');

        $CommandResult = new CommandResult();
        $CommandResult->setDate(new DateTimeImmutable());
        $CommandResult->setCommand($this->getName() . (!empty($extraName) ? ' ' . $extraName : ''));
        $CommandResult->setResult('success');
        $CommandResult->setOutput($this->loggableOutput->getLinesData());
        $CommandResult->setDuration($duration);

        $this->entityManager->persist($CommandResult);
        $this->entityManager->flush();

        $this->loggableOutput->writeln('Done in ' . $duration . ' seconds.');
    }

    protected function saveException(Exception $exception, $extraName = ''): int
    {
        $this->loggableOutput->writeln('<error>ERROR:</error> ' . $exception->getMessage());
        $this->loggableOutput->writeln('<comment>On file: ' . $exception->getFile() . ':' . $exception->getLine() . '</comment>');

        $CommandResult = new CommandResult();
        $CommandResult->setDate(new DateTimeImmutable());
        $CommandResult->setCommand($this->getName() . (!empty($extraName) ? ' ' . $extraName : ''));
        $CommandResult->setResult('error');
        $CommandResult->setOutput($this->loggableOutput->getLinesData());
        $CommandResult->setDuration(round((microtime(true) - $this->startingAt), 4));

        $this->entityManager->persist($CommandResult);
        $this->entityManager->flush();

        return Command::FAILURE;
    }


    protected static function getLoggableOutput(): object
    {
        return new class {
            private string $linesData = '';
            public OutputInterface $output;

            public function write(string $data): void
            {
                $this->linesData .= $data;
                $this->output->write($data);
            }

            public function writeln(string $data): void
            {
                $data = '[<info>' . self::getTime() . '</info>] ' . $data;
                $this->linesData .= str_replace(['<info>', '</info>'], '', $data) . "\n";
                $this->output->writeln($data);
            }

            public function getLinesData(): string
            {
                return $this->linesData;
            }

            private static function getTime(): string
            {
                $date = DateTime::createFromFormat('U.u', sprintf('%.4f', microtime(true)));


                return substr($date->format('Y-m-d H:i:s.u'), 0, 24);
            }
        };
    }
}
