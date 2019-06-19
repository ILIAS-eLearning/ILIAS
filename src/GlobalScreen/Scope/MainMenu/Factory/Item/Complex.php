<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAsyncContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\UI\Component\Component;

/**
 * Class Complex
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Complex extends AbstractChildItem implements hasAsyncContent, hasContent
{

    /**
     * @var
     */
    private $content;
    /**
     * @var string
     */
    private $async_content_url = '';


    /**
     * @inheritDoc
     */
    public function getAsyncContentURL() : string
    {
        return $this->async_content_url;
    }


    /**
     * @param string $async_content_url
     *
     * @return Complex
     */
    public function withAsyncContentURL(string $async_content_url) : hasAsyncContent
    {
        $clone = clone($this);
        $clone->async_content_url = $async_content_url;

        return $clone;
    }


    /**
     * @param Component $ui_component
     *
     * @return Complex
     */
    public function withContent(Component $ui_component) : hasContent
    {
        $clone = clone($this);
        $clone->content = $ui_component;

        return $clone;
    }


    /**
     * @return Component
     */
    public function getContent() : Component
    {
        return $this->content;
    }
}
