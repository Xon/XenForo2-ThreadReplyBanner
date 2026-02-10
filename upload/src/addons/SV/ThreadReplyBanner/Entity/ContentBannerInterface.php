<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Phrase;

interface ContentBannerInterface
{
    public function canViewSvContentReplyBanner(?Phrase &$error = null) : bool;

    public function canManageSvContentReplyBanner(?Phrase &$error = null) : bool;

    public function getSvContentReplyBanner(bool $createNew = false): ?AbstractBanner;

    public function getSvReplyBannerEditHistoryRoute(): string;
}