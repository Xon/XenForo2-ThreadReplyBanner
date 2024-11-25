<?php
/**
 * @noinspection PhpMissingParentCallCommonInspection
 */

namespace SV\ThreadReplyBanner\EditHistory;

use SV\ThreadReplyBanner\Entity\AbstractBanner as AbstractBannerEntity;
use SV\ThreadReplyBanner\Service\ReplyBanner\Manager as ReplyBannerManagerSvc;
use XF\EditHistory\AbstractHandler;
use XF\Entity\EditHistory as EditHistoryEntity;
use XF\Mvc\Entity\Entity;

abstract class AbstractBanner extends AbstractHandler
{
    public function canViewHistory(Entity $content) : bool
    {
        /** @var AbstractBannerEntity $content */
        return $content->canViewEditHistory();
    }

    public function canRevertContent(Entity $content) : bool
    {
        /** @var AbstractBannerEntity $content */
        return $content->canEdit();
    }

    public function getContentText(Entity $content) : string
    {
        /** @var AbstractBannerEntity $content */
        return $content->raw_text ?? '';
    }

    /**
     * @param AbstractBannerEntity|Entity $content
     * @param EditHistoryEntity           $history
     * @param EditHistoryEntity|null      $previous
     */
    public function revertToVersion(Entity $content, EditHistoryEntity $history, ?EditHistoryEntity $previous = null)
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
    public function getHtmlFormattedContent($text, ?Entity $content = null) : string
    {
        return \XF::escapeString($text);
    }

    public function getEditCount(Entity $content) : int
    {
        /** @var AbstractBannerEntity $content */
        return $content->banner_edit_count;
    }

    protected function getReplyBannerManagerSvc(AbstractBannerEntity $banner) : ReplyBannerManagerSvc
    {
        return ReplyBannerManagerSvc::get($banner);
    }
}