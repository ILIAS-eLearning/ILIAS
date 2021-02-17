<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;
use ILIAS\UI\Component\Symbol\Glyph;
use ILIAS\UI\Component\Symbol\Icon;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Tree\Tree;

/**
 * Class TreeTool
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TreeTool extends AbstractBaseTool implements isTopItem, hasSymbol, isToolItem
{
    use SymbolDecoratorTrait;
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
     * @return TreeTool
     */
    public function withTitle(string $title) : hasTitle
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
        // bugfix mantis 25526: make aria labels mandatory
        if (($symbol instanceof Glyph\Glyph && $symbol->getAriaLabel() === "") ||
            ($symbol instanceof Icon\Icon && $symbol->getLabel() === "")) {
            throw new \LogicException("the symbol's aria label MUST be set to ensure accessibility");
        }

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
