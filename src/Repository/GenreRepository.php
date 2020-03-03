<?php

namespace App\Repository;

use App\Entity\Genre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Genre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Genre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Genre[]    findAll()
 * @method Genre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GenreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    public function findAllGetArray(): array
    {
        return $this->createQueryBuilder('g')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findByGenre($genre)
    {
        $result = $this->createQueryBuilder('g')
            ->andWhere('g.name = :val')
            ->setParameter('val', $genre)
            ->orderBy('g.ranking', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;

        if (empty($result)) {
            return false;
        } else {
            return $result[0];
        }
    }

    public function updateProgressOfPopularityGenres($currentGenre, $try)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'INSERT INTO progress_popularity_genres (genre, try) VALUES ("' . $currentGenre . '", "' . $try . '")';
        $conn->query($sql);
        // $stmt = $conn->prepare($sql);
        // $stmt->execute();
    }

    public function updateTries($genre, $tries)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'UPDATE genre SET tries = ' . $tries . ' WHERE name = "' . $genre . '"';
        $conn->query($sql);
    }
}
