<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int thread_id
 * @property string raw_text
 * @property bool banner_state
 * @property int banner_user_id
 * @property int banner_edit_count
 * @property int banner_last_edit_date
 * @property int banner_last_edit_user_id
 *
 * RELATIONS
 * @property \XF\Entity\User User
 * @property \SV\ThreadReplyBanner\XF\Entity\Thread Thread
 */
class ThreadBanner extends Entity
{
    const MAX_BANNER_LENGTH = 65536;

    /**
     * @return bool
     */
    public function canView()
    {
        return $this->Thread->canViewBanner();
    }

    /**
     * @return bool
     */
    public function canEdit()
    {
        return $this->Thread->canManageThreadReplyBanner();
    }

    /**
     * @param null $error
     * @return bool
     */
    public function canViewHistory(/** @noinspection PhpUnusedParameterInspection */ &$error = null)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        if (!$this->app()->options()->editHistory['enabled'])
        {
            return false;
        }

        return $this->Thread->canManageThreadReplyBanner();
    }

    protected function _postSave()
    {
        if ($this->getOption('log_moderator'))
        {
            $this->app()->logger()->logModeratorChanges('thread_banner', $this);
        }
    }

    /**
     * @param Structure $structure
     * @return Structure
     */
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_sv_thread_banner';
        $structure->shortName = 'SV\ThreadReplyBanner:ThreadBanner';
        $structure->contentType = 'thread_banner';
        $structure->primaryKey = 'thread_id';
        $structure->columns = [
            'thread_id'                => ['type' => self::UINT, 'required' => true],
            'raw_text'                 => [
                'type'      => self::STR,
                'maxLength' => self::MAX_BANNER_LENGTH,
                //'required'  => 'please_enter_valid_banner_text',
            ],
            'banner_state'             => ['type' => self::BOOL, 'required' => true],
            'banner_user_id'           => ['type' => self::UINT, 'required' => true],
            'banner_edit_count'        => ['type' => self::UINT, 'default' => 0],
            'banner_last_edit_date'    => ['type' => self::UINT, 'default' => 0],
            'banner_last_edit_user_id' => ['type' => self::UINT, 'default' => 0],
        ];
        // this is used to determine "ownership" of the banner
        $structure->relations['User'] = [
            'entity'     => 'XF:User',
            'type'       => self::TO_ONE,
            'conditions' => [['user_id', '=', '$banner_user_id']],
            'primary'    => true,
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
}