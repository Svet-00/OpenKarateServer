<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\Post;
use App\Entity\Document;

final class PostFixtures extends Fixture implements DependentFixtureInterface
{
  public function load(ObjectManager $manager): void
  {
    $document1 = $this->getReference(DocumentFixtures::DOCUMENT1);
    $document2 = $this->getReference(DocumentFixtures::DOCUMENT2);

    $post1 = new Post();
    $post1->setText('Первый пост. Короткий текст + 2 картинки + 1 документ.');

    $post1->addDocument($document1);

    $post2 = new Post();
    $post2->setText(
      <<<ASCI
;
;;
;';.
;  ;;
;   ;;
;    ;;
;    ;;
;   ;'
;  '
,;;;,;
;;;;;;
`;;;;'
ASCI
    );

    $post3 = new Post();
    $post3->setText(
      'Третий пост. Без картинок, 2 документа, ссылка: https://avatars.mds.yandex.net/get-zen_doc/1136050/pub_5c0fab47f0747600ae151c94_5c0fb15b46ef5c00aaa81c9c/scale_1200. И *текст в звездочках*.'
    );
    $post3->addDocument($document1);
    $post3->addDocument($document2);

    $post4 = new Post();
    $post4->setText(
      'Пост 4. Длинный текст. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'
    );

    $manager->persist($post1);
    $manager->persist($post2);
    $manager->persist($post3);
    $manager->persist($post4);

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
