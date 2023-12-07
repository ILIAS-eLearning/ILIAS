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

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Constraint;
use Exception;

class Parallel implements Constraint
{
    /**
     * @param Constraint[] $constraints
     */
    public function __construct(private readonly array $constraints)
    {
    }

    public function problemWith($value)
    {
        $problems = array_filter(array_map(fn($t) => $t->problemWith($value), $this->constraints));

        if ([] === $problems) {
            return null;
        }

        return new ExceptionCollection(array_map($this->ensureException(...), $problems));
    }

    /**
     * @param string|Exception
     */
    private function ensureException($exception): Exception
    {
        return is_string($exception) ? new Exception($exception) : $exception;
    }
}
