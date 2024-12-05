<?php

namespace App\Command;

use App\Entity\CategoryRule;
use App\Entity\CommandResult;
use App\Entity\Record;
use App\Repository\CategoryRuleRepository;
use App\Repository\RecordRepository;
use DateTimeImmutable;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'assign-categories',
    description: 'Assigns categories to records, forced or not',
)]
class AssignCategoriesCommand extends LoggableCommand
{
    /**
     * @var CategoryRule[]
     */
    private array $rules = [];

    protected function configure(): void
    {
        $this->addOption('force', 'f',InputOption::VALUE_OPTIONAL, 'Force reassigning for all records. Run as symfony console assign-categories --force=true', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->loggableOutput->output = $output;
        try {
            /** @var CategoryRuleRepository $categoryRuleRepo */
            $categoryRuleRepo = $this->entityManager->getRepository(CategoryRule::class);
            /** @var RecordRepository $recordsRepo */
            $recordsRepo = $this->entityManager->getRepository(Record::class);

            $this->loggableOutput->writeln('Starting assign-categories');
            $force = $input->getOption('force') === 'true';

            $this->rules = $categoryRuleRepo->findBy(['enabled' => true], ['id' => 'ASC']);

            foreach ($recordsRepo->getUncategorizedRecords($force) as $record) {
                $this->updated++;
                $detected = $this->detectCategory($record);
                $record->setCategory($detected['category']);
                $record->setSubCategory($detected['subCategory']);
                if (!empty($detected['tags'])) {
                    $tags = $this->tagService->loadOrCreateTags($detected['tags']);
                    $this->tagService->replaceTags($tags, $record, true, true);
                }
            }

            $this->entityManager->flush();

            $duration = round((microtime(true) - $this->startingAt), 4);
            $this->loggableOutput->writeln('Saving output. Done in ' . $duration . ' seconds.');

            $CommandResult = new CommandResult();
            $CommandResult->setDate(new DateTimeImmutable());
            $CommandResult->setCommand($this->getName());
            $CommandResult->setResult('success');
            $CommandResult->setOutput($this->loggableOutput->getLinesData());
            $CommandResult->setDuration($duration);

            $this->entityManager->persist($CommandResult);
            $this->entityManager->flush();

            $this->loggableOutput->writeln('Done in ' . $duration . ' seconds.');
        } catch (Exception $exception) {
            $this->loggableOutput->writeln('<error>ERROR:</error> ' . $exception->getMessage());
            $this->loggableOutput->writeln('<comment>On file: ' . $exception->getFile() . ':' . $exception->getLine() . '</comment>');

            $CommandResult = new CommandResult();
            $CommandResult->setDate(new DateTimeImmutable());
            $CommandResult->setCommand($this->getName());
            $CommandResult->setResult('error');
            $CommandResult->setOutput($this->loggableOutput->getLinesData());
            $CommandResult->setDuration(round((microtime(true) - $this->startingAt), 4));

            $this->entityManager->persist($CommandResult);
            $this->entityManager->flush();

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function detectCategory(Record $record): array
    {
        $category = $subCategory = null;
        $tags = [];
        foreach ($this->rules as $rule) {
            if (null !== $rule->getAccount() && $record->getAccount() !== $rule->getAccount()) {
                continue;
            }

            if (!empty($rule->getDebit())) {
                if (
                    !$this->expressionLanguage->evaluate(
                        str_replace('amount', $record->getDebit(), $rule->getDebit()),
                    )
                ) {
                    continue;
                }
            }

            if (!empty($rule->getCredit())) {
                if (
                    !$this->expressionLanguage->evaluate(
                        str_replace('amount', $record->getCredit(), $rule->getCredit()),
                    )
                ) {
                    continue;
                }
            }

            if (!empty($rule->getMatches())) {
                $matchedDescription = self::match($rule->getMatches(), $record->getDescription());
                $matchedDetails = self::match($rule->getMatches(), $record->getDetails());
                if ($matchedDetails === false && $matchedDescription === false) {
                    continue;
                }
            } else {
                continue;
            }

            $category = $rule->getCategory();
            $subCategory = $rule->getSubCategory();

            if ($matchedDescription !== false) {
                $tags[] = $matchedDescription;
            }
            if ($matchedDetails !== false) {
                $tags[] = $matchedDetails;
            }

            break;
        }
        if (null === $category) {
            //$this->entityManager->flush();dd($record);
        }
        return [
            'category' => $category,
            'subCategory' => $subCategory,
            'tags' => $tags,
        ];
    }

    private static function match(array $needles, string $subject): bool|string
    {
        foreach ($needles as $needle) {
            if (empty($needle)) {
                continue;
            }
            $touple = explode('|', $needle);
            if (count($touple) > 1) {
                $needle = $touple[0];
            }
            //echo $subject . ' - ' . $needle . ' - ' . (stripos($subject, $needle) ? 'yes' : 'no') . "\n";
            if (stripos($subject, $needle) !== false) {
                return count($touple) > 1 ? $touple[1] : $needle;
            }
        }

        return false;
    }
}
