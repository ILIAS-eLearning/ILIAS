<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class LinkItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LinkItem extends AbstractChildItem implements isItem, hasTitle, hasSymbol, isChild
{

    /**
     * @var Glyph
     */
    protected $glyph;
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var string
     */
    protected $action = "";


    /**
     * @param string $action
     *
     * @return LinkItem
     */
    public function withAction(string $action) : LinkItem
    {
        $clone = clone($this);
        $clone->action = $action;

        return $clone;
    }


    /**
     * @return string
     */
    public function getAction() : string
    {
        return $this->action;
    }


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone = clone($this);
        $clone->glyph = $symbol;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getSymbol() : \ILIAS\UI\Component\Symbol\Symbol
    {
        return $this->glyph;
    }


    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return ($this->glyph instanceof Symbol);
    }


    /**
     * @inheritDoc
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
}
