<?php

namespace SV\ThreadReplyBanner\XF\Entity;

use SV\ThreadReplyBanner\Entity\ContentBannerInterface as ContentBannerEntityInterface;
use SV\ThreadReplyBanner\Entity\ContentBannerTrait as ContentBannerEntityTrait;
use SV\ThreadReplyBanner\Entity\ForumBanner as ForumBannerEntity;
use XF\Mvc\Entity\Structure;
use XF\Phrase;

/**
 * @since 2.4.0
 * @extends \XF\Entity\Forum
 *
 * COLUMNS
 * @property bool $sv_has_forum_banner
 *
 * RELATIONS
 * @property-read ForumBannerEntity $svThreadReplyBanner
 */
class Forum extends XFCP_Forum implements ContentBannerEntityInterface
{
    use ContentBannerEntityTrait;

    public function canViewSvContentReplyBanner(?Phrase &$error = null) : bool
    {
        $visitor = \XF::visitor();
        if (
            !$visitor->hasNodePermission($this->node_id, 'sv_replybanner_show')
            && !$visitor->hasAdminPermission('node')
        )
        {
            return false;
        }

        $forumBanner = $this->svThreadReplyBanner;
        if ($forumBanner === null)
        {
            return false;
        }

        return $forumBanner->banner_state;
    }

    public function canManageSvContentReplyBanner(?Phrase &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasAdminPermission('node');
    }

    public function getSvReplyBannerEditHistoryRoute(): string
    {
        return 'forums/reply-banner-history';
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->_postDeleteForSvContentBanner();
    }

    protected static function getSvBannerContentType($contentType): string
    {
        // forums do not have content types :(
        return 'Forum';
    }

    /**
     * @param Structure $structure
     * @return Structure
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        static::setupDefaultStructureForSvBanner($structure);
    
        return $structure;
    }
}