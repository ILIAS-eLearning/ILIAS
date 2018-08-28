<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once './Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';
require_once './Modules/Test/classes/inc.AssessmentConstants.php';

/**
 * Single choice question GUI representation
 *
 * The assSingleChoiceGUI class encapsulates the GUI representation for single choice questions.
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *          
 * @version	$Id$
 *          
 * @ingroup ModulesTestQuestionPool
 * @ilCtrl_Calls assSingleChoiceGUI: ilFormPropertyDispatchGUI
 */
class assSingleChoiceGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
	/**
	 * assSingleChoiceGUI constructor
	 *
	 * The constructor takes possible arguments an creates an instance of the assSingleChoiceGUI object.
	 *
	 * @param integer $id The database id of a single choice question object
	 */
	public function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assSingleChoice.php";
		$this->object = new assSingleChoice();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}
	
	/**
	 * @return bool
	 */
	public function hasInlineFeedback()
	{
		return $this->object->feedbackOBJ->isSpecificAnswerFeedbackAvailable($this->object->getId());
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
	 * Get the single/multiline editing of answers
	 * - The settings of an already saved question is preferred
	 * - A new question will use the setting of the last edited question by the user
	 * @param bool	$checkonly	get the setting for checking a POST
	 * @return bool
	 */
	protected function getEditAnswersSingleLine($checkonly = false)
	{
		if ($checkonly)
		{
			// form posting is checked
			return ($_POST['types'] == 0) ? true : false;
		}

		$lastChange = $this->object->getLastChange();
		if (empty($lastChange) && !isset($_POST['types']))
		{
			// a new question is edited
			return $this->object->getMultilineAnswerSetting() ? false : true;
		}
		else
		{
			// a saved question is edited
			return $this->object->isSingleline;
		}
	}

	/**
	 * Creates an output of the edit form for the question
	 *
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
		$isSingleline = $this->getEditAnswersSingleLine($checkonly);
		if ($isSingleline)
		{
			$form->setMultipart(TRUE);
		}
		else
		{
			$form->setMultipart(FALSE);
		}
		$form->setTableWidth("100%");
		$form->setId("asssinglechoice");

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
	* Upload an image
	*/
	public function uploadchoice()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['uploadchoice']);
		$this->editQuestion();
	}

	/**
	* Remove an image
	*/
	public function removeimagechoice()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removeimagechoice']);
		$filename = $_POST['choice']['imagename'][$position];
		$this->object->removeAnswerImage($position);
		$this->editQuestion();
	}

	/**
	* Add a new answer
	*/
	public function addchoice()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['addchoice']);
		$this->object->addAnswer("", 0, $position+1);
		$this->editQuestion();
	}

	/**
	* Remove an answer
	*/
	public function removechoice()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removechoice']);
		$this->object->deleteAnswer($position);
		$this->editQuestion();
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
		// shuffle output
		$keys = $this->getChoiceKeys();

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution = $solution_value["value1"];
			}
		}
		else
		{
			$found_index = -1;
			$max_points = 0;
			foreach ($this->object->answers as $index => $answer)
			{
				if ($answer->getPoints() > $max_points)
				{
					$max_points = $answer->getPoints();
					$found_index = $index;
				}
			}
			$user_solution = $found_index;
		}
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_sr_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->answers[$answer_id];
			if (($active_id > 0) && (!$show_correct_solution))
			{
				if ($graphicalOutput)
				{
					// output of ok/not ok icons for user entered solutions
					$ok = FALSE;
					if (strcmp($user_solution, $answer_id) == 0)
					{
						if ($answer->getPoints() == $this->object->getMaximumPoints())
						{
							$ok = TRUE;
						}
						else
						{
							$ok = FALSE;
						}
						if ($ok)
						{
							$template->setCurrentBlock("icon_ok");
							$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.svg"));
							$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
							$template->parseCurrentBlock();
						}
						else
						{
							$template->setCurrentBlock("icon_not_ok");
							if ($answer->getPoints() > 0)
							{
								$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.svg"));
								$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
							}
							else
							{
								$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
								$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
							}
							$template->parseCurrentBlock();
						}
					}
					if (strlen($user_solution) == 0)
					{
						$template->setCurrentBlock("icon_not_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.svg"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
						$template->parseCurrentBlock();
					}
				}
			}
			if (strlen($answer->getImage()))
			{
				$template->setCurrentBlock("answer_image");
				if ($this->object->getThumbSize())
				{
					$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
				}
				else
				{
					$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
				}
				$alt = $answer->getImage();
				if (strlen($answer->getAnswertext()))
				{
					$alt = $answer->getAnswertext();
				}
				$alt = preg_replace("/<[^>]*?>/", "", $alt);
				$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
				$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
				$template->parseCurrentBlock();
			}
			if ($show_feedback)
			{
				$this->populateInlineFeedback($template, $answer_id, $user_solution);
			}
			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			
			if( $this->renderPurposeSupportsFormHtml() || $this->isRenderPurposePrintPdf() )
			{
				if (strcmp($user_solution, $answer_id) == 0)
				{
					$template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_checked.png")));
					$template->setVariable("SOLUTION_ALT", $this->lng->txt("checked"));
				}
				else
				{
					$template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
					$template->setVariable("SOLUTION_ALT", $this->lng->txt("unchecked"));
				}
			}
			else
			{
				$template->setVariable('QID', $this->object->getId());
				$template->setVariable('SUFFIX', $show_correct_solution ? 'bestsolution' : 'usersolution');
				$template->setVariable('SOLUTION_VALUE', $answer_id);
				if (strcmp($user_solution, $answer_id) == 0)
				{
					$template->setVariable('SOLUTION_CHECKED', 'checked');
				}
			}
			
			if ($result_output)
			{
				$points = $this->object->answers[$answer_id]->getPoints();
				$resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
				$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
			}
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		if ($show_question_text==true)
		{
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
		$questionoutput = $template->get();
		$feedback = ($show_feedback && !$this->isTestPresentationContext()) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
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
		
		if( $show_feedback && $this->hasInlineFeedback() )
		{
			$solutionoutput = $this->buildFocusAnchorHtml() .$solutionoutput;
		}
		
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = $this->getILIASPage($solutionoutput);
		}
		return $solutionoutput;
	}
	
	public function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		$keys = $this->getChoiceKeys();
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_sr_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->answers[$answer_id];
			if (strlen($answer->getImage()))
			{
				if ($this->object->getThumbSize())
				{
					$template->setCurrentBlock("preview");
					$template->setVariable("URL_PREVIEW", $this->object->getImagePathWeb() . $answer->getImage());
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
					$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
					list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
					$alt = $answer->getImage();
					if (strlen($answer->getAnswertext()))
					{
						$alt = $answer->getAnswertext();
					}
					$alt = preg_replace("/<[^>]*?>/", "", $alt);
					$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
					$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("answer_image");
					$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
					list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
					$alt = $answer->getImage();
					if (strlen($answer->getAnswertext()))
					{
						$alt = $answer->getAnswertext();
					}
					$alt = preg_replace("/<[^>]*?>/", "", $alt);
					$template->setVariable("ATTR", $attr);
					$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
					$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
					$template->parseCurrentBlock();
				}
			}
			if( $showInlineFeedback && is_object($this->getPreviewSession()) )
			{
				$this->populateInlineFeedback($template, $answer_id, $this->getPreviewSession()->getParticipantsSolution());
			}
			$template->setCurrentBlock("answer_row");
			$template->setVariable("QID", $this->object->getId().'ID');
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			
			if( is_object($this->getPreviewSession()) )
			{
				$user_solution = $this->getPreviewSession()->getParticipantsSolution();
				if (strcmp($user_solution, $answer_id) == 0)
				{
					$template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
				}
			}
			
			$template->parseCurrentBlock();
		}
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
	
	// hey: prevPassSolutions - pass will be always available from now on
	function getTestOutput($active_id, $pass, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	// hey.
	{
		$keys = $this->getChoiceKeys();

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
		if ($active_id)
		{
			// hey: prevPassSolutions - obsolete due to central check
			#$solutions = NULL;
			#include_once "./Modules/Test/classes/class.ilObjTest.php";
			#if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			#{
			#	if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			#}
			// hey.
			$solutions = $this->object->getTestOutputSolutions($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution = $solution_value["value1"];
			}
		}
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_sr_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->answers[$answer_id];
			if (strlen($answer->getImage()))
			{
				if ($this->object->getThumbSize())
				{
					$template->setCurrentBlock("preview");
					$template->setVariable("URL_PREVIEW", $this->object->getImagePathWeb() . $answer->getImage());
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
					$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $this->object->getThumbPrefix() . $answer->getImage());
					list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
					$alt = $answer->getImage();
					if (strlen($answer->getAnswertext()))
					{
						$alt = $answer->getAnswertext();
					}
					$alt = preg_replace("/<[^>]*?>/", "", $alt);
					$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
					$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("answer_image");
					$template->setVariable("ANSWER_IMAGE_URL", $this->object->getImagePathWeb() . $answer->getImage());
					list($width, $height, $type, $attr) = getimagesize($this->object->getImagePath() . $answer->getImage());
					$alt = $answer->getImage();
					if (strlen($answer->getAnswertext()))
					{
						$alt = $answer->getAnswertext();
					}
					$alt = preg_replace("/<[^>]*?>/", "", $alt);
					$template->setVariable("ATTR", $attr);
					$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($alt));
					$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($alt));
					$template->parseCurrentBlock();
				}
			}
			if ($show_feedback)
			{
				$feedbackOutputRequired = false;

				switch( $this->object->getSpecificFeedbackSetting() )
				{
					case 1:
						$feedbackOutputRequired = true;
						break;

					case 2:
						if (strcmp($user_solution, $answer_id) == 0)
						{
							$feedbackOutputRequired = true;
						}
						break;

					case 3:
						if ($this->object->getAnswer($answer_id)->getPoints() > 0)
						{
							$feedbackOutputRequired = true;
						}
						break;
				}

				if($feedbackOutputRequired)
				{
					$fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation(
						$this->object->getId(),0, $answer_id
					);
					if (strlen($fb))
					{
						$template->setCurrentBlock("feedback");
						$template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput( $fb, true ));
						$template->parseCurrentBlock();
					}
				}
			}
			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			if (strcmp($user_solution, $answer_id) == 0)
			{
				$template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
			}
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput, $show_feedback);
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
				array("editQuestion", "save", "saveEdit", "addchoice", "removechoice", "removeimagechoice", "uploadchoice", "originalSyncForm"),
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

	/*
	 * Create the key index numbers for the array of choices
	 * 
	 * @return array
	 */
	function getChoiceKeys()
	{
		$choiceKeys = array_keys($this->object->answers);
		
		if( $this->object->getShuffle() )
		{
			$choiceKeys = $this->object->getShuffler()->shuffle($choiceKeys);
		}
		
		return $choiceKeys;
	}
	
	function getSpecificFeedbackOutput($userSolution)
	{
		// No return value, this question type supports inline specific feedback.
		$output = "";
		return $this->object->prepareTextareaOutput($output, TRUE);
	}

	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
	{
		$this->object->setShuffle( $_POST["shuffle"] );
		$this->object->setMultilineAnswerSetting( $_POST["types"] );
		if (is_array( $_POST['choice']['imagename'] ) && $_POST["types"] == 1)
		{
			$this->object->isSingleline = true;
			ilUtil::sendInfo( $this->lng->txt( 'info_answer_type_change' ), true );
		}
		else
		{
			$this->object->isSingleline = ($_POST["types"] == 0) ? true : false;
		}
		$this->object->setThumbSize( (strlen( $_POST["thumb_size"] )) ? $_POST["thumb_size"] : "" );
	}

	public function populateQuestionSpecificFormPart(\ilPropertyFormGUI $form)
	{
		$isSingleline = $this->getEditAnswersSingleLine();
		// shuffle
		$shuffle = new ilCheckboxInputGUI($this->lng->txt( "shuffle_answers" ), "shuffle");
		$shuffle->setValue( 1 );
		$shuffle->setChecked( $this->object->getShuffle() );
		$shuffle->setRequired( FALSE );
		$form->addItem( $shuffle );

		if ($this->object->getId())
		{
			$hidden = new ilHiddenInputGUI("", "ID");
			$hidden->setValue( $this->object->getId() );
			$form->addItem( $hidden );
		}

		if (!$this->object->getSelfAssessmentEditingMode())
		{
			// Answer types
			$types = new ilSelectInputGUI($this->lng->txt( "answer_types" ), "types");
			$types->setRequired( false );
			$types->setValue( ($isSingleline) ? 0 : 1 );
			$types->setOptions( array(
									0 => $this->lng->txt( 'answers_singleline' ),
									1 => $this->lng->txt( 'answers_multiline' ),
								)
			);
			$form->addItem( $types );
		}

		if ($isSingleline)
		{
			// thumb size
			$thumb_size = new ilNumberInputGUI($this->lng->txt( "thumb_size" ), "thumb_size");
			$thumb_size->setSuffix($this->lng->txt("thumb_size_unit_pixel"));
			$thumb_size->setMinValue( 20 );
			$thumb_size->setDecimals( 0 );
			$thumb_size->setSize( 6 );
			$thumb_size->setInfo( $this->lng->txt( 'thumb_size_info' ) );
			$thumb_size->setValue( $this->object->getThumbSize() );
			$thumb_size->setRequired( false );
			$form->addItem( $thumb_size );
		}
		return $form;
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

	public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
	{
		// Delete all existing answers and create new answers from the form data
		$this->object->flushAnswers();
		if ($this->object->isSingleline)
		{
			foreach ($_POST['choice']['answer'] as $index => $answertext)
			{
				$answertext = ilUtil::secureString($answertext);

				$picturefile    = $_POST['choice']['imagename'][$index];
				$file_org_name  = $_FILES['choice']['name']['image'][$index];
				$file_temp_name = $_FILES['choice']['tmp_name']['image'][$index];

				if (strlen( $file_temp_name ))
				{
					// check suffix						
					$suffix = strtolower( array_pop( explode( ".", $file_org_name ) ) );
					if (in_array( $suffix, array( "jpg", "jpeg", "png", "gif" ) ))
					{
						// upload image
						$filename = $this->object->buildHashedImageFilename( $file_org_name );
						if ($this->object->setImageFile( $filename, $file_temp_name ) == 0)
						{
							$picturefile = $filename;
						}
					}
				}

				$this->object->addAnswer( $answertext, $_POST['choice']['points'][$index], $index, $picturefile );
			}
		}
		else
		{
			foreach ($_POST['choice']['answer'] as $index => $answer)
			{
				$answertext = $answer;
				$this->object->addAnswer( $answertext, $_POST['choice']['points'][$index], $index );
			}
		}
	}

	public function populateAnswerSpecificFormPart(\ilPropertyFormGUI $form)
	{
		$isSingleline = $this->getEditAnswersSingleLine();

		// Choices
		include_once "./Modules/TestQuestionPool/classes/class.ilSingleChoiceWizardInputGUI.php";
		$choices = new ilSingleChoiceWizardInputGUI($this->lng->txt( "answers" ), "choice");
		$choices->setRequired( true );
		$choices->setQuestionObject( $this->object );
		$choices->setSingleline( $isSingleline );
		$choices->setAllowMove( false );
		if ($this->object->getSelfAssessmentEditingMode())
		{
			$choices->setSize( 40 );
			$choices->setMaxLength( 800 );
		}
		if ($this->object->getAnswerCount() == 0)
			$this->object->addAnswer( "", 0, 0 );
		$choices->setValues( $this->object->getAnswers() );
		$form->addItem( $choices );
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
					$this->aggregateAnswers( $relevant_answers, $this->object->getAnswers() ) )->get();
	}

	public function aggregateAnswers($relevant_answers_chosen, $answers_defined_on_question)
	{
		$aggregate = array();
		foreach ($answers_defined_on_question as $answer)
		{
			$aggregated_info_for_answer 					= array();
			$aggregated_info_for_answer['answertext']		= $answer->getAnswerText();
			$aggregated_info_for_answer['count_checked']	= 0;

			foreach ($relevant_answers_chosen as $relevant_answer)
			{
				if ($relevant_answer['value1'] == $answer->getOrder())
				{
					$aggregated_info_for_answer['count_checked']++;
				}
			}
			$aggregated_info_for_answer['count_unchecked'] =
				ceil(count($relevant_answers_chosen) / count($answers_defined_on_question))
				- $aggregated_info_for_answer['count_checked'];

			$aggregate[] = $aggregated_info_for_answer;
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
		
		foreach ($aggregate as $line_data)
		{
			$tpl->setCurrentBlock( 'aggregaterow' );
			$tpl->setVariable( 'OPTION', $line_data['answertext'] );
			$tpl->setVariable( 'COUNT', $line_data['count_checked'] );
			$tpl->parseCurrentBlock();
		}
		return $tpl;
	}

	private function populateInlineFeedback($template, $answer_id, $user_solution)
	{
		$feedbackOutputRequired = false;

		switch($this->object->getSpecificFeedbackSetting())
		{
			case 1:
				$feedbackOutputRequired = true;
				break;

			case 2:
				if(strcmp($user_solution, $answer_id) == 0)
				{
					$feedbackOutputRequired = true;
				}
				break;

			case 3:
				if($this->object->getAnswer($answer_id)->getPoints() > 0)
				{
					$feedbackOutputRequired = true;
				}
				break;
		}

		if($feedbackOutputRequired)
		{
			$fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(),0, $answer_id);
			if(strlen($fb))
			{
				$template->setCurrentBlock("feedback");
				$template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
				$template->parseCurrentBlock();
			}
		}
	}
}
