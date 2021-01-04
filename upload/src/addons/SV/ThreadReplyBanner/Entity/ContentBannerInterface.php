<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\ThreadReplyBanner\Entity;

use XF\Phrase;
use SV\ThreadReplyBanner\Entity\AbstractBanner as AbstractBannerEntity;

interface ContentBannerInterface
{
    public function canViewSvContentReplyBanner(Phrase &$error = null) : bool;

    public function canManageSvContentReplyBanner(Phrase &$error = null) : bool;

    /**
     * @return AbstractBannerEntity|null;
     */
    public function getSvContentReplyBanner();
}