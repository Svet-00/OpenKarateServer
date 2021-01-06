<?php

namespace App\Enum;

use App\Exception\UnknownPhotoBucketException;
use App\Enum\BasicEnum;

final class PhotoBuckets extends BasicEnum
{
  /**
   * @var string
   */
  const GALLERY = 'gallery';
  /**
   * @var string
   */
  const GYMS = 'gyms';
  /**
   * @var string
   */
  const USERS = 'users';
  /**
   * @var string
   */
  const NOTIfICATIONS = 'notifications';

  private const CONFIG_MAPPINGS = [
    self::GALLERY => 'gallery_directory',
    self::GYMS => 'gym_pictures_directory',
    self::USERS => 'avatars_directory',
    self::NOTIfICATIONS => 'notification_pictures_directory'
  ];

  /**
   * Returns configuration key from services.yaml which is
   * associated with given bucket.
   *
   * @throws UnknownPhotoBucketException
   *
   * @param string $bucket
   */
  public static function getFileLocationParameterKey(string $bucket): string
  {
    if (!static::isValidValue($bucket)) {
      throw new UnknownPhotoBucketException(\sprintf('Unknown bucket: %s', $bucket));
    }
    return self::CONFIG_MAPPINGS[$bucket];
  }
}
