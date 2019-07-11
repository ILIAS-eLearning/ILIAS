<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Tool;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAsyncContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\UI\Component\Component;

/**
 * Class Tool
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tool extends AbstractParentItem implements isTopItem, hasContent, hasAsyncContent
{

    /**
     * @var
     */
    protected $icon;
    /**
     * @var Component
     */
    protected $content;
    /**
     * @var string
     */
    protected $async_content_url;
    /**
     * @var string
     */
    protected $title;


    /**
     * @param string $title
     *
     * @return Tool
     */
    public function withTitle(string $title) : Tool
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }


    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @inheritDoc
     */
    public function getAsyncContentURL() : string
    {
        return $this->async_content_url;
    }


    /**
     * @inheritDoc
     */
    public function withAsyncContentURL(string $async_content_url) : hasAsyncContent
    {
        $clone = clone($this);
        $clone->async_content_url = $async_content_url;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withContent(Component $ui_component) : hasContent
    {
        $clone = clone($this);
        $clone->content = $ui_component;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getContent() : Component
    {
        return $this->content;
    }
}
