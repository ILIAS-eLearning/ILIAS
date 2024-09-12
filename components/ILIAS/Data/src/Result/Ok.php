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

namespace ILIAS\Data\Result;

use ILIAS\Data\Result;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 * @template-covariant A
 * @implements Result<A>
 */
class Ok implements Result
{
    /**
     * @var A
     */
    protected $value;

    /**
     * @param A $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function isOK(): bool
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

    /**
     * @inheritdoc
     */
    public function isError(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function map(callable $f): Result
    {
        $clone = clone $this;
        $value = $f($this->value);
        $clone->value = $value;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function then(callable $f): Result
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

    /**
     * @inheritdoc
     */
    public function except(callable $f): Result
    {
        return $this;
    }
}
