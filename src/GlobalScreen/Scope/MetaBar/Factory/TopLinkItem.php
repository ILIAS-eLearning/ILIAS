<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\TopLinkItemRenderer;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Symbol;

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
 * Class TopLinkItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLinkItem extends AbstractBaseItem implements isItem, hasTitle, hasSymbol
{
    
    protected ?Symbol $symbol = null;
    protected string $title = "";
    protected string $action = "";
    
    /**
     * @inheritDoc
     */
    public function __construct(IdentificationInterface $provider_identification)
    {
        parent::__construct($provider_identification);
        $this->renderer = new TopLinkItemRenderer();
    }
    
    public function withAction(string $action) : self
    {
        $clone         = clone($this);
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
    public function withTitle(string $title) : hasTitle
    {
        $clone        = clone($this);
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
