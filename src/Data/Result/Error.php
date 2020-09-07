<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\Result;

use ILIAS\Data;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Error implements Data\Result
{

    /**
     * @var string | \Exception
     */
    protected $error;

    public function __construct($error)
    {
        if (!is_string($error) && !($error instanceof \Exception)) {
            throw new \InvalidArgumentException("Expected error to be a string or an Exception.");
        }
        $this->error = $error;
    }
    /**
     * @inheritdoc
     */
    public function isOK()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function value()
    {
        if ($this->error instanceof \Exception) {
            throw $this->error;
        }

        throw new Data\NotOKException($this->error);
    }

    /**
     * @inheritdoc
     */
    public function isError()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function valueOr($default)
    {
        return $default;
    }

    /**
     * @inheritdoc
     */
    public function map(callable $f)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function then(callable $f)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function except(callable $f)
    {
        $result = $f($this->error);

        if ($result === null) {
            return $this;
        }

        if (!$result instanceof Data\Result) {
            throw new \UnexpectedValueException("The returned type of callable is not an instance of interface Result");
        }

        return $result;
    }
}
