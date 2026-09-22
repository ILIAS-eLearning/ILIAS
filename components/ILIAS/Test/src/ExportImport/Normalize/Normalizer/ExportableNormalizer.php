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

namespace ILIAS\Test\ExportImport\Normalize\Normalizer;

use ILIAS\Test\ExportImport\Exportable;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;

/**
 * @implements Normalizer<Exportable, array>
 */
class ExportableNormalizer implements Normalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof Exportable) {
            throw new NormalizingException('Invalid exportable value', $value);
        }

        return $value->toExport();
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): Exportable
    {
        if (!in_array(Exportable::class, class_implements($type))) {
            throw new NormalizingException('Invalid exportable type', $type);
        }

        return $type::fromExport($value);
    }
}
