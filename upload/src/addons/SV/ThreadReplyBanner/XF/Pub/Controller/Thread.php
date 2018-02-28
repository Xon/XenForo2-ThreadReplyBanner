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

	public function actionIndex(ParameterBag $params) {
		$reply = parent::actionIndex($params);

		if ($reply instanceof View) {
			if ($reply->getParam('thread')->getRelation('ThreadBanner')) {
				$notice = [
					'title' => '',
					'message' => $reply->getParam('thread')->getRelation('ThreadBanner')->getValue('raw_text'),
					'active' => true,
					'display_order' => 1,
					'dismissible' => false,
					'user_criteria' => [],
					'page_criteria' => [],
					'notice_type' => 'scrolling',
					'display_style' => 'primary',
					'css_class' => '',
					'display_duration' => 0,
					'delay_duration' => 0,
					'auto_dismiss' => 0
				];

				$reply->setParam('bannerNotices', [$notice]);

			}
		}

		return $reply;
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
		if ($this->filter('banner_fields', 'boolean')) {
			$editor->setReplyBanner(
				$this->filter('thread_reply_banner', 'string'),
				$this->filter('thread_banner_state', 'boolean')
			);
		}
	}
}
