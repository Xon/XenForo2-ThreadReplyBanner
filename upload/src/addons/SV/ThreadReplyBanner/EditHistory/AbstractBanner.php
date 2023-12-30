<?php


namespace SV\ThreadReplyBanner\EditHistory;

use SV\ThreadReplyBanner\Entity\AbstractBanner as AbstractBannerEntity;
use SV\ThreadReplyBanner\Service\ReplyBanner\Manager as ReplyBannerManagerSvc;
use XF\App as BaseApp;
use XF\EditHistory\AbstractHandler;
use XF\Entity\EditHistory;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Router;
use XF\Service\AbstractService;

abstract class AbstractBanner extends AbstractHandler
{
    /**
     * @param Entity|AbstractBannerEntity $content
     *
     * @return bool
     */
    public function canViewHistory(Entity $content) : bool
    {
        return $content->canViewEditHistory();
    }

    /**
     * @param Entity|AbstractBannerEntity $content
     *
     * @return bool
     */
    public function canRevertContent(Entity $content) : bool
    {
        return $content->canEdit();
    }

    /**
     * @param Entity|AbstractBannerEntity $content
     *
     * @return string
     */
    public function getContentText(Entity $content) : string
    {
        return $content->raw_text ?? '';
    }

    /**
     * @param AbstractBannerEntity|Entity $content
     * @param EditHistory                 $history
     * @param EditHistory|null            $previous
     */
    public function revertToVersion(Entity $content, EditHistory $history, EditHistory $previous = null)
    {
        $managerSvc = $this->getReplyBannerManagerSvc($content)->setLogEdit(false)->setRawText($history->old_text);
        if (!$managerSvc->validate())
        {
            return;
        }

        $managerSvc->save();
    }

    /**
     * @param string $text
     * @param Entity|AbstractBannerEntity|null $content
     *
     * @return string
     */
    public function getHtmlFormattedContent($text, Entity $content = null) : string
    {
        return \XF::escapeString($text);
    }

    /**
     * @param Entity|AbstractBannerEntity $content
     * @return int
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function getEditCount(Entity $content) : int
    {
        return $content->banner_edit_count;
    }

    protected function getReplyBannerManagerSvc(AbstractBannerEntity $banner) : ReplyBannerManagerSvc
    {
        return ReplyBannerManagerSvc::get($banner);
    }
}