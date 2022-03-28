<?php declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\Result;

use ILIAS\Data\Result;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Ok implements Result
{

    /**
     * @var mixed
     */
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function isOK() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function value()
    {
        return $this->value;
    }

    public function isError() : bool
    {
        return false;
    }

    public function error()
    {
        throw new \LogicException("This is a OK result. No error message available");
    }

    /**
     * @inheritdoc
     */
    public function valueOr($default)
    {
        return $this->value;
    }

    public function map(callable $f) : Result
    {
        $clone = clone $this;
        $value = $f($this->value);
        $clone->value = $value;
        return $clone;
    }

    public function then(callable $f) : Result
    {
        $result = $f($this->value);

        if ($result === null) {
            return $this;
        }

        if (!$result instanceof Result) {
            throw new \UnexpectedValueException("The returned type of callable is not an instance of interface Result");
        }

        return $result;
    }

    public function except(callable $f) : Result
    {
        return $this;
    }
}
