<?php


namespace SV\ThreadReplyBanner\EditHistory;

use XF\EditHistory\AbstractHandler;
use XF\Mvc\Entity\Entity;

class ThreadBanner extends AbstractHandler {

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return bool
	 */
	public function canViewHistory(Entity $content) {
		return $content->getRelation('Thread')->canManageThreadReplyBanner() &&
		       $content->getRelation('Thread')->canView();
	}

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return bool
	 */
	public function canRevertContent(Entity $content) {
		return $content->getRelation('Thread')->canManageThreadReplyBanner();
	}

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return string
	 */
	public function getContentTitle(Entity $content) {
		$prefixEntity = $content->getRelation('Thread')->getRelation('Prefix');
		$prefix =  $prefixEntity ? "[".$prefixEntity->getTitle()."]" : "";
		return $prefix . ' ' . $content->getRelation('Thread')->title;
	}

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return mixed|null
	 */
	public function getContentText(Entity $content) {
		return $content->getValue('raw_text');
	}

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return mixed|string
	 */
	public function getContentLink(Entity $content) {
		return \XF::app()->router('public')->buildLink('threads', $content->getRelation('Thread'));
	}

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return array
	 */
	public function getBreadcrumbs(Entity $content) {
		return $content->getRelation('Thread')->getBreadcrumbs();
	}

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 * @param \XF\Entity\EditHistory $history
	 * @param \XF\Entity\EditHistory $previous
	 */
	public function revertToVersion(Entity $content, \XF\Entity\EditHistory $history, \XF\Entity\EditHistory $previous = null) {
		/** @var \SV\ThreadReplyBanner\XF\Service\Thread\Editor $editor */
		$editor = \XF::app()->service('XF:Thread\Editor', $content->getRelation('Thread'));

		$editor->logEdit(false);
		$editor->setReplyBanner($history->old_text, true);

		if (!$previous || $previous->edit_user_id != $content->banner_user_id)
		{
			// if previous is a mod edit, don't show as it may have been hidden
			$content->banner_last_edit_date = 0;
		}
		else if ($previous && $previous->edit_user_id == $content->banner_user_id)
		{
			$content->banner_last_edit_date = $previous->edit_date;
			$content->banner_last_edit_user_id = $previous->edit_user_id;
		}

		return $editor->save();
	}

	/**
	 * @param $text
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return string
	 */
	public function getHtmlFormattedContent($text, Entity $content = null) {
		return htmlspecialchars($text);
	}

	/**
	 * @param \SV\ThreadReplyBanner\Entity\ThreadBanner|Entity $content
	 *
	 * @return mixed|null
	 */
	public function getEditCount(Entity $content) {
		return $content->get('banner_edit_count');
	}

	/**
	 * @return array
	 */
	public function getEntityWith()
	{
		$visitor = \XF::visitor();
		return [
			'Thread',
			'Thread.Forum',
			'Thread.Forum.Node.Permissions|' . $visitor->permission_combination_id
		];
	}
}