<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProgressLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProgressLog>
 */
final class ProgressLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgressLog::class);
    }

    /**
     * Returns all Progress Logs for the given enrolment IDs.
     *
     * @param int[] $enrolmentsIds
     *
     * @return ProgressLog[]
     */
    public function findByEnrolments(array $enrolmentsIds): array
    {
        if ($enrolmentsIds === []) {
            return [];
        }

        return $this->createQueryBuilder('pl')
            ->select('pl', 'en')
            ->innerJoin('pl.enrolment', 'en')
            ->where('en.id IN (:enrolmentsIds)')
            ->setParameter('enrolmentsIds', $enrolmentsIds)
            ->getQuery()
            ->getResult();
    }
}
