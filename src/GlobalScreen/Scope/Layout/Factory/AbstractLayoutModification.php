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
    final public function withPriority(int $priority) : LayoutModification
    {
        if ((self::PRIORITY_LOW <= $priority) && ($priority <= self::PRIORITY_HIGH)) {
            $clone           = clone $this;
            $clone->priority = $priority;

            return $clone;
        }
        throw new LogicException("\$priority MUST be between LayoutModification::PRIORITY_LOW, LayoutModification::PRIORITY_MEDIUM or LayoutModification::PRIORITY_HIGH");

    }


    /**
     * @inheritDoc
     */
    final public function withHighPriority() : LayoutModification
    {
        $clone = clone $this;
        $clone->priority = LayoutModification::PRIORITY_HIGH;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    final public function withLowPriority() : LayoutModification
    {
        $clone = clone $this;
        $clone->priority = LayoutModification::PRIORITY_LOW;

        return $clone;
    }


    /**
     * @param Closure $closure
     *
     * @return LayoutModification|ContentModification|MainBarModification|MetaBarModification|BreadCrumbsModification|LogoModification|FooterModification
     */
    final public function withModification(Closure $closure) : LayoutModification
    {
        $clone = clone $this;
        $clone->modification = $closure;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    final public function getModification() : Closure
    {
        return $this->modification;
    }


    /**
     * @inheritDoc
     */
    final public function hasValidModification() : bool
    {
        return ($this->modification instanceof Closure && $this->checkClosure());
    }


    /**
     * @return bool
     */
    private function checkClosure() : bool
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
