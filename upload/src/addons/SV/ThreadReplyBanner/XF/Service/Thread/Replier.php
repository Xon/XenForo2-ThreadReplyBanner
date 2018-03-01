<?php


namespace SV\ThreadReplyBanner\XF\Service\Thread;


class Replier extends XFCP_Replier {
	protected function _validate() {
		$errors = parent::_validate();

		if ($this->getThread()->ThreadBanner) {
			$this->getThread()->ThreadBanner->preSave();
			$errors = array_merge($errors, $this->getThread()->ThreadBanner->getErrors());
		}

		return $errors;
	}
}