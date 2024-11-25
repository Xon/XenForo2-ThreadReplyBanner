<?php
/**
 * @noinspection PhpMissingParentCallCommonInspection
 */

namespace SV\ThreadReplyBanner\EditHistory;

use SV\ThreadReplyBanner\Entity\ThreadBanner as ThreadBannerEntity;
use XF\Mvc\Entity\Entity;

class ThreadBanner extends AbstractBanner
{
    public function getContentTitle(Entity $content) : string
    {
        /** @var ThreadBannerEntity $content */
        $thread = $content->Thread;
        $prefixEntity = $thread->Prefix;
        $prefix = $prefixEntity ? '[' . $prefixEntity->getTitle() . ']' : '';

        return $prefix . ' ' . $thread->title;
    }

    public function getContentLink(Entity $content) : string
    {
        /** @var ThreadBannerEntity $content */
        return \XF::app()->router('public')->buildLink('threads', $content->Thread);
    }

    public function getBreadcrumbs(Entity $content) : array
    {
        /** @var ThreadBannerEntity $content */
        return $content->Thread->getBreadcrumbs();
    }

    public function getEntityWith() : array
    {
        $visitor = \XF::visitor();

        return [
            'Thread',
            'Thread.Forum',
            'Thread.Forum.Node.Permissions|' . $visitor->permission_combination_id
        ];
    }
}