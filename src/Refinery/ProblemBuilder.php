<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

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
     * @inheritdoc
     */
    final public function withProblemBuilder(callable $builder)
    {
        $clone = clone $this;
        $clone->error = $builder;
        return $clone;
    }

    /**
     * Get the problem message
     */
    final public function getErrorMessage($value) : string
    {
        $error = $this->getError();
        if (!is_callable($error)) {
            return $error;
        }
        $lng_closure = $this->getLngClosure();
        return call_user_func($this->error, $lng_closure, $value);
    }

    /**
     * Get the closure to be passed to the error-function that does i18n and
     * sprintf.
     * @return  Closure
     */
    final protected function getLngClosure() : Closure
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
