<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';

require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * The assErrorTextGUI class encapsulates the GUI representation for error text questions.
 *
 * @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 * 
 * @version		$Id$
 * 
 * @ingroup 	ModulesTestQuestionPool
 * 
 * @ilctrl_iscalledby assErrorTextGUI: ilObjQuestionPoolGUI
 * 
 */
class assErrorTextGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
	/**
	 * assErrorTextGUI constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assOrderingHorizontalGUI object.
	 *
	 * @param integer $id The database id of a single choice question object
	 * @access public
	 */
	public function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assErrorText.php";
		$this->object = new assErrorText();
		$this->setErrorMessage($this->lng->txt("msg_form_save_error"));
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	/**
	 * Evaluates a posted edit form and writes the form data in the question object
	 *
	 * @param bool $always
	 *
	 * @return integer A positive value, if one of the required fields wasn't set, else 0
	 * @access private
	 */
	function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
			$this->writeQuestionGenericPostData();
			$this->writeQuestionSpecificPostData(new ilPropertyFormGUI());
			$this->writeAnswerSpecificPostData(new ilPropertyFormGUI());
			$this->saveTaxonomyAssignments();
			return 0;
		}
		return 1;
	}

	public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
	{
		if (is_array( $_POST['errordata']['key'] ))
		{
			$this->object->flushErrorData();
			foreach ($_POST['errordata']['key'] as $idx => $val)
			{
				$this->object->addErrorData( $val,
											 $_POST['errordata']['value'][$idx],
											 $_POST['errordata']['points'][$idx]
				);
			}
		}
	}

	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
	{
		$questiontext = $_POST["question"];
		$this->object->setQuestion( $questiontext );
		$this->object->setErrorText( $_POST["errortext"] );
		$points_wrong = str_replace( ",", ".", $_POST["points_wrong"] );
		if (strlen( $points_wrong ) == 0)
			$points_wrong = -1.0;
		$this->object->setPointsWrong( $points_wrong );

		if (!$this->object->getSelfAssessmentEditingMode())
		{
			$this->object->setTextSize( $_POST["textsize"] );
		}
	}

	/**
	 * Creates an output of the edit form for the question
	 * 
	 * @param bool $checkonly
	 *
	 * @return bool
	 */
	public function editQuestion($checkonly = FALSE)
	{
		$save = $this->isSaveCommand();
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("orderinghorizontal");

		$this->addBasicQuestionFormProperties( $form );

		$this->populateQuestionSpecificFormPart( $form );

		if (count($this->object->getErrorData()) || $checkonly)
		{
			$this->populateAnswerSpecificFormPart( $form );
		}

		$this->populateTaxonomyFormSection($form);

		$form->addCommandButton("analyze", $this->lng->txt('analyze_errortext'));
		$this->addQuestionFormCommandButtons($form);

		$errors = false;

		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		return $errors;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 * @return \ilPropertyFormGUI|void
	 */
	public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
	{
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle( $this->lng->txt( "errors_section" ) );
		$form->addItem( $header );

		include_once "./Modules/TestQuestionPool/classes/class.ilErrorTextWizardInputGUI.php";
		$errordata = new ilErrorTextWizardInputGUI($this->lng->txt( "errors" ), "errordata");
		$errordata->setKeyName( $this->lng->txt( 'text_wrong' ) );
		$errordata->setValueName( $this->lng->txt( 'text_correct' ) );
		$errordata->setValues( $this->object->getErrorData() );
		$form->addItem( $errordata );

		// points for wrong selection
		$points_wrong = new ilNumberInputGUI($this->lng->txt( "points_wrong" ), "points_wrong");
		$points_wrong->allowDecimals(true);
		$points_wrong->setValue( $this->object->getPointsWrong() );
		$points_wrong->setInfo( $this->lng->txt( "points_wrong_info" ) );
		$points_wrong->setSize( 6 );
		$points_wrong->setRequired( true );
		$form->addItem( $points_wrong );
		return $form;
	}

	/**
	 * @param $form ilPropertyFormGUI
	 * @return \ilPropertyFormGUI|void
	 */
	public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form)
	{
		// errortext
		$errortext = new ilTextAreaInputGUI($this->lng->txt( "errortext" ), "errortext");
		$errortext->setValue( $this->object->getErrorText() );
		$errortext->setRequired( TRUE );
		$errortext->setInfo( $this->lng->txt( "errortext_info" ) );
		$errortext->setRows( 10 );
		$errortext->setCols( 80 );
		$form->addItem( $errortext );

		if (!$this->object->getSelfAssessmentEditingMode())
		{
			// textsize
			$textsize = new ilNumberInputGUI($this->lng->txt( "textsize" ), "textsize");
			$textsize->setValue( strlen( $this->object->getTextSize() ) ? $this->object->getTextSize() : 100.0 );
			$textsize->setInfo( $this->lng->txt( "textsize_errortext_info" ) );
			$textsize->setSize( 6 );
			$textsize->setSuffix( "%" );
			$textsize->setMinValue( 10 );
			$textsize->setRequired( true );
			$form->addItem( $textsize );
		}
	}

	/**
	* Parse the error text
	*/
	public function analyze()
	{
		$this->writePostData(true);
		$this->object->setErrorData($this->object->getErrorsFromText($_POST['errortext']));
		$this->editQuestion();
	}

	/**
	 * Get the question solution output
	 *
	 * The getSolutionOutput() method is used to print either the
	 * user's pass' solution or the best possible solution for the
	 * current errorText question object.
	 *
	 * @param	integer		$active_id				The active test id
	 * @param	integer		$pass					The test pass counter
	 * @param	boolean		$graphicalOutput		Show visual feedback for right/wrong answers
	 * @param	boolean		$result_output			Show the reached points for parts of the question
	 * @param	boolean		$show_question_only		Show the question without the ILIAS content around
	 * @param	boolean		$show_feedback			Show the question feedback
	 * @param	boolean		$show_correct_solution	Show the correct solution instead of the user solution
	 * @param	boolean		$show_manual_scoring	Show specific information for the manual scoring output
	 *
	 * @return	string	HTML solution output
	 **/
	function getSolutionOutput(
				$active_id, $pass		= NULL,	
				$graphicalOutput		= FALSE,
				$result_output			= FALSE,
				$show_question_only		= TRUE,
				$show_feedback			= FALSE,
				$show_correct_solution	= FALSE,
				$show_manual_scoring	= FALSE,
				$show_question_text		= TRUE 
	)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$template = new ilTemplate("tpl.il_as_qpl_errortext_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");

		$selections = array();
		if (($active_id > 0) && (!$show_correct_solution)) {

			/* Retrieve tst_solutions entries. */
			$reached_points = $this->object->getReachedPoints($active_id, $pass);
			$solutions		=& $this->object->getSolutionValues($active_id, $pass);
			if (is_array($solutions)) {
				foreach ($solutions as $solution) {
					array_push($selections, (int) $solution['value1']);
				}
				$errortext_value = join(",", $selections);
			}
		}
		else {
			$selections		= $this->object->getBestSelection();
			$reached_points = $this->object->getPoints();
		}

		if ($result_output) {
			$resulttext = ($reached_points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")";
			$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $reached_points));
		}

		if ($this->object->getTextSize() >= 10)
			$template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");

		if ($show_question_text==true)
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));

		$errortext = $this->object->createErrorTextOutput($selections, $graphicalOutput, $show_correct_solution);

		$template->setVariable("ERRORTEXT", $errortext);
		$questionoutput = $template->get();

		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");

		$feedback = '';
		if($show_feedback)
		{
			$fb = $this->getGenericFeedbackOutput($active_id, $pass);
			$feedback .=  strlen($fb) ? $fb : '';
			
			$fb = $this->getSpecificFeedbackOutput($active_id, $pass);
			$feedback .=  strlen($fb) ? $fb : '';
		}
		if (strlen($feedback)) $solutiontemplate->setVariable("FEEDBACK", $feedback);
		
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get();
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}

	function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		$selections = is_object($this->getPreviewSession()) ? (array)$this->getPreviewSession()->getParticipantsSolution() : array();

		$template = new ilTemplate("tpl.il_as_qpl_errortext_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		if ($this->object->getTextSize() >= 10) $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		$errortext = $this->object->createErrorTextOutput($selections);
		$template->setVariable("ERRORTEXT", $errortext);
		$template->setVariable("ERRORTEXT_ID", "qst_" . $this->object->getId());
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/errortext.js");
		return $questionoutput;
	}

	function getTestOutput(
				$active_id, 
				$pass = NULL, 
				$is_postponed = FALSE, 
				$use_post_solutions = FALSE, 
				$show_feedback = FALSE
	)
	{
		// generate the question output
		$template = new ilTemplate("tpl.il_as_qpl_errortext_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
		}
		$errortext_value = "";
		$selections = array();
		if (is_array($solutions))
		{
			foreach ($solutions as $solution)
			{
				array_push($selections, $solution['value1']);
			}
			$errortext_value = join(",", $selections);
		}
		if ($this->object->getTextSize() >= 10) $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		$errortext = $this->object->createErrorTextOutput($selections);
		$this->ctrl->setParameterByClass($this->getTargetGuiClass(), 'errorvalue', '');
		$template->setVariable("ERRORTEXT", $errortext);
		$template->setVariable("ERRORTEXT_ID", "qst_" . $this->object->getId());
		$template->setVariable("ERRORTEXT_VALUE", $errortext_value);

		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/errortext.js");
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
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
			// edit question properties
			$ilTabs->addTarget("edit_question",
				$url,
				array("editQuestion", "save", "saveEdit", "analyze", "originalSyncForm"),
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
		$selection = $this->object->getBestSelection(false);

		if( !$this->object->feedbackOBJ->specificAnswerFeedbackExists(array_keys($selection)) )
		{
			return '';
		}

		$feedback = '<table class="test_specific_feedback"><tbody>';
		
		$elements = array();
		foreach(preg_split("/[\n\r]+/", $this->object->errortext) as $line)
		{
			$elements = array_merge( $elements, preg_split("/\s+/", $line));
		}
		
		$matchedIndexes = array();
		
		$i = 0;
		foreach ($selection as $index => $answer)
		{
			$element = array();
			foreach($answer as $answerPartIndex)
			{
				$element[] = $elements[$answerPartIndex];
			}
			
			$element = implode(' ', $element);
			$element = str_replace(array('((', '))', '#'), array('', '', ''), $element);
			
			$ordinal = $index + 1;
			
			$feedback .= '<tr>';
			
			$feedback .= '<td class="text-nowrap">' . $ordinal . '. ' . $element . ':</td>';
			
			foreach ($this->object->getErrorData() as $idx => $ans)
			{
				if( isset($matchedIndexes[$idx]) )
				{
					continue;
				}
				
				if ( preg_match('/'.preg_quote($ans->text_wrong, '/').'/', $element) )
				{
					$fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
							$this->object->getId(), $idx
					);
					
					$feedback .= '<td>'. $fb . '</td>';
					
					$matchedIndexes[$idx] = $idx;
					
					break;
				}
			}
			
			$feedback .= '</tr>';
		}
		
		$feedback .= '</tbody></table>';
		
		return $this->object->prepareTextareaOutput($feedback, TRUE);
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
	public function getAfterParticipationSuppressionAnswerPostVars()
	{
		return array();
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
		$errortext = $this->object->getErrorText();
		
		$passdata = array(); // Regroup answers into units of passes.
		foreach($relevant_answers as $answer_chosen)
		{
			$passdata[$answer_chosen['active_fi'].'-'. $answer_chosen['pass']][$answer_chosen['value2']][] = $answer_chosen['value1'];
		}
		
		$html = '';
		foreach($passdata as $key => $pass)
		{
			$passdata[$key] = $this->object->createErrorTextOutput($pass);
			$html .= $passdata[$key] . '<hr /><br />';
		}

		return $html;
	}
}