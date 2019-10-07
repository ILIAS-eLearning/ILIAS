<?php namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Tree\Tree;

/**
 * Class TreeTool
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TreeTool extends AbstractParentItem implements isTopItem, hasSymbol
{

    /**
     * @var
     */
    protected $symbol;
    /**
     * @var Tree
     */
    protected $tree;
    /**
     * @var string
     */
    protected $title;


    /**
     * @param string $title
     *
     * @return TreeTool
     */
    public function withTitle(string $title) : TreeTool
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
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone = clone($this);
        $clone->symbol = $symbol;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withTree(Tree $tree) : TreeTool
    {
        $clone = clone($this);
        $clone->tree = $tree;

        return $clone;
    }


    /**
     * @return Tree
     */
    public function getTree() : Tree
    {
        return $this->tree;
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
