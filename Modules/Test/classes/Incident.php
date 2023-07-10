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

namespace ILIAS\Modules\Test;

class Incident
{
    /**
     * @template A
     * @param callable(A): bool $are_you_ok
     * @param A[] $array
     */
    public function any(callable $are_you_ok, array $array): bool
    {
        foreach ($array as $x) {
            if ($are_you_ok($x)) {
                return true;
            }
        }

        return false;
    }
}
