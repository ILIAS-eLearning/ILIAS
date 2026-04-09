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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Pipe;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Pipes\NormalizeCarry;

/**
 * Finalize the normalization of the value by ensuring that the value is normalized. It will check on the top level
 * structure by recursively iterating over the normalized value. If the value is neither a scalar nor an array, an
 * exception will be thrown.
 */
class FinalizeNormalizing implements Pipe
{
    public function handle(mixed $passable, \Closure $next): mixed
    {
        if (!$passable instanceof NormalizeCarry) {
            return $next($passable);
        }

        $this->ensureNormalized($passable->result());
        return $next($passable);
    }

    private function ensureNormalized(mixed $value): mixed
    {
        if (is_scalar($value) || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return array_map($this->ensureNormalized(...), $value);
        }

        throw new NormalizingException('Value is not normalized: ' . get_debug_type($value));
    }
}
