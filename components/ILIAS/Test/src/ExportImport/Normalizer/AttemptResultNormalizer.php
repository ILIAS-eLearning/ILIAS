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

namespace ILIAS\Test\ExportImport\Normalizer;

use ILIAS\Test\Results\Data\AttemptResult;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Normalizer;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts\Transformations;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Attributes\Normalizes;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\Envelopes\Id;
use ILIAS\TestQuestionPool\ExportImport\Foundation\Normalizing\NormalizingException;

/**
 * @implements Normalizer<AttemptResult, array>
 */
#[Normalizes(AttemptResult::class)]
class AttemptResultNormalizer implements Normalizer
{
    public function __construct(
        private readonly Transformations $tt,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function normalize($value): array|float|bool|int|string|null
    {
        if (!$value instanceof AttemptResult) {
            throw new NormalizingException('Invalid value', $value);
        }

        return [
            'active_id' => $this->tt->normalize(new Id($value->getActiveId(), 'participant')),
            'attempt' => $value->getAttempt(),
            'max_points' => $value->getMaxPoints(),
            'reached_points' => $value->getReachedPoints(),
            'question_count' => $value->getQuestionCount(),
            'answered_questions' => $value->getAnsweredQuestions(),
            'working_time' => $value->getWorkingTime(),
            'timestamp' => $value->getTimestamp(),
            'exam_id' => $value->getExamId(),
            'finalized_by' => $value->getFinalizedBy(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function denormalize(array|float|bool|int|string|null $value, string $type): AttemptResult
    {
        if ($type !== AttemptResult::class) {
            throw new NormalizingException("Invalid type for AttemptResult: {$type}");
        }

        return new AttemptResult(
            $this->tt->denormalize($value['active_id'], Id::class)->getId(),
            $this->tt->int($value['attempt']),
            $this->tt->float($value['max_points']),
            $this->tt->float($value['reached_points']),
            $this->tt->int($value['question_count']),
            $this->tt->int($value['answered_questions']),
            $this->tt->int($value['working_time']),
            $this->tt->int($value['timestamp']),
            $this->tt->string($value['exam_id']),
            $this->tt->string($value['finalized_by']),
        );
    }
}
