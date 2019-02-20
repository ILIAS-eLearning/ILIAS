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
	 * @return string
	 */
	protected function getDefaultCommand()
	{
		return 'showManScoringByQuestionParticipantsTable';
	}
	
	/**
	 * @return string
	 */
	protected function getActiveSubTabId()
	{
		return 'man_scoring_by_qst';
	}
	
	/**
	 * @param array $manPointsPost
	 */
	protected function showManScoringByQuestionParticipantsTable($manPointsPost = array())
	{
		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI.php';
		include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		include_once 'Services/YUI/classes/class.ilYuiUtil.php';

		global $DIC; /* @var ILIAS\DI\Container $DIC */
		$tpl 		= $DIC->ui()->mainTemplate();
		$ilAccess 	= $DIC['ilAccess'];
		
		$DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_MANUAL_SCORING);

		if(
			!$ilAccess->checkAccess("write", "", $this->ref_id) &&
			!$ilAccess->checkAccess("man_scoring_access", "", $this->ref_id)
		){
			ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
			$this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
		}

		iljQueryUtil::initjQuery();
		ilYuiUtil::initPanel();
		ilYuiUtil::initOverlay();

		$mathJaxSetting = new ilSetting('MathJax');
		if($mathJaxSetting->get("enable")){
			$tpl->addJavaScript($mathJaxSetting->get("path_to_mathjax"));
		}

		$tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
		$tpl->addJavaScript("./Services/Form/js/Form.js");
		$tpl->addJavascript('./Services/UIComponent/Modal/js/Modal.js');
		$tpl->addCss($this->object->getTestStyleLocation("output"), "screen");
		$this->lng->toJSMap(array('answer' => $this->lng->txt('answer')));
		$table 				= new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
		$table->setManualScoringPointsPostData($manPointsPost);
		$qst_id  				= $table->getFilterItemByPostVar('question')->getValue();
		$passNr 				= $table->getFilterItemByPostVar('pass')->getValue();
		$finalized_filter 		= $table->getFilterItemByPostVar('finalize_evaluation')->getValue();
		$table_data 			= array();
		$selected_questionData 	= null;

		if(is_numeric($qst_id)){

			$scoring 				= ilObjAssessmentFolder::_getManualScoring();
			$info 					= assQuestion::_getQuestionInfo($qst_id);
			$selected_questionData 	= $info;
			$type 					= $info["question_type_fi"];

			if(in_array($type, $scoring)){
				$selected_questionData = $info;
			}
		}

		$complete_feedback = $this->object->getCompleteManualFeedback($qst_id);

		if($selected_questionData && is_numeric($passNr)){
			require_once 'Modules/Test/classes/class.ilTestParticipantData.php';

			$data 				= $this->object->getCompleteEvaluationData(FALSE);
			$participants 		= $data->getParticipants();
			$participantData 	= new ilTestParticipantData($DIC->database(), $DIC->language());
			$participantData->setActiveIdsFilter(array_keys($data->getParticipants()));
			$participantData->setParticipantAccessFilter(
				ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->ref_id)
			);
			
			$participantData->load($this->object->getTestId());
				
			foreach($participantData->getActiveIds() as $active_id){
				$participant 	= $participants[$active_id];
				$testResultData = $this->object->getTestResult($active_id, $passNr - 1);
				foreach($testResultData as $questionData){
					$feedback = array();

					if(
						array_key_exists($active_id, $complete_feedback) &&
						array_key_exists($passNr - 1, $complete_feedback[$active_id]) &&
						array_key_exists($qst_id, $complete_feedback[$active_id][$passNr - 1])
					){
						$feedback = $complete_feedback[$active_id][$passNr - 1][$qst_id];
					}

					if( !isset($questionData['qid']) || $questionData['qid'] != $selected_questionData['question_id'] ){
						continue;
					}

					if(
						($finalized_filter == 1 && $feedback['finalized_evaluation'] != 1) ||
						(
							array_key_exists('finalized_evaluation', $feedback) &&
							($finalized_filter == 2 && $feedback['finalized_evaluation'] == 1)
						)
					)
					{
						continue;
					}

					$table_data[] = array(
						'pass_id'        => $passNr - 1,
						'active_id'      => $active_id,
						'qst_id'         => $questionData['qid'],
						'reached_points' => assQuestion::_getReachedPoints($active_id, $questionData['qid'], $passNr - 1),
						'maximum_points' => assQuestion::_getMaximumPoints($questionData['qid']),
						'participant'    => $participant,
						'feedback'       => $feedback,
					);
				}
			}
		}else{
			$table->disable('header');
		}

		$table->setTitle($this->lng->txt('tst_man_scoring_by_qst'));
		if($selected_questionData){
			$maxpoints = assQuestion::_getMaximumPoints($selected_questionData['question_id']);
			$table->setCurQuestionMaxPoints($maxpoints);
			$maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('points') . ')';
			if($maxpoints == 1){
				$maxpoints = ' (' . $maxpoints . ' ' . $this->lng->txt('point') . ')';
			}

			$table->setTitle(
				$this->lng->txt('tst_man_scoring_by_qst') . ': ' . $selected_questionData['title'] . $maxpoints .
				' ['. $this->lng->txt('question_id_short') . ': ' . $selected_questionData['question_id']  . ']'
			);
		}

		$table->setData($table_data);
		$tpl->setContent($table->getHTML());
	}

	/**
	 * @param bool $ajax
	 */
	protected function saveManScoringByQuestion($ajax = false)
	{
		global $DIC;
		$ilAccess = $DIC['ilAccess'];

		if(
			!$ilAccess->checkAccess("write", "", $this->ref_id) &&
			!$ilAccess->checkAccess("man_scoring_access", "", $this->ref_id)
		){
			if($ajax){
				echo $this->lng->txt('cannot_edit_test');
				exit();
			}

			ilUtil::sendInfo($this->lng->txt('cannot_edit_test'), true);
			$this->ctrl->redirectByClass('ilobjtestgui', 'infoScreen');
		}

		if(!isset($_POST['scoring']) || !is_array($_POST['scoring'])){
			ilUtil::sendFailure($this->lng->txt('tst_save_manscoring_failed_unknown'));
			$this->showManScoringByQuestionParticipantsTable();
			return;
		}

		require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
		include_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		include_once 'Modules/Test/classes/class.ilObjTestAccess.php';
		include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';

		$pass 					= key($_POST['scoring']);
		$activeData 			= current($_POST['scoring']);
		$participantData 		= new ilTestParticipantData($DIC->database(), $DIC->language());
		$oneExceededMaxPoints 	= false;
		$manPointsPost 			= array();
		$skipParticipant 		= array();
		$maxPointsByQuestionId	= array();

		$participantData->setActiveIdsFilter(array_keys($activeData));
		$participantData->setParticipantAccessFilter(
			ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->ref_id)
		);
		$participantData->load($this->object->getTestId());

		foreach($participantData->getActiveIds() as $active_id){
			$questions = $activeData[$active_id];
			
			// check for existing test result data
			if(!$this->object->getTestResult($active_id, $pass)){
				if(!isset($skipParticipant[$pass])){
					$skipParticipant[$pass] = array();
				}
				$skipParticipant[$pass][$active_id] = true;
				
				continue;
			}
			
			foreach((array)$questions as $qst_id => $reached_points){
				$this->saveFeedback($active_id, $qst_id, $pass);

				if( !isset($manPointsPost[$pass]) ){
					$manPointsPost[$pass] = array();
				}
				if( !isset($manPointsPost[$pass][$active_id]) ){
					$manPointsPost[$pass][$active_id] = array();
				}
				$maxPointsByQuestionId[$qst_id] 			= assQuestion::_getMaximumPoints($qst_id);
				$manPointsPost[$pass][$active_id][$qst_id] 	= $reached_points;
				if( $reached_points > $maxPointsByQuestionId[$qst_id] ){
					$oneExceededMaxPoints = true;
				}
			}
		}
		
		if($oneExceededMaxPoints){
			ilUtil::sendFailure(sprintf($this->lng->txt('tst_save_manscoring_failed'), $pass + 1));
			$this->showManScoringByQuestionParticipantsTable($manPointsPost);
			return;
		}
		
		$changed_one 						= false;
		$lastAndHopefullyCurrentQuestionId 	= null;

		foreach($participantData->getActiveIds() as $active_id){
			$questions 			= $activeData[$active_id];
			$update_participant = false;
			
			if($skipParticipant[$pass][$active_id]){
				continue;
			}

			foreach((array)$questions as $qst_id => $reached_points){
				$update_participant = assQuestion::_setReachedPoints(
					$active_id, $qst_id, $reached_points, $maxPointsByQuestionId[$qst_id], $pass, 1, $this->object->areObligationsEnabled()
				);
			}

			if($update_participant){
				$changed_one 						= true;
				$lastAndHopefullyCurrentQuestionId 	= $qst_id;

				ilLPStatusWrapper::_updateStatus(
					$this->object->getId(), ilObjTestAccess::_getParticipantId($active_id)
				);
			}
		}

		$correction_feedback	= array();
		$correction_points 		= 0;

		if($changed_one){
			require_once './Modules/Test/classes/class.ilTestScoring.php';

			$qTitle = '';
			if($lastAndHopefullyCurrentQuestionId){
				$question 	= assQuestion::_instantiateQuestion($lastAndHopefullyCurrentQuestionId);
				$qTitle 	= $question->getTitle();
			}
			$msg 	= sprintf(
				$this->lng->txt('tst_saved_manscoring_by_question_successfully'), $qTitle, $pass + 1
			);

			ilUtil::sendSuccess($msg, true);

			$scorer = new ilTestScoring($this->object);
			$scorer->setPreserveManualScores(true);
			$scorer->recalculateSolutions();
			if(isset($active_id)){
				$scorer->recalculateSolution($active_id, $pass);
				$correction_feedback 	= $this->object->getSingleManualFeedback($active_id, $qst_id, $pass);
				$correction_points 		= assQuestion::_getReachedPoints($active_id, $qst_id, $pass);
			}else{
				$scorer->recalculateSolutions();
			}
		}

		if($ajax && is_array($correction_feedback) && count($correction_feedback) > 0){
			$correction_feedback['finalized_by'] 		= ilObjUser::_lookupFullname($correction_feedback['finalized_by_usr_id']);
			$correction_feedback['finalized_on_date'] 	= '';
			if(strlen($correction_feedback['finalized_tstamp']) > 0){
				$time = new ilDateTime($correction_feedback['finalized_tstamp'], IL_CAL_UNIX);
				$correction_feedback['finalized_on_date'] = $time->get(IL_CAL_DATETIME);
			}

			echo json_encode(array( 'feedback' => $correction_feedback, 'points' => $correction_points));
			exit();
		}else{
			$this->showManScoringByQuestionParticipantsTable();
		}
	}

	/**
	 * 
	 */
	protected function applyManScoringByQuestionFilter()
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
	protected function resetManScoringByQuestionFilter()
	{
		require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI.php';
		$table = new ilTestManScoringParticipantsBySelectedQuestionAndPassTableGUI($this);
		$table->resetOffset();
		$table->resetFilter();
		$this->showManScoringByQuestionParticipantsTable();
	}

	protected function getAnswerDetail()
	{
		$active_id   = (int)$_GET['active_id'];
		$pass        = (int)$_GET['pass_id'];
		$question_id = (int)$_GET['qst_id'];
		
		if(!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($active_id)){
			exit; // illegal ajax call
		}
		
		$data 			= $this->object->getCompleteEvaluationData(FALSE);
		$participant 	= $data->getParticipant($active_id);
		$question_gui 	= $this->object->createQuestionGUI('', $question_id);
		$result_output  = $question_gui->getSolutionOutput(
			$active_id,
			$pass,
			FALSE,
			FALSE,
			FALSE,
			$this->object->getShowSolutionFeedback(),
			FALSE,
			TRUE
		);
		$maxpoints 		= $question_gui->object->getMaximumPoints();
		$tmp_tpl 		= new ilTemplate('tpl.il_as_tst_correct_solution_output.html', TRUE, TRUE, 'Modules/Test');
		$this->appendUserNameToModal($tmp_tpl, $participant);
		$this->appendQuestionTitleToModal($tmp_tpl, $question_id, $maxpoints, $question_gui->object->getTitle());
		$this->appendSolutionAndPointsToModal(
			$tmp_tpl,
			$result_output,
			$question_gui->object->getReachedPoints($active_id, $pass),
			$maxpoints
		);
		$this->appendFormToModal($tmp_tpl, $pass, $active_id, $question_id, $maxpoints);
		$tmp_tpl->setVariable('TEXT_YOUR_SOLUTION', $this->lng->txt('answers_of') .' '. $participant->getName());
		$add_title 		= ' ['. $this->lng->txt('question_id_short') . ': ' . $question_id  . ']';
		$question_title = $this->object->getQuestionTitle($question_gui->object->getTitle());
		$lng 			= $this->lng->txt('points');
		if($maxpoints == 1){
			$lng = $this->lng->txt('point');
		}
		$tmp_tpl->setVariable(
			'QUESTION_TITLE',
			$question_title . ' (' . $maxpoints . ' ' . $lng . ')' . $add_title
		);
		$tmp_tpl->setVariable('SOLUTION_OUTPUT', $result_output);
		$tmp_tpl->setVariable(
					'RECEIVED_POINTS',
					sprintf(
						$this->lng->txt('part_received_a_of_b_points'),
						$question_gui->object->getReachedPoints($active_id, $pass),
						$maxpoints
					)
		);

		echo $tmp_tpl->get();
		exit();
	}

	/**
	 *
	 */
	public function checkConstraintsBeforeSaving()
	{
		$this->saveManScoringByQuestion(true);
	}

	/**
	 *
	 */
	private function enforceAccessConstraint()
	{
		global $DIC;
		$ilAccess = $DIC['ilAccess'];

		if(
			!$ilAccess->checkAccess("write", "", $this->ref_id) &&
			!$ilAccess->checkAccess("man_scoring_access", "", $this->ref_id)
		){
			exit();
		}
	}

	/**
	 * @param ilTemplate $tmp_tpl
	 * @param $participant
	 */
	private function appendUserNameToModal($tmp_tpl, $participant)
	{
		global $DIC;
		$ilAccess = $DIC['ilAccess'];

		$tmp_tpl->setVariable(
			'TEXT_YOUR_SOLUTION',
			$this->lng->txt('answers_of') . ' ' . $participant->getName()
		);
		if(
			$this->object->isFullyAnonymized() ||
			(
				$this->object->getAnonymity() == 2 &&
				!$ilAccess->checkAccess('write', '', $this->object->getRefId())
			)
		){
			$tmp_tpl->setVariable(
				'TEXT_YOUR_SOLUTION',
				$this->lng->txt('answers_of') . ' ' . $this->lng->txt('anonymous')
			);
		}
	}

	/**
	 * @param ilTemplate $tmp_tpl
	 * @param $question_id
	 * @param $max_points
	 * @param $title
	 */
	private function appendQuestionTitleToModal($tmp_tpl, $question_id, $max_points, $title)
	{
		$add_title 		= ' [' . $this->lng->txt('question_id_short') . ': ' . $question_id . ']';
		$question_title = $this->object->getQuestionTitle($title);
		$lng 			= $this->lng->txt('points');
		if($max_points == 1){
			$lng = $this->lng->txt('point');
		}
		$tmp_tpl->setVariable(
			'QUESTION_TITLE',
			$question_title . ' (' . $max_points . ' ' . $lng . ')' . $add_title
		);
	}

	/**
	 * @param ilTemplate $tmp_tpl
	 * @param $pass
	 * @param $active_id
	 * @param $question_id
	 * @param $max_points
	 */
	private function appendFormToModal($tmp_tpl, $pass, $active_id, $question_id, $max_points)
	{
		require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
		require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

		global $DIC;
		$ilCtrl 			= $DIC['ilCtrl'];
		$post_var 			= '[' . $pass . '][' . $active_id . '][' . $question_id . ']';
		$scoring_post_var 	= 'scoring'.$post_var;
		$reached_points 	= assQuestion::_getReachedPoints($active_id, $question_id, $pass);
		$form             	= new ilPropertyFormGUI();
		$feedback 			= $this->object->getSingleManualFeedback($active_id, $question_id, $pass);
		$disable 			= false;
		$form->setFormAction($ilCtrl->getFormAction($this, 'showManScoringByQuestionParticipantsTable'));
		$form->setTitle($this->lng->txt('manscoring'));

		if(array_key_exists('finalized_evaluation', $feedback) && $feedback['finalized_evaluation'] == 1){
			$disable 			= true;
			$hidden_points 		= new ilHiddenInputGUI($scoring_post_var);
			$scoring_post_var 	= $scoring_post_var . '_disabled';
			$hidden_points->setValue($reached_points);
			$form->addItem($hidden_points);
		}

		$text_area 			= new ilTextAreaInputGUI($this->lng->txt('set_manual_feedback'), 'm_feedback' . $post_var);
		$feedback_text 		= '';
		if(array_key_exists('feedback', $feedback)){
			$feedback_text 		= $feedback['feedback'];
		}
		$text_area->setDisabled($disable);
		$text_area->setValue($feedback_text);
		$form->addItem($text_area);

		$reached_points_form = new ilNumberInputGUI($this->lng->txt('tst_change_points_for_question'), $scoring_post_var);
		$reached_points_form->allowDecimals(true);
		$reached_points_form->setSize(5);
		$reached_points_form->setMaxValue($max_points, true);
		$reached_points_form->setMinValue(0);
		$reached_points_form->setDisabled($disable);
		$reached_points_form->setValue($reached_points);
		$form->addItem($reached_points_form);

		$hidden_points = new ilHiddenInputGUI('qst_max_points');
		$hidden_points->setValue($max_points);
		$form->addItem($hidden_points);

		$hidden_points_name = new ilHiddenInputGUI('qst_hidden_points_name');
		$hidden_points_name->setValue('scoring' . $post_var);
		$form->addItem($hidden_points_name);

		$hidden_feedback_name = new ilHiddenInputGUI('qst_hidden_feedback_name');
		$hidden_feedback_name->setValue('m_feedback' . $post_var);
		$form->addItem($hidden_feedback_name);

		$hidden_feedback_id = new ilHiddenInputGUI('qst_hidden_feedback_id');
		$post_id = '__' . $pass . '____' . $active_id . '____' . $question_id . '__';
		$hidden_feedback_id->setValue('m_feedback' . $post_id);
		$form->addItem($hidden_feedback_id);

		$evaluated = new ilCheckboxInputGUI($this->lng->txt('finalized_evaluation'), 'evaluated' . $post_var);
		if(array_key_exists('finalized_evaluation', $feedback) && $feedback['finalized_evaluation'] == 1){
			$evaluated->setChecked(true);
		}
		$form->addItem($evaluated);

		$form->addCommandButton('checkConstraintsBeforeSaving', $this->lng->txt('save'));

		$tmp_tpl->setVariable(
			'MANUAL_FEEDBACK',
			$form->getHTML()
		);
		$tmp_tpl->setVariable(
			'MODAL_AJAX_URL',
			$this->ctrl->getLinkTarget($this, 'checkConstraintsBeforeSaving', '', true, false)
		);
		$tmp_tpl->setVariable(
			'INFO_TEXT_MAX_POINTS_EXCEEDS',
			sprintf($this->lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'), $max_points)
		);
	}

	/**
	 * @param ilTemplate $tmp_tpl
	 * @param $result_output
	 * @param $reached_points
	 * @param $max_points
	 */
	private function appendSolutionAndPointsToModal($tmp_tpl, $result_output, $reached_points, $max_points)
	{
		$tmp_tpl->setVariable(
			'SOLUTION_OUTPUT',
			$result_output
		);
		$tmp_tpl->setVariable(
			'RECEIVED_POINTS',
			sprintf($this->lng->txt('part_received_a_of_b_points'),
				$reached_points, $max_points)
		);
	}

	/**
	 * @param $active_id
	 * @param $qst_id
	 * @param $pass
	 */
	protected function saveFeedback($active_id, $qst_id, $pass)
	{
		$feedback = null;
		if($this->doesValueExistsInPostArray('feedback',$active_id, $qst_id, $pass)){
			$feedback = ilUtil::stripSlashes($_POST['feedback'][$pass][$active_id][$qst_id]);
		}elseif($this->doesValueExistsInPostArray('m_feedback',$active_id, $qst_id, $pass)){
			$feedback = ilUtil::stripSlashes($_POST['m_feedback'][$pass][$active_id][$qst_id]);
		}
		$this->saveFinalization($active_id, $qst_id, $pass, $feedback);
	}

	/**
	 * @param $active_id
	 * @param $qst_id
	 * @param $pass
	 * @param $feedback
	 */
	protected function saveFinalization($active_id, $qst_id, $pass, $feedback)
	{
		$evaluated = false;
		if($this->doesValueExistsInPostArray('evaluated', $active_id, $qst_id, $pass)){
			$evaluated = (int)$_POST['evaluated'][$pass][$active_id][$qst_id];
			if($evaluated === 1){
				$evaluated = true;
			}
		}
		$this->object->saveManualFeedback($active_id, $qst_id, $pass, $feedback, $evaluated);
	}
	/**
	 * @param $post_value
	 * @param $active_id
	 * @param $qst_id
	 * @param $pass
	 * @return bool
	 */
	protected function doesValueExistsInPostArray($post_value, $active_id, $qst_id, $pass)
	{
		return (
			isset($_POST[$post_value][$pass][$active_id][$qst_id]) &&
			strlen($_POST[$post_value][$pass][$active_id][$qst_id]) > 0
		);
	}
}