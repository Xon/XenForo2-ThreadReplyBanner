<?php

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

use XF\Entity\Forum as ForumEntity;
use SV\ThreadReplyBanner\XF\Entity\Forum as ExtendedForumEntity;
use SV\ThreadReplyBanner\XF\Service\Thread\Creator as ExtendedThreadCreatorSvc;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\Reroute as RerouteReply;

class Forum extends XFCP_Forum
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
                'content_type' => 'sv_forum_banner',
                'content_id'   => $parameterBag->node_id
            ]
        );
    }

    /**
     * @param ForumEntity|ExtendedForumEntity $forum
     *
     * @return ExtendedThreadCreatorSvc
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function setupThreadCreate(ForumEntity $forum)
    {
        /** @var ExtendedThreadCreatorSvc $threadCreatorSvc */
        $threadCreatorSvc = parent::setupThreadCreate($forum);

        $thread = $threadCreatorSvc->getThread();
        if ($thread->canManageSvContentReplyBanner())
        {
            $replyBanner = (string)$this->filter('thread_reply_banner', 'str');
            $replyBannerActive = (bool)$this->filter('thread_banner_state', 'bool');
            if (strlen($replyBanner) !== 0)
            {
                $threadCreatorSvc->setupReplyBannerSvcForSv($replyBanner, $replyBannerActive);
            }
        }

        return $threadCreatorSvc;
    }
}