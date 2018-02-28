<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class ThreadBanner extends Entity {
	const MAX_BANNER_LENGTH = 65536;

	public static function getStructure(Structure $structure) {
		$structure->table = 'xf_thread_banner';
		$structure->shortName = 'SV\ThreadReplyBanner:ThreadBanner';
		$structure->contentType = 'thread_banner';
		$structure->primaryKey = 'thread_id';
		$structure->columns = [
			'thread_id' => ['type' => self::UINT, 'required' => true],
			'raw_text'     => [
				'type'      => self::STR,
				'maxLength' => self::MAX_BANNER_LENGTH,
				'required'  => 'please_enter_valid_banner_text',
			],
			'banner_state' => ['type' => self::UINT, 'required' => true],
            'banner_user_id' => ['type' => self::UINT, 'required' => true],
            'banner_edit_count' => ['type' => self::UINT, 'required' => true],
            'banner_last_edit_date' => ['type' => self::UINT, 'required' => true],
            'banner_last_edit_user_id' => ['type' => self::UINT, 'required' => true],
		];
		$structure->relations['Thread'] = [
			'entity' => 'XF:Thread',
			'type' => self::TO_ONE,
			'conditions' => 'thread_id',
			'primary' => true,
		];

		return $structure;
	}

}