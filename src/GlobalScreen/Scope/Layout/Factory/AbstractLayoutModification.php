<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);
namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use Closure;
use LogicException;
use ReflectionFunction;
use ReflectionException;

/**
 * Class AbstractLayoutModification
 * @package ILIAS\GlobalScreen\Scope\Layout\Factory
 */
abstract class AbstractLayoutModification implements LayoutModification
{
    /**
     * @var int
     */
    private $priority;
    /**
     * @var \Closure|null
     */
    private $modification;

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
        if ((self::PRIORITY_LOW > $priority) || ($priority > self::PRIORITY_HIGH)) {
            throw new LogicException("\$priority MUST be between LayoutModification::PRIORITY_LOW, LayoutModification::PRIORITY_MEDIUM or LayoutModification::PRIORITY_HIGH");
        }
        $clone = clone $this;
        $clone->priority = $priority;

        return $clone;
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
        try {
            return $this->checkClosure();
        } catch (\Throwable $e) {
            if (defined('DEVMODE') && ((bool) DEVMODE) === true) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * @return bool
     */
    private function checkClosure() : bool
    {
        $closure = $this->modification;
        if (!$closure instanceof Closure) {
            return false;
            //throw new InvalidModification($this, "Modification is not a Closure");
        }

        try {
            $r = new ReflectionFunction($closure);

            $requested_return_type = $this->getClosureReturnType();
            $requested_first_argument_type = $this->getClosureFirstArgumentType();

            // First Argument
            $param = $r->getParameters()[0] ?? null;
            // No first argument
            if ($param === null) {
                throw new InvalidModification($this, "Modification has no first parameter");
            }
            // First argument has no type
            if (!$param->hasType()) {
                throw new InvalidModification($this, "Modification's first parameter has no type");
            }
            // First argument has wrong type
            if ($param->getType()->getName() !== $requested_first_argument_type) {
                throw new InvalidModification($this, "Modification's first parameter does not match the requested type");
            }
            // First argument nullable
            if ($this->firstArgumentAllowsNull() && !$param->allowsNull()) {
                throw new InvalidModification($this, "Modification's first parameter must be nullable");
            }

            // Return type

            // Return type not available
            if (!$r->hasReturnType()) {
                throw new InvalidModification($this, "Modification has no return type");
            }
            // return type check
            if ($r->getReturnType()->getName() !== $requested_return_type) {
                throw new InvalidModification($this, "Modification's return type does not match the requested type");
            }

            // Return type nullable
            if ($this->returnTypeAllowsNull() && !$r->getReturnType()->allowsNull()) {
                throw new InvalidModification($this, "Modification's return type must be nullable");
            }
        } catch (ReflectionException $e) {
            throw new InvalidModification($this, "Modification threw an exception while checking the closure");
        }

        return true;
    }
}
