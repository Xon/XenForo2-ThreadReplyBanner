<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure as EntityStructure;
use SV\ThreadReplyBanner\XF\Entity\Forum as ExtendedForumEntity;
use XF\Phrase;

/**
 * @since 2.4.0
 *
 * COLUMNS
 * @property int node_id
 *
 * RELATIONS
 * @property ExtendedForumEntity Forum
 */
class ForumBanner extends AbstractBanner
{
    public function canView(Phrase &$error = null) : bool
    {
        return $this->Forum->canViewSvContentReplyBanner($error);
    }

    public function canEdit(Phrase &$error = null): bool
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id)
        {
            return false;
        }

        return $visitor->hasAdminPermission('node');
    }

    /**
     * @since 2.4.0
     *
     * @param Phrase|null $error
     *
     * @return bool
     */
    public function canViewEditHistory(Phrase &$error = null): bool
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

        return $visitor->hasAdminPermission('node');
    }

    /**
     * @inheritDoc
     */
    public function getAssociatedContent(): Entity
    {
        return $this->Forum;
    }

    public static function getStructure(EntityStructure $structure) : EntityStructure
    {
        static::setupDefaultStructure(
            $structure,
            'xf_sv_forum_banner',
            'SV\ThreadReplyBanner:ForumBanner',
            'sv_forum_banner',
            'node_id'
        );

        $structure->relations['Forum'] = [
            'entity'     => 'XF:Forum',
            'type'       => self::TO_ONE,
            'conditions' => 'node_id',
            'primary'    => true,
        ];

        return $structure;
    }
}