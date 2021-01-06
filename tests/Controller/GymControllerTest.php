<?php
namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use LogicException;
use ErrorException;
use Doctrine\Common\Collections\ArrayCollection;
use App\Utils\PreconfiguredComponents;
use App\Utils\EntityHelper;
use App\Repository\UserRepository;
use App\Entity\GymPicture;
use App\Entity\Gym;

class GymControllerTest extends WebTestCase
{
  public function testGetGyms()
  {
    // ? prepair environment
    $client = static::createClient();
    $serializer = PreconfiguredComponents::getSerializer();

    // ? get gyms
    $client->request('GET', '/api/v1.0/gyms');

    $this->assertResponseIsSuccessful();

    // ? deserialize result
    $data = json_decode($client->getResponse()->getContent(), true);

    foreach ($data as $result) {
      $gyms[] = $serializer->denormalize($result, Gym::class, 'json');
    }

    // ? checks against list of all gyms
    $this->assertCount(3, $gyms);
  }

  public function testAddGym()
  {
    // ? prepair environment
    $serializer = PreconfiguredComponents::getSerializer();
    $client = static::createClient();
    $userRepository = static::$container->get(UserRepository::class);
    $testUser = $userRepository->findOneByEmail('admin');
    $client->loginUser($testUser);

    // ? add gym
    $client->request('POST', '/api/v1.0/gym', [
      'title' => 'test title',
      'address' => 'test address',
      'email' => 'test email',
      'phone_number' => 'test phone number',
      'vk_link' => 'test vk link',
      'working_time' => 'test working time',
      'description' => 'test description'
    ]);

    // ? assert response code
    $this->assertResponseIsSuccessful();

    // ? deserialize result
    /** @var Gym $gym */
    $gym = $serializer->deserialize(
      $client->getResponse()->getContent(),
      Gym::class,
      'json'
    );

    // ? checks against added gym
    // check passed values
    $this->assertEquals('test title', $gym->getTitle());
    $this->assertEquals('test address', $gym->getAddress());
    $this->assertEquals('test email', $gym->getEmail());
    $this->assertEquals('test phone number', $gym->getPhoneNumber());
    $this->assertEquals('test vk link', $gym->getVkLink());
    $this->assertEquals('test working time', $gym->getWorkingTime());
    $this->assertEquals('test description', $gym->getDescription());

    // check empty fileds
    $this->assertEmpty($gym->getGymPictures());
    $this->assertEmpty($gym->getSchedules());

    // ? get all gyms
    $client->request('GET', '/api/v1.0/gyms');

    // ? assert response code
    $this->assertResponseIsSuccessful();

    // ? deserialize result
    $data = json_decode($client->getResponse()->getContent(), true);

    foreach ($data as $result) {
      $gyms[] = $serializer->denormalize($result, Gym::class, 'json');
    }

    // ? checks against list of all gyms
    $this->assertCount(4, $gyms);
  }

  public function testDeleteGym()
  {
    $client = static::createClient();
    $userRepository = static::$container->get(UserRepository::class);
    $testUser = $userRepository->findOneByEmail('admin');
    $client->loginUser($testUser);

    /** @var Gym $gym */
    $gym = $this->getGyms($client)->first();
    $gymId = $gym->getId();

    // erase id's of objects that should be deleted
    EntityHelper::setPrivate($gym, 'id', null);
    foreach ($gym->getGymPictures() as $picture) {
      EntityHelper::setPrivate($picture, 'id', null);
    }

    $client->request('DELETE', "/api/v1.0/gym/{$gymId}");

    $this->assertResponseIsSuccessful();

    $responseGym = $this->deserializeGym($client->getResponse()->getContent());
    $this->assertEquals($gym, $responseGym);
  }

  public function testAddGymPicture()
  {
    $client = static::createClient();
    $userRepository = static::$container->get(UserRepository::class);
    $testUser = $userRepository->findOneByEmail('admin');
    $client->loginUser($testUser);

    /** @var Gym $gym */
    $gym = $this->getGyms($client)->first();
    $gymId = $gym->getId();

    $imageString = \file_get_contents(__DIR__ . '/../assets/evolution.png');
    $imgEncoded = \base64_encode($imageString);

    $client->request('POST', "/api/v1.0/gym/{$gymId}/picture", [
      'undefined' => $imgEncoded
    ]);
    $this->assertResponseIsSuccessful();
  }

  // ! add new tests here

  public function testRestrictedRoutes()
  {
    $client = static::createClient();
    $userRepository = static::$container->get(UserRepository::class);
    $testUser = $userRepository->findOneByEmail('regular');
    $client->loginUser($testUser);

    /** @var array $gyms */
    $gym = $this->getGyms($client)->first();
    $gymId = $gym->getId();
    $picId = $gym
      ->getGymPictures()
      ->first()
      ->getId();

    $routesData = [
      ['POST', '/api/v1.0/gym'],
      ['POST', "/api/v1.0/gym/{$gymId}"],
      ['DELETE', "/api/v1.0/gym/{$gymId}"],
      ['POST', "/api/v1.0/gym/{$gymId}/picture"],
      ['POST', "/api/v1.0/gym/picture/{$picId}"],
      ['DELETE', "/api/v1.0/gym/picture/{$picId}"]
    ];

    // * these pages require ROLE_ADMIN

    foreach ($routesData as $data) {
      $client->request(...$data);
      $this->assertEquals(
        '/access_denied',
        $client->getResponse()->headers->get('Location')
      );
    }
  }

  // ? helper functions
  /**
   * Fetches and deserializes gyms from db
   */
  private function getGyms(KernelBrowser $client): ArrayCollection
  {
    $client->request('GET', '/api/v1.0/gyms');
    return $this->deserializeGymCollection(
      $client->getResponse()->getContent()
    );
  }

  private function deserializeGym($jsonstring): Gym
  {
    $data = \json_decode($jsonstring, true);
    if (isset($data[0])) {
      throw new LogicException(
        'Seems like you\'re trying to deserialize collection of gyms. ' .
          'Use deserializeGymCollection($jsonstring) instead.'
      );
    }
    return $this->denormalizeGym($data);
  }

  private function deserializeGymCollection($jsonstring): ArrayCollection
  {
    $gyms = new ArrayCollection();
    $data = \json_decode($jsonstring, true);
    if ($data[0] == null) {
      throw new ErrorException(
        'Data does not contain any element\'s. ' .
          'If you\'re trying to deserialize single gym, ' .
          'use deserializeGym($jsonstring) instead.'
      );
    }
    foreach ($data as $gymData) {
      $gyms->add($this->denormalizeGym($gymData));
    }
    return $gyms;
  }

  /** Deserializes Gym from assoc array */
  private function denormalizeGym($data): Gym
  {
    $serializer = PreconfiguredComponents::getSerializer();
    /** @var Gym $gym */
    $gym = $serializer->denormalize($data, Gym::class);
    EntityHelper::setPrivate($gym, 'id', $data['id']);
    foreach ($data['gym_pictures'] as $picData) {
      $picture = new GymPicture();
      EntityHelper::setPrivate($picture, 'id', $picData['id']);
      $gym->addGymPicture($picture);
    }
    return $gym;
  }
}
