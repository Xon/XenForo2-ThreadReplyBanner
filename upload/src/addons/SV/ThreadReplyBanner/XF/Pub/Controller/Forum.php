<?php

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

class Forum extends XFCP_Forum
{
    protected function setupThreadCreate(\XF\Entity\Forum $forum)
    {
        /** @var \SV\ThreadReplyBanner\XF\Service\Thread\Creator $creator */
        $creator = parent::setupThreadCreate($forum);

        /** @var \SV\ThreadReplyBanner\XF\Entity\Thread $thread */
        $thread = $creator->getThread();
        if ($thread->canManageThreadReplyBanner())
        {
            $replyBanner = $this->filter('thread_reply_banner', 'str');
            $replyBannerActive = $this->filter('thread_banner_state', 'bool');
            if (!empty($replyBanner))
            {
                $creator->setReplyBanner($replyBanner, $replyBannerActive);
            }
        }

        return $creator;
    }
}