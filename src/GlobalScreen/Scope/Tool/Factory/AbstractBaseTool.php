<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;

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
 * Class AbstractBaseTool
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseTool extends AbstractParentItem implements isToolItem
{
    use SymbolDecoratorTrait;
    
    protected ?Closure $close_callback = null;
    
    protected bool $initially_hidden = false;
    
    /**
     * @inheritDoc
     */
    public function withInitiallyHidden(bool $initially_hidden) : isToolItem
    {
        $clone = clone($this);
        $clone->initially_hidden = $initially_hidden;
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function isInitiallyHidden() : bool
    {
        return $this->initially_hidden;
    }
    
    /**
     * @inheritDoc
     */
    public function withCloseCallback(Closure $close_callback) : isToolItem
    {
        $clone = clone($this);
        $clone->close_callback = $close_callback;
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function getCloseCallback() : Closure
    {
        return $this->close_callback;
    }
    
    /**
     * @inheritDoc
     */
    public function hasCloseCallback() : bool
    {
        return $this->close_callback instanceof Closure;
    }
}
