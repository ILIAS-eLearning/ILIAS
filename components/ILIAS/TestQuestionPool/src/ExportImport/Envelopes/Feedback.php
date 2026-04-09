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

namespace ILIAS\TestQuestionPool\ExportImport\Envelopes;

use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Envelope;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;

/**
 * Transfer object for feedback content of a question.
 */
class Feedback implements Envelope
{
    public function __construct(
        private Id $question_id,
        private string $generic_uncompleted,
        private string $generic_completed,
        /** @var list<array{answer_index: int, question_index: int, feedback: string}> */
        private array $specific_feedback = [],
    ) {
    }

    public function getGenericUncompleted(): string
    {
        return $this->generic_uncompleted;
    }

    public function getGenericCompleted(): string
    {
        return $this->generic_completed;
    }

    /**
     * @return list<array{answer_index: int, question_index: int, feedback: string}>
     */
    public function getSpecificFeedback(): array
    {
        return $this->specific_feedback;
    }

    /**
     * @inheritDoc
     */
    public function toArray(Transformations $tt): array
    {
        return [
            'question_id' => $tt->normalize($this->question_id),
            'generic_uncompleted' => $this->generic_uncompleted,
            'generic_completed' => $this->generic_completed,
            'specific' => $this->specific_feedback,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromArray(array $value, Transformations $tt): static
    {
        return new self(
            $tt->denormalize($value['question_id'], Id::class),
            $value['generic_uncompleted'],
            $value['generic_completed'],
            $value['specific'],
        );
    }
}
