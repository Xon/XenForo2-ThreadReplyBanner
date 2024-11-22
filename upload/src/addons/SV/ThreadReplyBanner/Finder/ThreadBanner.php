<?php

namespace SV\ThreadReplyBanner\Finder;

use SV\StandardLib\Helper;
use XF\Mvc\Entity\AbstractCollection as AbstractCollection;
use XF\Mvc\Entity\Finder as Finder;
use SV\ThreadReplyBanner\Entity\ThreadBanner as ThreadBannerEntity;

 /**
 * @method AbstractCollection<ThreadBannerEntity>|ThreadBannerEntity[] fetch(?int $limit = null, ?int $offset = null)
 * @method ThreadBannerEntity|null fetchOne(?int $offset = null)
 * @implements \IteratorAggregate<string|int,ThreadBannerEntity>
 * @extends Finder<ThreadBannerEntity>
 */
class ThreadBanner extends Finder
{
    /**
      * @return static
      */
    public static function finder(): self
    {
        return Helper::finder(self::class);
    }
}
