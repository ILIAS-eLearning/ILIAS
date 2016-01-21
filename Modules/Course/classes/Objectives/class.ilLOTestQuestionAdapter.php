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
	protected $settings = NULL;
	protected $assignments = null;
	
	protected $user_id = 0;
	protected $container_id = 0;
	
	
	
	/**
	 * 
	 * @param type $a_user_id
	 * @param type $a_course_id
	 */
	public function __construct($a_user_id, $a_course_id)
	{
		$this->user_id = $a_user_id;
		$this->container_id = $a_course_id;
		
		$this->settings = ilLOSettings::getInstanceByObjId($this->container_id);
		$this->assignments = ilLOTestAssignments::getInstance($this->container_id);
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
		if(!$this->getSettings()->hasSeparateInitialTests())
		{
			if($a_tst_ref_id == $this->getSettings()->getInitialTest())
			{
				$relevant_objective_ids = $objective_ids;
			}
		}
		elseif(!$this->getSettings()->hasSeparateQualifiedTests())
		{
			if($a_tst_ref_id == $this->getSettings()->getQualifiedTest())
			{
				$relevant_objective_ids = $objective_ids;
			}
		}

		foreach((array) $objective_ids as $objective_id)
		{
			$assigned_itest = $assignments->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL);
			if($assigned_itest == $a_tst_ref_id)
			{
				$relevant_objective_ids[] = $objective_id;
			}
			$assigned_qtest = $assignments->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED);
			if($assigned_qtest == $a_tst_ref_id)
			{
				$relevant_objective_ids[] = $objective_id;
			}
		}
		
		$relevant_objective_ids = array_unique($relevant_objective_ids);
		
		if(count($relevant_objective_ids) <= 1)
		{
			return $relevant_objective_ids;
		}
		
		// filter passed objectives
		$test_type = $assignments->getTypeByTest($a_tst_ref_id);
		
		$passed_objectives = array();
		include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
		$results = new ilLOUserResults($a_container_id,$a_user_id);
		
		$passed = $results->getCompletedObjectiveIds();
		$GLOBALS['ilLog']->write(__METHOD__.': Passed objectives are '.print_r($passed,TRUE).' test_type = '.$test_type);
		
		
		// all completed => show all objectives
		if(count($passed) >= count($relevant_objective_ids))
		{
			return $relevant_objective_ids;
		}
		
		$unpassed = array();
		foreach($relevant_objective_ids as $objective_id)
		{
			if(!in_array($objective_id, $passed))
			{
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
		$GLOBALS['ilLog']->write(__METHOD__.': Notify test start ' . print_r($relevant_objectives,TRUE));

		// delete test runs
		include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
		ilLOTestRun::deleteRun(
				$a_test_session->getObjectiveOrientedContainerId(),
				$a_test_session->getUserId(),
				$a_test_obj_id
		);
		
		foreach((array) $relevant_objectives as $oid)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Adding new run for objective with id '.$oid);
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
		$this->updateQuestions($a_test_session, $a_test_sequence);

		if($this->getSettings()->getPassedObjectiveMode() == ilLOSettings::MARK_PASSED_OBJECTIVE_QST)
		{
			$this->setQuestionsOptional($a_test_sequence);
		}
		elseif($this->getSettings()->getPassedObjectiveMode() == ilLOSettings::HIDE_PASSED_OBJECTIVE_QST)
		{
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
		foreach( $a_test_sequence->getQuestionIds() as $questionId )
		{
			if( $a_test_sequence instanceof ilTestRandomQuestionSequence )
			{
				$definitionId = $a_test_sequence->getResponsibleSourcePoolDefinitionId($questionId);
				$objectiveIds = $this->lookupObjectiveIdByRandomQuestionSelectionDefinitionId($definitionId);
			}
			else
			{
				$objectiveIds = $this->lookupObjectiveIdByFixedQuestionId($questionId);
			}

			if( count($objectiveIds) )
			{
				$a_objectives_list->addQuestionRelatedObjectives($questionId, $objectiveIds);
			}
		}
	}
	
	protected function lookupObjectiveIdByRandomQuestionSelectionDefinitionId($a_id)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
		return ilLORandomTestQuestionPools::lookupObjectiveIdsBySequence($this->getContainerId(),$a_id);
	}

	protected function lookupObjectiveIdByFixedQuestionId($a_question_id)
	{
		include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
		return ilCourseObjectiveQuestion::lookupObjectivesOfQuestion($a_question_id);
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
		if($this->isQualifiedStartRun($session))
		{
			$is_qualified_run = true;
		}
		
		foreach($this->run as $run)
		{
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

			$max_attempts = ilLOUtils::lookupMaxAttempts($this->container_id, $run->getObjectiveId());
			if($max_attempts)
			{
				// check if current test is start object and fullfilled
				// if yes => do not increase tries.
				$GLOBALS['ilLog']->write(__METHOD__.': check for qualified...');
				if(!$is_qualified_run)
				{
					$GLOBALS['ilLog']->write(__METHOD__.': and increase attempts');
					++$old_result['tries'];
				}
				$old_result['is_final'] = ($old_result['tries'] >= $max_attempts);
			}
			
			$ur = new ilLOUserResults($this->container_id,$this->user_id);
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
		if($this->getAssignments()->getTypeByTest($session->getRefId()) == ilLOSettings::TYPE_TEST_INITIAL)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': is initial');
			return false;
		}
		
		if($session->getRefId() != $this->getSettings()->getQualifiedTest())
		{
			$GLOBALS['ilLog']->write(__METHOD__.': is not qualified');
			return false;
		}
		include_once './Services/Container/classes/class.ilContainerStartObjects.php';
		if(!ilContainerStartObjects::isStartObject($this->getContainerId(), $session->getRefId()))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': no start object');
			return false;
		}
		// Check if start object is fullfilled
		
		$container_ref_ids = ilObject::_getAllReferences($this->getContainerId());
		$container_ref_id = end($container_ref_ids);
		
		$start = new ilContainerStartObjects(
				$container_ref_id,
				$this->getContainerId()
		);
		if($start->isFullfilled($this->getUserId(),$session->getRefId()))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': is fullfilled');

			return false;
		}
		$GLOBALS['ilLog']->write(__METHOD__.': is not fullfilled');
		return true;
	}
	
	/**
	 * update question result of run
	 * @param ilTestSession $session
	 * @param assQuestion $qst
	 */
	public function updateQuestionResult(ilTestSession $session, assQuestion $qst)
	{
		foreach($this->run as $run)
		{
			if($run->questionExists($qst->getId()))
			{
				$GLOBALS['ilLog']->write(__METHOD__.': reached points are '.$qst->getReachedPoints($session->getActiveId(),$session->getPass()));
				$run->setQuestionResult(
						$qst->getId(),
						$qst->getReachedPoints($session->getActiveId(),$session->getPass())
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
				
				$ur = new ilLOUserResults($this->container_id,$this->user_id);
				$ur->saveObjectiveResult(
						$run->getObjectiveId(), 
						$this->getAssignments()->getTypeByTest($session->getRefId()),
						$comp = ilLOUtils::isCompleted(
								$this->container_id, 
								$session->getRefId(), 
								$run->getObjectiveId(),
								$res['max'],$res['reached'],$old_result['limit_perc']) ?
								ilLOUserResults::STATUS_COMPLETED :
								ilLOUserResults::STATUS_FAILED,
						(int) $res['percentage'], 
						$old_result['limit_perc'],
						$old_result['tries'], 
						$old_result['is_final']
				);
				$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($run->getResult(),true));
				$GLOBALS['ilLog']->write(__METHOD__.'!!!!!!!!!!!!!!!!!!!!: '.print_r($comp,TRUE));
				
				include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
				ilLPStatusWrapper::_updateStatus($this->container_id,$this->user_id);
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
		foreach($seq->getQuestionIds() as $qid)
		{
			if(!$this->isInRun($qid)) // but is assigned to any LO
			{
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
		foreach($seq->getQuestionIds() as $qid)
		{
			if(!$this->isInRun($qid))
			{
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
		foreach ($this->run as $tst_run)
		{
			$tst_run->update();
		}
	}


	protected function updateQuestions(ilTestSession $session, ilTestSequence $seq)
	{
		if($this->getAssignments()->isSeparateTest($session->getRefId()))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': separate run');
			return $this->updateSeparateTestQuestions($session, $seq);
		}
		if($seq instanceof ilTestSequenceFixedQuestionSet)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': fixed run');
			return $this->updateFixedQuestions($session, $seq);
		}
		if($seq instanceof ilTestSequenceRandomQuestionSet)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': random run');
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
		foreach($this->run as $tst_run)
		{
			$tst_run->clearQuestions();
			$points = 0;
			foreach($seq->getQuestionIds() as $idx => $qst_id)
			{
				$tst_run->addQuestion($qst_id);
				include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
				$points += ilCourseObjectiveQuestion::_lookupMaximumPointsOfQuestion($qst_id);
			}
			$tst_run->setMaxPoints($points);
		}
	}
	
	
	protected function updateFixedQuestions(ilTestSession $session, ilTestSequence $seq)
	{
		foreach ($this->run as $tst_run)
		{
			$tst_run->clearQuestions();
			include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';
			$qst = ilCourseObjectiveQuestion::lookupQuestionsByObjective(
					ilObject::_lookupObjId($session->getRefId()),
					$tst_run->getObjectiveId()
			);
			$points = 0;
			foreach($qst as $id)
			{
				$tst_run->addQuestion($id);
				$points += ilCourseObjectiveQuestion::_lookupMaximumPointsOfQuestion($id);
			}
			$tst_run->setMaxPoints($points);
		}
	}
	
	protected function updateRandomQuestions(ilTestSession $session, ilTestSequenceRandomQuestionSet $seq)
	{
		include_once './Modules/Course/classes/Objectives/class.ilLORandomTestQuestionPools.php';
		include_once './Modules/Course/classes/class.ilCourseObjectiveQuestion.php';

		foreach($this->run as $tst_run)
		{
			// Clear questions of previous run
			$tst_run->clearQuestions();
			$rnd = new ilLORandomTestQuestionPools(
					$this->container_id, 
					$tst_run->getObjectiveId(),
					($this->getSettings()->getQualifiedTest() == $session->getRefId() ? 
						ilLOSettings::TYPE_TEST_QUALIFIED : 
						ilLOSettings::TYPE_TEST_INITIAL)
			);
			$stored_sequence_id = $rnd->getQplSequence();
			$points = 0;
			foreach($seq->getQuestionIds() as $qst)
			{
				if($stored_sequence_id  == $seq->getResponsibleSourcePoolDefinitionId($qst))
				{
					$tst_run->addQuestion($qst);
					$points += ilCourseObjectiveQuestion::_lookupMaximumPointsOfQuestion($qst);
				}
			}
			$tst_run->setMaxPoints($points);
		}
	}
	
	protected function isInRun($a_qid)
	{
		foreach($this->run as $run)
		{
			if($run->questionExists($a_qid))
			{
				return true;
			}
		}
		return false;
	}
	
	
	private static function getQuestionData($testObjId, $questionIds)
	{
		global $ilDB, $lng, $ilPluginAdmin;
		
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
		
		$adapter->initTestRun($a_test_session);
		
		return $adapter;
	}
}
?>