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
            if ($force) {
                $connection = $this->entityManager->getConnection();
                $platform   = $connection->getDatabasePlatform();
                $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
                $connection->executeQuery('UPDATE record SET category_id = NULL, sub_category_id = NULL;');
                foreach (['tag', 'tagging'] as $table) {
                    $truncateSql = $platform->getTruncateTableSQL($table);
                    $connection->executeStatement($truncateSql);
                }
                $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
            }

            $this->rules = $categoryRuleRepo->findBy(['enabled' => true], ['position' => 'ASC', 'id' => 'ASC']);

            foreach ($recordsRepo->getUncategorizedRecords() as $record) {
                $detected = $this->detectCategory($record);
                if (count($detected['matches']) > 1) {
                    $this->ignored ++;
                    $this->loggableOutput->writeln('WARNING Record: ' . $record->getId() . ' (' . $record->getDescription() . ') matches more than 1 time. ' . "\n" . implode(', ', $detected['matches']));
                } else {
                    if ($detected['category'] !== null && $detected['subCategory'] !== null) {
                        $this->updated++;
                        $record->setCategory($detected['category']);
                        $record->setSubCategory($detected['subCategory']);
                        if (!empty($detected['tags'])) {
                            $tags = $this->tagService->loadOrCreateTags($detected['tags']);
                            $this->tagService->replaceTags($tags, $record, true, true);
                        }
                    } else {
                        if ($force) {
                            $this->loggableOutput->writeln('WARNING Record: ' . $record->getId() . ' not matched');
                        }
                        $this->ignored++;
                    }
                }
            }

            $this->entityManager->flush();

            $duration = round((microtime(true) - $this->startingAt), 4);
            $this->loggableOutput->writeln('Saving output. Done in ' . $duration . ' seconds.');

            $this->saveOutput();
        } catch (Exception $exception) {
            $this->saveException($exception);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function detectCategory(Record $record): array
    {
        $category = $subCategory = null;
        $tags = [];
        $matches = [];
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

            $matches[] = $rule->getName();
            if ($rule->isStop()) {
                break;
            }
        }
        if (null === $category) {
            //$this->entityManager->flush();dd($record);
        }
        return [
            'category' => $category,
            'subCategory' => $subCategory,
            'tags' => $tags,
            'matches' => $matches,
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
