<?php


namespace SV\ThreadReplyBanner\XF\Service\Thread;


class Creator extends XFCP_Creator {

	/** @var \SV\ThreadReplyBanner\Entity\ThreadBanner */
	protected $threadBanner = null;

	public function setReplyBanner($text, $active) {

		$thread = $this->getThread();
		$thread->has_banner = $active;

		$threadBanner = $thread->getRelationOrDefault('ThreadBanner');

		$threadBanner->banner_user_id = \XF::visitor()->user_id;
		$threadBanner->banner_edit_count = 0;
		$threadBanner->banner_last_edit_date = \XF::$time;
		$threadBanner->banner_last_edit_user_id = \XF::visitor()->user_id;
		$threadBanner->raw_text = $text;
		$threadBanner->banner_state = $active;

		$this->threadBanner = $threadBanner;
	}

	public function _validate() {
		$errors = parent::_validate();

		if ($this->threadBanner) {
			$this->threadBanner->preSave();
			$errors = array_merge($errors, $this->threadBanner->getErrors());
		}

		return $errors;
	}

	protected function _save() {
		$thread = parent::_save();

		if ($this->threadBanner) {
			$this->threadBanner->save();
		}

		return $thread;
	}
}