<?php

namespace SV\ThreadReplyBanner\SV\AdvancedBbCodesPack\BbCode\Tag;

use SV\ThreadReplyBanner\Entity\ThreadBanner as ThreadBannerEntity;

/**
 * @extends \SV\AdvancedBbCodesPack\BbCode\Tag\ModInterrupt
 */
class ModInterrupt extends XFCP_ModInterrupt
{
    /**
     * @var array
     */
    protected $renderOptions = [];

    /**
     * @param string $context
     * @return bool|null
     * @noinspection PhpMissingReturnTypeInspection
     */
    protected function validContext(string $context)
    {
        $entity = $this->renderOptions['entity'] ?? [];
        if ($entity instanceof ThreadBannerEntity)
        {
            return true;
        }

        return parent::validContext($context);
    }

    /**
     * @param array           $tagChildren
     * @param string|string[] $tagOption
     * @param array           $tag
     * @param array           $options
     * @return string
     */
    public function render(array $tagChildren, $tagOption, array $tag, array $options): string
    {
        $this->renderOptions = $options;
        return parent::render($tagChildren, $tagOption, $tag, $options);
    }
}