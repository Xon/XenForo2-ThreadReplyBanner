<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\ThreadReplyBanner\XF\Service\Thread;

use SV\ThreadReplyBanner\Service\ReplyBanner\Manager as ReplyBannerManagerSvc;
use SV\ThreadReplyBanner\XF\Entity\Thread as ExtendedThreadEntity;
use XF\Entity\Thread as ThreadEntity;

/**
 * @method ExtendedThreadEntity getThread()
 */
class Creator extends XFCP_Creator
{
    /**
     * @var null|ReplyBannerManagerSvc
     */
    protected $replyBannerManagerSvcForSv;

    public function getReplyBannerManagerSvcForSv(): ?ReplyBannerManagerSvc
    {
        return $this->replyBannerManagerSvcForSv;
    }

    /**
     * @deprecated Since 2.4.0
     *
     * @param string $text
     * @param bool $active
     */
    public function setReplyBanner(string $text, bool $active)
    {
        $this->setupReplyBannerSvcForSv($text, $active);
    }

    /**
     * @since 2.4.0
     *
     * @param string $text
     * @param bool $active
     */
    public function setupReplyBannerSvcForSv(string $text, bool $active)
    {
        if ($this->getReplyBannerManagerSvcForSv())
        {
            return;
        }

        /** @var ReplyBannerManagerSvc $replyBannerManagerSvc */
        $replyBannerManagerSvc = $this->service('SV\ThreadReplyBanner:ReplyBanner\Manager', $this->getThread());

        $this->replyBannerManagerSvcForSv = $replyBannerManagerSvc
            ->setUser(\XF::visitor())
            ->setRawText($text)
            ->setIsActive($active);
    }

    /**
     * @return array
     */
    protected function _validate()
    {
        $errors = parent::_validate();

        $replyBannerManagerSvc = $this->getReplyBannerManagerSvcForSv();
        if ($replyBannerManagerSvc &&
            !$replyBannerManagerSvc->validate($moreErrors) &&
            \is_array($moreErrors))
        {
            $errors = \array_merge($errors, $moreErrors);
        }

        return $errors;
    }

    /**
     * @return ThreadEntity
     */
    protected function _save()
    {
        $db = $this->db();
        $db->beginTransaction();

        $thread = parent::_save();
        $replyBannerManagerSvc = $this->getReplyBannerManagerSvcForSv();
        if ($replyBannerManagerSvc)
        {
            $replyBannerManagerSvc->save();
        }

        $db->commit();

        return $thread;
    }
}