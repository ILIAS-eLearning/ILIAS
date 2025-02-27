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

use ILIAS\Test\Results\Presentation\Settings as ResultPresentationSettings;
use ILIAS\Language\Language;
use ILIAS\Test\Results\Data\StatusOfAttempt;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\Test\Scoring\Marks\Mark;

class AttemptOverview
{
    public function __construct(
        private readonly int $active_id,
        private readonly int $attempt_id,
        private readonly ResultPresentationSettings $settings,
        private readonly string $exam_id = '',
        private readonly float $reached_points = 0.0,
        private readonly float $available_points = 0.0,
        private readonly ?Mark $mark = null,
        private readonly int $nr_of_answered_questions = 0,
        private readonly int $nr_of_questions_in_attempt = 0,
        private readonly ?int $requested_hints_count = null,
        private readonly int $time_on_task = 0,
        private readonly ?\DateTimeImmutable $attempt_started_date = null,
        private readonly ?\DateTimeImmutable $last_access = null,
        private readonly int $nr_of_attempts = 0,
        private readonly ?int $scored_attempt = null,
        private readonly ?int $rank = 0,
        private readonly StatusOfAttempt $status_of_attempt = StatusOfAttempt::NOT_YET_STARTED
    ) {
    }

    public function getActiveId(): int
    {
        return $this->active_id;
    }

    public function getAttempt(): int
    {
        return $this->attempt_id;
    }

    public function getExamId(): string
    {
        return $this->exam_id;
    }

    public function getStartedDate(): ?\DateTimeImmutable
    {
        return $this->attempt_started_date;
    }

    public function getNrOfAnsweredQuestions(): int
    {
        return $this->nr_of_answered_questions;
    }

    public function getNrOfTotalQuestions(): int
    {
        return $this->nr_of_questions_in_attempt;
    }

    public function hasAnsweredQuestions(): bool
    {
        return $this->nr_of_answered_questions > 0;
    }

    public function getMark(): ?string
    {
        return $this->mark?->getShortName();
    }

    public function hasPassingMark(): bool
    {
        return $this->mark?->getPassed() ?? false;
    }

    public function getReachedPoints(): float
    {
        return $this->reached_points;
    }

    public function getAvailablePoints(): float
    {
        return $this->available_points;
    }

    public function getReachedPointsInPercent(): float
    {
        if ($this->reached_points === 0.0 || $this->available_points === 0.0) {
            return 0.0;
        }
        return $this->reached_points / $this->available_points * 100;
    }

    public function getStatusOfAttempt(): StatusOfAttempt
    {
        return $this->status_of_attempt;
    }

    public function getNrOfAttempts(): int
    {
        return $this->nr_of_attempts;
    }

    public function getAsDescriptiveListing(
        Language $lng,
        UIFactory $ui_factory,
        array $environment
    ): DescriptiveListing {
        $items = [
            $lng->txt('tst_stat_result_resultspoints') => $this->reached_points
                . ' ' . strtolower($lng->txt('of')) . ' ' . $this->available_points
            . ' (' . sprintf('%2.2f', $this->getReachedPointsInPercent()) . ' %)',
            $lng->txt('tst_stat_result_resultsmarks') => $this->mark?->getShortName() ?? ''
        ];

        if ($this->settings->getShowHints()) {
            $items[$lng->txt('tst_question_hints_requested_hint_count_header')] = (string) $this->requested_hints_count;
        }

        return $ui_factory->listing()->descriptive(
            $items + [
                $lng->txt('tst_stat_result_timeontask') => $this->buildHumanReadableTime($this->time_on_task),
                $lng->txt('tst_stat_result_firstvisit') => $this->attempt_started_date
                    ?->setTimezone($environment['timezone'])
                    ->format($environment['datetimeformat']) ?? '',
                $lng->txt('tst_stat_result_lastvisit') => $this->last_access
                    ?->setTimezone($environment['timezone'])
                    ->format($environment['datetimeformat']) ?? '',
                $lng->txt('tst_nr_of_passes') => (string) $this->nr_of_attempts,
                $lng->txt('scored_pass') => (string) ($this->scored_attempt + 1),
                $lng->txt('tst_stat_result_rank_participant') => (string) $this->rank
            ]
        );
    }

    private function buildHumanReadableTime(int $time): string
    {
        $diff_seconds = $time;
        $diff_hours = floor($diff_seconds / 3600);
        $diff_seconds -= $diff_hours * 3600;
        $diff_minutes = floor($diff_seconds / 60);
        $diff_seconds -= $diff_minutes * 60;
        return sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds);
    }
}
