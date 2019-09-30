<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class TopParentItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItem extends AbstractParentItem implements isTopItem, hasTitle, hasSymbol
{

    /**
     * @var string
     */
    protected $title;
    /**
     * @var Symbol
     */
    protected $symbol;


    /**
     * @param string $title
     *
     * @return TopParentItem
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
