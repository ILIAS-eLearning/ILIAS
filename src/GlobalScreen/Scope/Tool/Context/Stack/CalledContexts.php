<?php namespace ILIAS\GlobalScreen\Scope\Tool\Context\Stack;

use ILIAS\GlobalScreen\Scope\Tool\Context\ToolContext;
use LogicException;

/**
 * Class CalledContexts
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
final class CalledContexts extends ContextCollection
{

    /**
     * @var array
     */
    private $call_locations = [];


    /**
     * @return ToolContext
     */
    public function current() : ToolContext
    {
        return $this->getLast();
    }


    /**
     * @param ToolContext $context
     */
    public function push(ToolContext $context)
    {
        $this->claim($context);
    }


    /**
     * @param ToolContext $context
     */
    public function claim(ToolContext $context)
    {
        $this->checkCallLocation($context);

        if (in_array($context, $this->stack)) {
            throw new LogicException("A context can only be claimed once");
        }
        if (end($this->stack) instanceof ToolContext) {
            // $context->replaceLayoutDefinition($this->getLast()->getLayoutDefinition());
            $context = $context->withAdditionalData($this->getLast()->getAdditionalData());
        }

        parent::push($context);
    }


    /**
     * @param ToolContext $context
     */
    private function checkCallLocation(ToolContext $context)
    {
        $called_classes = array_filter(
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), function ($item) {
            return (!in_array($item['class'], [CalledContexts::class, ContextCollection::class]));
        }
        );
        array_walk(
            $called_classes, function (&$item) {
            $item = $item['class'] . ":" . $item['line'];
        }
        );

        $call_location = reset($called_classes);

        if (isset($this->call_locations[$context->getUniqueContextIdentifier()])) {
            $first_location = $this->call_locations[$context->getUniqueContextIdentifier()];
            throw new LogicException("context already claimed in $first_location, second try in $call_location");
        }
        $this->call_locations[$context->getUniqueContextIdentifier()] = $call_location;

        $context->addAdditionalData('called_in', $call_location);
    }
}
