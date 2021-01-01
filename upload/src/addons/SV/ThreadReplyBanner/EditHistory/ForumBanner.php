<?php

namespace SV\ThreadReplyBanner\EditHistory;

use SV\ThreadReplyBanner\Entity\ForumBanner as ForumBannerEntity;
use XF\Mvc\Entity\Entity;

/**
 * @since 2.4.0
 */
class ForumBanner extends AbstractBanner
{
    /**
     * @param ForumBannerEntity|Entity $content
     *
     * @return string
     */
    public function getContentTitle(Entity $content) : string
    {
        return $content->Forum->Node->title;
    }

    /**
     * @param ForumBannerEntity|Entity $content
     *
     * @return string
     */
    public function getContentLink(Entity $content) : string
    {
        return $this->router('public')->buildLink('forums', $content->Forum);
    }

    /**
     * @param ForumBannerEntity|Entity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content) : array
    {
        return $content->Forum->Node->getBreadcrumbs();
    }

    public function getEntityWith() : array
    {
        $visitor = \XF::visitor();

        return [
            'Forum',
            'Forum.Node.Permissions|' . $visitor->permission_combination_id
        ];
    }
}