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
	/** @var ilObjTest $test */
	protected $test;
	
	/** @var ilObjTestGUI $testGUI*/
	protected $testGUI;

	/** @var bool $preserve_manual_scores */
	protected $preserve_manual_scores;

	public function __construct(ilObjTest $test)
	{
		$this->test = $test;
		$this->preserve_manual_scores = false;

		require_once './Modules/Test/classes/class.ilObjTestGUI.php';
		$this->testGUI = new ilObjTestGUI();
	}

	/**
	 * @param boolean $preserve_manual_scores
	 */
	public function setPreserveManualScores( $preserve_manual_scores )
	{
		$this->preserve_manual_scores = $preserve_manual_scores;
	}

	/**
	 * @return boolean
	 */
	public function getPreserveManualScores()
	{
		return $this->preserve_manual_scores;
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
		require_once './Modules/Test/classes/class.ilTestEvaluationGUI.php'; // Below!
		require_once './Modules/Test/classes/class.ilTestPDFGenerator.php';
		require_once './Modules/Test/classes/class.ilTestArchiver.php';

		$passes = $userdata->getPasses();
		foreach ($passes as $pass => $passdata)
		{
			if (is_object( $passdata ))
			{
				$this->recalculatePass( $passdata, $active_id, $pass );
				if ($this->test->getEnableArchiving())
				{
					// requires out of the loop!
					$test_evaluation_gui = new ilTestEvaluationGUI($this->test);
					$result_array = $this->test->getTestResult($active_id, $pass);
					$overview = $test_evaluation_gui->getPassListOfAnswers($result_array, $active_id, $pass, true, false, false, true);
					$filename = ilUtil::getWebspaceDir() . '/assessment/scores-'.$this->test->getId() . '-' . $active_id . '-' . $pass . '.pdf';
					ilTestPDFGenerator::generatePDF($overview, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);
					$archiver = new ilTestArchiver($this->test->getId());
					$archiver->handInTestResult($active_id, $pass, $filename);
					unlink($filename);
				}
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
		/** @var assQuestion $question_gui */
		if (is_object( $question_gui ))
		{
			$reached = $question_gui->object->calculateReachedPoints( $active_id, $pass );
			$actual_reached = $question_gui->object->adjustReachedPointsByScoringOptions($reached, $active_id, $pass);

			if ($this->preserve_manual_scores == true && $questiondata['manual'] == '1')
			{
				// Do we need processing here?
			}
			else
			{
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

	/**
	 * @return string HTML with the best solution output.
	 */
	public function calculateBestSolutionForTest()
	{
		$solution = '';
		foreach ($this->test->getAllQuestions() as $question)
		{
			/** @var AssQuestionGUI $question_gui */
			$question_gui = $this->test->createQuestionGUI("", $question['question_id'] );
			$solution .= $question_gui->getSolutionOutput(0, null, true, true, false, false, true, false);
		}
		
		return $solution;
	}
}