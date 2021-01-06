<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Schedule;
use App\Entity\Gym;

final class ScheduleFixtures extends Fixture
{
  /**
   * @var string[]
   */
  private const DAYS_OF_WEEK = [
    'Понедельник',
    'Вторник',
    'Среда',
    'Четверг',
    'Пятница',
    'Суббота',
    'Воскресенье'
  ];
  public function load(ObjectManager $manager): void
  {
    $gyms = $manager->getRepository(Gym::class)->findAll();

    foreach ($gyms as $gym) {
      for ($i = 0; $i < 8; $i++) {
        $ip2 = $i + 2;
        $schedule = new Schedule();
        $schedule->setDescription(\sprintf('Описание %s', $i));
        $schedule->setTime(\sprintf('1%s:00 - 1%s:00', $i, $ip2));
        $schedule->setDayOfWeek(self::DAYS_OF_WEEK[$i % 7]);
        $schedule->setGym($gym);
        $manager->persist($schedule);
      }
    }

    $manager->flush();
  }
}
