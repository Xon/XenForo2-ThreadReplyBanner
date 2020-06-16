<?php

namespace SV\ThreadReplyBanner\SV\AdvancedBbCodesPack\BbCode\Tag;



/**
 * Extends \SV\AdvancedBbCodesPack\BbCode\Tag\ModInterrupt
 */
class ModInterrupt extends XFCP_ModInterrupt
{
    /**
     * @var array
     */
    protected $tagOptions;

    /**
     * @param string $context
     * @return bool|null
     */
    protected function validContext($context)
    {
        if (is_array($this->tagOptions) &&
            isset($this->tagOptions['entity']) &&
            ($this->tagOptions['entity'] instanceof \SV\ThreadReplyBanner\Entity\ThreadBanner))
        {
            return true;
        }

        return parent::validContext($context);
    }

    /**
     * @param array  $tagChildren
     * @param string $tagOption
     * @param array  $tag
     * @param array  $options
     * @return string
     */
    public function render(array $tagChildren, $tagOption, array $tag, array $options)
    {
        $this->tagOptions = $options;
        return parent::render($tagChildren, $tagOption, $tag, $options);
    }
}