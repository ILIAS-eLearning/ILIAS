<?php namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class Tool
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tool extends AbstractParentItem implements isTopItem, hasContent, hasSymbol
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


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone = clone($this);
        $clone->icon = $symbol;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return $this->icon;
    }


    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return ($this->icon instanceof Symbol);
    }
}
