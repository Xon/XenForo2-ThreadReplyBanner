<?php

namespace SV\ThreadReplyBanner\XF\Entity;

/*
 * Extends \XF\Entity\Thread
 */
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null thread_id
 * @property int node_id
 * @property string title
 * @property int reply_count
 * @property int view_count
 * @property int user_id
 * @property string username
 * @property int post_date
 * @property bool sticky
 * @property string discussion_state
 * @property bool discussion_open
 * @property string discussion_type
 * @property int first_post_id
 * @property int last_post_date
 * @property int last_post_id
 * @property int last_post_user_id
 * @property string last_post_username
 * @property int first_post_likes
 * @property int prefix_id
 * @property array custom_fields_
 * @property array tags
 *
 * GETTERS
 * @property \XF\Draft draft_reply
 * @property array post_ids
 * @property array last_post_cache
 * @property \XF\CustomField\Set custom_fields
 *
 * RELATIONS
 * @property \XF\Entity\Forum Forum
 * @property \XF\Entity\User User
 * @property \XF\Entity\Post FirstPost
 * @property \XF\Entity\Post LastPost
 * @property \XF\Entity\User LastPoster
 * @property \XF\Entity\ThreadPrefix Prefix
 * @property \XF\Entity\ThreadRead[] Read
 * @property \XF\Entity\ThreadWatch[] Watch
 * @property \XF\Entity\ThreadUserPost[] UserPosts
 * @property \XF\Entity\DeletionLog DeletionLog
 * @property \XF\Entity\Draft[] DraftReplies
 * @property \XF\Entity\ApprovalQueue ApprovalQueue
 * @property \XF\Entity\ThreadRedirect Redirect
 * @property \XF\Entity\ThreadReplyBan[] ReplyBans
 * @property \XF\Entity\Poll Poll
 * @property \XF\Entity\ThreadFieldValue[] CustomFields
 * @property \SV\ThreadReplyBanner\Entity\ThreadBanner ThreadBanner
 */
class Thread extends XFCP_Thread
{
	public function canManageThreadReplyBanner(&$error = null) {
		return \XF::visitor()->hasNodePermission(
			$this->node_id,
			'sv_replybanner_manage'
		);
	}

	public function canViewBanner() {
		return (
			\XF::visitor()->hasPermission('forum','sv_replybanner_show') ||
			\XF::visitor()->hasPermission('forum','sv_replybanner_manage')
		) && $this->ThreadBanner && $this->ThreadBanner->banner_state;
	}

	public function getThreadBanner() {
		if ($this->has_banner) {
			return [
				'title' => '',
				'message' => $this->ThreadBanner->getRenderedBannerText(),
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
		} else {
			return null;
		}
	}

	public static function getStructure(Structure $structure) {
		$structure = parent::getStructure($structure);

		$structure->columns['has_banner'] = ['type' => self::BOOL, 'default' => false];

		$structure->relations['ThreadBanner'] = [
			'entity' => 'SV\ThreadReplyBanner:ThreadBanner',
			'type' => self::TO_ONE,
			'conditions' => 'thread_id',
			'primary' => true,
		];

		return $structure;
	}


}
