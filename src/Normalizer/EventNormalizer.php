<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer;
use App\Utils\EntityHelper;
use App\Entity\Event;

final class EventNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface
{
  use NormalizerAwareTrait;

  /**
   * @var DocumentNormalizer
   */
  private $documentNormalizer;

  public function __construct(DocumentNormalizer $ds)
  {
    $this->documentNormalizer = $ds;
  }

  /** @inheritDoc */
  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(Event::class, $context[ProxyNormalizerInterface::USED_PROXIES])
    ) {
      return false;
    }

    return $data instanceof Event ||
      EntityHelper::isArrayOfType($data, Event::class);
  }

  /**
   * @inheritDoc
   */
  public function normalize($data, $format = '', $context = [])
  {
    $userCallback = function ($innerObject, $outerObject, $format): array {
      $ids = [];
      /** @var ArrayCollection $innerObject */
      /** @var User $user */
      foreach ($innerObject as $user) {
        $ids[] = $user->getId();
      }
      return $ids;
    };

    $documentsCallback = function ($inner, $outer, $format) {
      return $this->documentNormalizer->normalize($inner);
    };

    $context[AbstractNormalizer::IGNORED_ATTRIBUTES][] = 'participants';

    $context[AbstractNormalizer::CALLBACKS]['participants'] = $userCallback;
    $context[AbstractNormalizer::CALLBACKS]['documents'] = $documentsCallback;

    $context[ProxyNormalizerInterface::USED_PROXIES][] = Event::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
