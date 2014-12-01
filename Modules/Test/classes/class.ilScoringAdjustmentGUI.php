<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php';

/**
 * Class ilScoringAdjustmentGUI
 *
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup		ModulesTest
 *
 * @ilCtrl_IsCalledBy ilScoringAdjustmentGUI: ilObjTestGUI
 */
class ilScoringAdjustmentGUI 
{
	/** @var \ilLanguage $lng */
	protected $lng;
	
	/** @var \ilTemplate $tpl */
	protected $tpl;
	
	/** @var ilCtrl $ctrl */
	protected $ctrl;
	
	/** @var ILIAS $ilias */
	protected $ilias;
	
	/** @var \ilObjTest $object */
	public $object; // Public due to law of demeter violation in ilTestQuestionsTableGUI.
	
	/** @var \ilTree $tree */
	protected $tree;
	
	/** @var int $ref_id */
	protected $ref_id;
	
	/** @var \ilTestService $service */
	protected $service;

	/**
	 * Default constructor
	 * 
	 * @param ilObjTest $a_object
	 */
	public function __construct(ilObjTest $a_object)
	{
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

		$this->lng 		= $lng;
		$this->tpl 		= $tpl;
		$this->ctrl 	= $ilCtrl;
		$this->ilias 	= $ilias;
		$this->object 	= $a_object;
		$this->tree 	= $tree;
		$this->ref_id 	= $a_object->ref_id;

		require_once './Modules/Test/classes/class.ilTestService.php';
		$this->service 	= new ilTestService($a_object);
	}

	/**
	 * execute command
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		switch($next_class)
		{
			default:
				return $this->dispatchCommand($cmd);
		}
	}

	protected function dispatchCommand($cmd)
	{
		switch (strtolower($cmd))
		{
			case 'savescoringfortest':
				$this->saveQuestion();
				break;
				
			case 'adjustscoringfortest':
				$this->editQuestion();
				break;
			
			case 'showquestionlist':
			default: 
				$this->questionsObject();
		}
	}

	protected function questionsObject()
	{
		/** @var $ilAccess ilAccessHandler */
		global $ilAccess;

		if (!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			// allow only write access
			ilUtil::sendInfo($this->lng->txt("cannot_edit_test"), true);
			$this->ctrl->redirect($this, "infoScreen");
		}

		if ($_GET['browse'])
		{
			exit('Browse??');
			return $this->object->questionbrowser();
		}

