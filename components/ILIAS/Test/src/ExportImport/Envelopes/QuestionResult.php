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

class QuestionResult implements Envelope
{
    public function __construct(
        public readonly Id $active_id,
        public readonly Id $question_id,
        public readonly int $attempt,
        public readonly float $points,
        public readonly bool $answered,
        public readonly bool $manual,
        public readonly ?int $step,
        public readonly int $timestamp
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toArray(Transformations $tt): array
    {
        return [
            'active_id' => $tt->normalize($this->active_id),
            'question_id' => $tt->normalize($this->question_id),
            'attempt' => $this->attempt,
            'points' => $this->points,
            'answered' => $this->answered,
            'manual' => $this->manual,
            'step' => $this->step,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value, Transformations $tt): static
    {
        return new self(
            $tt->denormalize($value['active_id'], Id::class),
            $tt->denormalize($value['question_id'], Id::class),
            $tt->int($value['attempt']),
            $tt->float($value['points']),
            $tt->bool($value['answered']),
            $tt->bool($value['manual']),
            $tt->nullableInt($value['step']),
            $tt->int($value['timestamp']),
        );
    }

    public static function fromRow(array $row): static
    {
        return new self(
            new Id($row['active_fi'], 'participant'),
            new Id($row['question_fi'], 'question'),
            (int) $row['pass'],
            (float) $row['points'],
            (bool) $row['answered'],
            (bool) $row['manual'],
            $row['step'] !== null ? (int) $row['step'] : null,
            (int) $row['tstamp'],
        );
    }
}
