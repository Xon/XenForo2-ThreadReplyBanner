<?php


namespace SV\ThreadReplyBanner\XF\Service\Thread;


class Creator extends XFCP_Creator {

	protected $bannerText = null;
	protected $bannerActive = null;

	public function setReplyBanner($text, $active) {
		$this->bannerText = $text;
		$this->bannerActive = $active;
	}
	
	protected function _save() {
		$thread = parent::_save();

		if ($this->bannerText) {
			$threadBanner = $this->em()->create('SV\ThreadReplyBanner:ThreadBanner');
			$threadBanner->thread_id = $thread->thread_id;

			$threadBanner->banner_user_id = \XF::visitor()->user_id;
			$threadBanner->banner_edit_count = 0;
			$threadBanner->banner_last_edit_date = \XF::$time;
			$threadBanner->banner_last_edit_user_id = \XF::visitor()->user_id;
			$threadBanner->raw_text = $this->bannerText;
			$threadBanner->banner_state = $this->bannerActive;

			$threadBanner->save();
		}


		return $thread;
	}
}