<?php


namespace SV\ThreadReplyBanner\ModeratorLog;

use XF\Entity\ModeratorLog;
use XF\ModeratorLog\AbstractHandler;
use XF\Mvc\Entity\Entity;

/**
 * Class ThreadBanner
 *
 * @package SV\ThreadReplyBanner\ModeratorLog
 */
class ThreadBanner extends AbstractHandler
{
    /**
     * @param Entity $content
     * @param string $field
     * @param mixed $newValue
     * @param mixed $oldValue
     *
     * @return bool|string
     */
    protected function getLogActionForChange(Entity $content, $field, $newValue, $oldValue)
    {
        switch ($field)
        {
            case 'raw_text':
                return 'thread_reply_banner_edit';
            case 'banner_state':
                return $newValue ? 'activated' : 'deactivated';
        }

        return false;
    }

    /**
     * @param ModeratorLog $log
     * @param Entity       $content
     */
    protected function setupLogEntityContent(ModeratorLog $log, Entity $content)
    {
        /** @var \SV\ThreadReplyBanner\Entity\ThreadBanner $content */
        $log->content_user_id = $content->Thread->user_id;
        $log->content_username = $content->Thread->username;
        $log->content_title = $content->Thread->title;
        $log->content_url = \XF::app()->router('public')->buildLink('nopath:threads', $content->Thread);
        $log->discussion_content_type = 'thread_banner';
        $log->discussion_content_id = $content->thread_id;
    }
}