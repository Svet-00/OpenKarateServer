<?php

namespace App\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Post;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class PostRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Post::class);
  }

  public function findAll()
  {
    return $this->createQueryBuilder('p')
      ->orderBy('p.createdAt', 'DESC')
      ->getQuery()
      ->getResult();
  }

  /**
   * @return Post[] Returns an array of Post objects
   */

  public function getWithOffset(int $start, int $count): array
  {
    return $this->createQueryBuilder('p')
      ->orderBy('p.createdAt', 'DESC')
      ->setFirstResult($start)
      ->setMaxResults($count)
      ->getQuery()
      ->getResult();
  }

  /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
