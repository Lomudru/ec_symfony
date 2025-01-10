<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Method to find all Book entities
     * @return array
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->getQuery()
            ->getResult();
    }

    /**
     * Method to find all Book entities by bookId
     * @param int $bookId
     * @return array
     */
    public function findById(int $bookId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.id = :bookId')
            ->setParameter('bookId', $bookId)
            ->getQuery()
            ->getResult();
    }

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


//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
