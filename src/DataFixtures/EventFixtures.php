<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use DateTime;
use DateInterval;
use App\Entity\Link;
use App\Entity\Event\EventType;
use App\Entity\Event\EventLevel;
use App\Entity\Event;
use App\Entity\Document;

final class EventFixtures extends Fixture implements DependentFixtureInterface
{
  public function load(ObjectManager $manager): void
  {
    $linkWithTitle = $this->getReference(LinkFixtures::LINK_WITH_TITLE);
    $linkWithoutTitle = $this->getReference(LinkFixtures::LINK_WITHOUT_TITLE);

    $document1 = $this->getReference(DocumentFixtures::DOCUMENT1);
    $document2 = $this->getReference(DocumentFixtures::DOCUMENT2);

    $event1 = new Event();
    $event1
      ->setTitle('Самарский Кайман')
      ->setAddress('ул. Котина, 3, Санкт-Петербург')
      ->setDescription('Описание описание описание описание описание')
      ->setStartDate((new DateTime())->modify('+1 days'))
      ->setEndDate((new DateTime())->modify('+2 days'))
      ->setLevel('Городские соревнования')
      ->setType('Ката')
      ->addDocument($document1)
      ->addLink($linkWithTitle)
      ->addLink($linkWithoutTitle);

    $event2 = new Event();
    $event2
      ->setTitle('Городские соревнования по ката')
      ->setAddress('ул. Котина, 3, Санкт-Петербург')
      ->setDescription('Ещё одно длиннейшее описание и даже со смайликом ❤️')
      ->setStartDate((new DateTime())->modify('+2 days'))
      ->setLevel('Городские соревнования')
      ->setType('Ката')
      ->addLink($linkWithoutTitle);

    $manager->persist($event1);
    $manager->persist($event2);
    $manager->flush();
  }

  /**
   * @return string[]
   */
  public function getDependencies(): array
  {
    return [DocumentFixtures::class, LinkFixtures::class];
  }
}
