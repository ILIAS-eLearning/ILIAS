<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
include_once './Modules/Test/classes/inc.AssessmentConstants.php';

class assLongMenuGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable
{
	/**
	 * assJavaAppletGUI constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assJavaAppletGUI object.
	 *
	 * @param integer $id The database id of a image map question object
	 *
	 * @return \assJavaAppletGUI
	 */
	function __construct($id = -1)
	{
		parent::__construct();
		include_once './Modules/TestQuestionPool/classes/class.assLongMenu.php';
		$this->object = new assLongMenu();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		if (substr($cmd, 0, 6) == "delete")
		{
			$cmd = "delete";
		}
		return $cmd;
	}

	/**
	 * Evaluates a posted edit form and writes the form data in the question object
	 *
	 * @param bool $always
	 *
	 * @return integer A positive value, if one of the required fields wasn't set, else 0
	 *
	 */
	public function writePostData($always = false)
	{
		$form = $this->buildEditForm();
		$form->setValuesByPost();
		if( !$form->checkInput() )
		{
			$this->editQuestion($form);
			return 1;
		}
		$this->writeQuestionGenericPostData();
		$this->writeQuestionSpecificPostData($form);
		//$this->writeAnswerSpecificPostData(new ilPropertyFormGUI());
		$this->saveTaxonomyAssignments();
		return 0;
	}

	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
	{
			$longmenu_text = ilUtil::stripSlashesRecursive($_POST['longmenu_text']);
			//$longmenu_text = $this->removeIndizesFromGapText( $longmenu_text );
			$_POST['longmenu_text'] = $longmenu_text;
			$this->object->setQuestion($_POST['question_text']);
			$this->object->setLongMenuTextValue($_POST["longmenu_text"]);
			//$this->object->flushGaps();
			$this->saveTaxonomyAssignments();
	}

	protected function editQuestion(ilPropertyFormGUI $form = null)
	{
		if( $form === null )
		{
			$form = $this->buildEditForm();
		}

		$this->getQuestionTemplate();
		$this->tpl->addCss('Modules/Test/templates/default/ta.css');

		$this->tpl->setVariable("QUESTION_DATA", $this->ctrl->getHTML($form));
	}
	/**
	 * @return ilPropertyFormGUI
	 */
	private function buildEditForm()
	{
		$form = $this->buildBasicEditFormObject();

		$this->addQuestionFormCommandButtons($form);

		$this->addBasicQuestionFormProperties($form);

		$this->populateQuestionSpecificFormPart($form);
		$this->populateAnswerSpecificFormPart($form);

		$this->populateTaxonomyFormSection($form);

		return $form;
	}

	public function removeIndizesFromGapText( $question_text )
	{
		$parts         = preg_split( '/\[Longmenu \d*\]/', $question_text );
		$question_text = implode( '[Longmenu]', $parts );
		return $question_text;
	}
	
