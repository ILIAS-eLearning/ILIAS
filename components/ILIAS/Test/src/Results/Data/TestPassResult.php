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

namespace ILIAS\Test\Results\Data;

/**
 * Class TestPassResult is a model representation of an entry in the tst_pass_result table.
 */
class TestPassResult
{
    public function __construct(
        protected int $active_id,
        protected int $attempt,
        protected float $max_points,
        protected float $reached_points,
        protected int $question_count,
        protected int $answered_questions,
        protected int $working_time,
        protected int $timestamp,
        protected int $hint_count,
        protected float $hint_points,
        protected string $exam_id,
        protected string $finalized_by,
    ) {
        $this->reached_points = max(0.0, $this->reached_points);
    }

    public function withMaxPoints(float $max_points): self
    {
        $clone = clone $this;
        $clone->max_points = $max_points;
        return $clone;
    }

    public function withQuestionCount(int $question_count): self
    {
        $clone = clone $this;
        $clone->question_count = $question_count;
        return $clone;
    }

    public function withWorkingTime(int $working_time): self
    {
        $clone = clone $this;
        $clone->working_time = $working_time;
        return $clone;
    }

    public function withExamId(string $exam_id): self
    {
        $clone = clone $this;
        $clone->exam_id = $exam_id;
        return $clone;
    }

    public function withTimestamp(int $timestamp = -1): self
    {
        $clone = clone $this;
        $clone->timestamp = $timestamp > 0 ? $timestamp : time();
        return $clone;
    }

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function getAttempt(): int
    {
        return $this->attempt;
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

    public function getFinalizedBy(): string
    {
        return $this->finalized_by;
    }
}
