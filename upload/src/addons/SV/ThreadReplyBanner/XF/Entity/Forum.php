<?php

namespace SV\ThreadReplyBanner\XF\Entity;

use SV\ThreadReplyBanner\Entity\ContentBannerInterface as ContentBannerEntityInterface;
use SV\ThreadReplyBanner\Entity\ContentBannerTrait as ContentBannerEntityTrait;
use SV\ThreadReplyBanner\Entity\ForumBanner as ForumBannerEntity;
use XF\Mvc\Entity\Structure as EntityStructure;
use XF\Phrase;

/**
 * @since 2.4.0
 *
 * COLUMNS
 * @property bool sv_has_forum_banner
 *
 * RELATIONS
 * @property ForumBannerEntity SvForumBanner
 */
class Forum extends XFCP_Forum implements ContentBannerEntityInterface
{
    use ContentBannerEntityTrait;

    public function canViewSvContentReplyBanner(Phrase &$error = null) : bool
    {
        if (!$this->sv_has_forum_banner)
        {
            return false;
        }

        $forumBanner = $this->SvForumBanner;
        if (!$forumBanner)
        {
            return false;
        }

        return $forumBanner->banner_state;
    }

    public function canManageSvContentReplyBanner(Phrase &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasAdminPermission('node');
    }

    /**
     * @inheritDoc
     */
    public function getSvContentReplyBanner()
    {
        if (!$this->sv_has_forum_banner)
        {
            return null;
        }

        return $this->SvForumBanner;
    }

    /**
     * @throws \XF\PrintableException
     */
    protected function _postDelete()
    {
        parent::_postDelete();

        $this->_postDeleteForSvContentBanner();
    }

    /**
     * @param EntityStructure $structure
     *
     * @return EntityStructure
     */
    public static function getStructure(EntityStructure $structure)
    {
        $structure = parent::getStructure($structure);

        static::setupDefaultStructureForSvBanner($structure);
    
        return $structure;
    }
}