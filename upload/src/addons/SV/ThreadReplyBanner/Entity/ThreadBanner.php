<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Structure;
use XF\Phrase;
use SV\ThreadReplyBanner\XF\Entity\Thread as ExtendedThreadEntity;

/**
 * COLUMNS
 * @property int $thread_id
 *
 * RELATIONS
 * @property-read ExtendedThreadEntity|null $Thread
 */
class ThreadBanner extends AbstractBanner
{
    public const SUPPORTS_MOD_LOG = true;

    public function canView(Phrase &$error = null): bool
    {
        return $this->Thread->canViewSvContentReplyBanner($error);
    }

    public function canEdit(Phrase &$error = null): bool
    {
        return $this->Thread->canManageSvContentReplyBanner($error);
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

        if (!\XF::options()->editHistory['enabled'])
        {
            return false;
        }

        return $this->Thread->canManageSvContentReplyBanner($error);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public static function getStructure(Structure $structure) : Structure
    {
        return static::setupDefaultStructure(
            $structure,
            'xf_sv_thread_banner',
            'SV\ThreadReplyBanner:ThreadBanner',
            'sv_thread_banner',
            'thread_id',
            'Thread',
            [
                'entity'     => 'XF:Thread',
                'type'       => self::TO_ONE,
                'conditions' => 'thread_id',
                'primary'    => true,
            ]
        );
    }
}