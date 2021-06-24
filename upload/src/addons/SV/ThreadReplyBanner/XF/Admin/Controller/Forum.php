<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\ThreadReplyBanner\XF\Admin\Controller;

use SV\ThreadReplyBanner\Service\ReplyBanner\Manager as ReplyBannerManagerSvc;
use XF\ControllerPlugin\Editor as EditorControllerPlugin;
use XF\Mvc\FormAction;
use XF\Entity\Node as NodeEntity;
use SV\ThreadReplyBanner\XF\Entity\Forum as ExtendedForumEntity;
use XF\Entity\Forum as ForumEntity;
use XF\Service\AbstractService;

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

        $replyBannerSvc = $this->getReplyBannerManagerSvcForSv($forum);

        /** @var EditorControllerPlugin $editorPlugin */
        $editorPlugin = $this->plugin('XF:Editor');
        $rawText = $editorPlugin->fromInput('sv_forum_reply_banner_raw_text');
        $active = $this->filter('sv_forum_reply_banner_is_active', 'bool');

        $formAction->basicValidateServiceSave($replyBannerSvc, function () use($replyBannerSvc, $rawText, $active)
        {
            $replyBannerSvc->setRawText($rawText)
                           ->setIsActive($active);
        });

        return $formAction;
    }

    /**
     * @param ForumEntity $forum
     * @return ReplyBannerManagerSvc|AbstractService
     */
    protected function getReplyBannerManagerSvcForSv(ForumEntity $forum) : ReplyBannerManagerSvc
    {
        return $this->service('SV\ThreadReplyBanner:ReplyBanner\Manager', $forum);
    }
}