		if ($_GET["eqid"] && $_GET["eqpl"])
		{
			$this->ctrl->setParameter($this, 'q_id', $_GET["eqid"]);
			$this->ctrl->setParameter($this, 'qpl_id', $_GET["eqpl"]);
			$this->ctrl->redirect($this, 'adjustscoringfortest');
		}


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "Modules/Test");

		$this->tpl->setCurrentBlock("adm_content");

		include_once "./Modules/Test/classes/tables/class.ilTestQuestionsTableGUI.php";
		$checked_move = is_array($_SESSION['tst_qst_move_' . $this->object->getTestId()]) 
			&& (count($_SESSION['tst_qst_move_' . $this->object->getTestId()]));

		$table_gui = new ilTestQuestionsTableGUI(
			$this, 
			'showquestionlist', 
			(($ilAccess->checkAccess("write", "", $this->ref_id) ? true : false)), 
			$checked_move, 0);

		$data = $this->object->getTestQuestions();
		// @TODO Ask object for random test.
		if (!$data)
		{
			$this->object->getPotentialRandomTestQuestions();
		}

		$filtered_data = array();
		foreach($data as $question)
		{
			$question_object = assQuestion::instantiateQuestionGUI($question['question_id']);

			if ( $this->supportsAdjustment( $question_object ) && $this->allowedInAdjustment( $question_object ) )
			{
				$filtered_data[] = $question;
			}
		}
		$table_gui->setData($filtered_data);

		$table_gui->clearActionButtons();
		$table_gui->clearCommandButtons();
		$table_gui->multi = array();
		$table_gui->setRowTemplate('tpl.il_as_tst_adjust_questions_row.html', 'Modules/Test');
		$table_gui->header_commands = array();
		$table_gui->setSelectAllCheckbox(null);

		$this->tpl->setVariable('QUESTIONBROWSER', $table_gui->getHTML());
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $this->ctrl->getFormAction($this, 'showquestionlist'));
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * Returns if the given question object support scoring adjustment.
	 * 
	 * @param $question_object assQuestionGUI
	 *
	 * @return bool True, if relevant interfaces are implemented to support scoring adjustment.
	 */
	protected function supportsAdjustment(\assQuestionGUI $question_object)
	{
		return ($question_object instanceof ilGuiQuestionScoringAdjustable
			|| $question_object instanceof ilGuiAnswerScoringAdjustable)
			&& ($question_object->object instanceof ilObjQuestionScoringAdjustable
			|| $question_object->object instanceof ilObjAnswerScoringAdjustable);
	}

	/**
	 * Returns if the question type is allowed for adjustments in the global test administration.
	 * 
	 * @param assQuestionGUI $question_object
	 * @return bool
	 */
	protected function allowedInAdjustment(\assQuestionGUI $question_object)
	{
		$setting = new ilSetting('assessment');
		$types = explode(',',$setting->get('assessment_scoring_adjustment'));
		require_once './Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
		$type_def = array();
		foreach ($types as $type)
		{
			$type_def[$type] = ilObjQuestionPool::getQuestionTypeByTypeId($type);
		}

		$type = $question_object->getQuestionType();
		if (in_array($type,$type_def))
		{
			return true; 
		}
		return false;
	}

	protected function editQuestion()
	{
		$form = $this->buildAdjustQuestionForm( (int)$_GET['q_id'], (int)$_GET['qpl_id'] );
		$this->outputAdjustQuestionForm( $form );
	}

	/**
	 * @param $form
	 */
	protected function outputAdjustQuestionForm($form)
	{
		$this->tpl->addBlockFile( "ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", "Modules/Test" );
		$this->tpl->setCurrentBlock( "adm_content" );
		$this->tpl->setVariable( 'QUESTIONBROWSER', $form->getHTML() );
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * @param $question_id
	 * @param $question_pool_id
	 *
	 * @return ilPropertyFormGUI
	 */
	protected function buildAdjustQuestionForm($question_id, $question_pool_id)
	{
		require_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';

		$form = new ilPropertyFormGUI();
		$form->setFormAction( $this->ctrl->getFormAction( $this ) );
		$form->setMultipart( FALSE );
		$form->setTableWidth( "100%" );
		$form->setId( "adjustment" );

		/** @var $question assQuestionGUI|ilGuiQuestionScoringAdjustable|ilGuiAnswerScoringAdjustable */
		$question = assQuestion::instantiateQuestionGUI( $question_id );
		$form->setTitle( $question->object->getTitle() . '<br /><small>(' . $question->outQuestionType() . ')</small>' );

		$hidden_question_id = new ilHiddenInputGUI('q_id');
		$hidden_question_id->setValue( $question_id );
		$form->addItem( $hidden_question_id );

		$hidden_qpl_id = new ilHiddenInputGUI('qpl_id');
		$hidden_qpl_id->setValue( $question_pool_id );
		$form->addItem( $hidden_qpl_id );

		$this->populateScoringAdjustments( $question, $form );

		$manscoring_section = new ilFormSectionHeaderGUI();
		$manscoring_section->setTitle($this->lng->txt('manscoring'));
		$form->addItem($manscoring_section);
		
		$manscoring_preservation = new ilCheckboxInputGUI($this->lng->txt('preserve_manscoring'), 'preserve_manscoring');
		$manscoring_preservation->setChecked(true);
		$manscoring_preservation->setInfo($this->lng->txt('preserve_manscoring_info'));
		$form->addItem($manscoring_preservation);

		$form->addCommandButton("savescoringfortest", $this->lng->txt("save"));

		$participants = $this->object->getParticipants();
		$active_ids = array_keys($participants);
		$results = array();
		
		foreach ($active_ids as $active_id)
		{
			$passes[] = $this->object->_getPass($active_id);
			foreach ($passes as $key => $pass)
			{
				for ($i = 0; $i <= $pass; $i++)
				{
					$results[] = $question->object->getSolutionValues($active_id, $i);
				}
			}
		}

		$relevant_answers = array();
		foreach ($results as $result)
		{
			foreach ($result as $answer)
			{
				if( $answer['question_fi'] == $question->object->getId() )
				{
					$relevant_answers[] = $answer;
				}
			}
		}

		$answers_view = $question->getAggregatedAnswersView($relevant_answers);

		include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
		iljQueryUtil::initjQuery();
		include_once 'Services/YUI/classes/class.ilYuiUtil.php';
		ilYuiUtil::initPanel();
		ilYuiUtil::initOverlay();
		$this->tpl->addJavascript('./Services/UIComponent/Overlay/js/ilOverlay.js');
		$this->tpl->addJavaScript("./Services/JavaScript/js/Basic.js");
		
		$container = new ilTemplate('tpl.il_as_tst_adjust_answer_aggregation_container.html', true, true, 'Modules/Test');
		$container->setVariable('FORM_ELEMENT_NAME', 'aggr_usr_answ');
		$container->setVariable('CLOSE_HTML', json_encode(ilGlyphGUI::get(ilGlyphGUI::CLOSE, $this->lng->txt('close'))));
		$container->setVariable('TXT_SHOW_ANSWER_OVERVIEW', $this->lng->txt('show_answer_overview'));
		$container->setVariable('TXT_CLOSE', $this->lng->txt('close'));
		$container->setVariable('ANSWER_OVERVIEW', $answers_view);

		$custom_input = new ilCustomInputGUI('', 'aggr_usr_answ');
		$custom_input->setHtml($container->get());
		$form->addItem($custom_input);
		return $form;
	}

	protected function suppressPostParticipationFormElements(\ilPropertyFormGUI $form, $postvars_to_suppress)
	{
		foreach ($postvars_to_suppress as $postvar)
		{
			/** @var $item ilFormPropertyGUI */
			$item = $form->getItemByPostVar($postvar);
			$item->setDisabled(true);
		}
		return $form;
	}

	protected function saveQuestion()
	{
		$question_id = $_POST['q_id'];
		$question_pool_id = $_POST['qpl_id'];
		$form = $this->buildAdjustQuestionForm($question_id, $question_pool_id);

		$form->setValuesByPost($_POST);

		if (!$form->checkInput())
		{
			ilUtil::sendFailure($this->lng->txt('adjust_question_form_error'));
			$this->outputAdjustQuestionForm($form);
			return;
		}

		require_once './Modules/TestQuestionPool/classes/class.assQuestion.php';
		/** @var $question assQuestionGUI|ilGuiQuestionScoringAdjustable */
		$question = assQuestion::instantiateQuestionGUI( $question_id );

		if ($question instanceof ilGuiQuestionScoringAdjustable)
		{
			$question->writeQuestionSpecificPostData($form);

		}
		
		if ($question->object instanceof ilObjQuestionScoringAdjustable)
		{
			$question->object->saveAdditionalQuestionDataToDb();
		}
		
		if ($question instanceof ilGuiAnswerScoringAdjustable)
		{
			$question->writeAnswerSpecificPostData($form);
		}
		
		if($question->object instanceof ilObjAnswerScoringAdjustable)
		{
			$question->object->saveAnswerSpecificDataToDb();
		}

		$question->object->setPoints($question->object->getMaximumPoints());
		$question->object->saveQuestionDataToDb();

		require_once './Modules/Test/classes/class.ilTestScoring.php';
		$scoring = new ilTestScoring($this->object);
		$scoring->setPreserveManualScores($_POST['preserve_manscoring'] == 1 ? true : false);
		$scoring->recalculateSolutions();

		ilUtil::sendSuccess($this->lng->txt('saved_adjustment'));
		$this->questionsObject();
		
	}

	/**
	 * @param $question
	 * @param $form
	 */
	protected function populateScoringAdjustments( $question, $form )
	{
		if ( $question instanceof ilGuiQuestionScoringAdjustable )
		{
			$question->populateQuestionSpecificFormPart( $form );
			$this->suppressPostParticipationFormElements( $form,
														  $question->getAfterParticipationSuppressionQuestionPostVars()
			);
		}

		if ( $question instanceof ilGuiAnswerScoringAdjustable )
		{
			$question->populateAnswerSpecificFormPart( $form );
			$this->suppressPostParticipationFormElements( $form,
														  $question->getAfterParticipationSuppressionAnswerPostVars()
			);
		}
	}
}