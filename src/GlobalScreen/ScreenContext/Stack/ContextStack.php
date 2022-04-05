<?php namespace ILIAS\GlobalScreen\ScreenContext\Stack;

use ILIAS\GlobalScreen\ScreenContext\ScreenContext;

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
 * Class ContextStack
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextStack
{
    
    /**
     * @var ScreenContext[]
     */
    protected array $stack = [];
    
    /**
     * @param ScreenContext $context
     */
    public function push(ScreenContext $context) : void
    {
        if (in_array($context, $this->stack)) {
            throw new \LogicException("A context can only be claimed once");
        }
        $this->stack[] = $context;
    }
    
    /**
     * @return ScreenContext
     */
    public function getLast() : ScreenContext
    {
        return end($this->stack);
    }
    
    /**
     * @return ScreenContext[]
     */
    public function getStack() : array
    {
        return $this->stack;
    }
    
    /**
     * @return string[]
     */
    public function getStackAsArray() : array
    {
        $return = [];
        foreach ($this->stack as $item) {
            $return[] = $item->getUniqueContextIdentifier();
        }
        
        return $return;
    }
}
