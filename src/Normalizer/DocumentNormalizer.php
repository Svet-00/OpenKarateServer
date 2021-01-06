<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Utils\EntityHelper;
use App\Entity\Document;

final class DocumentNormalizer implements
  ContextAwareNormalizerInterface,
  NormalizerAwareInterface,
  ProxyNormalizerInterface
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

  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    if (
      isset($context[ProxyNormalizerInterface::USED_PROXIES]) &&
      in_array(
        Document::class,
        $context[ProxyNormalizerInterface::USED_PROXIES]
      )
    ) {
      return false;
    }

    return $data instanceof Document ||
      EntityHelper::isArrayOfType($data, Document::class);
  }

  /**
   * @inheritDoc
   */
  public function normalize($data, string $format = null, array $context = [])
  {
    $urlCallback = function ($innerObject, $outerObject, $format): string {
      /** @var Document $outerObject */
      return $this->urlGenerator->generate(
        'get_document_file',
        ['filename' => $outerObject->getFilename()],
        UrlGeneratorInterface::ABSOLUTE_URL
      );
    };

    $context[AbstractNormalizer::CALLBACKS]['url'] = $urlCallback;

    $context[AbstractNormalizer::IGNORED_ATTRIBUTES][] = 'filename';

    $context[ProxyNormalizerInterface::USED_PROXIES][] = Document::class;

    return $this->normalizer->normalize($data, $format, $context);
  }
}
