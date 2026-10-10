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

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\NormalizingException;

/**
 * @implements Normalizer<Envelope, array>
 */
class EnvelopeNormalizer implements Normalizer
{
    public function __construct(
        private readonly Transformations $tt,
    ) {
    }

    #[\Override]
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!($value instanceof Envelope)) {
            throw new NormalizingException('Invalid envelope value', $value);
        }

        return $value->unpack($this->tt);
    }

    #[\Override]
    public function denormalize(array|float|bool|int|string|null $value, string $type): Envelope
    {
        if (!in_array(Envelope::class, class_implements($type))) {
            throw new NormalizingException('Invalid envelope type', $type);
        }

        return $type::pack($value, $this->tt);
    }
}
