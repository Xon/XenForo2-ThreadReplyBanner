<?php

namespace SV\ThreadReplyBanner\XF\Admin\Controller;

use SV\StandardLib\Helper;
use SV\ThreadReplyBanner\Service\ReplyBanner\Manager as ReplyBannerManagerSvc;
use XF\ControllerPlugin\Editor as EditorControllerPlugin;
use XF\Mvc\FormAction;
use XF\Entity\Node as NodeEntity;
use SV\ThreadReplyBanner\XF\Entity\Forum as ExtendedForumEntity;

/**
 * @since 2.4.0
 * @extends \XF\Admin\Controller\Forum
 */
class Forum extends XFCP_Forum
{
    /**
     * @param NodeEntity $node
     * @return FormAction
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function nodeSaveProcess(NodeEntity $node)
    {
        $formAction = parent::nodeSaveProcess($node);

        /** @var ExtendedForumEntity $forum */
        $forum = $node->getDataRelationOrDefault(false);

        $replyBannerSvc = ReplyBannerManagerSvc::get($forum);

        $editorPlugin = Helper::plugin($this, EditorControllerPlugin::class);
        $rawText = $editorPlugin->fromInput('sv_forum_reply_banner_raw_text');
        $active = (bool)$this->filter('sv_forum_reply_banner_is_active', 'bool');

        $formAction->basicValidateServiceSave($replyBannerSvc, function () use($replyBannerSvc, $rawText, $active)
        {
            $replyBannerSvc->setRawText($rawText)
                           ->setIsActive($active);
        });

        return $formAction;
    }
}