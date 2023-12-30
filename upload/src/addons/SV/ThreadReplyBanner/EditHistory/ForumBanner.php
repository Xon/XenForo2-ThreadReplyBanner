<?php
/**
 * @noinspection PhpMissingParentCallCommonInspection
 */

namespace SV\ThreadReplyBanner\EditHistory;

use SV\ThreadReplyBanner\Entity\ForumBanner as ForumBannerEntity;
use XF\Mvc\Entity\Entity;

/**
 * @since 2.4.0
 */
class ForumBanner extends AbstractBanner
{
    public function getContentTitle(Entity $content) : string
    {
        /** @var ForumBannerEntity $content */
        $forum = $content->Forum;
        return $forum->Node->title;
    }

    public function getContentLink(Entity $content) : string
    {
        /** @var ForumBannerEntity $content */
        $forum = $content->Forum;

        return \XF::app()->router('public')->buildLink('forums', $forum);
    }

    public function getBreadcrumbs(Entity $content) : array
    {
        /** @var ForumBannerEntity $content */
        $forum = $content->Forum;
        return $forum->Node->getBreadcrumbs();
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