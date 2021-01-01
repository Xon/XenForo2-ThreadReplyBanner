<?php

namespace SV\ThreadReplyBanner\XF\Admin\Controller;

use SV\ThreadReplyBanner\Service\ReplyBanner\Manager as ReplyBannerManagerSvc;
use XF\ControllerPlugin\Editor as EditorControllerPlugin;
use XF\Mvc\FormAction;
use XF\Mvc\Reply\AbstractReply;
use XF\Mvc\Reply\View as ViewReply;
use XF\Mvc\Reply\Redirect as RedirectReply;
use XF\Mvc\Reply\Reroute as RerouteReply;
use XF\Mvc\Reply\Message as MessageReply;
use XF\Mvc\Reply\Exception as ExceptionReply;
use XF\Mvc\Reply\Error as ErrorReply;
use XF\Entity\Node as NodeEntity;
use SV\ThreadReplyBanner\XF\Entity\Forum as ExtendedForumEntity;
use XF\Entity\Forum as ForumEntity;

/**
 * @since 2.4.0
 */
class Forum extends XFCP_Forum
{
    /**
     * @param NodeEntity $node
     *
     * @return FormAction
     */
    protected function nodeSaveProcess(NodeEntity $node)
    {
        $formAction = parent::nodeSaveProcess($node);

        /** @var ExtendedForumEntity $forum */
        $forum = $node->getDataRelationOrDefault(false);
        $formAction->setupEntityInput($forum, $this->filter([
            'sv_has_forum_banner' => 'bool'
        ]));

        $replyBannerSvc = $this->getReplyBannerManagerSvcForSv($forum);

        /** @var EditorControllerPlugin $editorPlugin */
        $editorPlugin = $this->plugin('XF:Editor');
        $rawText = $editorPlugin->fromInput('sv_forum_reply_banner_raw_text');
        $isActive = $this->filter('sv_forum_reply_banner_is_active', 'bool');

        $formAction->basicValidateServiceSave($replyBannerSvc, function () use($replyBannerSvc, $rawText, $isActive)
        {
            $replyBannerSvc->setRawText($rawText)->setIsActive($isActive);
        });

        return $formAction;
    }

    protected function getReplyBannerManagerSvcForSv(ForumEntity $forum) : ReplyBannerManagerSvc
    {
        return $this->service('SV\ThreadReplyBanner:ReplyBanner\Manager', $forum);
    }
}