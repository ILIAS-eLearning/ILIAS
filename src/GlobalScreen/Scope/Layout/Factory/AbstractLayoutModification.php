<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use Closure;
use LogicException;
use ReflectionFunction;

/**
 * Class AbstractLayoutModification
 *
 * @package ILIAS\GlobalScreen\Scope\Layout\Factory
 */
abstract class AbstractLayoutModification implements LayoutModification
{

    /**
     * @var int
     */
    private $priority;
    /**
     * @var Closure
     */
    private $modification = null;


    /**
     * @inheritDoc
     */
    public function isFinal() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function getPriority() : int
    {
        return $this->priority ?? LayoutModification::PRIORITY_LOW;
    }


    /**
     * @inheritDoc
     */
    public final function withPriority(int $priority) : LayoutModification
    {
        if (!in_array($priority, [LayoutModification::PRIORITY_LOW, LayoutModification::PRIORITY_HIGH])) {
            throw new LogicException("\$priority MUST be LayoutModification::PRIORITY_LOW, LayoutModification::PRIORITY_MEDIUM or LayoutModification::PRIORITY_HIGH");
        }
        $clone = clone $this;
        $clone->priority = $priority;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public final function withHighPriority() : LayoutModification
    {
        $clone = clone $this;
        $clone->priority = LayoutModification::PRIORITY_HIGH;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public final function withLowPriority() : LayoutModification
    {
        $clone = clone $this;
        $clone->priority = LayoutModification::PRIORITY_LOW;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public final function withModification(Closure $closure) : LayoutModification
    {
        $clone = clone $this;
        $clone->modification = $closure;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public final function getModification() : Closure
    {
        return $this->modification;
    }


    /**
     * @inheritDoc
     */
    public final function hasValidModification() : bool
    {
        return ($this->modification instanceof Closure && $this->checkClosure());
    }


    /**
     * @return bool
     */
    private final function checkClosure() : bool
    {
        $closure = $this->modification;
        $return_type = $this->getClosureReturnType();

        try {
            $r = new ReflectionFunction($closure);
            // First Argument
            if (!$this->firstArgumentAllowsNull()) {
                $first_argument_type = $this->getClosureFirstArgumentType();
                if (!isset($r->getParameters()[0])
                    || !$r->getParameters()[0]->hasType()
                    || ($r->getParameters()[0]->getType()->getName() !== $first_argument_type)
                ) {
                    return false;
                }
            }

            // Return type
            if (!$this->returnTypeAllowsNull()) {
                if (!$r->hasReturnType()
                    || ($r->getReturnType()->getName() !== $return_type)
                ) {
                    return false;
                }
            }
        } catch (\ReflectionException $e) {
            return false;
        }

        return true;
    }
}