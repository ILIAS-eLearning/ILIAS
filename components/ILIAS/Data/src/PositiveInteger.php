<?php

declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Refinery\ConstraintViolationException;

class PositiveInteger
{
    private int $value;

    /**
     * @throws ConstraintViolationException
     */
    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new ConstraintViolationException(
                sprintf('The value "%s" is not a positive integer', $value),
                'exception_not_positive_integer',
                array($value)
            );
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
