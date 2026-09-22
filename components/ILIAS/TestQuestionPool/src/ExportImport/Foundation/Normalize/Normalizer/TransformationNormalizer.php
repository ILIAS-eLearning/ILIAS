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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

/**
 * @implements Normalizer<Transformation, mixed>
 */
class TransformationNormalizer implements Normalizer
{
    public function __construct(private readonly Refinery $refinery) {
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if ($value instanceof Transformation) {
            return $value->transform([]);
        }

        throw new NormalizingException('Invalid transformation value', $value);
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): Transformation
    {
        return $this->refinery->custom()->transformation(static fn() => $value);
    }
}
