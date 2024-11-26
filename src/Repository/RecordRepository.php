<?php

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Record;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Record::class);
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
