<?php

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\ContextAwareEncoderInterface;
use Symfony\Component\Serializer\Encoder\ContextAwareDecoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Gamez\Symfony\Component\Serializer\Normalizer\UuidNormalizer;
use Doctrine\Common\Annotations\AnnotationReader;
use App\Normalizer\UserNormalizer;
use App\Normalizer\UserAwareNormalizerInterface;
use App\Normalizer\UserAwairNormalizer;
use App\Normalizer\ScheduleNormalizer;
use App\Normalizer\PostNormalizer;
use App\Normalizer\PhotoNormalizer;
use App\Normalizer\LinkNormalizer;
use App\Normalizer\GymNormalizer;
use App\Normalizer\EventNormalizer;
use App\Normalizer\DocumentNormalizer;

class MySerializer implements
  SerializerInterface,
  ContextAwareNormalizerInterface,
  ContextAwareDenormalizerInterface,
  ContextAwareEncoderInterface,
  ContextAwareDecoderInterface
{
  private $urlGenerator;
  private $realSerializer;
  /** @var Security */
  private $security;

  public function __construct(
    UrlGeneratorInterface $urlGenerator,
    Security $security
  ) {
    $this->urlGenerator = $urlGenerator;
    $this->security = $security;
    $this->realSerializer = $this->configureSerializer();
  }

  /** @inheritDoc */
  public function supportsNormalization(
    $data,
    string $format = null,
    $context = []
  ) {
    return $this->realSerializer->supportsNormalization(
      $data,
      $format,
      $context
    );
  }

  /** @inheritDoc */
  public function normalize($object, string $format = null, array $context = [])
  {
    return $this->realSerializer->normalize($object, $format, $context);
  }

  /** @inheritDoc */
  public function supportsDenormalization(
    $data,
    string $type,
    string $format = null,
    array $context = []
  ) {
    return $this->realSerializer->supportsDenormalization(
      $data,
      $type,
      $format,
      $context
    );
  }

  /** @inheritDoc */
  public function denormalize(
    $data,
    string $type,
    string $format = null,
    array $context = []
  ) {
    return $this->realSerializer->denormalize($data, $type, $format, $context);
  }

  public function supportsEncoding(string $format, array $context = [])
  {
    return $this->realSerializer->supportsEncoding($format, $context);
  }

  public function encode($data, string $format, array $context = [])
  {
    return $this->realSerializer->encode($data, $format, $context);
  }

  public function supportsDecoding(string $format, array $context = [])
  {
    return $this->realSerializer->supportsDecoding($format, $context);
  }

  public function decode($data, string $format, array $context = [])
  {
    return $this->realSerializer->decode($data, $format, $context);
  }

  public function serialize($data, string $format, array $context = [])
  {
    return $this->realSerializer->serialize($data, $format, $context);
  }

  public function deserialize(
    $data,
    string $type,
    string $format,
    array $context = []
  ) {
    return $this->deserialize($data, $type, $format, $context);
  }

  private function configureSerializer(): Serializer
  {
    $metadataFactory = new ClassMetadataFactory(
      new AnnotationLoader(new AnnotationReader())
    );

    $encoders = [new JsonEncoder()];

    $documentNormalizer = new DocumentNormalizer($this->urlGenerator);
    $linkNormalizer = new LinkNormalizer();
    $photoNormalizer = new PhotoNormalizer($this->urlGenerator);
    $eventNormalizer = new EventNormalizer($documentNormalizer);
    $gymNormalizer = new GymNormalizer($photoNormalizer);
    $scheduleNormalizer = new ScheduleNormalizer();
    $userNormalizer = new UserNormalizer($this->urlGenerator);
    $postNormalizer = new PostNormalizer($documentNormalizer, $photoNormalizer);

    $normalizers = [
      $linkNormalizer,
      $documentNormalizer,
      $photoNormalizer,
      $eventNormalizer,
      $gymNormalizer,
      $postNormalizer,
      $scheduleNormalizer,
      $userNormalizer,
      new UuidNormalizer(),
      new DateTimeNormalizer(),
      new GetSetMethodNormalizer(
        $metadataFactory,
        new CamelCaseToSnakeCaseNameConverter(),
        $this->getTypeExtractor(),
        null,
        null,
        [
          AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ]
      ),
      new ArrayDenormalizer()
    ];

    foreach ($normalizers as $normalizer) {
      if ($normalizer instanceof UserAwareNormalizerInterface) {
        $user = $this->security->getUser();
        $normalizer->setUser($user);
      }
    }

    return new Serializer($normalizers, $encoders);
  }

  private function getTypeExtractor(): PropertyTypeExtractorInterface
  {
    // a full list of extractors is shown further below
    $phpDocExtractor = new PhpDocExtractor();
    $reflectionExtractor = new ReflectionExtractor();

    // list of PropertyListExtractorInterface (any iterable)
    $listExtractors = [$reflectionExtractor];

    // list of PropertyTypeExtractorInterface (any iterable)
    $typeExtractors = [$phpDocExtractor, $reflectionExtractor];

    // list of PropertyDescriptionExtractorInterface (any iterable)
    $descriptionExtractors = [$phpDocExtractor];

    // list of PropertyAccessExtractorInterface (any iterable)
    $accessExtractors = [$reflectionExtractor];

    // list of PropertyInitializableExtractorInterface (any iterable)
    $propertyInitializableExtractors = [$reflectionExtractor];

    $propertyInfo = new PropertyInfoExtractor(
      $listExtractors,
      $typeExtractors,
      $descriptionExtractors,
      $accessExtractors,
      $propertyInitializableExtractors
    );
    return $propertyInfo;
  }
}
