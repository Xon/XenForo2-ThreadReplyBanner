<?php

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure as EntityStructure;
use function array_key_exists;
use function preg_replace;
use function str_replace;
use function ucwords;
use function mb_strtolower;

/**
 * @since 2.4.0
 */
trait ContentBannerTrait
{
    protected function _postDeleteForSvContentBanner(): void
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        [$contentType, $hasBannerCol, $relationship] = static::getSvBannerDefinitions($this->structure()->contentType);

        $contentBanner = $this->getRelationOrDefault($relationship, false);
        if (!$contentBanner->exists())
        {
            return;
        }

        $contentBanner->delete();
    }

    public function updateHasSvContentBanner(bool $hasBanner, bool $newTransaction = true)
    {
        if ($this->isDeleted()) // already deleted so no need to make this change
        {
            return;
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        [$contentType, $hasBannerCol, $relationship] = static::getSvBannerDefinitions($this->structure()->contentType);

        if ($this->get($hasBannerCol) === $hasBanner)
        {
            return;
        }

        $this->set($hasBannerCol, $hasBanner);
        unset($this->_getterCache[$relationship]);

        if ($this->exists()) // this helps with content that hasn't been saved yet
        {
            $this->save(true, $newTransaction);
        }
    }

    public function getSvContentReplyBanner(bool $createNew = false): ?AbstractBanner
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        [$contentType, $hasBannerCol, $relationship] = static::getSvBannerDefinitions($this->structure()->contentType);

        $replyBanner = null;
        if ($replyBanner === null && array_key_exists($relationship, $this->_getterCache))
        {
            $replyBanner = $this->_getterCache[$relationship];
        }

        if ($replyBanner === null && array_key_exists($relationship, $this->_relations))
        {
            $replyBanner = $this->_relations[$relationship];
        }

        if ($replyBanner === null && $this->get($hasBannerCol))
        {
            $replyBanner = $this->get($relationship . '_');
        }

        if ($createNew && $replyBanner === null)
        {
            $replyBanner = $this->getRelationOrDefault($relationship);
        }

        return $replyBanner;
    }

    /** @noinspection PhpUnnecessaryLocalVariableInspection */
    protected static function getSvBannerContentType($contentType): string
    {
        $contentType = preg_replace('#[^a-z0-9]#i', ' ', $contentType);
        $contentType = str_replace(' ', '', ucwords($contentType));

        return $contentType;
    }

    public static function getSvBannerDefinitions($contentType): array
    {
        $contentType = static::getSvBannerContentType($contentType);
        $hasBannerCol = 'sv_has_' . mb_strtolower($contentType) . '_banner';
        $relationship = 'Sv' . $contentType . 'Banner';

        return [$contentType, $hasBannerCol, $relationship];
    }

    protected static function setupDefaultStructureForSvBanner(EntityStructure $structure): void
    {
        [$contentType, $hasBannerCol, $relationship] = static::getSvBannerDefinitions($structure->contentType);

        $structure->columns[$hasBannerCol] = [
            'type'    => Entity::BOOL,
            'default' => false
        ];

        $structure->relations[$relationship] = [
            'entity'     => 'SV\ThreadReplyBanner:' . $contentType . 'Banner',
            'type'       => Entity::TO_ONE,
            'conditions' => $structure->primaryKey,
            'primary'    => true,
        ];
        $structure->getters['svThreadReplyBanner'] = ['getter' => 'getSvContentReplyBanner', 'cache' => true];

        $structure->options['svThreadReplyBanner'] = true; // used for detecting if the class is being extended
    }
}