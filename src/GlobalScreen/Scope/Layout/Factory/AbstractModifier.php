<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use Closure;
use ReflectionFunction;

/**
 * Class AbstractModifier
 *
 * @package ILIAS\GlobalScreen\Scope\Layout\Factory
 */
abstract class AbstractModifier implements Modifier
{

    /**
     * @var Closure
     */
    private $modification = null;


    /**
     * @inheritDoc
     */
    public function withModification(Closure $closure) : Modifier
    {
        $clone = clone $this;
        $clone->modification = $closure;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getModification() : Closure
    {
        return $this->modification;
    }


    /**
     * @inheritDoc
     */
    public function hasValidModification() : bool
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
        $first_argument_type = $this->getClosureFirstArgumentTypeOrNull();
        try {
            $r = new ReflectionFunction($closure);

            if ($first_argument_type !== null) {
                if (!isset($r->getParameters()[0])
                    || !$r->getParameters()[0]->hasType()
                    || $r->getParameters()[0]->getType()->getName() !== $first_argument_type
                ) {
                    return false;
                }
            }

            if (!$r->hasReturnType()
                || $r->getReturnType()->getName() !== $return_type
            ) {
                return false;
            }
        } catch (\ReflectionException $e) {
            return false;
        }

        return true;
    }
}