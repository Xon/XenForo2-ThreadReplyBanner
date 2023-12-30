<?php

namespace SV\ThreadReplyBanner\XF\Entity;

use SV\ThreadReplyBanner\Entity\ContentBannerInterface as ContentBannerEntityInterface;
use SV\ThreadReplyBanner\Entity\ContentBannerTrait as ContentBannerEntityTrait;
use XF\Mvc\Entity\Structure;
use XF\Phrase;
use SV\ThreadReplyBanner\Entity\ThreadBanner as ThreadBannerEntity;

/**
 * Extends \XF\Entity\Thread
 *
 * COLUMNS
 * @property bool sv_has_thread_banner
 *
 * RELATIONS
 * @property ThreadBannerEntity svThreadReplyBanner
 */
class Thread extends XFCP_Thread implements ContentBannerEntityInterface
{
    use ContentBannerEntityTrait;

    public function canViewSvContentReplyBanner(Phrase &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (
            !$visitor->hasNodePermission($this->node_id, 'sv_replybanner_show')
            && !$visitor->hasNodePermission($this->node_id, 'sv_replybanner_manage')
        )
        {
            return false;
        }

        $threadBanner = $this->svThreadReplyBanner;
        if ($threadBanner === null)
        {
            return false;
        }

        return $threadBanner->banner_state;
    }

    public function canManageSvContentReplyBanner(Phrase &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasNodePermission($this->node_id, 'sv_replybanner_manage');
    }

    public function getSvReplyBannerEditHistoryRoute(): string
    {
        return 'threads/reply-banner-history';
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->_postDeleteForSvContentBanner();
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
