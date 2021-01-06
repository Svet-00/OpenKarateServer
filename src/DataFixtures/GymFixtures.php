<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\GymPicture;
use App\Entity\Gym;

final class GymFixtures extends Fixture
{
  /**
   * @var string
   */
  private const CONST_89998887711_ = '89998887711';
  public function load(ObjectManager $manager): void
  {
    $gym1 = new Gym();
    $gym1->setTitle('Заголовок зала 1');
    $gym1->setDescription('Описание зала 1');
    $gym1->setAddress('Адрес зала 1');
    $gym1->setEmail('email1@svetdevserver.tk');
    $gym1->setPhoneNumber(self::CONST_89998887711_);
    $gym1->setWorkingTime('ПН-ПТ: 8:00 - 16:00\nСБ-ВС: выходные');
    $gym1->setVkLink('vklink1');

    $manager->persist($gym1);

    $gym2 = new Gym();
    $gym2->setTitle('Заголовок зала 2');
    $gym2->setDescription('Описание зала 2');
    $gym2->setAddress('Адрес зала 2');
    $gym2->setEmail('email1@svetdevserver.tk');

    $gym2->setPhoneNumber(self::CONST_89998887711_);
    $gym2->setWorkingTime('ПН-ПТ: 8:00 - 16:00\nСБ-ВС: выходные');

    $gym2->setVkLink('vklink2');

    $manager->persist($gym2);

    $gym3 = new Gym();

    $gym3->setTitle('Заголовок зала 3');
    $gym3->setDescription('Описание зала 3');

    $gym3->setAddress('Адрес зала 3');
    $gym3->setEmail('email1@svetdevserver.tk');
    $gym3->setPhoneNumber(self::CONST_89998887711_);

    $gym3->setWorkingTime('ПН-ПТ: 8:00 - 16:00\nСБ-ВС: выходные');

    $gym3->setVkLink('vklink3');

    $manager->persist($gym3);

    $manager->flush();
  }
}
