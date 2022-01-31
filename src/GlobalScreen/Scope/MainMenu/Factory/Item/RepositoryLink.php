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

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

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
    
    protected int $ref_id = 0;
    protected string $alt_text;
    protected string $title = '';
    
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
    
    public function withAltText(string $alt_text) : self
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
    
    public function withRefId(int $ref_id) : self
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
