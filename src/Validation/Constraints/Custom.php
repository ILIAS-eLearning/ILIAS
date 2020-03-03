<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class Custom implements Constraint
{
    /**
     * @var ILIAS\Data\Factory
     */
    protected $data_factory;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var callable
     */
    protected $is_ok;

    /**
     * @var callable
     */
    protected $error;

    /**
     * If $error is a callable it needs to take two parameters:
     *      - one callback $txt($lng_id, ($value, ...)) that retrieves the lang var
     *        with the given id and uses sprintf to replace placeholder if more
     *        values are provide.
     *      - the $value for which the error message should be build.
     *
     * @param string|callable	$error
     */
    public function __construct(callable $is_ok, $error, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->is_ok = $is_ok;

        if (!is_callable($error)) {
            $this->error = function () use ($error) {
                return $error;
            };
        } else {
            $this->error = $error;
        }

        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }

    /**
     * @inheritdoc
     */
    final public function check($value)
    {
        if (!$this->accepts($value)) {
            throw new \UnexpectedValueException($this->getErrorMessage($value));
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    final public function accepts($value)
    {
        return call_user_func($this->is_ok, $value);
    }

    /**
     * @inheritdoc
     */
    final public function problemWith($value)
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    final public function restrict(Result $result)
    {
        if ($result->isError()) {
            return $result;
        }

        $problem = $this->problemWith($result->value());
        if ($problem !== null) {
            $error = $this->data_factory->error($problem);
            return $error;
        }

        return $result;
    }

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
     *
     * @return string
     */
    final public function getErrorMessage($value)
    {
        $lng_closure = $this->getLngClosure();
        return call_user_func($this->error, $lng_closure, $value);
    }

    /**
     * Get the closure to be passed to the error-function that does i18n and
     * sprintf.
     *
     * @return	\Closure
     */
    final protected function getLngClosure()
    {
        return function () {
            $args = func_get_args();
            if (count($args) < 1) {
                throw new \InvalidArgumentException(
                    "Expected an id of a lang var as first parameter"
                );
            }
            $error = $this->lng->txt($args[0]);
            if (count($args) > 1) {
                $args[0] = $error;
                for ($i = 0; $i < count($args); $i++) {
                    $v = $args[$i];
                    if ((is_array($v) || is_object($v) || is_null($v))
                    && !method_exists($v, "__toString")) {
                        if (is_array($v)) {
                            $args[$i] = "array";
                        } elseif (is_null($v)) {
                            $args[$i] = "null";
                        } else {
                            $args[$i] = get_class($v);
                        }
                    }
                }
                $error = call_user_func_array("sprintf", $args);
            }
            return $error;
        };
    }
}
