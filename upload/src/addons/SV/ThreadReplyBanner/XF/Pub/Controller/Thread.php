<?php

namespace SV\ThreadReplyBanner\XF\Pub\Controller;

/*
 * Extends \XF\Pub\Controller\Thread
 */
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;
use XF\Repository\Notice;

class Thread extends XFCP_Thread
{
	public function actionReplyBannerHistory(ParameterBag $params) {
		return $this->rerouteController(
			'XF:EditHistory', 'index',
			[
				'content_type' => 'thread_banner',
				'content_id'   => $params->get('thread_id')
			]
		);
	}

	protected function setupThreadEdit(\XF\Entity\Thread $thread)
	{
		$editor = parent::setupThreadEdit($thread);

		$this->addBannerFields($editor);

		return $editor;
	}

	/**
	 * @param \SV\ThreadReplyBanner\XF\Service\Thread\Editor $editor
	*/
	protected function addBannerFields(&$editor) {
		if (
			$editor->getThread()->canManageThreadReplyBanner() &&
			$this->filter('banner_fields', 'boolean')
		) {
			$editor->setReplyBanner(
				$this->filter('thread_reply_banner', 'string'),
				$this->filter('thread_banner_state', 'boolean')
			);
		}
	}
}
