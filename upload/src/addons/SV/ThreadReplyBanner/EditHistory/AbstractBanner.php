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
        return $content->raw_text;
    }

    /**
     * @param AbstractBannerEntity|Entity $content
     * @param EditHistory               $history
     * @param EditHistory               $previous
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
     *
     * @return int
     */
    public function getEditCount(Entity $content) : int
    {
        return $content->banner_edit_count;
    }

    protected function app() : BaseApp
    {
        return \XF::app();
    }

    protected function router(string $type = null) : Router
    {
        return $this->app()->router($type);
    }

    protected function service(string $identifier, ...$arguments) : AbstractService
    {
        return $this->app()->service($identifier, ...$arguments);
    }

    /**
     * @param AbstractBannerEntity $banner
     *
     * @return AbstractService|ReplyBannerManagerSvc
     */
    protected function getReplyBannerManagerSvc(AbstractBannerEntity $banner) : ReplyBannerManagerSvc
    {
        return $this->service('SV\ThreadReplyBanner:ReplyBanner\Manager', $banner);
    }
}