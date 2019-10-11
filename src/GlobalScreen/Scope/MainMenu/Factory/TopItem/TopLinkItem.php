<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractBaseItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class TopLinkItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLinkItem extends AbstractBaseItem implements hasTitle, hasAction, isTopItem, hasSymbol
{

    /**
     * @var Symbol
     */
    protected $symbol;
    /**
     * @var bool
     */
    protected $is_external_action = false;
    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $action = '';


    /**
     * @param string $title
     *
     * @return hasTitle|TopLinkItem
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
     * @param string $action
     *
     * @return hasAction|TopLinkItem
     */
    public function withAction(string $action) : hasAction
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
     * @param bool $is_external
     *
     * @return TopLinkItem
     */
    public function withIsLinkToExternalAction(bool $is_external) : hasAction
    {
        $clone = clone $this;
        $clone->is_external_action = $is_external;

        return $clone;
    }


    /**
     * @return bool
     */
    public function isLinkWithExternalAction() : bool
    {
        return $this->is_external_action;
    }


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone = clone $this;
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
