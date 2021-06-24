<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Phrase;
use SV\ThreadReplyBanner\Entity\AbstractBanner as AbstractBannerEntity;

interface ContentBannerInterface
{
    public function canViewSvContentReplyBanner(Phrase &$error = null) : bool;

    public function canManageSvContentReplyBanner(Phrase &$error = null) : bool;

    /**
     * @param bool $createNew
     * @return AbstractBannerEntity|null
     */
    public function getSvContentReplyBanner(bool $createNew = false);

    public function getSvReplyBannerEditHistoryRoute(): string;
}