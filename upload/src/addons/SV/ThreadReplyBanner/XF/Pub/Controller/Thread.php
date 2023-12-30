<?php

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

use SV\ThreadReplyBanner\XF\Service\Thread\Editor as ExtendedEditorService;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Exception as ReplyException;
use XF\Mvc\Reply\Reroute as RerouteReply;
use XF\Service\Thread\Editor as EditorService;

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
     * @return ExtendedEditorService|EditorService
     */
    protected function setupThreadEdit(\XF\Entity\Thread $thread)
    {
        /** @var ExtendedEditorService $editor */
        $editor = parent::setupThreadEdit($thread);

        $this->addBannerFields($editor);

        return $editor;
    }

    /**
     * @param ExtendedEditorService $editor
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected function addBannerFields(ExtendedEditorService &$editor): void
    {
        /** @var \SV\ThreadReplyBanner\XF\Entity\Thread $thread */
        $thread = $editor->getThread();
        if ($thread->canManageSvContentReplyBanner() && $this->filter('banner_fields', 'bool'))
        {
            $editor->setupReplyBannerSvcForSv(
                (string)$this->filter('thread_reply_banner', 'str'),
                (bool)$this->filter('thread_banner_state', 'bool')
            );
        }
    }

    /**
     * @param ?int   $threadId
     * @param array $extraWith
     * @return \XF\Entity\Thread
     * @throws ReplyException
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function assertViewableThread($threadId, array $extraWith = [])
    {
        $extraWith[] = 'SvThreadBanner';

        return parent::assertViewableThread($threadId, $extraWith);
    }
}
