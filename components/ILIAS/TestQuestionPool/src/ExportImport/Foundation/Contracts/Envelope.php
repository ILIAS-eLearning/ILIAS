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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;

/**
 * An envelope carries intermediate data between pipes before the final normalization step.
 */
interface Envelope
{
    /**
     * Convert the envelope state into its normalized array representation.
     *
     * @param Transformations $tt Transformation helpers used for value casting and mapping.
     * @return array<string, mixed> Language-neutral normalized representation.
     */
    public function toArray(Transformations $tt): array;

    /**
     * Reconstruct an envelope instance from its normalized array representation.
     *
     * @param array<string, mixed> $value Normalized envelope payload.
     * @param Transformations $tt Transformation helpers used for value casting and mapping.
     * @return static Reconstructed envelope instance.
     */
    public static function fromArray(array $value, Transformations $tt): static;
}
