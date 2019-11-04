<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAsyncContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class Complex
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Complex extends AbstractChildItem implements hasAsyncContent, hasContent, hasTitle, hasSymbol
{

    /**
     * @var Closure
     */
    private $content_wrapper;
    /**
     * @var
     */
    private $content;
    /**
     * @var string
     */
    private $async_content_url = '';
    /**
     * @var string
     */
    private $title = '';
    /**
     * @var Symbol
     */
    private $symbol;


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
     * @param string $title
     *
     * @return Complex
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
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
        return $this->symbol instanceof Symbol;
    }
}
