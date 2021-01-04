<?php
/**
 * @noinspection PhpMissingReturnTypeInspection
 */

namespace SV\ThreadReplyBanner\SV\AdvancedBbCodesPack\BbCode\Tag;

/**
 * Extends \SV\AdvancedBbCodesPack\BbCode\Tag\ModInterrupt
 */
class ModInterrupt extends XFCP_ModInterrupt
{
    /**
     * @var array
     */
    protected $renderOptions;

    /**
     * @param string $context
     * @return bool|null
     */
    protected function validContext(string $context)
    {
        if (is_array($this->renderOptions) &&
            isset($this->renderOptions['entity']) &&
            ($this->renderOptions['entity'] instanceof \SV\ThreadReplyBanner\Entity\ThreadBanner))
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