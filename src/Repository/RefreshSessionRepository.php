<?php

namespace App\Repository;

use App\Entity\RefreshSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RefreshSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefreshSession[]    findAll()
 * @method RefreshSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class RefreshSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshSession::class);
    }

    // /**
    //  * @return RefreshSession[] Returns an array of RefreshSession objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RefreshSession
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
