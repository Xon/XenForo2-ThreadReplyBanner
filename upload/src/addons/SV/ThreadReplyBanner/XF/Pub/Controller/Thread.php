<?php

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

/*
 * Extends \XF\Pub\Controller\Thread
 */
class Thread extends XFCP_Thread
{
    public function actionReplyBannerHistory(ParameterBag $params)
    {
        return $this->rerouteController(
            'XF:EditHistory', 'index',
            [
                'content_type' => 'thread_banner',
                'content_id'   => $params->get('thread_id')
            ]
        );
    }

    protected function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        /** @var \SV\ThreadReplyBanner\XF\Service\Thread\Editor $editor */
        $editor = parent::setupThreadEdit($thread);

        $this->addBannerFields($editor);

        return $editor;
    }

    /**
     * @param \SV\ThreadReplyBanner\XF\Service\Thread\Editor $editor
     */
    protected function addBannerFields(&$editor)
    {
        /** @var \SV\ThreadReplyBanner\XF\Entity\Thread $thread */
        $thread = $editor->getThread();
        if ($thread->canManageThreadReplyBanner() &&
            $this->filter('banner_fields', 'boolean'))
        {
            $editor->setReplyBanner(
                $this->filter('thread_reply_banner', 'string'),
                $this->filter('thread_banner_state', 'boolean')
            );
        }
    }

    protected function assertViewableThread($threadId, array $extraWith = [])
    {
        $extraWith[] = 'ThreadBanner';
        return parent::assertViewableThread($threadId, $extraWith);
    }
}
