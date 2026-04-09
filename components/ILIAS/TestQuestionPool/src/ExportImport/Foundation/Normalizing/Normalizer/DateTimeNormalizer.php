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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Normalizer;

use DateTime;
use DateTimeImmutable;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * @implements Normalizer<DateTimeImmutable|DateTime, string>
 */
#[Normalizes(DateTime::class, DateTimeImmutable::class)]
class DateTimeNormalizer implements Normalizer
{
    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if ($value instanceof DateTimeImmutable || $value instanceof DateTime) {
            return $value->format(DATE_ATOM);
        }

        throw new NormalizingException('Invalid datetime value', $value);
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): DateTime|DateTimeImmutable
    {
        return match($type) {
            DateTimeImmutable::class => DateTimeImmutable::createFromFormat(DATE_ATOM, $value),
            DateTime::class => DateTime::createFromFormat(DATE_ATOM, $value),
            default => throw new NormalizingException("Invalid type for datetime: {$type}")
        };
    }
}
