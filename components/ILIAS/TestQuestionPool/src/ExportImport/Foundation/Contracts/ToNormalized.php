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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Transformations;

interface ToNormalized
{
    /**
     * Convert the internal state of the implementing object into a language-neutral array structure. The resulting
     * array must contain only null, scalar values (string, int, float, bool) and nested arrays following the same
     * rules. 
     * 
     * @param array<string, mixed> $context
     * @return array<array-key, mixed>|float|bool|int|string|null
     */
    public function toNormalized(
        Transformations $transformations,
        array $context = []
    ): array|float|bool|int|string|null;
}
