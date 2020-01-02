<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';

/**
 * Test question filter
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOTestQuestionAdapter
{
    /**
     * @var ilLogger
     */
    protected $logger = null;

    protected $settings = null;
    protected $assignments = null;
    
    protected $user_id = 0;
    protected $container_id = 0;
    
    protected $testRefId = null;
    
    /**
     *
     * @param type $a_user_id
     * @param type $a_course_id
     */
    public function __construct($a_user_id, $a_course_id)
    {
        $this->logger = $GLOBALS['DIC']->logger()->crs();
        
        $this->user_id = $a_user_id;
        $this->container_id = $a_course_id;
        
        $this->settings = ilLOSettings::getInstanceByObjId($this->container_id);
        $this->assignments = ilLOTestAssignments::getInstance($this->container_id);
    }
    
    /**
     * @return null
     */
    public function getTestRefId()
    {
        return $this->testRefId;
    }
    
    /**
     * @param null $testRefId
     */
    public function setTestRefId($testRefId)
    {
        $this->testRefId = $testRefId;
    }
    
    /**
     * Lookup all relevant objective ids for a specific test
     * @return array
     */
    protected function lookupRelevantObjectiveIdsForTest($a_container_id, $a_tst_ref_id, $a_user_id)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
        $assignments = ilLOTestAssignments::getInstance($a_container_id);
        
        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        $objective_ids = ilCourseObjective::_getObjectiveIds($a_container_id);
        
        $relevant_objective_ids = array();
        if (!$this->getSettings()->hasSeparateInitialTests()) {
            if ($a_tst_ref_id == $this->getSettings()->getInitialTest()) {
                $relevant_objective_ids = $objective_ids;
            }
        } elseif (!$this->getSettings()->hasSeparateQualifiedTests()) {
            if ($a_tst_ref_id == $this->getSettings()->getQualifiedTest()) {
                $relevant_objective_ids = $objective_ids;
            }
        }

        foreach ((array) $objective_ids as $objective_id) {
            $assigned_itest = $assignments->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL);
            if ($assigned_itest == $a_tst_ref_id) {
                $relevant_objective_ids[] = $objective_id;
            }
            $assigned_qtest = $assignments->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED);
            if ($assigned_qtest == $a_tst_ref_id) {
                $relevant_objective_ids[] = $objective_id;
            }
        }
        
        $relevant_objective_ids = array_unique($relevant_objective_ids);
        
        if (count($relevant_objective_ids) <= 1) {
            return $relevant_objective_ids;
        }
        
        // filter passed objectives
        $test_type = $assignments->getTypeByTest($a_tst_ref_id);
        
        $passed_objectives = array();
        include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
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
     * @param ilTestSession $a_test_session
     * @param integer $a_test_obj_id
     */
    public function notifyTestStart(ilTestSession $a_test_session, $a_test_obj_id)
    {
        $relevant_objectives = $this->lookupRelevantObjectiveIdsForTest(
            $a_test_session->getObjectiveOrientedContainerId(),
            $a_test_session->getRefId(),
            $a_test_session->getUserId()
        );
        $this->logger->debug('Notify test start: ' . print_r($relevant_objectives, true));

        // delete test runs
        include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
        ilLOTestRun::deleteRun(
            $a_test_session->getObjectiveOrientedContainerId(),
            $a_test_session->getUserId(),
            $a_test_obj_id
        );
        
        foreach ((array) $relevant_objectives as $oid) {
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
     * @param ilTestSession $a_test_session
     * @param ilTestSequence $a_test_sequence
     */
    public function prepareTestPass(ilTestSession $a_test_session, ilTestSequence $a_test_sequence)
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

    /**
     * @param ilTestSequence $a_test_sequence
     * @param ilTestQuestionRelatedObjectivesList $a_objectives_list
     */
    public function buildQuestionRelatedObjectiveList(ilTestQuestionSequence $a_test_sequence, ilTestQuestionRelatedObjectivesList $a_objectives_list)
    {
        $testType = $this->assignments->getTypeByTest($this->getTestRefId());
        
        if ($testType == ilLOSettings::TYPE_TEST_INITIAL && $this->getSettings()->hasSeparateInitialTests()) {
            $this->buildQuestionRelatedObjectiveListByTest($a_test_sequence, $a_objectives_list);
        } elseif ($testType == ilLOSettings::TYPE_TEST_QUALIFIED && $this->getSettings()->hasSeparateQualifiedTests()) {
            $this->buildQuestionRelatedObjectiveListByTest($a_test_sequence, $a_objectives_list);
        } else {
            $this->buildQuestionRelatedObjectiveListByQuestions($a_test_sequence, $a_objectives_list);
        }
    }
    
    protected function buildQuestionRelatedObjectiveListByTest(ilTestQuestionSequence $a_test_sequence, ilTestQuestionRelatedObjectivesList $a_objectives_list)
    {
        $objectiveIds = array($this->getRelatedObjectivesForSeparatedTest($this->getTestRefId()));
        
        foreach ($a_test_sequence->getQuestionIds() as $questionId) {
            $a_objectives_list->addQuestionRelatedObjectives($questionId, $objectiveIds);
        }
    }
    
    protected function buildQuestionRelatedObjectiveListByQuestions(ilTestQuestionSequence $a_test_sequence, ilTestQuestionRelatedObjectivesList $a_objectives_list)
    {
        foreach ($a_test_sequence->getQuestionIds() as $questionId) {
            if ($a_test_sequence instanceof ilTestRandomQuestionSequence) {
                $definitionId = $a_test_sequence->getResponsibleSourcePoolDefinitionId($questionId);
                $objectiveIds = $this->lookupObjectiveIdByRandomQuestionSelectionDefinitionId($definitionId);
            } else {
                $objectiveIds = $this->lookupObjectiveIdByFixedQuestionId($questionId);
            }
            
            if (count($objectiveIds)) {
                $a_objectives_list->addQuestionRelatedObjectives($questionId, $objectiveIds);
            }
        }
    }
    
    protected function lookupObjectiveIdByRandomQuestionSelectionDefinitionId($a_id)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
        return ilLORandomTestQuestionPools::lookupObjectiveIdsBySequence($this->getContainerId(), $a_id);
    }

    protected function lookupObjectiveIdByFixedQuestionId($a_question_id)
    {
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
        return ilCourseObjectiveQuestion::lookupObjectivesOfQuestion($a_question_id);
    }
    
    protected function getRelatedObjectivesForSeparatedTest($testRefId)
    {
        foreach ($this->getAssignments()->getAssignments() as $assignment) {
            if ($assignment->getTestRefId() == $testRefId) {
                return $assignment->getObjectiveId();
            }
        }
        
        return null;
    }
    
    protected function getUserId()
    {
        return $this->user_id;
    }
    
    protected function getContainerId()
    {
        return $this->container_id;
    }
    
    /**
     * Get loc settings
     * @return ilLOSettings
     */
    protected function getSettings()
    {
        return $this->settings;
    }
    
    /**
     *
     * @return ilLOTestAssignments
     */
    protected function getAssignments()
    {
        return $this->assignments;
    }
    
    /**
     * init user result
     */
    protected function initUserResult(ilTestSession $session)
    {
        // check if current test is start object and fullfilled
        // if yes => do not increase tries.
        $is_qualified_run = false;
        if ($this->isQualifiedStartRun($session)) {
            $is_qualified_run = true;
        }
        
        foreach ($this->run as $run) {
            include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
            include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
                
            $old_result = ilLOUserResults::lookupResult(
                $this->container_id,
                $this->user_id,
                $run->getObjectiveId(),
                $this->getAssignments()->getTypeByTest($session->getRefId())
            );
            
            include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
            
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
     * @param ilTestSession $session
     * @return boolean
     */
    protected function isQualifiedStartRun(ilTestSession $session)
    {
        if ($this->getAssignments()->getTypeByTest($session->getRefId()) == ilLOSettings::TYPE_TEST_INITIAL) {
            $this->logger->debug('Initial test');
            return false;
        }
        
        if ($session->getRefId() != $this->getSettings()->getQualifiedTest()) {
            $this->logger->debug('No qualified test run');
            return false;
        }
        include_once './Services/Container/classes/class.ilContainerStartObjects.php';
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
     * @param ilTestSession $session
     * @param assQuestion $qst
     */
    public function updateQuestionResult(ilTestSession $session, assQuestion $qst)
    {
        foreach ($this->run as $run) {
            if ($run->questionExists($qst->getId())) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': reached points are ' . $qst->getReachedPoints($session->getActiveId(), $session->getPass()));
                $run->setQuestionResult(
                    $qst->getId(),
                    $qst->getReachedPoints($session->getActiveId(), $session->getPass())
                );
                $run->update();
                
                $res = $run->getResult();
                
                include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
                include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
                
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
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($run->getResult(), true));
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . '!!!!!!!!!!!!!!!!!!!!: ' . print_r($comp, true));
                
                include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
                ilLPStatusWrapper::_updateStatus($this->container_id, $this->user_id);
            }
        }
        return false;
    }

    /**
     * set questions optional
     * @param ilTestSequence $seq
     */
    protected function setQuestionsOptional(ilTestSequence $seq)
    {
        // first unset optional on all questions
        $seq->clearOptionalQuestions();
        foreach ($seq->getQuestionIds() as $qid) {
            if (!$this->isInRun($qid)) { // but is assigned to any LO
                $seq->setQuestionOptional($qid);
            }
        }
    }
    
    /**
     * Hide questions
     * @param ilTestSequence $seq
     */
    protected function hideQuestions(ilTestSequence $seq)
    {
        // first unhide all questions
        $seq->clearHiddenQuestions();
        foreach ($seq->getQuestionIds() as $qid) {
            if (!$this->isInRun($qid)) {
                $seq->hideQuestion($qid);
            }
        }
    }

    protected function initTestRun(ilTestSession $session)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
        $this->run = ilLOTestRun::getRun(
            $this->container_id,
            $this->user_id,
            ilObject::_lookupObjId($session->getRefId())
        );
    }

    /**
     * Store test run in DB
     */
    protected function storeTestRun()
    {
        foreach ($this->run as $tst_run) {
            $tst_run->update();
        }
    }


    protected function updateQuestions(ilTestSession $session, ilTestSequence $seq)
    {
        if ($this->getAssignments()->isSeparateTest($session->getRefId())) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': separate run');
            return $this->updateSeparateTestQuestions($session, $seq);
        }
        if ($seq instanceof ilTestSequenceFixedQuestionSet) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': fixed run');
            return $this->updateFixedQuestions($session, $seq);
        }
        if ($seq instanceof ilTestSequenceRandomQuestionSet) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': random run');
            return $this->updateRandomQuestions($session, $seq);
        }
    }
    
    /**
     * Update questions for separate tests
     * @param ilTestSession $session
     * @param ilTestSequence $seq
     */
    protected function updateSeparateTestQuestions(ilTestSession $session, ilTestSequence $seq)
    {
        foreach ($this->run as $tst_run) {
            $tst_run->clearQuestions();
            $points = 0;
            foreach ($seq->getQuestionIds() as $idx => $qst_id) {
                $tst_run->addQuestion($qst_id);
                include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
                $points += ilCourseObjectiveQuestion::_lookupMaximumPointsOfQuestion($qst_id);
            }
            $tst_run->setMaxPoints($points);
        }
    }
    
    
    protected function updateFixedQuestions(ilTestSession $session, ilTestSequence $seq)
    {
        foreach ($this->run as $tst_run) {
            $tst_run->clearQuestions();
            include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
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
    
    /**
     * update random questions
     * @param ilTestSession $session
     * @param ilTestSequenceRandomQuestionSet $seq
     */
    protected function updateRandomQuestions(ilTestSession $session, ilTestSequenceRandomQuestionSet $seq)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
        include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';

        foreach ($this->run as $tst_run) {
            // Clear questions of previous run
            $tst_run->clearQuestions();
            
            $sequences = ilLORandomTestQuestionPools::lookupSequencesByType(
                $this->container_id,
                $tst_run->getObjectiveId(),
                ilObject::_lookupObjId($session->getRefId()),
                (
                    ($this->getSettings()->getQualifiedTest() == $session->getRefId()) ?
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
    
    protected function isInRun($a_qid)
    {
        foreach ($this->run as $run) {
            if ($run->questionExists($a_qid)) {
                return true;
            }
        }
        return false;
    }
    
    
    private static function getQuestionData($testObjId, $questionIds)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilPluginAdmin = $DIC['ilPluginAdmin'];
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
        $questionList = new ilAssQuestionList($ilDB, $lng, $ilPluginAdmin);
        $questionList->setParentObjId($testObjId);

        $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
        $questionList->setQuestionIdsFilter($questionIds);

        $questionList->load();

        return $questionList->getQuestionDataArray();
    }
    
    public static function getInstance(ilTestSession $a_test_session)
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
