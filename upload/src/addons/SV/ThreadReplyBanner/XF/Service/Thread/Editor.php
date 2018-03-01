<?php

namespace SV\ThreadReplyBanner\XF\Service\Thread;

use XF\Entity\Thread;


class Editor extends XFCP_Editor
{

    protected $logEdit = true;

    protected $bannerText;
    protected $bannerActive;
    protected $oldBanner;

    protected $logDelay;
    protected $logHistory = true;

    protected $threadBanner;

    public function __construct(\XF\App $app, Thread $thread)
    {
        $upstream = parent::__construct($app, $thread);

        /** @var \SV\ThreadReplyBanner\Entity\ThreadBanner $threadBanner */
        $this->threadBanner = $this->finder('SV\ThreadReplyBanner:ThreadBanner')->whereId($thread->thread_id)->fetchOne();

        return $upstream;
    }


    public function logDelay($logDelay)
    {
        $this->logDelay = $logDelay;
    }

    public function logEdit($logEdit)
    {
        $this->logEdit = $logEdit;
    }

    public function logHistory($logHistory)
    {
        $this->logHistory = $logHistory;
    }

    public function setReplyBanner($banner, $active)
    {
        $oldBanner = isset($this->threadBanner['raw_text']) ? $this->threadBanner['raw_text'] : '';

        $this->thread->has_banner = $active;

        $this->bannerText = $banner;
        $this->bannerActive = $active;

        if (!empty($this->threadBanner))
        {
            $this->threadBanner->raw_text = $banner;
            $this->threadBanner->banner_state = $active;
        }

        if (empty($this->threadBanner) || $this->threadBanner->isChanged('raw_text'))
        {
            $this->setupReplyBannerEditHistory($oldBanner);
        }
    }

    protected function setupReplyBannerEditHistory($oldBanner)
    {
        $thread = $this->thread;
        $threadBanner = $this->threadBanner;

        $options = $this->app->options();
        if ($options->editLogDisplay['enabled'] && $this->logEdit)
        {
            $delay = is_null($this->logDelay) ? $options->editLogDisplay['delay'] * 60 : $this->logDelay;
            if ($thread->post_date + $delay <= \XF::$time)
            {
                if (!empty($threadBanner))
                {
                    $threadBanner->banner_edit_count++;
                    $threadBanner->banner_last_edit_date = \XF::$time;
                    $threadBanner->banner_last_edit_user_id = \XF::visitor()->user_id;
                }
            }
        }

        if ($options->editHistory['enabled'] && $this->logHistory)
        {
            $this->oldBanner = $oldBanner;
        }
    }


    /**
     * @return \XF\Entity\Thread
     */
    protected function _save()
    {
        $visitor = \XF::visitor();

        $db = $this->db();
        $db->beginTransaction();

        $thread = parent::_save();

        if (empty($this->threadBanner))
        {
            $this->threadBanner = $thread->getRelationOrDefault('ThreadBanner');

            $this->threadBanner->banner_user_id = \XF::visitor()->user_id;
            $this->threadBanner->banner_edit_count = 0;
            $this->threadBanner->banner_last_edit_date = \XF::$time;
            $this->threadBanner->banner_last_edit_user_id = \XF::visitor()->user_id;
            $this->threadBanner->raw_text = $this->bannerText;
            $this->threadBanner->banner_state = $this->bannerActive;
        }

        $this->threadBanner->save();

        if ($this->oldBanner)
        {
            /** @var \XF\Repository\EditHistory $repo */
            $repo = $this->repository('XF:EditHistory');
            $repo->insertEditHistory('thread_banner', $this->threadBanner, $visitor, $this->oldBanner, $this->app->request()->getIp());
        }

        $db->commit();

        return $thread;
    }
}