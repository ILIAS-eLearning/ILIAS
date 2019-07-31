<?php namespace ILIAS\GlobalScreen\ScreenContext\Stack;

use ILIAS\GlobalScreen\ScreenContext\ScreenContext;

/**
 * Class ContextStack
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextStack
{

    /**
     * @var ScreenContext[]
     */
    protected $stack = [];


    /**
     * @param ScreenContext $context
     */
    public function push(ScreenContext $context)
    {
        if (in_array($context, $this->stack)) {
            throw new \LogicException("A context can only be claimed once");
        }
        array_push($this->stack, $context);
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


    public function getStackAsArray() : array
    {
        $return = [];
        foreach ($this->stack as $item) {
            $return[] = $item->getUniqueContextIdentifier();
        }

        return $return;
    }
}
