<?php

namespace SV\ThreadReplyBanner\XF\Entity;

/*
 * Extends \XF\Entity\Thread
 */
use XF\Mvc\Entity\Structure;

class Thread extends XFCP_Thread
{
	public function canManageThreadReplyBanner(&$error = null) {
		return \XF::visitor()->hasNodePermission(
			$this->node_id,
			'sv_replybanner_manage'
		);
	}

	public static function getStructure(Structure $structure) {
		$structure = parent::getStructure($structure);

		$structure->relations['ThreadBanner'] = [
			'entity' => 'SV\ThreadReplyBanner:ThreadBanner',
			'type' => self::TO_ONE,
			'conditions' => 'thread_id',
			'primary' => true,
		];

		return $structure;
	}


}
