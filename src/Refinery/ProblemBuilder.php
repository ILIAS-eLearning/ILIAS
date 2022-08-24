<?php

declare(strict_types=1);

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

namespace ILIAS\Refinery;

use Closure;
use InvalidArgumentException;

trait ProblemBuilder
{
    /**
     * @return string|callable
     */
    abstract protected function getError();

    /**
     * @inheritDoc
     */
    final public function withProblemBuilder(callable $builder): self
    {
        $clone = clone $this;
        $clone->error = $builder;
        return $clone;
    }

    /**
     * Get the problem message
     * @param mixed $value
     * @return string
     */
    final public function getErrorMessage($value): string
    {
        $error = $this->getError();
        if (!is_callable($error)) {
            return $error;
        }
        $lng_closure = $this->getLngClosure();
        return call_user_func($this->error, $lng_closure, $value);
    }

    /**
     * Get the closure to be passed to the error-function that does i18n and sprintf.
     * @return Closure
     */
    final protected function getLngClosure(): Closure
    {
        return function () {
            $args = func_get_args();
            if (count($args) < 1) {
                throw new InvalidArgumentException(
                    "Expected an id of a lang var as first parameter"
                );
            }

            $error = $this->lng->txt($args[0]);
            if (count($args) > 1) {
                $args[0] = $error;
                for ($i = 0, $numArgs = count($args); $i < $numArgs; $i++) {
                    $v = $args[$i];
                    if (is_array($v) || is_null($v) || (is_object($v) && !method_exists($v, "__toString"))) {
                        if (is_array($v)) {
                            $args[$i] = "array";
                        } elseif (is_null($v)) {
                            $args[$i] = "null";
                        } else {
                            $args[$i] = get_class($v);
                        }
                    }
                }
                $error = sprintf(...$args);
            }

            return $error;
        };
    }
}
