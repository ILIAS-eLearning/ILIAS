<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Numeric question GUI representation
 *
 * The assNumericGUI class encapsulates the GUI representation
 * for numeric questions.
 *
 * @author		Helmut SchottmÃ¼ller <helmut.schottmueller@mac.com>
 * @author		Nina Gharib <nina@wgserve.de>
 * @author		Björn Heyser <bheyser@databay.de>
 * @author		Maximilian Becker <mbecker@databay.de>
 *
 * @version	$Id$
 *
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assNumericGUI: ilFormPropertyDispatchGUI
 */
class assNumericGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
	/**
	 * assNumericGUI constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assNumericGUI object.
	 *
	 * @param integer $id The database id of a Numeric question object
	 *
	 * @return assNumericGUI
	 */
	public function __construct($id = -1)
	{
		parent::__construct();
		require_once './Modules/TestQuestionPool/classes/class.assNumeric.php';
		$this->object = new assNumeric();
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
	 * {@inheritdoc}
	 */
	protected function writePostData($always = false)
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
		$this->editForm = $form;

		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(TRUE);
		$form->setTableWidth("100%");
		$form->setId("assnumeric");

		$this->addBasicQuestionFormProperties( $form );
		$this->populateQuestionSpecificFormPart( $form );
		$this->populateAnswerSpecificFormPart( $form );
		$this->populateTaxonomyFormSection($form);
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
	* Checks the range limits
	*
	* Checks the Range limits Upper and Lower for their correctness
	*
	* @return boolean 
	*/
	public function checkRange()
	{
		include_once "./Services/Math/classes/class.EvalMath.php";
		$eval = new EvalMath();
		$eval->suppress_errors = TRUE;
		if (($eval->e($_POST["rang_lower_limit"]) !== FALSE) AND ($eval->e($_POST ["range_upper_limit"]) !== FALSE))
		{
			if ($eval->e($_POST["rang_lower_limit"]) < $eval->e($_POST["range_upper_limit"]))
			{
				return TRUE;
			}
			else 
			{
				return FALSE;
			}
		}
		return FALSE;
	}

	/**
	 * Get the question solution output
	 *
	 * @param integer $active_id             The active user id
	 * @param integer $pass                  The test pass
	 * @param boolean $graphicalOutput       Show visual feedback for right/wrong answers
	 * @param boolean $result_output         Show the reached points for parts of the question
	 * @param boolean $show_question_only    Show the question without the ILIAS content around
	 * @param boolean $show_feedback         Show the question feedback
	 * @param boolean $show_correct_solution Show the correct solution instead of the user solution
	 * @param boolean $show_manual_scoring   Show specific information for the manual scoring output
	 * @param bool    $show_question_text
	 *
	 * @return string The solution output of the question as HTML code
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
		// get the solution of the user for the active pass or from the last pass if allowed
		$solutions = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
		}
		else
		{
			array_push($solutions, array("value1" => sprintf($this->lng->txt("value_between_x_and_y"), $this->object->getLowerLimit(), $this->object->getUpperLimit())));
		}
		
		// generate the question output
		require_once './Services/UICore/classes/class.ilTemplate.php';
		$template = new ilTemplate("tpl.il_as_qpl_numeric_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		if (is_array($solutions))
		{
			if (($active_id > 0) && (!$show_correct_solution))
			{
				if ($graphicalOutput)
				{
					if($this->object->getStep() === NULL)
					{
						$reached_points = $this->object->getReachedPoints($active_id, $pass);
					}
					else
					{
						$reached_points = $this->object->calculateReachedPoints($active_id, $pass);
					}
					// output of ok/not ok icons for user entered solutions
					if ($reached_points == $this->object->getMaximumPoints())
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
						$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						$template->parseCurrentBlock();
					}
				}
			}
			foreach ($solutions as $solution)
			{
				$template->setVariable("NUMERIC_VALUE", $solution["value1"]);
			}
			if (count($solutions) == 0)
			{
				$template->setVariable("NUMERIC_VALUE", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			}
		}
		$template->setVariable("NUMERIC_SIZE", $this->object->getMaxChars());
		$questiontext = $this->object->getQuestion();
		if ($show_question_text==true)
		{
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		$questionoutput = $template->get();
		//$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id, $pass) : ""; // Moving new method 
																								 // due to deprecation.
		$feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getGenericFeedbackOutput($active_id, $pass) : "";
		if (strlen($feedback))
		{
			$cssClass = ( $this->hasCorrectSolution($active_id, $pass) ?
				ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_CORRECT : ilAssQuestionFeedback::CSS_CLASS_FEEDBACK_WRONG
			);
			
			$solutiontemplate->setVariable("ILC_FB_CSS_CLASS", $cssClass);
			$solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput( $feedback, true ));
		}
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get(); 
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}

	/**
	 * @param bool $show_question_only
	 *
	 * @return string
	 */
	public function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		// generate the question output
		require_once './Services/UICore/classes/class.ilTemplate.php';
		$template = new ilTemplate("tpl.il_as_qpl_numeric_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		if( is_object($this->getPreviewSession()) )
		{
			$template->setVariable("NUMERIC_VALUE", " value=\"".$this->getPreviewSession()->getParticipantsSolution()."\"");
		}
		$template->setVariable("NUMERIC_SIZE", $this->object->getMaxChars());
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	/**
	 * @param integer		$active_id
	 * @param integer|null	$pass
	 * @param bool			$is_postponed
	 * @param bool			$use_post_solutions
	 *
	 * @return string
	 */
	// hey: prevPassSolutions - pass will be always available from now on
	public function getTestOutput($active_id, $pass, $is_postponed = FALSE, $use_post_solutions = FALSE, $inlineFeedback)
	// hey.
	{
		$solutions = NULL;
		// get the solution of the user for the active pass or from the last pass if allowed
		if ($active_id)
		{
			
			// hey: prevPassSolutions - obsolete due to central check
			#include_once "./Modules/Test/classes/class.ilObjTest.php";
			#if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			#{
			#	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			#}
			$solutions = $this->object->getTestOutputSolutions($active_id, $pass);
			// hey.
		}
		
		// generate the question output
		require_once './Services/UICore/classes/class.ilTemplate.php';
		$template = new ilTemplate("tpl.il_as_qpl_numeric_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		if (is_array($solutions))
		{
			foreach ($solutions as $solution)
			{
				$template->setVariable("NUMERIC_VALUE", " value=\"".$solution["value1"]."\"");
			}
		}
		$template->setVariable("NUMERIC_SIZE", $this->object->getMaxChars());
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	/**
	 * Sets the ILIAS tabs for this question type
	 * 
	 * @todo:	MOVE THIS STEPS TO COMMON QUESTION CLASS assQuestionGUI
	 */
	public function setQuestionTabs()
	{
		/** @var $rbacsystem ilRbacSystem */
		/** @var $ilTabs ilTabsGUI */
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
				array("editQuestion", "save", "cancel", "saveEdit", "originalSyncForm"),
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

	/**
	 * @param int $active_id
	 * @param int $pass
	 *
	 * @return mixed|string
	 */
	public function getSpecificFeedbackOutput($userSolution)
	{
		$output = "";
		return $this->object->prepareTextareaOutput($output, TRUE);
	}

	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
	{
		$this->object->setMaxChars( $_POST["maxchars"] );
	}

	public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
	{
		$this->object->setLowerLimit( $_POST['lowerlimit'] );
		$this->object->setUpperLimit( $_POST['upperlimit'] );
		$this->object->setPoints( $_POST['points'] );
	}

	public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
	{
		// maxchars
		$maxchars = new ilNumberInputGUI($this->lng->txt( "maxchars" ), "maxchars");
		$maxchars->setInfo($this->lng->txt('qpl_maxchars_info_numeric_question'));
		$maxchars->setSize( 10 );
		$maxchars->setDecimals( 0 );
		$maxchars->setMinValue( 1 );
		$maxchars->setRequired( true );
		if ($this->object->getMaxChars() > 0)
		{
			$maxchars->setValue($this->object->getMaxChars());
		}
		$form->addItem( $maxchars );
	}

	public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form)
	{
		// points
		$points = new ilNumberInputGUI($this->lng->txt( "points" ), "points");
		$points->allowDecimals(true);
		$points->setValue( $this->object->getPoints() > 0 ? $this->object->getPoints() : '' );
		$points->setRequired( TRUE );
		$points->setSize( 3 );
		$points->setMinValue( 0.0 );
		$points->setMinvalueShouldBeGreater( true );
		$form->addItem( $points );

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle( $this->lng->txt( "range" ) );
		$form->addItem( $header );

		// lower bound
		$lower_limit = new ilFormulaInputGUI($this->lng->txt( "range_lower_limit" ), "lowerlimit");
		$lower_limit->setSize( 25 );
		$lower_limit->setMaxLength( 20 );
		$lower_limit->setRequired( true );
		$lower_limit->setValue( $this->object->getLowerLimit() );
		$form->addItem( $lower_limit );

		// upper bound
		$upper_limit = new ilFormulaInputGUI($this->lng->txt( "range_upper_limit" ), "upperlimit");
		$upper_limit->setSize( 25 );
		$upper_limit->setMaxLength( 20 );
		$upper_limit->setRequired( true );
		$upper_limit->setValue( $this->object->getUpperLimit() );
		$form->addItem( $upper_limit );

		// reset input length, if max chars are set
		if( $this->object->getMaxChars() > 0 )
		{
			$lower_limit->setSize( $this->object->getMaxChars() );
			$lower_limit->setMaxLength( $this->object->getMaxChars() );
			$upper_limit->setSize( $this->object->getMaxChars() );
			$upper_limit->setMaxLength( $this->object->getMaxChars() );
		}
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
		return  $this->renderAggregateView(
					$this->aggregateAnswers( $relevant_answers ) )->get();
	}

	public function aggregateAnswers($relevant_answers_chosen)
	{
		$aggregate = array();

			foreach ($relevant_answers_chosen as $relevant_answer)
			{
				if ( array_key_exists($relevant_answer['value1'], $aggregate) )
				{
					$aggregate[$relevant_answer['value1']]++;
				} 
				else 
				{
					$aggregate[$relevant_answer['value1']] = 1;
				}
			}
		return $aggregate;
	}

	/**
	 * @param $aggregate
	 *
	 * @return ilTemplate
	 */
	public function renderAggregateView($aggregate)
	{
		$tpl = new ilTemplate('tpl.il_as_aggregated_answers_table.html', true, true, "Modules/TestQuestionPool");

		$tpl->setCurrentBlock('headercell');
		$tpl->setVariable('HEADER', $this->lng->txt('tst_answer_aggr_answer_header'));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock('headercell');
		$tpl->setVariable('HEADER', $this->lng->txt('tst_answer_aggr_frequency_header'));
		$tpl->parseCurrentBlock();
		
		foreach ($aggregate as $key => $value)
		{
			$tpl->setCurrentBlock( 'aggregaterow' );
			$tpl->setVariable( 'OPTION', $key );
			$tpl->setVariable( 'COUNT', $value );
			$tpl->parseCurrentBlock();
		}
		return $tpl;
	}
}