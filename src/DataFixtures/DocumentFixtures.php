<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Document;

final class DocumentFixtures extends Fixture
{
  /**
   * @var string
   */
  public const DOCUMENT1 = 'document1';
  /**
   * @var string
   */
  public const DOCUMENT2 = 'document2';

  public function load(ObjectManager $manager): void
  {
    $document1 = new Document();
    $document1->setFilename('document1.pdf');
    $document1->setOriginalFilename('document 1 original filename.ext');

    $document2 = new Document();
    $document2->setFilename('document2.pdf');
    $document2->setOriginalFilename('document 2 original filename.pdf');

    $manager->persist($document1);
    $manager->persist($document2);
    $manager->flush();

    $this->addReference(self::DOCUMENT1, $document1);
    $this->addReference(self::DOCUMENT2, $document1);
  }
}