	/**
	 * @param ilPropertyFormGUI $form
	 * @return ilPropertyFormGUI
	 */
	public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form)
	{
		// shuffle answers
		/*	$shuffleAnswers = new ilCheckboxInputGUI($this->lng->txt( "shuffle_answers" ), "shuffle_answers_enabled");
			$shuffleAnswers->setChecked( $this->object->isShuffleAnswersEnabled() );
			$form->addItem($shuffleAnswers);
	
			if( !$this->object->getSelfAssessmentEditingMode() )
			{
				// answer mode (single-/multi-line)
				$answerType = new ilSelectInputGUI($this->lng->txt('answer_types'), 'answer_type');
				$answerType->setOptions($this->object->getAnswerTypeSelectOptions($this->lng));
				$answerType->setValue( $this->object->getAnswerType() );
				$form->addItem($answerType
			}
	
			/*if( !$this->object->getSelfAssessmentEditingMode() && $this->object->isSingleLineAnswerType($this->object->getAnswerType()) )
			{
				// thumb size
				$thumbSize = new ilNumberInputGUI($this->lng->txt('thumb_size'), 'thumb_size');
				$thumbSize->setSuffix($this->lng->txt("thumb_size_unit_pixel"));
				$thumbSize->setInfo( $this->lng->txt('thumb_size_info') );
				$thumbSize->setDecimals(false);
				$thumbSize->setMinValue(20);
				$thumbSize->setSize(6);
				$thumbSize->setValue( $this->object->getThumbSize() );
				$form->addItem($thumbSize);
			}
		
		// points
		$points = new ilNumberInputGUI($this->lng->txt('points'), 'points');
		$points->setRequired(true);
		$points->setSize(3);
		$points->allowDecimals(true);
		$points->setMinValue(0);
		$points->setMinvalueShouldBeGreater(true);
		$points->setValue($this->object->getPoints());
		$form->addItem($points);
		*/
		// cloze text
		$long_menu_text = new ilTextAreaInputGUI($this->lng->txt("longmenu_text"), 'longmenu_text');
		$long_menu_text->setRequired(true);
		$long_menu_text->setInfo($this->lng->txt("longmenu_hint"));
		$long_menu_text->setRows( 10 );
		$long_menu_text->setCols( 80 );
		if (!$this->object->getSelfAssessmentEditingMode())
		{
			if( $this->object->getAdditionalContentEditingMode() == assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_DEFAULT )
			{
				include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
				$long_menu_text->setRteTags(ilObjAdvancedEditing::_getUsedHTMLTags("assessment"));
				$long_menu_text->addPlugin("latex");
				$long_menu_text->addButton("latex");
				$long_menu_text->addButton("pastelatex");
			}
		}
		else
		{
			$long_menu_text->setRteTags(self::getSelfAssessmentTags());
			$long_menu_text->setUseTagsForRteOnly(false);
		}
		$long_menu_text->setUseRte(TRUE);
		$long_menu_text->setRTESupport($this->object->getId(), "qpl", "assessment");
		$long_menu_text->setValue($this->object->getLongMenuTextValue());
		$form->addItem($long_menu_text);
		
		$tpl = new ilTemplate("tpl.il_as_qpl_cloze_gap_button_code.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$tpl->setVariable('INSERT_GAP', $this->lng->txt('insert_gap'));
		$tpl->parseCurrentBlock();
		$button = new ilCustomInputGUI('&nbsp;','');
		$button->setHtml($tpl->get());
		$form->addItem($button);

		require_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
		$modal = ilModalGUI::getInstance();
		$modal->setHeading('');
		$modal->setId("ilGapModal");
		//$modal->setBackdrop(ilModalGUI::BACKDROP_OFF);
		$modal->setBody('');
		
		$tpl = new ilTemplate("tpl.il_as_qpl_long_menu_gap.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$tpl->setVariable('CORRECT_ANSWERS', $this->object->getCorrectAnswers());
		$tpl->setVariable('ALL_ANSWERS', $this->object->getAnswersObject());
		$tpl->setVariable('SELECT_BOX', $this->lng->txt('insert_gap'));
		$tpl->setVariable("SELECT", 	$this->lng->txt('select'));
		$tpl->setVariable("TEXT", 		$this->lng->txt('text'));
		$tpl->setVariable("POINTS", 	$this->lng->txt('points'));
		$tpl->setVariable("INFO_TEXT_UPLOAD", 	$this->lng->txt('INFO_TEXT_UPLOAD'));
		$tpl->setVariable("INFO_TEXT_GAP", 	$this->lng->txt('INFO_TEXT_GAP'));
		$tpl->setVariable("MANUAL_EDITING", 	$this->lng->txt('MANUAL_EDITING'));
		$tpl->setVariable("MY_MODAL", 	$modal->getHTML());
		$tpl->parseCurrentBlock();
		$button = new ilCustomInputGUI('&nbsp;','');
		$button->setHtml($tpl->get());
		$form->addItem($button);
		return $form;
	}
	
	/**
	 * @param ilPropertyFormGUI $form
	 * @return ilPropertyFormGUI
	 */
	public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
	{
		return $form;
	}

	/**
	 * Get the question solution output
	 *
	 * @param integer $active_id The active user id
	 * @param integer $pass The test pass
	 * @param boolean $graphicalOutput Show visual feedback for right/wrong answers
	 * @param boolean $result_output Show the reached points for parts of the question
	 * @param boolean $show_question_only Show the question without the ILIAS content around
	 * @param boolean $show_feedback Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring Show specific information for the manual scoring output
	 * @return The solution output of the question as HTML code
	 */
	function getSolutionOutput(
		$active_id,
		$pass = NULL,
		$graphicalOutput = FALSE,
		$result_output = FALSE,
		$show_question_only = TRUE,
		$show_feedback = FALSE,
		$show_correct_solution = FALSE,
		$show_manual_scoring = FALSE,
		$show_question_text = TRUE
	)
	{
		
	}

	function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		
	}

	/**
	 * Sets the ILIAS tabs for this question type
	 *
	 * @access public
	 *
	 * @todo:	MOVE THIS STEPS TO COMMON QUESTION CLASS assQuestionGUI
	 */
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;

		$ilTabs->clearTargets();

		$this->ctrl->setParameterByClass("ilAssQuestionPageGUI", "q_id", $_GET["q_id"]);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$q_type = $this->object->getQuestionType();

		if (strlen($q_type))
		{
			$classname = $q_type . "GUI";
			$this->ctrl->setParameterByClass(strtolower($classname), "sel_question_types", $q_type);
			$this->ctrl->setParameterByClass(strtolower($classname), "q_id", $_GET["q_id"]);
		}

		if ($_GET["q_id"])
		{
			if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
			{
				// edit page
				$ilTabs->addTarget("edit_page",
					$this->ctrl->getLinkTargetByClass("ilAssQuestionPageGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}

			$this->addTab_QuestionPreview($ilTabs);
		}

		$force_active = false;
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^delete_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_question",
				$url,
				array("editQuestion", "save", "saveEdit", "addkvp", "removekvp", "originalSyncForm"),
				$classname, "", $force_active);
		}

		// add tab for question feedback within common class assQuestionGUI
		$this->addTab_QuestionFeedback($ilTabs);

		// add tab for question hint within common class assQuestionGUI
		$this->addTab_QuestionHints($ilTabs);

		// add tab for question's suggested solution within common class assQuestionGUI
		$this->addTab_SuggestedSolution($ilTabs, $classname);

		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}

		$this->addBackTab($ilTabs);
	}

	function getSpecificFeedbackOutput($active_id, $pass)
	{
		$output = "";
		return $this->object->prepareTextareaOutput($output, TRUE);
	}

	/**
	 * Returns a list of postvars which will be suppressed in the form output when used in scoring adjustment.
	 * The form elements will be shown disabled, so the users see the usual form but can only edit the settings, which
	 * make sense in the given context.
	 *
	 * E.g. array('cloze_type', 'image_filename')
	 *
	 * @return string[]
	 */
	public function getAfterParticipationSuppressionQuestionPostVars()
	{
		return array();
	}

	/**
	 * Returns an html string containing a question specific representation of the answers so far
	 * given in the test for use in the right column in the scoring adjustment user interface.
	 *
	 * @param array $relevant_answers
	 *
	 * @return string
	 */
	public function getAggregatedAnswersView($relevant_answers)
	{
		// Empty implementation here since a feasible way to aggregate answer is not known.
		return ''; //print_r($relevant_answers,true);
	}
}