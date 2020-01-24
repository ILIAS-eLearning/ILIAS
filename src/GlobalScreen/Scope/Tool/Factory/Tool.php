<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Glyph;
use ILIAS\UI\Component\Symbol\Icon;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class Tool
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Tool extends AbstractBaseTool implements isTopItem, hasContent, supportsTerminating
{

    /**
     * @var Closure
     */
    protected $terminated_callback;
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
     * @var Closure
     */
    protected $close_callback;

    /**
     * @param string $title
     * @return Tool
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone        = clone($this);
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
        $clone                  = clone($this);
        $clone->content_wrapper = $content_wrapper;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withContent(Component $ui_component) : hasContent
    {
        $clone          = clone($this);
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
        // bugfix mantis 25526: make aria labels mandatory
        if (($symbol instanceof Icon\Icon || $symbol instanceof Glyph\Glyph)
            && ($symbol->getAriaLabel() === "")
        ) {
            throw new \LogicException("the symbol's aria label MUST be set to ensure accessibility");
        }

        $clone         = clone($this);
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

    /**
     * @inheritDoc
     */
    public function withTerminatedCallback(Closure $callback) : supportsTerminating
    {
        $clone                      = clone $this;
        $clone->terminated_callback = $callback;

        return $clone;
    }

    /**
     * @return Closure|null
     */
    public function getTerminatedCallback() : ?Closure
    {
        return $this->terminated_callback;
    }

    /**
     * @return bool
     */
    public function hasTerminatedCallback() : bool
    {
        return $this->terminated_callback instanceof Closure;
    }
}
