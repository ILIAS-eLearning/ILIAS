<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestScoring
 *
 * This class holds a mechanism to get the scoring for 
 * - a test,
 * - a user in a test,
 * - a pass in a users passes in a test, or
 * - a question in a pass in a users passes in a test.
 * 
 * Warning:
 * Please use carefully, this is one of the classes that may cause funny spikes on your servers load graph on large
 * datasets in the test.
 * 
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup 	ModulesTest
 */
class ilTestScoring 
{
	/** @var $test ilObjTest */
	protected $test;
	
	public function __construct(ilObjTest $test)
	{
		$this->test = $test;
	}

	public function recalculateSolutions()
	{
		$participants = $this->test->getCompleteEvaluationData(false)->getParticipants();
		if (is_array($participants))
		{
			require_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			foreach ($participants as $active_id => $userdata)
			{
				if (is_object($userdata) && is_array($userdata->getPasses()))
				{
					$this->recalculatePasses( $userdata, $active_id );
				}
				assQuestion::_updateTestResultCache($active_id);
			}
		}
	}

	/**
	 * @param $userdata
	 * @param $active_id
	 */
	public function recalculatePasses($userdata, $active_id)
	{
		$passes = $userdata->getPasses();
		foreach ($passes as $pass => $passdata)
		{
			if (is_object( $passdata ))
			{
				$this->recalculatePass( $passdata, $active_id, $pass );
			}
		}
	}

	/**
	 * @param $passdata
	 * @param $active_id
	 * @param $pass
	 */
	public function recalculatePass($passdata, $active_id, $pass)
	{
		$questions = $passdata->getAnsweredQuestions();
		if (is_array( $questions ))
		{
			foreach ($questions as $questiondata)
			{
				$question_gui = $this->test->createQuestionGUI( "", $questiondata['id'] );
				$this->recalculateQuestionScore( $question_gui, $active_id, $pass, $questiondata );
			}
		}
	}

	/**
	 * @param $question_gui
	 * @param $active_id
	 * @param $pass
	 * @param $questiondata
	 */
	public function recalculateQuestionScore($question_gui, $active_id, $pass, $questiondata)
	{
		if (is_object( $question_gui ))
		{
			$reached = $question_gui->object->calculateReachedPoints( $active_id, $pass );
			$actual_reached = $question_gui->object->adjustReachedPointsByScoringOptions($reached, $active_id, $pass);
			assQuestion::_setReachedPoints( $active_id,
											$questiondata['id'],
											$actual_reached,
											$question_gui->object->getMaximumPoints(),
											$pass,
											false,
											true
			);
		}
	}
}