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

class AttemptResult
{
    public function __construct(
        private int $active_id,
        private int $attempt_id,
        private float $max_points,
        private float $reached_points,
        private int $question_count,
        private int $answered_questions,
        private int $working_time,
        private int $timestamp,
        private string $exam_id,
        private string $finalized_by,
    ) {
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
        return $this->attempt_id;
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

    public function getExamId(): string
    {
        return $this->exam_id;
    }

    public function getFinalizedBy(): string
    {
        return $this->finalized_by;
    }
}
