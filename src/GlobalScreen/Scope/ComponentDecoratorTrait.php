<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope;

use Closure;
use ILIAS\UI\Component\Component;
use LogicException;
use ReflectionFunction;
use ReflectionType;

/**
 * Trait ComponentDecoratorTrait
 *
 * @package ILIAS\GlobalScreen\Scope
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
trait ComponentDecoratorTrait
{

    /**
     * @var Closure
     */
    private $component_decorator;


    /**
     * @param Closure $component_decorator
     *
     * @return isGlobalScreenItem
     */
    public function addComponentDecorator(Closure $component_decorator) : isGlobalScreenItem
    {
        if (!$this->checkClosure($component_decorator)) {
            throw new LogicException('first argument and return value of closure must be type-hinted to \ILIAS\UI\Component\Component');
        }
        if ($this->component_decorator instanceof Closure) {
            $existing = $this->component_decorator;
            $this->component_decorator = static function (Component $c) use ($component_decorator, $existing) : Component {
                $component = $existing($c);

                return $component_decorator($component);
            };
        } else {
            $this->component_decorator = $component_decorator;
        }

        return $this;
    }


    /**
     * @return Closure|null
     */
    public function getComponentDecorator() : ?Closure
    {
        return $this->component_decorator;
    }


    private function checkClosure(Closure $c) : bool
    {
        try {
            $r = new ReflectionFunction($c);
            if (count($r->getParameters()) !== 1) {
                return false;
            }
            $first_param_type = $r->getParameters()[0]->getType();
            if ($first_param_type instanceof ReflectionType && $first_param_type->getName() !== Component::class) {
                return false;
            }
            $return_type = $r->getReturnType();
            if ($return_type === null) {
                return false;
            }
            if ($return_type->getName() !== Component::class) {
                return false;
            }

            return true;
        } catch (\Throwable $i) {
            return false;
        }
    }
}
