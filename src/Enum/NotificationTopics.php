<?php

namespace App\Enum;

use App\Enum\BasicEnum;

final class NotificationTopics extends BasicEnum
{
  /**
   * @var string
   */
  const GENERAL = 'general';

  /**
   * @var string
   */
  const NEWS_POST_ADDED = 'news_post_added';
  /**
   * @var string
   */
  const NEWS_POST_UPDATED = 'news_post_updated';

  /**
   * @var string
   */
  const EVENT_ADDED = 'event_added';
  /**
   * @var string
   */
  const EVENT_UPDATED = 'event_updated';

  const SCHEDULE_UPDATED = 'schedule_updated';
}
