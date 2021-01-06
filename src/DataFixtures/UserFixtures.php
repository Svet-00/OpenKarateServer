<?php

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\UserLevel;
use App\Entity\User;

final class UserFixtures extends Fixture
{
  /**
   * @var UserPasswordEncoderInterface
   */
  private $passwordEncoder;
  /**
   * @var string
   */
  private const ADMIN0 = 'admin0';
  public function __construct(UserPasswordEncoderInterface $passwordEncoder)
  {
    $this->passwordEncoder = $passwordEncoder;
  }
  public function load(ObjectManager $manager): void
  {
    $admin = new User();
    $admin->setEmail('admin');
    $admin->setPassword(
      $this->passwordEncoder->encodePassword($admin, self::ADMIN0)
    );
    $admin->setName('Светозар');
    $admin->setSurname('Волков');
    $admin->setPatronymic('Павлович');
    $admin->setBirthday(new \DateTime('28.03.2000 00:00'));
    $admin->setLevel(UserLevel::fromString('5 Кю')->toInt());
    $admin->setRoles(['ROLE_ADMIN']);

    $manager->persist($admin);

    $regular = new User();
    $regular->setEmail('regular');
    $regular->setPassword(
      $this->passwordEncoder->encodePassword($regular, self::ADMIN0)
    );
    $regular->setName('Светозар');
    $regular->setSurname('Волков');
    $regular->setPatronymic('Павлович');
    $regular->setBirthday(new \DateTime('28.03.2000 00:00'));
    $regular->setLevel(UserLevel::fromString('5 Кю')->toInt());

    $regular->setRoles(['ROLE_USER']);

    $manager->persist($regular);

    $user = new User();
    $user->setEmail('sv@sv.sv');
    $user->setPassword(
      $this->passwordEncoder->encodePassword($user, self::ADMIN0)
    );
    $user->setName('Светозар');
    $user->setSurname('Волков');
    $user->setPatronymic('Павлович');
    $user->setBirthday(new \DateTime('28.03.2000 00:00'));

    $user->setLevel(UserLevel::fromString('5 Кю')->toInt());

    $user->setRoles(['ROLE_ADMIN']);

    $manager->persist($user);

    $user1 = new User();
    $user1->setEmail('mail@sv.sv');
    $user1->setPassword(
      $this->passwordEncoder->encodePassword($user, self::ADMIN0)
    );
    $user1->setName('Иван');

    $user1->setSurname('Иванов');

    $user1->setPatronymic('Иванович');

    $user1->setBirthday(new \DateTime('1.10.1995 00:00'));

    $manager->persist($user1);

    $user2 = new User();
    $user2->setEmail('mail2@sv.sv');
    $user2->setPassword(
      $this->passwordEncoder->encodePassword($user, self::ADMIN0)
    );
    $user2->setName('Петр');

    $user2->setSurname('Петров');

    $user2->setPatronymic('Петрович');

    $user2->setBirthday(new \DateTime('12.05.2004 00:00'));

    $user2->setLevel(UserLevel::fromString('3 Дан')->toInt());

    $manager->persist($user2);

    $manager->flush();
  }
}
