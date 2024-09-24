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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 */
class ilTestResultHeaderLabelBuilder
{
    public const LO_TEST_TYPE_INITIAL = 'loTestInitial';
    public const LO_TEST_TYPE_QUALIFYING = 'loTestQualifying';

    protected ?int $objectiveOrientedContainerId = null;
    protected ?int $testObjId = null;
    protected ?int $testRefId = null;
    protected ?int $userId = null;
    protected ?string $attempt_last_access_date = null;
    protected ?string $crsTitle = null;
    protected ?string $testType = null;
    protected array $objectives = [];

    public function __construct(
        protected ilLanguage $lng,
        protected ilObjectDataCache $objCache
    ) {
    }

    public function getObjectiveOrientedContainerId(): ?int
    {
        return $this->objectiveOrientedContainerId;
    }

    public function setObjectiveOrientedContainerId(int $objectiveOrientedContainerId): void
    {
        $this->objectiveOrientedContainerId = $objectiveOrientedContainerId;
    }

    public function getTestObjId(): ?int
    {
        return $this->testObjId;
    }

    public function setTestObjId(int $testObjId): void
    {
        $this->testObjId = $testObjId;
    }

    public function getTestRefId(): ?int
    {
        return $this->testRefId;
    }

    public function setTestRefId(int $testRefId): void
    {
        $this->testRefId = $testRefId;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setAttemptLastAccessDate(string $formatted_date): void
    {
        $this->attempt_last_access_date = $formatted_date;
    }

    public function initObjectiveOrientedMode(): void
    {
        $this->initTestType();
        $this->initObjectives();
        $this->initCourseTitle();
    }

    private function initTestType()
    {
        $loSettings = ilLOSettings::getInstanceByObjId($this->getObjectiveOrientedContainerId());

        if ($loSettings->getInitialTest() == $this->getTestRefId()) {
            $this->testType = self::LO_TEST_TYPE_INITIAL;
        } elseif ($loSettings->getQualifiedTest() == $this->getTestRefId()) {
            $this->testType = self::LO_TEST_TYPE_QUALIFYING;
        }
    }

    private function initObjectives()
    {
        $loRuns = ilLOTestRun::getRun($this->getObjectiveOrientedContainerId(), $this->getUserId(), $this->getTestObjId());

        $this->objectives = [];

        foreach ($loRuns as $loRun) {
            /* @var ilLOTestRun $loRun */

            $this->objectives[$loRun->getObjectiveId()] = $this->getObjectiveTitle($loRun);
        }
    }

    private function initCourseTitle()
    {
        $this->crsTitle = $this->objCache->lookupTitle((int) $this->getObjectiveOrientedContainerId());
    }

    /**
     * @return string
     */
    public function getPassOverviewHeaderLabel(): string
    {
        if (!$this->getObjectiveOrientedContainerId()) {
            return $this->lng->txt('tst_results_overview');
        }

        if ($this->isInitialTestForAllObjectives()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_initial_all_objectives'),
                $this->crsTitle
            );
        } elseif ($this->isInitialTestPerObjective()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_initial_per_objective'),
                $this->getObjectivesString(),
                $this->crsTitle
            );
        } elseif ($this->isQualifyingTestForAllObjectives()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_qualifying_all_objectives'),
                $this->crsTitle
            );
        } elseif ($this->isQualifyingTestPerObjective()) {
            return sprintf(
                $this->lng->txt('tst_pass_overview_header_lo_qualifying_per_objective'),
                $this->getObjectivesString(),
                $this->crsTitle
            );
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPassDetailsHeaderLabel($attempt): string
    {
        if (!$this->getObjectiveOrientedContainerId()) {
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
        } elseif ($this->isQualifyingTest()) {
            return sprintf(
                $this->lng->txt('tst_pass_details_header_lo_qualifying'),
                $this->getObjectivesString(),
                $this->getAttemptLabel($attempt)
            );
        }

        return '';
    }

    private function isInitialTest(): bool
    {
        return $this->testType == self::LO_TEST_TYPE_INITIAL;
    }

    private function isQualifyingTest(): bool
    {
        return $this->testType == self::LO_TEST_TYPE_QUALIFYING;
    }

    private function isInitialTestForAllObjectives(): bool
    {
        if ($this->testType != self::LO_TEST_TYPE_INITIAL) {
            return false;
        }

        if (count($this->objectives) <= 1) {
            return false;
        }

        return true;
    }

    private function isInitialTestPerObjective(): bool
    {
        if ($this->testType != self::LO_TEST_TYPE_INITIAL) {
            return false;
        }

        if (count($this->objectives) > 1) {
            return false;
        }

        return true;
    }

    private function isQualifyingTestForAllObjectives(): bool
    {
        if ($this->testType != self::LO_TEST_TYPE_QUALIFYING) {
            return false;
        }

        if (count($this->objectives) <= 1) {
            return false;
        }

        return true;
    }

    private function isQualifyingTestPerObjective(): bool
    {
        if ($this->testType != self::LO_TEST_TYPE_QUALIFYING) {
            return false;
        }

        if (count($this->objectives) > 1) {
            return false;
        }

        return true;
    }

    private function getObjectiveTitle(ilLOTestRun $loRun)
    {
        return ilCourseObjective::lookupObjectiveTitle($loRun->getObjectiveId());
    }

    private function getObjectivesString(): string
    {
        return implode(', ', $this->objectives);
    }

    private function getAttemptLabel(int $attempt): string
    {
        return sprintf($this->lng->txt('tst_res_lo_try_n'), $attempt);
    }

    public function getListOfAnswersHeaderLabel(int $attempt): string
    {
        $langVar = 'tst_eval_results_by_pass';

        if ($this->getObjectiveOrientedContainerId()) {
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
}
