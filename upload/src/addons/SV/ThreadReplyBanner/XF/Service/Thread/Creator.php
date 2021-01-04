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

    /**
     * @return ReplyBannerManagerSvc|null
     */
    public function getReplyBannerManagerSvcForSv()
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
        if ($replyBannerManagerSvc)
        {
            if (!$replyBannerManagerSvc->validate($moreErrors) && \is_array($moreErrors))
            {
                foreach ($moreErrors AS $index => $error)
                {
                    if (\is_numeric($index))
                    {
                        $errors[] = $error;
                    }
                    else
                    {
                        if (\array_key_exists($index, $errors))
                        {
                            $errors[] = $error;
                        }
                        else
                        {
                            $errors[$index] = $error;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @return ThreadEntity|ExtendedThreadEntity
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