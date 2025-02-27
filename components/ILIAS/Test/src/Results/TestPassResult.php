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

namespace ILIAS\Test\Results;

/**
 * Class TestPassResult is a model representation of an entry in the tst_pass_result table.
 */
class TestPassResult
{
    /**
     * Constructor ensures that the provided values are semantically correct (e.G. reached points are never negative).
     */
    public function __construct(
        protected int $active_id,
        protected int $pass,
        protected float $max_points,
        protected float $reached_points,
        protected int $question_count,
        protected int $answered_questions,
        protected int $working_time,
        protected int $timestamp,
        protected int $hint_count,
        protected float $hint_points,
        protected string $exam_id,
        protected bool $finalized_by,
    ) {
        $this->reached_points = max(0.0, $this->reached_points);
    }

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function getPass(): int
    {
        return $this->pass;
    }

    public function getMaxPoints(): float
    {
        return $this->max_points;
    }

    public function getReachedPoints(): float
    {
        return $this->reached_points;
    }

    public function getQuestionCount(): int
    {
        return $this->question_count;
    }

    public function getAnsweredQuestions(): int
    {
        return $this->answered_questions;
    }

    public function getWorkingTime(): int
    {
        return $this->working_time;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getHintCount(): int
    {
        return $this->hint_count;
    }

    public function getHintPoints(): float
    {
        return $this->hint_points;
    }

    public function getExamId(): string
    {
        return $this->exam_id;
    }

    public function isFinalizedBy(): bool
    {
        return $this->finalized_by;
    }
}
