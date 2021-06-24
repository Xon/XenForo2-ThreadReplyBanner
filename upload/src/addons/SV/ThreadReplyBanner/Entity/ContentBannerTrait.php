<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\ThreadReplyBanner\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure as EntityStructure;

/**
 * @since 2.4.0
 */
trait ContentBannerTrait
{
    /**
     * @throws \XF\PrintableException
     */
    protected function _postDeleteForSvContentBanner()
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($contentType, $hasBannerCol, $relationship) = static::getSvBannerDefinitions($this->structure()->contentType);

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
        list($contentType, $hasBannerCol, $relationship) = static::getSvBannerDefinitions($this->structure()->contentType);

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

    /**
     * @param bool $createNew
     * @return null|\SV\ThreadReplyBanner\EditHistory\AbstractBanner
     */
    public function getSvContentReplyBanner(bool $createNew = false)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        list($contentType, $hasBannerCol, $relationship) = static::getSvBannerDefinitions($this->structure()->contentType);

        $replyBanner =  null;
        if ($replyBanner === null && \array_key_exists($relationship, $this->_getterCache))
        {
            $replyBanner = $this->_getterCache[$relationship];
        }

        if ($replyBanner === null && \array_key_exists($relationship, $this->_relations))
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

    protected static function getSvBannerContentType($contentType): string
    {
        $contentType = preg_replace('#[^a-z0-9]#i', ' ', $contentType);
        $contentType = str_replace(' ', '', ucwords($contentType));

        return $contentType;
    }

    public static function getSvBannerDefinitions($contentType): array
    {
        $contentType = static::getSvBannerContentType($contentType);
        $hasBannerCol = 'sv_has_' . utf8_strtolower($contentType) . '_banner';
        $relationship = 'Sv' . $contentType . 'Banner';

        return [$contentType, $hasBannerCol, $relationship];
    }

    protected static function setupDefaultStructureForSvBanner(EntityStructure $structure)
    {
        list($contentType, $hasBannerCol, $relationship) = static::getSvBannerDefinitions($structure->contentType);

        $structure->columns[$hasBannerCol] = [
            'type' => Entity::BOOL,
            'default' => false
        ];

        $structure->relations[$relationship] = [
            'entity'     => 'SV\ThreadReplyBanner:' . $contentType . 'Banner',
            'type'       => Entity::TO_ONE,
            'conditions' => $structure->primaryKey,
            'primary'    => true,
        ];
        $structure->getters[$relationship] = ['getter' => 'getSvContentReplyBanner', 'cache' => true];

        $structure->options['svThreadReplyBanner'] = true; // used for detecting if the class is being extended
    }
}