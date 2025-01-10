<?php

namespace App\Repository;

use App\Entity\BookRead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookRead>
 */
class BookReadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookRead::class);
    }

    /**
     * Method to find all ReadBook entities by user_id
     * @param int $userId
     * @param bool $readState
     * @return array
     */
    public function findByUserId(int $userId, bool $readState): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.user_id = :userId')
            ->andWhere('r.is_read = :isRead')
            ->setParameter('userId', $userId)
            ->setParameter('isRead', $readState)
            ->orderBy('r.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLastByUserId(int $userId, bool $readState): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.user_id = :userId')
            ->andWhere('r.is_read = :isRead')
            ->setParameter('userId', $userId)
            ->setParameter('isRead', $readState)
            ->orderBy('r.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }
    /**
     * Method to find ReadBook entities by is id
     * @param int $id
     * @return array
     */
    public function findById(int $id): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }



}

