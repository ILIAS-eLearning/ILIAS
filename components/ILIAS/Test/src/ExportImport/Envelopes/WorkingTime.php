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

namespace ILIAS\Test\ExportImport\Envelopes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;

class WorkingTime implements Envelope
{
    public function __construct(
        public readonly Id $active_id,
        public readonly int $attempt,
        public readonly string $started,
        public readonly string $finished,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(Transformations $tt): array
    {
        return [
            'active_id' => $tt->normalize($this->active_id),
            'attempt' => $this->attempt,
            'started' => $this->started,
            'finished' => $this->finished,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value, Transformations $tt): static
    {
        return new self(
            $tt->denormalize($value['active_id'], Id::class)->getId(),
            $tt->int($value['attempt']),
            $tt->string($value['started']),
            $tt->string($value['finished']),
        );
    }

    public static function fromRow(array $row): static
    {
        return new self(
            new Id($row['active_fi'], 'participant'),
            (int) $row['pass'],
            $row['started'],
            $row['finished'],
        );
    }
}
