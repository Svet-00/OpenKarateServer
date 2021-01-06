<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Link;

final class LinkFixtures extends Fixture
{
  /**
   * @var string
   */
  public const LINK_WITH_TITLE = 'link-with-title';
  /**
   * @var string
   */
  public const LINK_WITHOUT_TITLE = 'link-without-title';

  public function load(ObjectManager $manager): void
  {
    $link1 = new Link();
    $link1->setTitle('Link 1 title')->setUrl('https://vk.com/feed');

    $link2 = new Link();
    $link2->setUrl('https://wikipedia.org');

    $manager->persist($link1);
    $manager->persist($link2);
    $manager->flush();

    $this->addReference(self::LINK_WITH_TITLE, $link1);
    $this->addReference(self::LINK_WITHOUT_TITLE, $link2);
  }
}
