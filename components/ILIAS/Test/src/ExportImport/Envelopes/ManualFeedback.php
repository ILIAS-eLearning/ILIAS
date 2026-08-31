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

class ManualFeedback implements Envelope
{
    public function __construct(
        public readonly Id $active_id,
        public readonly Id $question_id,
        public readonly int $attempt,
        public readonly string $feedback,
        public readonly bool $finalized_evaluation,
        public readonly int $finalized_timestamp,
        public readonly Id $finalized_by
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
            'feedback' => $this->feedback,
            'finalized_evaluation' => $this->finalized_evaluation,
            'finalized_timestamp' => $this->finalized_timestamp,
            'finalized_by' => $tt->normalize($this->finalized_by),
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
            $tt->string($value['feedback']),
            $tt->bool($value['finalized_evaluation']),
            $tt->int($value['finalized_timestamp']),
            $tt->denormalize($value['finalized_by'], Id::class),
        );
    }

    public static function fromRow(array $row): static
    {
        return new self(
            new Id($row['active_fi'], 'participant'),
            new Id($row['question_fi'], 'question'),
            (int) $row['pass'],
            $row['feedback'],
            (bool) $row['finalized_evaluation'],
            (int) $row['finalized_tstamp'],
            new Id($row['finalized_by_usr_id'], 'user'),
        );
    }
}
