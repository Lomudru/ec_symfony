<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    private $connection;

    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, Category::class);
        $this->connection = $connection;
    }

    public function CountCategoryByUser(int $userId)
    {
        $sql = "
        SELECT COUNT(br.book_id) AS book_count, c.name, c.id 
        FROM category AS c 
        LEFT JOIN book AS b ON b.category_id = c.id 
        LEFT JOIN book_read AS br ON br.book_id = b.id AND br.user_id = :userId 
        GROUP BY c.id;
        ";

        $stmt = $this->connection->executeQuery($sql, ['userId' => $userId]);

        return $stmt->fetchAllAssociative();
    }
}
