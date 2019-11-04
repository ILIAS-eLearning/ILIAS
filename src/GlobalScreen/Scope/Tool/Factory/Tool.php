<?php namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;
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
     * @var Symbol
     */
    protected $symbol;
    /**
     * @var Component
     */
    protected $content;
    /**
     * @var Closure
     */
    protected $content_wrapper;
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
    public function withContentWrapper(Closure $content_wrapper) : hasContent
    {
        $clone = clone($this);
        $clone->content_wrapper = $content_wrapper;

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
        if ($this->content_wrapper !== null) {
            $wrapper = $this->content_wrapper;

            return $wrapper();
        }

        return $this->content;
    }


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone = clone($this);
        $clone->symbol = $symbol;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return $this->symbol;
    }


    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return ($this->symbol instanceof Symbol);
    }
}
