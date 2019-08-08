<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class BaseItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItem extends AbstractBaseItem implements isItem, hasSymbol, hasTitle, isParent
{

    /**
     * @var isChild[]
     */
    protected $children = [];
    /**
     * @var Glyph
     */
    protected $glyph;
    /**
     * @var string
     */
    protected $title = "";


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
    public function getSymbol() : Symbol
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


    /**
     * @inheritDoc
     */
    public function getChildren() : array
    {
        return $this->children;
    }


    /**
     * @inheritDoc
     */
    public function withChildren(array $children) : isParent
    {
        $clone = clone($this);
        $clone->children = $children;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function appendChild(isChild $child) : isParent
    {
        $this->children[] = $child;

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function hasChildren() : bool
    {
        return count($this->children) > 0;
    }
}
