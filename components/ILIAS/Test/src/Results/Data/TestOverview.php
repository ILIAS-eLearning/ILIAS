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

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;

class TestOverview
{
    public function __construct(
        private readonly int $test_obj_id,
        private readonly int $total_number_of_participants = 0,
        private readonly int $total_number_of_participants_with_finished_attempts = 0,
        private readonly int $total_number_of_participants_with_passing_result = 0,
        private readonly int $average_time_on_task_with_finished_attempts = 0,
        private readonly int $average_time_on_task_passed = 0,
        private readonly int $rank_of_median = 0,
        private readonly string $mark_of_median = '',
        private readonly float $points_of_median = 0,
        private readonly float $average_points_passed = 0
    ) {
    }

    public function getTestObjId(): int
    {
        return $this->test_obj_id;
    }

    public function getAsDescriptiveListing(
        Language $lng,
        UIFactory $ui_factory
    ): DescriptiveListing {
        return $ui_factory->listing()->descriptive([
            $lng->txt('tst_eval_total_persons') => (string) $this->total_number_of_participants,
            $lng->txt('tst_stat_result_rank_median') => (string) $this->rank_of_median,
            $lng->txt('tst_stat_result_median') => sprintf('%2.2f', $this->points_of_median),
            $lng->txt('tst_stat_result_mark_median') => $this->mark_of_median,
            $lng->txt('tst_eval_total_finished') => (string) $this->total_number_of_participants_with_finished_attempts,
            $lng->txt('tst_eval_total_finished_average_time') => $this->buildHumanReadableTime(
                $this->average_time_on_task_with_finished_attempts
            ),
            $lng->txt('tst_eval_total_passed') => (string) $this->total_number_of_participants_with_passing_result,
            $lng->txt('tst_eval_total_passed_average_time') => $this->buildHumanReadableTime(
                $this->average_time_on_task_passed
            ),
            $lng->txt('tst_eval_total_passed_average_points') => sprintf('%2.2f', $this->average_points_passed)
        ]);
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
