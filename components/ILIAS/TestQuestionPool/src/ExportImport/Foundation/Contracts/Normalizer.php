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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * A Normalizer is responsible for converting a value into a normalized form and back. The normalized form is an array
 * of null, scalar values or other normalized arrays.
 *
 * @phpstan-type NormalizedArray array<array-key, null|scalar|NormalizedArray>
 *
 * @template TValue
 * @template TNormalized of null|scalar|NormalizedArray
 */
interface Normalizer
{
    /**
     * Converts a value into a normalized form.
     *
     * @param TValue $value
     * @return TNormalized
     *
     * @throws NormalizingException if the normalizer does not support the given type
     */
    public function normalize($value): array|float|bool|int|string|null;

    /**
     * Converts a normalized form back into a value. It uses the type hint to determine the expected type, which will be
     * returned.
     *
     * @param TNormalized $normalized
     * @param class-string<TValue> $type
     * @return TValue
     *
     * @throws NormalizingException if the normalizer does not support the given type
     */
    public function denormalize(array|float|bool|int|string|null $normalized, string $type);
}
