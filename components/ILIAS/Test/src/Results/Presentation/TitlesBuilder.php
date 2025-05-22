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

namespace ILIAS\Test\Results\Presentation;

use ILIAS\Language\Language;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 */
class TitlesBuilder
{
    private const LO_TEST_TYPE_INITIAL = 'loTestInitial';
    private const LO_TEST_TYPE_QUALIFYING = 'loTestQualifying';

    private ?int $objective_oriented_container_id = null;
    private ?int $test_obj_id = null;
    private ?int $test_ref_id = null;
    private ?int $user_id = null;
    private ?string $attempt_last_access_date = null;
    private ?string $crs_title = null;
    private ?string $test_type = null;
    private array $objectives = [];

    public function __construct(
        protected Language $lng,
        protected \ilObjectDataCache $obj_cache
    ) {
    }

    public function initObjectiveOrientedMode(): void
    {
        $this->initTestType();
        $this->initObjectives();
        $this->initCourseTitle();
    }

    public function setObjectiveOrientedContainerId(int $container_id): void
    {
        $this->objective_oriented_container_id = $container_id;
    }

    public function setTestObjId(int $testObjId): void
    {
        $this->test_obj_id = $testObjId;
    }

    public function setTestRefId(int $testRefId): void
    {
        $this->test_ref_id = $testRefId;
    }

    public function setUserId(int $userId): void
    {
        $this->user_id = $userId;
    }

    public function setAttemptLastAccessDate(string $formatted_date): void
    {
        $this->attempt_last_access_date = $formatted_date;
    }

    public function getPassOverviewHeaderLabel(): string
    {
        if (!$this->objective_oriented_container_id) {
            return $this->lng->txt('tst_results_overview');
        }

        if ($this->isInitialTestForAllObjectives()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_initial_all_objectives'),
                $this->crs_title
            );
        }

        if ($this->isInitialTestPerObjective()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_initial_per_objective'),
                $this->getObjectivesString(),
                $this->crs_title
            );
        }

        if ($this->isQualifyingTestForAllObjectives()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_qualifying_all_objectives'),
                $this->crs_title
            );
        }

        if ($this->isQualifyingTestPerObjective()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_qualifying_per_objective'),
                $this->getObjectivesString(),
                $this->crs_title
            );
        }

        return '';
    }

    public function getPassDetailsHeaderLabel(int $attempt): string
    {
        if (!$this->objective_oriented_container_id) {
            return sprintf(
                $this->lng->txt('tst_pass_details_overview_table_title'),
                $attempt
            );
        }

        if ($this->isInitialTest()) {
            return sprintf(
                $this->lng->txt('tst_pass_details_header_lo_initial'),
                $this->getObjectivesString(),
                $this->getAttemptLabel($attempt)
            );
        }

        if ($this->isQualifyingTest()) {
            return sprintf(
                $this->lng->txt('tst_pass_details_header_lo_qualifying'),
                $this->getObjectivesString(),
                $this->getAttemptLabel($attempt)
            );
        }

        return '';
    }

    public function getListOfAnswersHeaderLabel(int $attempt): string
    {
        $langVar = 'tst_eval_results_by_pass';

        if ($this->objective_oriented_container_id) {
            $langVar = 'tst_eval_results_by_pass_lo';
        }

        $title = sprintf($this->lng->txt($langVar), $attempt);

        if ($this->attempt_last_access_date === null) {
            return $title;
        }

        return "{$title} - {$this->attempt_last_access_date}";
    }

    public function getVirtualListOfAnswersHeaderLabel(): string
    {
        return $this->lng->txt('tst_eval_results_lo');
    }

    public function getVirtualPassDetailsHeaderLabel($objectiveTitle): string
    {
        if ($this->isInitialTest()) {
            return sprintf(
                $this->lng->txt('tst_virtual_pass_header_lo_initial'),
                $objectiveTitle
            );
        } elseif ($this->isQualifyingTest()) {
            return sprintf(
                $this->lng->txt('tst_virtual_pass_header_lo_qualifying'),
                $objectiveTitle
            );
        }

        return '';
    }

    private function initTestType()
    {
        $loSettings = \ilLOSettings::getInstanceByObjId($this->objective_oriented_container_id);

        if ($loSettings->getInitialTest() == $this->test_ref_id) {
            $this->test_type = self::LO_TEST_TYPE_INITIAL;
        } elseif ($loSettings->getQualifiedTest() == $this->test_ref_id) {
            $this->test_type = self::LO_TEST_TYPE_QUALIFYING;
        }
    }

    private function initObjectives()
    {
        $lo_attempts = \ilLOTestRun::getRun($this->objective_oriented_container_id, $this->user_id, $this->test_obj_id);

        $this->objectives = [];

        foreach ($lo_attempts as $lo_attempt) {
            $this->objectives[$lo_attempt->getObjectiveId()] = $this->getObjectiveTitle($lo_attempt);
        }
    }

    private function initCourseTitle()
    {
        $this->crs_title = $this->obj_cache->lookupTitle($this->objective_oriented_container_id);
    }

    private function isInitialTest(): bool
    {
        return $this->test_type === self::LO_TEST_TYPE_INITIAL;
    }

    private function isQualifyingTest(): bool
    {
        return $this->test_type === self::LO_TEST_TYPE_QUALIFYING;
    }

    private function isInitialTestForAllObjectives(): bool
    {
        if ($this->test_type !== self::LO_TEST_TYPE_INITIAL) {
            return false;
        }

        if (count($this->objectives) <= 1) {
            return false;
        }

        return true;
    }

    private function isInitialTestPerObjective(): bool
    {
        if ($this->test_type !== self::LO_TEST_TYPE_INITIAL) {
            return false;
        }

        if (count($this->objectives) > 1) {
            return false;
        }

        return true;
    }

    private function isQualifyingTestForAllObjectives(): bool
    {
        if ($this->test_type !== self::LO_TEST_TYPE_QUALIFYING) {
            return false;
        }

        if (count($this->objectives) <= 1) {
            return false;
        }

        return true;
    }

    private function isQualifyingTestPerObjective(): bool
    {
        if ($this->test_type !== self::LO_TEST_TYPE_QUALIFYING) {
            return false;
        }

        if (count($this->objectives) > 1) {
            return false;
        }

        return true;
    }

    private function getObjectiveTitle(\ilLOTestRun $loRun)
    {
        return \ilCourseObjective::lookupObjectiveTitle($loRun->getObjectiveId());
    }

    private function getObjectivesString(): string
    {
        return implode(', ', $this->objectives);
    }

    private function getAttemptLabel(int $attempt): string
    {
        return sprintf($this->lng->txt('tst_res_lo_try_n'), $attempt);
    }
}
