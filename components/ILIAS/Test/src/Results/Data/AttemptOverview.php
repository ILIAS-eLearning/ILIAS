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
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;

class AttemptOverview
{
    public function __construct(
        private readonly int $active_id,
        private readonly int $attempt_id,
        private readonly ResultPresentationSettings $settings,
        private readonly float $reached_points = 0.0,
        private readonly float $reachable_points = 0.0,
        private readonly string $mark = '',
        private readonly ?int $requested_hints_count = null,
        private readonly int $time_on_task = 0,
        private readonly ?\DateTimeImmutable $first_access = null,
        private readonly ?\DateTimeImmutable $last_access = null,
        private readonly int $nr_of_attempts = 0,
        private readonly ?int $scored_attempt = null,
        private readonly ?int $rank = 0
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

    public function getAsDescriptiveListing(
        Language $lng,
        UIFactory $ui_factory,
        array $environment
    ): DescriptiveListing {
        $items = [
            $lng->txt('tst_stat_result_resultspoints') => $this->reached_points
                . ' ' . strtolower($lng->txt('of')) . ' ' . $this->reachable_points
            . ' (' . sprintf('%2.2f', $this->getReachedPointsInPercent()) . ' %)',
            $lng->txt('tst_stat_result_resultsmarks') => $this->mark
        ];

        if ($this->settings->getShowHints()) {
            $items[$lng->txt('tst_question_hints_requested_hint_count_header')] = (string) $this->requested_hints_count;
        }

        return $ui_factory->listing()->descriptive(
            $items + [
                $lng->txt('tst_stat_result_timeontask') => $this->buildHumanReadableTime($this->time_on_task),
                $lng->txt('tst_stat_result_firstvisit') => $this->first_access
                    ->setTimezone($environment['timezone'])
                    ->format($environment['datetimeformat']),
                $lng->txt('tst_stat_result_lastvisit') => $this->last_access
                    ->setTimezone($environment['timezone'])
                    ->format($environment['datetimeformat']),
                $lng->txt('tst_nr_of_passes') => (string) $this->nr_of_attempts,
                $lng->txt('scored_pass') => (string) $this->scored_attempt,
                $lng->txt('tst_stat_result_rank_participant') => (string) $this->rank
            ]
        );
    }

    private function getReachedPointsInPercent(): float
    {
        if ($this->reachable_points === 0.0 || $this->reachable_points === 0.0) {
            return 0.0;
        }
        return $this->reached_points / $this->reachable_points * 100;
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
