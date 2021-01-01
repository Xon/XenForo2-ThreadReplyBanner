<?php


namespace SV\ThreadReplyBanner\EditHistory;

use SV\ThreadReplyBanner\Entity\ThreadBanner as ThreadBannerEntity;
use XF\Mvc\Entity\Entity;

/**
 * Class ThreadBanner
 *
 * @package SV\ThreadReplyBanner\EditHistory
 */
class ThreadBanner extends AbstractBanner
{
    /**
     * @param ThreadBannerEntity|Entity $content
     *
     * @return string
     */
    public function getContentTitle(Entity $content) : string
    {
        $thread = $content->Thread;
        $prefixEntity = $thread->Prefix;
        $prefix = $prefixEntity ? '[' . $prefixEntity->getTitle() . ']' : '';

        return $prefix . ' ' . $thread->title;
    }

    /**
     * @param ThreadBannerEntity|Entity $content
     *
     * @return string
     */
    public function getContentLink(Entity $content) : string
    {
        return $this->router('public')->buildLink('threads', $content->Thread);
    }

    /**
     * @param ThreadBannerEntity|Entity $content
     *
     * @return array
     */
    public function getBreadcrumbs(Entity $content) : array
    {
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