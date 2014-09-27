<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';

/**
 * Test question filter
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 */
class ilLOTestQuestionAdapter
{
	protected $settings = NULL;
	
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
	}
	
	
	
	
	/**
	 * Called from learning objective test
	 * @param ilTestSession $a_test_session
	 * @param ilTestSequence $a_test_sequence
	 */
	public static function filterQuestions(ilTestSession $a_test_session, ilTestSequence $a_test_sequence)
	{
		
		$adapter = new self(
				$a_test_session->getUserId(),
				$a_test_session->getObjectiveOrientedContainerId()
		);
		$adapter->initTestRun($a_test_session);
		$adapter->updateQuestions($a_test_session, $a_test_sequence);
		$adapter->hideQuestions($a_test_sequence);
		$adapter->storeTestRun();
		$adapter->initUserResult($a_test_session);
		
		// Save test sequence
		$a_test_sequence->saveToDb();
		
		$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($a_test_sequence,true));
		return true;
	}
	
	/**
	 * Store result and update objective status
	 * @param ilTestSession $a_test_session
	 * @param ilTestSequence $a_test_sequence
	 * @param assQuestion $a_question
	 */
	public static function updateObjectiveStatus(ilTestSession $a_test_session, ilTestSequence $a_test_sequence, assQuestion $a_question)
	{
		$adapter = new self(
				$a_test_session->getUserId(),
				$a_test_session->getObjectiveOrientedContainerId()
		);
		$adapter->initTestRun($a_test_session);
		$adapter->updateQuestionResult($a_test_session,$a_question);
		return true;
		
		/*
		$usr_id = $a_test_session->getUserId();
		$crs_id = $a_test_session->getObjectiveOrientedContainerId();
		
		$question_id = $a_question->getId();
		
		$points_reached = $a_question->getReachedPoints($a_test_session->getActiveId(), $a_test_session->getPass());
		//$points_max = $a_question->getMaxPoints();

		if( $a_test_sequence instanceof ilTestSequenceFixedQuestionSet )
		{
			// make some noise (with question id only)
		}
		elseif( $a_test_sequence instanceof ilTestSequenceRandomQuestionSet )
		{
			$respSrcPoolDefId = $a_test_sequence->getResponsibleSourcePoolDefinitionId($question_id);

			// make some noise (with question id and responsible source pool definition)
		}
		 */
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
					($this->getSettings()->getQualifiedTest() == $session->getRefId()) ? 
						ilLOUserResults::TYPE_QUALIFIED :
						ilLOUserResults::TYPE_INITIAL
			);
			
			include_once './Modules/Course/classes/Objectives/class.ilLOUtils.php';
			
			$limit = ilLOUtils::lookupObjectiveRequiredPercentage(
					$this->container_id, 
					$run->getObjectiveId(),
					($this->getSettings()->getQualifiedTest() == $session->getRefId()) ?
						ilLOSettings::TYPE_TEST_QUALIFIED :
						ilLOSettings::TYPE_TEST_INITIAL,
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
					($this->getSettings()->getQualifiedTest() == $session->getRefId()) ?
						ilLOUserResults::TYPE_QUALIFIED :
						ilLOUserResults::TYPE_INITIAL,
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
		if($session->getRefId() == $this->getSettings()->getInitialTest())
		{
			$GLOBALS['ilLog']->write(__METHOD__.': is intial');
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
	protected function updateQuestionResult(ilTestSession $session, assQuestion $qst)
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
						($this->getSettings()->getQualifiedTest() == $session->getRefId()) ? 
							ilLOUserResults::TYPE_QUALIFIED :
							ilLOUserResults::TYPE_INITIAL
				);
				
				$ur = new ilLOUserResults($this->container_id,$this->user_id);
				$ur->saveObjectiveResult(
						$run->getObjectiveId(), 
						($this->getSettings()->getQualifiedTest() == $session->getRefId()) ?
							ilLOUserResults::TYPE_QUALIFIED :
							ilLOUserResults::TYPE_INITIAL,
						ilLOUtils::isCompleted(
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
				
			}
		}
		return false;
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
		#$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($this->run,true));
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
		if($seq instanceof ilTestSequenceFixedQuestionSet)
		{
			$this->updateFixedQuestions($session, $seq);
		}
		if($seq instanceof ilTestSequenceRandomQuestionSet)
		{
			$this->updateRandomQuestions($session, $seq);
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
			$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($qst,true));
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
		$questionList = new ilAssQuestionList($ilDB, $lng, $ilPluginAdmin, $testObjId);

		$questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
		$questionList->setQuestionIdsFilter($questionIds);

		$questionList->load();

		return $questionList->getQuestionDataArray();
	}
}
?>