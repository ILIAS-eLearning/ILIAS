<?php namespace ILIAS\GlobalScreen\ScreenContext\Stack;

use ILIAS\GlobalScreen\ScreenContext\ScreenContext;
use LogicException;

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
 * Class CalledContexts
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class CalledContexts extends ContextCollection
{
    
    private array $call_locations = [];
    
    /**
     * @return ScreenContext
     */
    public function current() : ScreenContext
    {
        return $this->getLast();
    }
    
    /**
     * @param ScreenContext $context
     */
    public function push(ScreenContext $context): void
    {
        $this->claim($context);
    }
    
    public function clear() : void
    {
        $this->call_locations = [];
        $this->stack          = [];
    }
    
    /**
     * @param ScreenContext $context
     */
    public function claim(ScreenContext $context): void
    {
        $this->checkCallLocation($context);
        
        if (in_array($context, $this->stack)) {
            throw new LogicException("A context can only be claimed once");
        }
        if (end($this->stack) instanceof ScreenContext) {
            $context = $context->withAdditionalData($this->getLast()->getAdditionalData());
        }
        
        parent::push($context);
    }
    
    /**
     * @param ScreenContext $context
     */
    private function checkCallLocation(ScreenContext $context): void
    {
        $called_classes = array_filter(
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            function ($item): bool {
                if (!isset($item['class'])) {
                    return false;
                }
                
                return (!in_array($item['class'], [CalledContexts::class, ContextCollection::class]));
            }
        );
        array_walk(
            $called_classes,
            function (&$item): void {
                $item = $item['class'] . ":" . $item['line'];
            }
        );
        
        $call_location = reset($called_classes);
        
        if (isset($this->call_locations[$context->getUniqueContextIdentifier()])) {
            $first_location = $this->call_locations[$context->getUniqueContextIdentifier()];
            throw new LogicException("context '{$context->getUniqueContextIdentifier()}' already claimed in $first_location, second try in $call_location");
        }
        $this->call_locations[$context->getUniqueContextIdentifier()] = $call_location;
    }
}
