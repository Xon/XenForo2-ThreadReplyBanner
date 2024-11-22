<?php

namespace SV\ThreadReplyBanner\Finder;

use SV\StandardLib\Helper;
use XF\Mvc\Entity\AbstractCollection as AbstractCollection;
use XF\Mvc\Entity\Finder as Finder;
use SV\ThreadReplyBanner\Entity\ForumBanner as ForumBannerEntity;

 /**
 * @method AbstractCollection<ForumBannerEntity>|ForumBannerEntity[] fetch(?int $limit = null, ?int $offset = null)
 * @method ForumBannerEntity|null fetchOne(?int $offset = null)
 * @implements \IteratorAggregate<string|int,ForumBannerEntity>
 * @extends Finder<ForumBannerEntity>
 */
class ForumBanner extends Finder
{
    /**
      * @return static
      */
    public static function finder(): self
    {
        return Helper::finder(self::class);
    }
}
