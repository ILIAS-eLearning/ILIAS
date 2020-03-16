<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use Closure;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;
use LogicException;
use ReflectionFunction;
use ReflectionType;

/**
 * Trait SymbolDecoratorTrait
 * @package ILIAS\GlobalScreen\Scope
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
trait SymbolDecoratorTrait
{

    /**
     * @var Closure
     */
    private $symbol_decorator;

    /**
     * @param Closure $symbol_decorator
     * @return hasSymbol
     */
    public function addSymbolDecorator(Closure $symbol_decorator) : hasSymbol
    {
        if (!$this->checkClosure($symbol_decorator)) {
            throw new LogicException('first argument of closure must be type-hinted to \ILIAS\UI\Component\Symbol\Symbol');
        }
        if ($this->symbol_decorator instanceof Closure) {
            $existing = $this->symbol_decorator;
            $this->symbol_decorator = static function (Symbol $c) use ($symbol_decorator, $existing) : Symbol {
                $component = $existing();

                return $symbol_decorator($component);
            };
        } else {
            $this->symbol_decorator = $symbol_decorator;
        }

        return $this;
    }

    /**
     * @return Closure|null
     */
    public function getSymbolDecorator() : ?Closure
    {
        return $this->symbol_decorator;
    }

    private function checkClosure(Closure $c) : bool
    {
        try {
            $r = new ReflectionFunction($c);
            if (count($r->getParameters()) !== 1) {
                return false;
            }
            $first_param_type = $r->getParameters()[0]->getType();
            if ($first_param_type instanceof ReflectionType && $first_param_type->getName() !== Symbol::class) {
                return false;
            }
            $return_type = $r->getReturnType();
            if ($return_type === null) {
                return false;
            }
            if ($return_type->getName() !== Symbol::class) {
                return false;
            }

            return true;
        } catch (\Throwable $i) {
            return false;
        }
    }
}
