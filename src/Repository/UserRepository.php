<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class UserRepository extends ServiceEntityRepository implements
  PasswordUpgraderInterface
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, User::class);
  }

  /**
   * Used to upgrade (rehash) the user's password automatically over time.
   */
  public function upgradePassword(
    UserInterface $user,
    string $newEncodedPassword
  ): void {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(
        \sprintf('Instances of "%s" are not supported.', \get_class($user))
      );
    }

    $user->setPassword($newEncodedPassword);
    $this->_em->persist($user);
    $this->_em->flush();
  }

  public function getUserCount(): int
  {
    $qb = $this->createQueryBuilder('u');

    $qb->select($qb->expr()->count('u'));

    $query = $qb->getQuery();

    $res = $query->execute();
    return $res[0][1];
  }

  public function getUserCountByRegistrationDate(DateTimeInterface $dt)
  {
    $qb = $this->createQueryBuilder('u');

    $qb
      ->select($qb->expr()->count('u'))
      ->where('DATE_DIFF(:dt, u.registeredAt) < 1')
      ->setParameter('dt', $dt);

    $query = $qb->getQuery();

    $res = $query->execute();
    return $res[0][1];
  }

  /**
   * @return User[] Returns an array of users with given role
   * WARNING! Does not support nested roles
   */
  public function findByRole($role): array
  {
    return $this->createQueryBuilder('u')
      ->where('u.roles LIKE :role')
      ->setParameter('role', '%"' . $role . '"%')
      ->getQuery()
      ->setCacheable(true)
      ->getResult();
  }

  // /**
  //  * @return User[] Returns an array of User objects
  //  */
  /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

  /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
