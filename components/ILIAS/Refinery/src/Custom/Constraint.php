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

namespace ILIAS\Refinery\Custom;

use ILIAS\Refinery\Constraint as ConstraintInterface;
use ILIAS\Data;
use ILIAS\Data\Result;
use InvalidArgumentException;

class Constraint implements ConstraintInterface
{
    private readonly Closure $is_ok;

    /**
     * If $error is a callable it needs to take two parameters:
     *      - one callback $txt($lng_id, ($value, ...)) that retrieves the lang var
     *        with the given id and uses sprintf to replace placeholder if more
     *        values are provide.
     *      - the $value for which the error message should be build.
     *
     * @param callable $is_ok
     */
    public function __construct(callable $is_ok)
    {
        $this->is_ok = Closure::fromCallable($is_ok);
    }

    final public function problemWith($value): ?string
    {
        if (!($this->is_ok)($value)) {
            return new InvalidArgumentException('Not ok');
        }

        return null;
    }
}
