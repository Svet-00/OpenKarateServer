<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Utils\PreconfiguredComponents;
use App\Utils\EntityHelper;
use App\Entity\Schedule;

final class ScheduleNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface
{
  use NormalizerAwareTrait;

  public function __construct()
  {
  }

  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(
        Schedule::class,
        $context[ProxyNormalizerInterface::USED_PROXIES]
      )
    ) {
      return false;
    }
    return $data instanceof Schedule ||
      EntityHelper::isArrayOfType($data, Schedule::class);
  }

  public function normalize($data, $format = '', $context = [])
  {
    $crh = function ($object, $format, $context) {
      return $object->getId();
    };

    $gymCallback = function ($innerObject) {
      return $innerObject->getId();
    };

    $context[AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER] = $crh;
    $context[AbstractNormalizer::CALLBACKS]['gym'] = $gymCallback;

    $context[ProxyNormalizerInterface::USED_PROXIES][] = Schedule::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
