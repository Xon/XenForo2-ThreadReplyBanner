<?php

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

/**
 * Class Forum
 *
 * @package SV\ThreadReplyBanner\XF\Pub\Controller
 */
class Forum extends XFCP_Forum
{
    /**
     * @param \XF\Entity\Forum $forum
     * @return \SV\ThreadReplyBanner\XF\Service\Thread\Creator|\XF\Service\Thread\Creator
     */
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