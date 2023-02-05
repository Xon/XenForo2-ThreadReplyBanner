<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure as EntityStructure;
use XF\Entity\User as UserEntity;
use XF\Phrase;
use SV\ThreadReplyBanner\Entity\ContentBannerInterface as ContentBannerEntityInterface;
use SV\ThreadReplyBanner\Entity\ContentBannerTrait as ContentBannerEntityTrait;

/**
 * @since 2.4.0
 *
 * COLUMNS
 * @property string|null raw_text
 * @property int banner_state
 * @property int banner_user_id
 * @property int banner_edit_count
 * @property int banner_last_edit_date
 * @property int banner_last_edit_user_id
 *
 * RELATIONS
 * @property UserEntity User
 */
abstract class AbstractBanner extends Entity
{
    const MAX_BANNER_LENGTH = 65536;

    const SUPPORTS_MOD_LOG = false;

    abstract public function canView(Phrase &$error = null) : bool;

    abstract public function canEdit(Phrase &$error = null) : bool;

    abstract public function canViewEditHistory(Phrase &$error = null) : bool;

    /**
     * @return Entity|ContentBannerEntityInterface|ContentBannerEntityTrait
     */
    abstract public function getAssociatedContent() : Entity;

    protected function _preSave(): void
    {
        if ($this->isInsert() && !$this->isChanged('banner_user_id'))
        {
            $this->banner_user_id = \XF::visitor()->user_id;
        }
    }

    protected function _postSave(): void
    {
        if (static::SUPPORTS_MOD_LOG && $this->getOption('log_moderator'))
        {
            $this->app()->logger()->logModeratorChanges('sv_thread_banner', $this);
        }
    }

    protected static function setupDefaultStructure(
        EntityStructure $structure,
        string $table,
        string $shortName,
        string $contentType,
        string $primaryKey
    )
    {
        $structure->table = $table;
        $structure->shortName = $shortName;
        $structure->contentType = $contentType;
        $structure->primaryKey = $primaryKey;
        $structure->columns = [
            $primaryKey                => ['type' => static::UINT, 'required' => true],
            'raw_text'                 => [
                'type'      => static::STR,
                'maxLength' => static::MAX_BANNER_LENGTH,
                //'required'  => 'please_enter_valid_banner_text',
            ],
            'banner_state'             => ['type' => static::BOOL, 'default' => false],
            'banner_user_id'           => ['type' => static::UINT, 'required' => true],
            'banner_edit_count'        => ['type' => static::UINT, 'default' => 0],
            'banner_last_edit_date'    => ['type' => static::UINT, 'default' => 0],
            'banner_last_edit_user_id' => ['type' => static::UINT, 'default' => 0],
        ];
        // this is used to determine "ownership" of the banner
        $structure->relations['User'] = [
            'entity'     => 'XF:User',
            'type'       => static::TO_ONE,
            'conditions' => [
                ['user_id', '=', '$banner_user_id']
            ],
            'primary'    => true,
        ];

        if (static::SUPPORTS_MOD_LOG)
        {
            $structure->options = [
                'log_moderator' => true
            ];
        }
    }
}