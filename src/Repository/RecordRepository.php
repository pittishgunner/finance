<?php

namespace App\Repository;

use App\Entity\Record;
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
}
