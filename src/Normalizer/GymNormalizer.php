<?php
namespace App\Normalizer;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Common\Collections\Collection;
use App\Utils\PreconfiguredComponents;
use App\Utils\EntityHelper;
use App\Entity\Gym;

final class GymNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface,
  UserAwareNormalizerInterface
{
  use NormalizerAwareTrait;
  use UserAwareTrait;

  /**
   * @var PhotoNormalizer
   */
  private $photoService;

  public function __construct(PhotoNormalizer $ps)
  {
    $this->photoService = $ps;
  }

  /** @inheritDoc */
  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(Gym::class, $context[ProxyNormalizerInterface::USED_PROXIES])
    ) {
      return false;
    }
    return $data instanceof Gym ||
      EntityHelper::isArrayOfType($data, Gym::class);
  }

  /**
   * @inheritDoc
   */
  public function normalize($data, $format = '', $context = [])
  {
    $circularReferenceHandler = function ($object, $format, $context) {
      return $object->getId();
    };

    $photosCallback = function ($inner, $outer, $format): Collection {
      $callback = function ($data): array {
        return $this->photoService->normalize($data);
      };
      /** @var PersistentCollection $inner */
      $inner->initialize();
      return $inner->map($callback);
    };

    $isFavoriteCallback = function ($inner, $outer, $format) {
      if ($this->user == null) {
        return false;
      }
      $gymId = $outer->getId();
      $isFavorite = $this->user
        ->getfavoriteGyms()
        ->map(function ($gym) {
          return $gym->getId();
        })
        ->contains($gymId);
      return $isFavorite;
    };

    $context[
      AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER
    ] = $circularReferenceHandler;

    $context[AbstractNormalizer::CALLBACKS]['photos'] = $photosCallback;
    $context[AbstractNormalizer::CALLBACKS]['isFavorite'] = $isFavoriteCallback;

    $context[AbstractNormalizer::IGNORED_ATTRIBUTES][] = 'schedules';
    if ($this->user == null) {
      $context[AbstractNormalizer::IGNORED_ATTRIBUTES][] = 'isFavorite';
    }

    $context[ProxyNormalizerInterface::USED_PROXIES][] = Gym::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
