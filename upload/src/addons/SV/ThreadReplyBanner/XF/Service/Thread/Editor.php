<?php

namespace SV\ThreadReplyBanner\XF\Service\Thread;

use SV\ThreadReplyBanner\Entity\ThreadBanner;
use XF\Entity\Thread;
use XF\PrintableException;

class Editor extends XFCP_Editor
{
    /** @var bool  */
    protected $logEdit = true;
    /** @var int */
    protected $logDelay;
    /** @var bool */
    protected $logHistory = true;

    /** @var ThreadBanner */
    protected $threadBanner = null;
    /** @var string */
    protected $oldBanner = null;

    /**
     * @param int $logDelay
     */
    public function logDelay($logDelay)
    {
        $this->logDelay = $logDelay;
    }

    /**
     * @param bool $logEdit
     */
    public function logEdit($logEdit)
    {
        $this->logEdit = $logEdit;
    }

    /**
     * @param bool $logHistory
     */
    public function logHistory($logHistory)
    {
        $this->logHistory = $logHistory;
    }

    /**
     * @param string $banner
     * @param bool   $active
     * @return ThreadBanner|null
     */
    public function setReplyBanner($banner, $active)
    {
        /** @var \SV\ThreadReplyBanner\XF\Entity\Thread $thread */
        $thread = $this->thread;
        $threadBanner = $thread->ThreadBanner;

        if (!$threadBanner)
        {
            // do not create a banner if one doesn't exist and the text is empty (even if the active flag is set)
            if (empty($banner))
            {
                return null;
            }

            $threadBanner = $thread->getRelationOrDefault('ThreadBanner');
            $threadBanner->banner_user_id = \XF::visitor()->user_id;
            $threadBanner->banner_edit_count = 0;
            $threadBanner->banner_last_edit_date = \XF::$time;
            $threadBanner->banner_last_edit_user_id = \XF::visitor()->user_id;
            $oldBanner = '';
        }
        else
        {
            $thread->addCascadedSave($threadBanner);
            $oldBanner = $threadBanner->raw_text;
        }

        $threadBanner->raw_text = $banner;
        $threadBanner->banner_state = $active;
        $thread->has_banner = $active;

        if ($threadBanner && $threadBanner->isChanged('raw_text'))
        {
            $this->setupReplyBannerEditHistory($oldBanner);
        }

        $this->threadBanner = $threadBanner;

        return $threadBanner;
    }

    /**
     * @param string $oldBanner
     */
    protected function setupReplyBannerEditHistory($oldBanner)
    {
        /** @var \SV\ThreadReplyBanner\XF\Entity\Thread $thread */
        $thread = $this->thread;
        $threadBanner = $thread->ThreadBanner;

        $options = $this->app->options();
        if ($options->editLogDisplay['enabled'] && $this->logEdit)
        {
            $delay = is_null($this->logDelay) ? $options->editLogDisplay['delay'] * 60 : $this->logDelay;
            if ($thread->post_date + $delay <= \XF::$time)
            {
                $threadBanner->banner_edit_count++;
                $threadBanner->banner_last_edit_date = \XF::$time;
                $threadBanner->banner_last_edit_user_id = \XF::visitor()->user_id;
            }
        }

        if ($options->editHistory['enabled'] && $this->logHistory)
        {
            $this->oldBanner = $oldBanner;
        }
    }


    /**
     * @return Thread
     * @throws \Exception
     */
    protected function _save()
    {
        $visitor = \XF::visitor();

        $db = $this->db();
        $db->beginTransaction();

        $thread = parent::_save();

        if ($this->threadBanner && $this->oldBanner)
        {
            /** @var \XF\Repository\EditHistory $repo */
            $repo = $this->repository('XF:EditHistory');
            $repo->insertEditHistory('thread_banner', $this->threadBanner, $visitor, $this->oldBanner, $this->app->request()->getIp());
        }

        $db->commit();

        return $thread;
    }
}