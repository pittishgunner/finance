<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Record;
use App\Repository\AccountRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Record>
 */
class RecordRepository extends ServiceEntityRepository
{
    private \App\Repository\AccountRepository $accountRepository;

    public function __construct(ManagerRegistry $registry, AccountRepository $accountRepository)
    {
        parent::__construct($registry, Record::class);
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param string $year
     * @param string $month
     * @return Record[]
     */
    public function dailyForPeriod(string $from, string $to): array
    {
        $account = $this->accountRepository->find(7);
        $qb = $this->qbByRange($from, $to)
            ->andWhere('r.account = :account')
            ->setParameter('account', $account)
            ->andWhere('r.debit > 0')
            ->orderBy('r.date')
            ->addOrderBy('r.notifiedAt');

        return $qb->getQuery()->getResult();
    }

    private function qbByRange(string $from, string $to): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.date BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;
    }

    /**
     * @param bool $force
     * @return Record[]
     */
    public function getUncategorizedRecords(bool $force = false): array
    {
        $qb = $this->createQueryBuilder('r');
        if (!$force) {
            $qb->andWhere('r.category IS NULL');
        }

        //$qb->setMaxResults(140);

        return $qb->getQuery()->getResult();
    }

    public function findNotifiedAndUnreconciledRecords(Account $account, DateTime $dateTime, float $debit = 0, float $credit = 0): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.notifiedAt IS NOT NULL')
            ->andWhere('r.reconciled = :reconciled')
            ->setParameter('reconciled', false)
            ->andWhere('r.account = :account')
            ->setParameter('account', $account)
            ->andWhere('r.date = :date')
            ->setParameter('date', $dateTime->format('Y-m-d'))
            ->andWhere('r.debit = :debit')
            ->setParameter('debit', $debit)
            ->andWhere('r.credit = :credit')
            ->setParameter('credit', $credit);

        return $qb->getQuery()->getResult();
    }

    public function deleteNotifiedAndUnreconciledRecordsByDateAndAccount(string $date, Account $account): void
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        $this->createQueryBuilder('r')
            ->delete()
            ->where('r.notifiedAt IS NOT NULL')
            ->andWhere('r.reconciled = :reconciled')
            ->setParameter('reconciled', false)
            ->andWhere('r.account = :account')
            ->setParameter('account', $account)
            ->andWhere('r.date = :date')
            ->setParameter('date', $dateTime->format('Y-m-d'))
            ->getQuery()
            ->execute()
        ;
    }
}
