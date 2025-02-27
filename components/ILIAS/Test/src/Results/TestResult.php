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

use ILIAS\Test\Scoring\Marks\Mark;

/**
 * Class TestResult is a model representation of an entry in the test_result_cache table.
 */
class TestResult
{
    /**
     * Constructor ensures that the provided values are semantically correct (e.G. reached points are never negative).
     */
    public function __construct(
        protected int $active_id,
        protected int $pass,
        protected float $max_points,
        protected float $reached_points,
        protected string $mark_short,
        protected string $mark_official,
        protected bool $passed,
        protected bool $failed,
        protected int $timestamp,
        protected int $hint_count,
        protected float $hint_points,
        protected bool $passed_once,
    ) {
        $this->reached_points = max(0.0, $this->reached_points);
        $this->failed = !$this->passed;
    }

    public function withMark(Mark $mark): self
    {
        $clone = clone $this;
        $clone->mark_short = $mark->getShortName() ?? ' ';
        $clone->mark_official = $mark->getOfficialName() ?? ' ';
        return $clone;
    }

    public function withPassedOnce(bool $passed_once): self
    {
        $clone = clone $this;
        $clone->passed_once = $passed_once;
        return $clone;
    }

    public function getPercentage(): float
    {
        return $this->max_points > 0 ? $this->reached_points / $this->max_points * 100 : 0.0;
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

    public function getMarkShort(): string
    {
        return $this->mark_short;
    }

    public function getMarkOfficial(): string
    {
        return $this->mark_official;
    }

    public function isPassed(): bool
    {
        return $this->passed;
    }

    public function isFailed(): bool
    {
        return $this->failed;
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

    public function isPassedOnce(): bool
    {
        return $this->passed_once;
    }
}
