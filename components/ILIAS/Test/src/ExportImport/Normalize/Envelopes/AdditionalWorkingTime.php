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

namespace ILIAS\Test\ExportImport\Normalize\Envelopes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalize\Envelopes\Id;

class AdditionalWorkingTime implements Envelope
{
    public function __construct(
        public readonly Id $user_id,
        public readonly Id $test_id,
        public readonly int $time,
        public readonly int $timestamp,
    ) {
    }

    #[\Override]
    public function unpack(Transformations $tt): array
    {
        return [
            'user_id' => $tt->normalize($this->user_id),
            'test_id' => $tt->normalize($this->test_id),
            'time' => $this->time,
            'timestamp' => $this->timestamp,
        ];
    }

    #[\Override]
    public static function pack(array $value, Transformations $tt): static
    {
        return new self(
            $tt->denormalize($value['user_id'], Id::class),
            $tt->denormalize($value['test_id'], Id::class),
            $tt->int($value['time']),
            $tt->int($value['timestamp']),
        );
    }

    public static function fromRow(array $row): static
    {
        return new self(
            new Id($row['user_fi'], 'user'),
            new Id($row['test_fi'], 'tst'),
            (int) $row['additionaltime'],
            (int) $row['tstamp'],
        );
    }
}
