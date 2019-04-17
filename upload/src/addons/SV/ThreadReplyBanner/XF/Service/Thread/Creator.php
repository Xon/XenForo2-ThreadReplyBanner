<?php

namespace SV\ThreadReplyBanner\XF\Service\Thread;

/**
 * Class Creator
 *
 * @package SV\ThreadReplyBanner\XF\Service\Thread
 */
class Creator extends XFCP_Creator
{
    /**
     * @var \SV\ThreadReplyBanner\Entity\ThreadBanner
     */
    protected $threadBanner;

    /**
     * @param string $text
     * @param bool   $active
     */
    public function setReplyBanner($text, $active)
    {
        /** @var \SV\ThreadReplyBanner\XF\Entity\Thread $thread */
        $thread = $this->getThread();
        $thread->has_banner = $active;

        /** @var \SV\ThreadReplyBanner\Entity\ThreadBanner $threadBanner */
        $threadBanner = $thread->getRelationOrDefault('ThreadBanner');

        $threadBanner->banner_user_id = \XF::visitor()->user_id;
        $threadBanner->banner_edit_count = 0;
        $threadBanner->banner_last_edit_date = 0;
        $threadBanner->banner_last_edit_user_id = 0;
        $threadBanner->raw_text = $text;
        $threadBanner->banner_state = $active;

        $this->threadBanner = $threadBanner;
    }

    /**
     * @return \SV\ThreadReplyBanner\Entity\ThreadBanner
     */
    public function getThreadBanner()
    {
        return $this->threadBanner;
    }
}