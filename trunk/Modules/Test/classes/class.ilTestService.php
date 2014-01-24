<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Service class for tests.
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTest
 */
class ilTestService
{
	/**
	 * @access protected
	 * @var ilObjTest
	 */
	protected $object = null;
	
	/**
	 * @access public
	 * @param	ilObjTest $a_object
	 */
	public function __construct(ilObjTest $a_object)
	{
		$this->object = $a_object;
	}

	/**
	 * @access public
	 * @global	ilObjUser	$ilUser
	 * @param	integer		$active_id
	 * @param	boolean		$short
	 * @return	array		$passOverwiewData
	 */
	public function getPassOverviewData($active_id, $short = false)
	{
		$passOverwiewData = array();
		
		global $ilUser;

		$scoredPass = $this->object->_getResultPass($active_id);
		$lastPass = $this->object->_getPass($active_id);

		$testPercentage = 0;
		$testReachedPoints = 0;
		$testMaxPoints = 0;

		for( $pass = 0; $pass <= $lastPass; $pass++)
		{
			$passFinishDate = $this->object->getPassFinishDate($active_id, $pass);
			
			if( $passFinishDate <= 0 )
			{
				continue;
			}

			if( !$short )
			{
				$resultData =& $this->object->getTestResult($active_id, $pass);

				if (!$resultData["pass"]["total_max_points"])
				{
					$passPercentage = 0;
				}
				else
				{
					$passPercentage = ($resultData["pass"]["total_reached_points"]/$resultData["pass"]["total_max_points"])*100;
				}

				$passMaxPoints = $resultData["pass"]["total_max_points"];
				$passReachedPoints = $resultData["pass"]["total_reached_points"];
				
				$passAnsweredQuestions = $this->object->getAnsweredQuestionCount($active_id, $pass);
				$passTotalQuestions = count($resultData) - 2;

				if( $pass == $scoredPass )
				{
					$isScoredPass = true;
					
					if (!$resultData["test"]["total_max_points"])
					{
						$testPercentage = 0;
					}
					else
					{
						$testPercentage = ($resultData["test"]["total_reached_points"]/$resultData["test"]["total_max_points"])*100;
					}
					
					$testMaxPoints = $resultData["test"]["total_max_points"];
					$testReachedPoints = $resultData["test"]["total_reached_points"];
					
					$passOverwiewData['test'] = array(
						'active_id' => $active_id,
						'scored_pass' => $scoredPass,
						'max_points' => $testMaxPoints,
						'reached_points' => $testReachedPoints,
						'percentage' => $testPercentage
					);
				}
				else $isScoredPass = false;
				
				$passOverwiewData['passes'][] = array(
					'active_id' => $active_id,
					'pass' => $pass,
					'finishdate' => $passFinishDate,
					'max_points' => $passMaxPoints,
					'reached_points' => $passReachedPoints,
					'percentage' => $passPercentage,
					'answered_questions' => $passAnsweredQuestions,
					'total_questions' => $passTotalQuestions,
					'is_scored_pass' => $isScoredPass
				);
			}
		}

		return $passOverwiewData;
	}
	
	/**
	 * Returns the list of answers of a users test pass and offers a scoring option
	 *
	 * @access public
	 * @param integer $active_id Active ID of the active user
	 * @param integer $pass Test pass
	 * @return string HTML code of the list of answers
	 */
	public function getManScoringQuestionGuiList($activeId, $pass)
	{
		include_once "./Modules/Test/classes/class.ilObjAssessmentFolder.php";
		$manScoringQuestionTypes = ilObjAssessmentFolder::_getManualScoring();

		$testResultData = $this->object->getTestResult($activeId, $pass);
		
		$manScoringQuestionGuiList = array();
		
		foreach($testResultData as $questionData)
		{
			if( !isset($questionData['qid']) )
			{
				continue;
			}
			
			if( !isset($questionData['type']) )
			{
				throw new ilTestException('no question type given!');
			}

			$questionGUI = $this->object->createQuestionGUI("", $questionData['qid']);
			
			if( !in_array($questionGUI->object->getQuestionTypeID(), $manScoringQuestionTypes) )
			{
				continue;
			}
			
			$manScoringQuestionGuiList[ $questionData['qid'] ] = $questionGUI;
		}
		
		return $manScoringQuestionGuiList;
	}
	
	/**
	 * reads the flag wether manscoring is done for the given test active or not
	 * from the global settings (scope: assessment / key: manscoring_done_<activeId>)
	 *
	 * @access public
	 * @static
	 * @param integer $activeId
	 * @return boolean $manScoringDone
	 */
	public static function isManScoringDone($activeId)
	{
		$assessmentSetting = new ilSetting("assessment");
		return $assessmentSetting->get("manscoring_done_" . $activeId, false);
	}
	
	/**
	 * stores the flag wether manscoring is done for the given test active or not
	 * within the global settings (scope: assessment / key: manscoring_done_<activeId>)
	 *
	 * @access public
	 * @static
	 * @param integer $activeId
	 * @param boolean $manScoringDone 
	 */
	public static function setManScoringDone($activeId, $manScoringDone)
	{
		$assessmentSetting = new ilSetting("assessment");
		$assessmentSetting->set("manscoring_done_" . $activeId, (bool)$manScoringDone);
	}
}

