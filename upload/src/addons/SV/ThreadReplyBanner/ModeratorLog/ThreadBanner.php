<?php

namespace SV\ThreadReplyBanner\ModeratorLog;

use SV\ThreadReplyBanner\XF\Entity\Thread;
use XF\Entity\ModeratorLog as ModeratorLogEntity;
use XF\ModeratorLog\AbstractHandler;
use XF\Mvc\Entity\Entity;

class ThreadBanner extends AbstractHandler
{
    /**
     * @param Entity $content
     * @param string $field
     * @param mixed $newValue
     * @param mixed $oldValue
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
     * @param ModeratorLogEntity $log
     * @param Entity             $content
     */
    protected function setupLogEntityContent(ModeratorLogEntity $log, Entity $content): void
    {
        /** @var \SV\ThreadReplyBanner\Entity\ThreadBanner $content */
        /** @var Thread $thread */
        $thread = $content->Thread;
        $log->content_user_id = $thread->user_id;
        $log->content_username = $thread->username;
        $log->content_title = $thread->title;
        $log->content_url = \XF::app()->router('public')->buildLink('nopath:threads', $thread);
        $log->discussion_content_type = 'thread';
        $log->discussion_content_id = $content->thread_id;
    }
}