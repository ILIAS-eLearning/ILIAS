<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Modules/Test/classes/inc.AssessmentConstants.php';
include_once 'Modules/Test/classes/class.ilTestScoringGUI.php';

/**
 * ilTestScoringByQuestionsGUI
 * @author     Michael Jansen <mjansen@databay.de>
 * @author     Björn Heyser <bheyser@databay.de>
 * @version    $Id$
 * @ingroup    ModulesTest
 * @extends    ilTestServiceGUI
 */
class ilTestScoringByQuestionsGUI extends ilTestScoringGUI
{
	/**
	 * @param ilObjTest $a_object
	 */
	public function __construct(ilObjTest $a_object)
	{
		parent::__construct($a_object);
	}

	/**
	 * @return mixed
	 */
	public function executeCommand()
	{
		/**
		 * @var $ilAccessHandler
		 */
		global $ilAccess;

		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('cannot_edit_test'), true);
			$this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
		}

		require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
		if(!ilObjAssessmentFolder::_mananuallyScoreableQuestionTypesExists())
		{
			ilUtil::sendFailure($this->lng->txt('manscoring_not_allowed'), true);
			$this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
		}

		$cmd        = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);
		if(strlen($cmd) == 0)
		{
			$this->ctrl->redirect($this, 'manscoring');
		}

		$cmd = $this->getCommand($cmd);
		$this->buildSubTabs('man_scoring_by_qst');
		switch($next_class)
		{
			default:
				$ret = $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	 *
	 */
	private function showManScoringByQuestionParticipantsTable($manPointsPost = array())
	{
		/**
		 * @var $tpl      ilTemplate
		 * @var $ilAccess ilAccessHandler
		 */
		global $tpl, $ilAccess;

		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
			$this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
		}

		include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		iljQueryUtil::initjQuery();

		include_once 'Services/YUI/classes/class.ilYuiUtil.php';
		ilYuiUtil::initPanel();
		ilYuiUtil::initOverlay();
		$tpl->addJavascript('./Services/UIComponent/Overlay/js/ilOverlay.js');
		$tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
		$tpl->addJavaScript("./Services/Form/js/Form.js");
		$tpl->addCss($this->object->getTestStyleLocation("output"), "screen");

		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI.php';
		$table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
		
		$table->setManualScoringPointsPostData($manPointsPost);

		$qst_id  = $table->getFilterItemByPostVar('question')->getValue();
		$pass_id = $table->getFilterItemByPostVar('pass')->getValue();

		$table_data = array();

		$selected_questionData = null;

		if(is_numeric($qst_id))
		{
			$scoring = ilObjAssessmentFolder::_getManualScoring();
			$info = assQuestion::_getQuestionInfo($qst_id);
			$selected_questionData = $info;
			$type = $info["question_type_fi"];
			if(in_array($type, $scoring))
			{
				$selected_questionData = $info;
			}
		}

		if($selected_questionData && is_numeric($pass_id))
		{
			$data = $this->object->getCompleteEvaluationData(FALSE);
			
			foreach($data->getParticipants() as $active_id => $participant)
			{
				$testResultData = $this->object->getTestResult($active_id, $pass_id - 1);
				foreach($testResultData as $questionData)
				{
					if( !isset($questionData['qid']) || $questionData['qid'] != $selected_questionData['question_id'] )
					{
						continue;
					}

					$table_data[] = array(
						'pass_id'        => $pass_id - 1,
						'active_id'      => $active_id,
						'qst_id'         => $questionData['qid'],
						'reached_points' => assQuestion::_getReachedPoints($active_id, $questionData['qid'], $pass_id - 1),
						'maximum_points' => assQuestion::_getMaximumPoints($questionData['qid']),
						'participant'    => $participant,
					);
				}
			}
		}
		else
		{
			$table->disable('header');
		}

		if($selected_questionData)
		{
			$maxpoints = assQuestion::_getMaximumPoints($selected_questionData['question_id']);
			if($maxpoints == 1)
			{
				$maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('point') . ')';
			}
			else
			{
				$maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('points') . ')';
			}
			$table->setTitle($this->lng->txt('tst_man_scoring_by_qst') . ': ' . $selected_questionData['title'] . $maxpoints . ' ['. $this->lng->txt('question_id_short') . ': ' . $selected_questionData['question_id']  . ']');
		}
		else
		{
			$table->setTitle($this->lng->txt('tst_man_scoring_by_qst'));
		}

		$table->setData($table_data);
		$tpl->setContent($table->getHTML());
	}
	
	public function saveManScoringByQuestion()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		global  $ilAccess, $lng;

		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
			$this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
		}
		
		if(!isset($_POST['scoring']) || !is_array($_POST['scoring']))
		{
			ilUtil::sendFailure($this->lng->txt('tst_save_manscoring_failed_unknown'));
			$this->showManScoringByQuestionParticipantsTable();
			return;
		}

		include_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		include_once 'Modules/Test/classes/class.ilObjTestAccess.php';
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';

		$oneExceededMaxPoints = false;
		$manPointsPost = array();
		$skipParticipant = array();
		$maxPointsByQuestionId = array();
		foreach($_POST['scoring'] as $pass => $active_ids)
		{
			foreach((array)$active_ids as $active_id => $questions)
			{
				// check for existing test result data
				if( !$this->object->getTestResult($active_id, $pass) )
				{
					if( !isset($skipParticipant[$pass]) )
					{
						$skipParticipant[$pass] = array();
					}
					
					$skipParticipant[$pass][$active_id] = true;
					
					continue;
				}
				
				foreach((array)$questions as $qst_id => $reached_points)
				{
					if( !isset($manPointsPost[$pass]) )
					{
						$manPointsPost[$pass] = array();
					}

					if( !isset($manPointsPost[$pass][$active_id]) )
					{
						$manPointsPost[$pass][$active_id] = array();
					}

					$maxPointsByQuestionId[$qst_id] = assQuestion::_getMaximumPoints($qst_id);
					
					if( $reached_points > $maxPointsByQuestionId[$qst_id] )
					{
						$oneExceededMaxPoints = true;
					}
						
					$manPointsPost[$pass][$active_id][$qst_id] = $reached_points;
				}
			}
		}
		
		if( $oneExceededMaxPoints )
		{
			ilUtil::sendFailure(sprintf($this->lng->txt('tst_save_manscoring_failed'), $pass + 1));
			$this->showManScoringByQuestionParticipantsTable($manPointsPost);
			return;
		}
		
		$changed_one = false;
		foreach($_POST['scoring'] as $pass => $active_ids)
		{
			foreach((array)$active_ids as $active_id => $questions)
			{
				$update_participant = false;
				
				if($skipParticipant[$pass][$active_id])
				{
					continue;
				}

				foreach((array)$questions as $qst_id => $reached_points)
				{
					$update_participant = assQuestion::_setReachedPoints(
						$active_id, $qst_id, $reached_points, $maxPointsByQuestionId[$qst_id], $pass, 1, $this->object->areObligationsEnabled()
					);
				}

				if($update_participant)
				{
					$changed_one = true;
					
					ilLPStatusWrapper::_updateStatus(
						$this->object->getId(), ilObjTestAccess::_getParticipantId($active_id)
					);
				}
			}
		}

		if($changed_one)
		{
			if($this->object->getAnonymity() == 0)
			{
				$user_name 				= $user_name = ilObjUser::_lookupName( ilObjTestAccess::_getParticipantId($active_id));
				$name_real_or_anon 		= $user_name['firstname'].' '. $user_name['lastname'];
			}
			else
			{
				$name_real_or_anon 		= $lng->txt('anonymous');
			}
			ilUtil::sendSuccess(sprintf($this->lng->txt('tst_saved_manscoring_successfully'), $pass + 1,$name_real_or_anon), true);

			require_once './Modules/Test/classes/class.ilTestScoring.php';
			$scorer = new ilTestScoring($this->object);
			$scorer->setPreserveManualScores(true);
			$scorer->recalculateSolutions();
		}
		
		$this->showManScoringByQuestionParticipantsTable();
	}

	/**
	 * 
	 */
	private function applyManScoringByQuestionFilter()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI.php';
		$table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->showManScoringByQuestionParticipantsTable();
	}

	/**
	 * 
	 */
	private function resetManScoringByQuestionFilter()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI.php';
		$table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
		$table->resetOffset();
		$table->resetFilter();
		$this->showManScoringByQuestionParticipantsTable();
	}

	private function getAnswerDetail()
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		global $ilAccess;
		
		if(!$ilAccess->checkAccess('write', '', $this->ref_id))
		{
			exit();
		}

		$active_id   = $_GET['active_id'];
		$pass        = $_GET['pass_id'];
		$question_id = $_GET['qst_id'];

		$data = $this->object->getCompleteEvaluationData(FALSE);
		$participant = $data->getParticipant($active_id);
		
		$question_gui = $this->object->createQuestionGUI('', $question_id);

		$tmp_tpl = new ilTemplate('tpl.il_as_tst_correct_solution_output.html', TRUE, TRUE, 'Modules/Test');
		$result_output = $question_gui->getSolutionOutput($active_id, $pass, FALSE, FALSE, FALSE, $this->object->getShowSolutionFeedback());
		$tmp_tpl->setVariable('TEXT_YOUR_SOLUTION', $this->lng->txt('answers_of') .' '. $participant->getName());
		$maxpoints = $question_gui->object->getMaximumPoints();

		$add_title = ' ['. $this->lng->txt('question_id_short') . ': ' . $question_id  . ']';
		
		if($maxpoints == 1)
		{
			$tmp_tpl->setVariable('QUESTION_TITLE', $this->object->getQuestionTitle($question_gui->object->getTitle()) . ' (' . $maxpoints . ' ' . $this->lng->txt('point') . ')' . $add_title);
		}
		else
		{
			$tmp_tpl->setVariable('QUESTION_TITLE', $this->object->getQuestionTitle($question_gui->object->getTitle()) . ' (' . $maxpoints . ' ' . $this->lng->txt('points') . ')' . $add_title);
		}
		$tmp_tpl->setVariable('SOLUTION_OUTPUT', $result_output);
		$tmp_tpl->setVariable('RECEIVED_POINTS', sprintf($this->lng->txt('part_received_a_of_b_points'), $question_gui->object->getReachedPoints($active_id, $pass), $maxpoints));

		echo $tmp_tpl->get();
		exit();
	}
}