<?php

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Forum extends XFCP_Forum {

	protected function setupThreadCreate(\XF\Entity\Forum $forum)
	{
		/** @var \SV\ThreadReplyBanner\XF\Service\Thread\Creator $creator */
		$creator = parent::setupThreadCreate($forum);

		$replyBanner = $this->filter('thread_reply_banner', 'str');
		$replyBannerActive = $this->filter('thread_banner_state', 'bool');
		if (!empty($replyBanner)) {
			$creator->setReplyBanner($replyBanner, $replyBannerActive);
		}

		return $creator;
	}
}