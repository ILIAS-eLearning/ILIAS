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

use ILIAS\Test\Scoring\Marks\Mark;

/**
 * Class ParticipantResult is a model representation of an entry in the test_result_cache table.
 */
class ParticipantResult
{
    public function __construct(
        private int $active_id,
        private int $attempt,
        private float $max_points,
        private float $reached_points,
        private Mark $mark,
        private int $timestamp,
        private bool $passed_once,
    ) {
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

    public function getMark(): Mark
    {
        return $this->mark;
    }

    public function getMarkOfficial(): string
    {
        return $this->mark->getOfficialName();
    }

    public function getMarkShort(): string
    {
        return $this->mark->getShortName();
    }

    public function isPassed(): bool
    {
        return $this->mark->getPassed();
    }

    public function isFailed(): bool
    {
        return !$this->mark->getPassed();
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function isPassedOnce(): bool
    {
        return $this->passed_once;
    }
}
