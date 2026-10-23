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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;

/**
 * @deprecated fromNormalized should be implement in a factory. This is only a temporary solution to support current
 * active record implementations.
 */
interface FromNormalized
{
    /** 
     * Restore the object from its normalized representation.
     * 
     * @param array<array-key, mixed> $normalized
     */
    public function fromNormalized(
        array $normalized,
        Transformations $transformations
    ): object;
}
