<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int thread_id
 * @property string raw_text
 * @property int banner_state
 * @property int banner_user_id
 * @property int banner_edit_count
 * @property int banner_last_edit_date
 * @property int banner_last_edit_user_id
 *
 * RELATIONS
 * @property \XF\Entity\Thread Thread
 */
class ThreadBanner extends Entity
{
    const MAX_BANNER_LENGTH = 65536;

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_thread_banner';
        $structure->shortName = 'SV\ThreadReplyBanner:ThreadBanner';
        $structure->contentType = 'thread_banner';
        $structure->primaryKey = 'thread_id';
        $structure->columns = [
            'thread_id'                => ['type' => self::UINT, 'required' => true],
            'raw_text'                 => [
                'type'      => self::STR,
                'maxLength' => self::MAX_BANNER_LENGTH,
                'required'  => 'please_enter_valid_banner_text',
            ],
            'banner_state'             => ['type' => self::UINT, 'required' => true],
            'banner_user_id'           => ['type' => self::UINT, 'required' => true],
            'banner_edit_count'        => ['type' => self::UINT, 'required' => true],
            'banner_last_edit_date'    => ['type' => self::UINT, 'required' => true],
            'banner_last_edit_user_id' => ['type' => self::UINT, 'required' => true],
        ];
        $structure->relations['Thread'] = [
            'entity'     => 'XF:Thread',
            'type'       => self::TO_ONE,
            'conditions' => 'thread_id',
            'primary'    => true,
        ];
        $structure->options = [
            'log_moderator' => true
        ];

        return $structure;
    }

    public function getRenderedBannerText()
    {
        return \XF::app()->bbCode()->render(
            $this->raw_text,
            'html',
            'post',
            null
        );
    }

    protected function _postSave()
    {
        if ($this->isUpdate() && $this->getOption('log_moderator'))
        {
            $this->app()->logger()->logModeratorChanges('thread_banner', $this);
        }
    }
}