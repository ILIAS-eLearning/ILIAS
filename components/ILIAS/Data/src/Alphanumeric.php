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
