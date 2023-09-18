<?php

declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Refinery\ConstraintViolationException;

class Alphanumeric
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     * @throws ConstraintViolationException
     */
    public function __construct($value)
    {
        $matches = null;
        if (!preg_match('/^[a-zA-Z0-9]+$/', (string) $value, $matches)) {
            throw new ConstraintViolationException(
                sprintf('The value "%s" is not an alphanumeric value.', $value),
                'exception_not_alphanumeric',
                array($value)
            );
        }

        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function asString(): string
    {
        return (string) $this->value;
    }
}
