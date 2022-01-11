<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbolTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class TopParentItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItem extends AbstractParentItem implements isTopItem, hasTitle, hasSymbol, supportsAsynchronousLoading
{
    use SymbolDecoratorTrait;
    use hasSymbolTrait;
    
    protected string $title = '';
    
    protected bool $supports_async_loading = false;
    
    /**
     * @param string $title
     * @return TopParentItem
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
    
    public function withSupportsAsynchronousLoading(bool $supported) : supportsAsynchronousLoading
    {
        $clone                         = clone($this);
        $clone->supports_async_loading = $supported;
        
        return $clone;
    }
    
    public function supportsAsynchronousLoading() : bool
    {
        return $this->supports_async_loading;
    }
    
}
