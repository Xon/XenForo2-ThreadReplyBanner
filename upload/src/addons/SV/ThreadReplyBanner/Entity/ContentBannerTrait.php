<?php

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
        $contentType = $this->getEntityContentType();

        if (!$this->getValue('sv_has_' . $contentType . '_banner'))
        {
            return;
        }

        $contentBanner = $this->getRelationOrDefault('Sv' . utf8_ucfirst($contentType) . 'Banner');
        if (!$contentBanner->exists())
        {
            return;
        }

        $contentBanner->delete();
    }

    public function markHasSvContentBanner(bool $newTransaction = true)
    {
        $this->updateHasSvContentBanner(true, $newTransaction);
    }

    public function markDoesNotHaveSvContentBanner(bool $newTransaction = true)
    {
        $this->updateHasSvContentBanner(false, $newTransaction);
    }

    protected function updateHasSvContentBanner(bool $hasBanner, bool $newTransaction = true)
    {
        if ($this->isDeleted()) // already deleted so no need to make this change
        {
            return;
        }

        $columnName = 'sv_has_' . $this->getEntityContentType() . '_banner';
        if ($this->get($columnName) === $hasBanner)
        {
            return;
        }

        $this->set($columnName, $hasBanner);

        if ($this->exists()) // this helps with content that hasn't been saved yet
        {
            $this->save(true, $newTransaction);
        }
    }

    protected static function setupDefaultStructureForSvBanner(EntityStructure $structure)
    {
        $contentType = $structure->contentType;
        $structure->columns['sv_has_' . $contentType . '_banner'] = [
            'type' => Entity::BOOL,
            'default' => false
        ];

        $ucContentType = utf8_ucfirst($contentType);
        $structure->relations['Sv' . $ucContentType . 'Banner'] = [
            'entity'     => 'SV\ThreadReplyBanner:' . $ucContentType . 'Banner',
            'type'       => Entity::TO_ONE,
            'conditions' => $structure->primaryKey,
            'primary'    => true,
        ];

        $structure->options['svThreadReplyBanner'] = true; // used for detecting if the class is being extended
    }
}