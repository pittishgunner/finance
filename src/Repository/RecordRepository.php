<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Record;
use App\Entity\Tag;
use App\Entity\Tagging;
use App\Repository\AccountRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
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

    public function getUnmatchedRecords(string $from, string $to, array $accountIds = []): array
    {
        $accounts = $this->accountRepository->findBy(['id' => $accountIds]);
        $qb = $this->qbByRange($from, $to)
            ->andWhere('r.account IN (:accounts)')
            ->setParameter('accounts', $accounts)
            ->andWhere('r.category IS NULL')
//            ->orderBy('r.debit', 'DESC')
            ->orderBy('r.date', 'DESC')
            ->addOrderBy('r.notifiedAt', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $from
     * @param string $to
     * @param array $accountIds
     * @return Record[]
     */
    public function dailyForPeriod(string $from, string $to, array $accountIds = []): array
    {
        $accounts = $this->accountRepository->findBy(['id' => $accountIds]);
        $qb = $this->qbByRange($from, $to)
            ->andWhere('r.account IN (:accounts)')
            ->setParameter('accounts', $accounts)
            ->andWhere('r.debit > 0')
            ->andWhere('r.ignored = :ignored')
            ->setParameter('ignored', false)
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
    public function getUncategorizedRecords(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.category IS NULL')
        ;

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
