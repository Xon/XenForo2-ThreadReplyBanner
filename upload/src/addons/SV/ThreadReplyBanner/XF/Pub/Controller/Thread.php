<?php
/**
 * @noinspection PhpMissingParamTypeInspection
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Reroute as RerouteReply;

class Thread extends XFCP_Thread
{
    /**
     * @param ParameterBag $parameterBag
     *
     * @return RerouteReply
     */
    public function actionReplyBannerHistory(ParameterBag $parameterBag) : AbstractReply
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->rerouteController(
            'XF:EditHistory', 'index',
            [
                'content_type' => 'sv_thread_banner',
                'content_id'   => $parameterBag->thread_id
            ]
        );
    }

    /**
     * @param \XF\Entity\Thread $thread
     * @return \SV\ThreadReplyBanner\XF\Service\Thread\Editor|\XF\Service\Thread\Editor
     */
    protected function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        /** @var \SV\ThreadReplyBanner\XF\Service\Thread\Editor $editor */
        $editor = parent::setupThreadEdit($thread);

        $this->addBannerFields($editor);

        return $editor;
    }

    /**
     * @param \SV\ThreadReplyBanner\XF\Service\Thread\Editor $editor
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected function addBannerFields(\SV\ThreadReplyBanner\XF\Service\Thread\Editor &$editor)
    {
        /** @var \SV\ThreadReplyBanner\XF\Entity\Thread $thread */
        $thread = $editor->getThread();
        if ($thread->canManageSvContentReplyBanner() && $this->filter('banner_fields', 'boolean'))
        {
            $editor->setupReplyBannerSvcForSv(
                $this->filter('thread_reply_banner', 'string'),
                $this->filter('thread_banner_state', 'boolean')
            );
        }
    }

    /**
     * @param int   $threadId
     * @param array $extraWith
     * @return \XF\Entity\Thread
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertViewableThread($threadId, array $extraWith = [])
    {
        $extraWith[] = 'SvThreadBanner';

        return parent::assertViewableThread($threadId, $extraWith);
    }
}
