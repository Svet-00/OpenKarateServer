<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\PreconfiguredComponents;
use App\Utils\EntityHelper;
use App\Enum\PhotoBuckets;
use App\Enum\AvatarFormats;
use App\Entity\User\UserLevel;
use App\Entity\User;
use App\Entity\Event;

final class UserNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface
{
  use NormalizerAwareTrait;

  /**
   * @var UrlGeneratorInterface
   */
  private $urlGenerator;

  public function __construct(UrlGeneratorInterface $urlGenerator)
  {
    $this->urlGenerator = $urlGenerator;
  }
  /** @inheritDoc */
  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(User::class, $context[ProxyNormalizerInterface::USED_PROXIES])
    ) {
      return false;
    }
    return $data instanceof User ||
      EntityHelper::isArrayOfType($data, User::class);
  }

  /**
   * @inheritDoc
   */
  public function normalize($data, $format = '', $context = [])
  {
    $eventsCallback = function ($innerObject, $outerObject, $format): array {
      $ids = [];
      /** @var ArrayCollection $innerObject */
      /** @var Event $user */
      foreach ($innerObject as $event) {
        $ids[] = $event->getId();
      }
      return $ids;
    };

    $avatarCallback = function ($innerObject, $outerObject, $format): array {
      /** @var User $outerObject */
      $avatar = [];
      $avatar['square'] = $this->urlGenerator->generate(
        'get_image',
        [
          'bucket' => PhotoBuckets::USERS,
          'filename' => $outerObject->getAvatarFilename(AvatarFormats::Square)
        ],
        UrlGeneratorInterface::ABSOLUTE_URL
      );
      $avatar['wide'] = $this->urlGenerator->generate(
        'get_image',
        [
          'bucket' => PhotoBuckets::USERS,
          'filename' => $outerObject->getAvatarFilename(AvatarFormats::Wide)
        ],
        UrlGeneratorInterface::ABSOLUTE_URL
      );
      return $avatar;
    };

    $context[AbstractNormalizer::GROUPS][] = 'api';

    $context[AbstractNormalizer::CALLBACKS]['avatar'] = $avatarCallback;
    $context[AbstractNormalizer::CALLBACKS]['events'] = $eventsCallback;

    $context[ProxyNormalizerInterface::USED_PROXIES][] = User::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
