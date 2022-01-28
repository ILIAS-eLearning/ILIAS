<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbolTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItemTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;
use ILIAS\UI\Component\Changeable;
use ILIAS\UI\Component\Symbol\Symbol;
use ilLink;
use ilObject2;

/**
 * Class Link
 * Attention: This is not the same as the \ILIAS\UI\Component\Link\Link. Please
 * read the difference between GlobalScreen and UI in the README.md of the GlobalScreen Service.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class RepositoryLink extends AbstractChildItem implements hasTitle, hasAction, hasSymbol, isInterchangeableItem, isTopItem
{
    use hasSymbolTrait;
    use SymbolDecoratorTrait;
    use isInterchangeableItemTrait;

    /**
     * @var int
     */
    protected $ref_id = 0;
    /**
     * @var string
     */
    protected $alt_text;
    /**
     * @var string
     */
    protected $title = '';

    /**
     * @param string $title
     * @return RepositoryLink
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
     * @param string $alt_text
     * @return RepositoryLink
     */
    public function withAltText(string $alt_text) : RepositoryLink
    {
        $clone = clone($this);
        $clone->alt_text = $alt_text;

        return $clone;
    }

    /**
     * @return string
     */
    public function getAltText() : string
    {
        return $this->alt_text;
    }

    /**
     * @return string
     */
    final public function getAction() : string
    {
        return ilLink::_getLink($this->ref_id);
    }

    /**
     * @param string $action
     * @return hasAction
     */
    public function withAction(string $action) : hasAction
    {
        $clone = clone $this;
        $clone->ref_id = (int) $action;

        return $clone;
    }

    /**
     * @param int $ref_id
     * @return RepositoryLink
     */
    public function withRefId(int $ref_id) : RepositoryLink
    {
        $clone = clone $this;
        $clone->ref_id = $ref_id;

        return $clone;
    }

    public function getSymbol() : Symbol
    {
        return $this->symbol;
    }

    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->ref_id;
    }

    /**
     * @inheritDoc
     */
    public function withIsLinkToExternalAction(bool $is_external) : hasAction
    {
        throw new \LogicException("Repository-Links are always internal");
    }

    /**
     * @inheritDoc
     */
    public function isLinkWithExternalAction() : bool
    {
        return false;
    }
}
