<?php

declare(strict_types=0);
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

/**
 * Test question filter
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilLOTestQuestionAdapter
{
    protected ilLOSettings $settings;
    protected ilLOTestAssignments $assignments;
    protected array $run = [];
    protected int $user_id = 0;
    protected int $container_id = 0;
    protected ?int $testRefId = null;

    protected ilLogger $logger;

    public function __construct(int $a_user_id, int $a_course_id)
    {
        global $DIC;

        $this->logger = $DIC->logger()->crs();
        $this->user_id = $a_user_id;
        $this->container_id = $a_course_id;
        $this->settings = ilLOSettings::getInstanceByObjId($this->container_id);
        $this->assignments = ilLOTestAssignments::getInstance($this->container_id);
    }

    public function getTestRefId(): ?int
    {
        return $this->testRefId;
    }

    public function setTestRefId(int $testRefId): void
    {
        $this->testRefId = $testRefId;
    }

    protected function lookupRelevantObjectiveIdsForTest(int $a_container_id, int $a_tst_ref_id, int $a_user_id): array
    {
        $assignments = ilLOTestAssignments::getInstance($a_container_id);

        $objective_ids = ilCourseObjective::_getObjectiveIds($a_container_id);

        $relevant_objective_ids = array();
        if (!$this->getSettings()->hasSeparateInitialTests()) {
            if ($a_tst_ref_id === $this->getSettings()->getInitialTest()) {
                $relevant_objective_ids = $objective_ids;
            }
        } elseif (!$this->getSettings()->hasSeparateQualifiedTests()) {
            if ($a_tst_ref_id === $this->getSettings()->getQualifiedTest()) {
                $relevant_objective_ids = $objective_ids;
            }
        }

        foreach ($objective_ids as $objective_id) {
            $assigned_itest = $assignments->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL);
            if ($assigned_itest === $a_tst_ref_id) {
                $relevant_objective_ids[] = $objective_id;
            }
            $assigned_qtest = $assignments->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED);
            if ($assigned_qtest === $a_tst_ref_id) {
                $relevant_objective_ids[] = $objective_id;
            }
        }

        $relevant_objective_ids = array_unique($relevant_objective_ids);

        if (count($relevant_objective_ids) <= 1) {
            return $relevant_objective_ids;
        }

        // filter passed objectives
        $test_type = $assignments->getTypeByTest($a_tst_ref_id);
        $results = new ilLOUserResults($a_container_id, $a_user_id);

        $passed = $results->getCompletedObjectiveIds();
        $this->logger->debug('Passed objectives are ' . print_r($passed, true) . ' for test type: ' . $test_type);

        // all completed => show all objectives
        if (count($passed) >= count($relevant_objective_ids)) {
            return $relevant_objective_ids;
        }

        $unpassed = array();
        foreach ($relevant_objective_ids as $objective_id) {
            if (!in_array($objective_id, $passed)) {
                $unpassed[] = $objective_id;
            }
        }
        return $unpassed;
    }

    /**
     * Called from learning objective test on actual test start
     */
    public function notifyTestStart(ilTestSession $a_test_session, int $a_test_obj_id): void
    {
        $relevant_objectives = $this->lookupRelevantObjectiveIdsForTest(
            $a_test_session->getObjectiveOrientedContainerId(),
            $a_test_session->getRefId(),
            $a_test_session->getUserId()
        );
        $this->logger->debug('Notify test start: ' . print_r($relevant_objectives, true));

        // delete test runs
        ilLOTestRun::deleteRun(
            $a_test_session->getObjectiveOrientedContainerId(),
            $a_test_session->getUserId(),
            $a_test_obj_id
        );

        foreach ($relevant_objectives as $oid) {
            $this->logger->debug('Adding new run for objective with id: ' . $oid);
            $run = new ilLOTestRun(
                $a_test_session->getObjectiveOrientedContainerId(),
                $a_test_session->getUserId(),
                $a_test_obj_id,
                $oid
            );
            $run->create();
        }

        // finally reinitialize test runs
        $this->initTestRun($a_test_session);
    }

    /**
     * Called from learning objective test
     */
    public function prepareTestPass(ilTestSession $a_test_session, ilTestSequence $a_test_sequence): bool
    {
        $this->logger->debug('Prepare test pass called');

        $this->updateQuestions($a_test_session, $a_test_sequence);

        if ($this->getSettings()->getPassedObjectiveMode() == ilLOSettings::MARK_PASSED_OBJECTIVE_QST) {
            $this->setQuestionsOptional($a_test_sequence);
        } elseif ($this->getSettings()->getPassedObjectiveMode() == ilLOSettings::HIDE_PASSED_OBJECTIVE_QST) {
            $this->hideQuestions($a_test_sequence);
        }

        $this->storeTestRun();
        $this->initUserResult($a_test_session);

        // Save test sequence
        $a_test_sequence->saveToDb();

        return true;
    }

    public function buildQuestionRelatedObjectiveList(
        ilTestQuestionSequence $a_test_sequence,
        ilTestQuestionRelatedObjectivesList $a_objectives_list
    ): void {
        $testType = $this->assignments->getTypeByTest($this->getTestRefId());

        if ($testType == ilLOSettings::TYPE_TEST_INITIAL && $this->getSettings()->hasSeparateInitialTests()) {
            $this->buildQuestionRelatedObjectiveListByTest($a_test_sequence, $a_objectives_list);
        } elseif ($testType == ilLOSettings::TYPE_TEST_QUALIFIED && $this->getSettings()->hasSeparateQualifiedTests()) {
            $this->buildQuestionRelatedObjectiveListByTest($a_test_sequence, $a_objectives_list);
        } else {
            $this->buildQuestionRelatedObjectiveListByQuestions($a_test_sequence, $a_objectives_list);
        }
    }

    protected function buildQuestionRelatedObjectiveListByTest(
        ilTestQuestionSequence $a_test_sequence,
        ilTestQuestionRelatedObjectivesList $a_objectives_list
    ): void {
        $objectiveIds = array($this->getRelatedObjectivesForSeparatedTest($this->getTestRefId()));

        foreach ($a_test_sequence->getQuestionIds() as $questionId) {
            $a_objectives_list->addQuestionRelatedObjectives($questionId, $objectiveIds);
        }
    }

    protected function buildQuestionRelatedObjectiveListByQuestions(
        ilTestQuestionSequence $a_test_sequence,
        ilTestQuestionRelatedObjectivesList $a_objectives_list
    ): void {
        foreach ($a_test_sequence->getQuestionIds() as $questionId) {
            if ($a_test_sequence instanceof ilTestRandomQuestionSequence) {
                $definitionId = $a_test_sequence->getResponsibleSourcePoolDefinitionId($questionId);
                $objectiveIds = $this->lookupObjectiveIdByRandomQuestionSelectionDefinitionId($definitionId);
            } else {
                $objectiveIds = $this->lookupObjectiveIdByFixedQuestionId($questionId);
            }

            if ($objectiveIds !== []) {
                $a_objectives_list->addQuestionRelatedObjectives($questionId, $objectiveIds);
            }
        }
    }

    protected function lookupObjectiveIdByRandomQuestionSelectionDefinitionId(int $a_id): array
    {
        return ilLORandomTestQuestionPools::lookupObjectiveIdsBySequence($this->getContainerId(), $a_id);
    }

    protected function lookupObjectiveIdByFixedQuestionId(int $a_question_id): array
    {
        return ilCourseObjectiveQuestion::lookupObjectivesOfQuestion($a_question_id);
    }

    protected function getRelatedObjectivesForSeparatedTest(int $testRefId): ?int
    {
        foreach ($this->getAssignments()->getAssignments() as $assignment) {
            if ($assignment->getTestRefId() === $testRefId) {
                return $assignment->getObjectiveId();
            }
        }
        return null;
    }

    protected function getUserId(): int
    {
        return $this->user_id;
    }

    protected function getContainerId(): int
    {
        return $this->container_id;
    }

    protected function getSettings(): ilLOSettings
    {
        return $this->settings;
    }

    protected function getAssignments(): ilLOTestAssignments
    {
        return $this->assignments;
    }

    protected function initUserResult(ilTestSession $session): void
    {
        // check if current test is start object and fullfilled
        // if yes => do not increase tries.
        $is_qualified_run = false;
        if ($this->isQualifiedStartRun($session)) {
            $is_qualified_run = true;
        }

        foreach ($this->run as $run) {
            $old_result = ilLOUserResults::lookupResult(
                $this->container_id,
                $this->user_id,
                $run->getObjectiveId(),
                $this->getAssignments()->getTypeByTest($session->getRefId())
            );

            $limit = ilLOUtils::lookupObjectiveRequiredPercentage(
                $this->container_id,
                $run->getObjectiveId(),
                $session->getRefId(),
                $run->getMaxPoints()
            );

            $max_attempts = ilLOUtils::lookupMaxAttempts(
                $this->container_id,
                $run->getObjectiveId(),
                $session->getRefId()
            );

            $this->logger->debug('Max attempts = ' . $max_attempts);

            if ($max_attempts) {
                // check if current test is start object and fullfilled
                // if yes => do not increase tries.
                $this->logger->debug('Checking for qualified test...');
                if (!$is_qualified_run) {
                    $this->logger->debug(' and increasing attempts.');
                    ++$old_result['tries'];
                }
                $old_result['is_final'] = ($old_result['tries'] >= $max_attempts);
            }

            $ur = new ilLOUserResults($this->container_id, $this->user_id);
            $ur->saveObjectiveResult(
                $run->getObjectiveId(),
                $this->getAssignments()->getTypeByTest($session->getRefId()),
                $old_result['status'],
                $old_result['result_perc'],
                $limit,
                $old_result['tries'],
                $old_result['is_final']
            );
        }
    }

    /**
     * Check if current run is a start object run
     */
    protected function isQualifiedStartRun(ilTestSession $session): bool
    {
        if ($this->getAssignments()->getTypeByTest($session->getRefId()) == ilLOSettings::TYPE_TEST_INITIAL) {
            $this->logger->debug('Initial test');
            return false;
        }

        if ($session->getRefId() !== $this->getSettings()->getQualifiedTest()) {
            $this->logger->debug('No qualified test run');
            return false;
        }
        if (!ilContainerStartObjects::isStartObject($this->getContainerId(), $session->getRefId())) {
            $this->logger->debug('No start object');
            return false;
        }
        // Check if start object is fullfilled

        $container_ref_ids = ilObject::_getAllReferences($this->getContainerId());
        $container_ref_id = end($container_ref_ids);

        $start = new ilContainerStartObjects(
            $container_ref_id,
            $this->getContainerId()
        );
        if ($start->isFullfilled($this->getUserId(), $session->getRefId())) {
            $this->logger->debug('Is fullfilled');
            return false;
        }
        $this->logger->debug('Is not fullfilled');
        return true;
    }

    /**
     * update question result of run
     */
    public function updateQuestionResult(ilTestSession $session, assQuestion $qst): void
    {
        foreach ($this->run as $run) {
            if ($run->questionExists($qst->getId())) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': reached points are ' . $qst->getReachedPoints(
                    $session->getActiveId(),
                    $session->getPass()
                ));
                $run->setQuestionResult(
                    $qst->getId(),
                    $qst->getReachedPoints($session->getActiveId(), $session->getPass())
                );
                $run->update();

                $res = $run->getResult();

                $old_result = ilLOUserResults::lookupResult(
                    $this->container_id,
                    $this->user_id,
                    $run->getObjectiveId(),
                    $this->getAssignments()->getTypeByTest($session->getRefId())
                );

                $ur = new ilLOUserResults($this->container_id, $this->user_id);
                $ur->saveObjectiveResult(
                    $run->getObjectiveId(),
                    $this->getAssignments()->getTypeByTest($session->getRefId()),
                    $comp = ilLOUtils::isCompleted(
                        $this->container_id,
                        $session->getRefId(),
                        $run->getObjectiveId(),
                        $res['max'],
                        $res['reached'],
                        $old_result['limit_perc']
                    ) ?
                        ilLOUserResults::STATUS_COMPLETED :
                        ilLOUserResults::STATUS_FAILED,
                    (int) $res['percentage'],
                    $old_result['limit_perc'],
                    $old_result['tries'],
                    $old_result['is_final']
                );
                ilLPStatusWrapper::_updateStatus($this->container_id, $this->user_id);
            }
        }
    }

    protected function setQuestionsOptional(ilTestSequence $seq): void
    {
        // first unset optional on all questions
        $seq->clearOptionalQuestions();
        foreach ($seq->getQuestionIds() as $qid) {
            if (!$this->isInRun($qid)) { // but is assigned to any LO
                $seq->setQuestionOptional($qid);
            }
        }
    }

    protected function hideQuestions(ilTestSequence $seq): void
    {
        // first unhide all questions
        $seq->clearHiddenQuestions();
        foreach ($seq->getQuestionIds() as $qid) {
            if (!$this->isInRun($qid)) {
                $seq->hideQuestion($qid);
            }
        }
    }

    protected function initTestRun(ilTestSession $session): void
    {
        $this->run = ilLOTestRun::getRun(
            $this->container_id,
            $this->user_id,
            ilObject::_lookupObjId($session->getRefId())
        );
    }

    protected function storeTestRun(): void
    {
        foreach ($this->run as $tst_run) {
            $tst_run->update();
        }
    }

    protected function updateQuestions(ilTestSession $session, ilTestSequence $seq): void
    {
        if ($this->getAssignments()->isSeparateTest($session->getRefId())) {
            $this->updateSeparateTestQuestions($session, $seq);
            return;
        }
        if ($seq instanceof ilTestSequenceFixedQuestionSet) {
            $this->updateFixedQuestions($session, $seq);
            return;
        }
        if ($seq instanceof ilTestSequenceRandomQuestionSet) {
            $this->updateRandomQuestions($session, $seq);
        }
    }

    protected function updateSeparateTestQuestions(ilTestSession $session, ilTestSequence $seq): void
    {
        foreach ($this->run as $tst_run) {
            $tst_run->clearQuestions();
            $points = 0;
            foreach ($seq->getQuestionIds() as $qst_id) {
                $tst_run->addQuestion($qst_id);
                $points += ilCourseObjectiveQuestion::_lookupMaximumPointsOfQuestion($qst_id);
            }
            $tst_run->setMaxPoints($points);
        }
    }

    protected function updateFixedQuestions(ilTestSession $session, ilTestSequence $seq): void
    {
        foreach ($this->run as $tst_run) {
            $tst_run->clearQuestions();
            $qst = ilCourseObjectiveQuestion::lookupQuestionsByObjective(
                ilObject::_lookupObjId($session->getRefId()),
                $tst_run->getObjectiveId()
            );
            $points = 0;
            foreach ($qst as $id) {
                $tst_run->addQuestion($id);
                $points += ilCourseObjectiveQuestion::_lookupMaximumPointsOfQuestion($id);
            }
            $tst_run->setMaxPoints($points);
        }
    }

    protected function updateRandomQuestions(ilTestSession $session, ilTestSequenceRandomQuestionSet $seq): void
    {
        foreach ($this->run as $tst_run) {
            // Clear questions of previous run
            $tst_run->clearQuestions();

            $sequences = ilLORandomTestQuestionPools::lookupSequencesByType(
                $this->container_id,
                $tst_run->getObjectiveId(),
                ilObject::_lookupObjId($session->getRefId()),
                (
                    ($this->getSettings()->getQualifiedTest() === $session->getRefId()) ?
                    ilLOSettings::TYPE_TEST_QUALIFIED :
                    ilLOSettings::TYPE_TEST_INITIAL
                )
            );

            $points = 0;
            foreach ($seq->getQuestionIds() as $qst) {
                if (in_array($seq->getResponsibleSourcePoolDefinitionId($qst), $sequences)) {
                    $tst_run->addQuestion($qst);
                    $points += ilCourseObjectiveQuestion::_lookupMaximumPointsOfQuestion($qst);
                }
            }
            $tst_run->setMaxPoints($points);
        }
    }

    protected function isInRun(int $a_qid): bool
    {
        foreach ($this->run as $run) {
            if ($run->questionExists($a_qid)) {
                return true;
            }
        }
        return false;
    }

    public static function getInstance(ilTestSession $a_test_session): self
    {
        $adapter = new self(
            $a_test_session->getUserId(),
            $a_test_session->getObjectiveOrientedContainerId()
        );

        $adapter->setTestRefId($a_test_session->getRefId());
        $adapter->initTestRun($a_test_session);
        return $adapter;
    }
}
