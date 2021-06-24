<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\ThreadReplyBanner\XF\Service\Thread;

use SV\ThreadReplyBanner\Service\ReplyBanner\Manager as ReplyBannerManagerSvc;
use XF\Entity\Thread;
use XF\Entity\Thread as ThreadEntity;

/**
 * Class Editor
 *
 * @package SV\ThreadReplyBanner\XF\Service\Thread
 */
class Editor extends XFCP_Editor
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
     * @deprecated Since 2.4.0
     *
     * @param string $banner
     * @param bool   $active
     *
     * @return null
     */
    public function setReplyBanner(string $banner, bool $active)
    {
        $this->setupReplyBannerSvcForSv($banner, $active);

        return null;
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