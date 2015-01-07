<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
require_once 'Modules/TestQuestionPool/interfaces/interface.ilGuiQuestionScoringAdjustable.php';
require_once 'Modules/TestQuestionPool/interfaces/interface.ilGuiAnswerScoringAdjustable.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/TestQuestionPool
 *
 * @ilCtrl_Calls assKprimChoiceGUI: ilPropertyFormGUI
 */
class assKprimChoiceGUI extends assQuestionGUI implements ilGuiQuestionScoringAdjustable, ilGuiAnswerScoringAdjustable
{
	/**
	 * @var assKprimChoice
	 */
	public $object;
	
	/**
	 * @param $qId
	 */
	public function __construct($qId = -1)
	{
		parent::__construct();
		
		require_once 'Modules/TestQuestionPool/classes/class.assKprimChoice.php';
		$this->object = new assKprimChoice();
		
		if ($qId > 0)
		{
			$this->object->loadFromDb($qId);
		}
	}

	protected function getAdditionalEditQuestionCommands()
	{
		return array('uploadImage', 'removeImage');
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

	protected function uploadImage()
	{
		$result = $this->writePostData(true);
		
		if( $result == 0 )
		{
			$this->object->saveToDb();
			$this->editQuestion();
		}
	}
	
	public function removeImage()
	{
		$position = key($_POST['cmd']['removeImage']);
		$this->object->removeAnswerImage($position);

		$this->object->saveToDb();
		$this->editQuestion();
	}

	public function downkprim_answers()
	{
		if( isset($_POST['cmd'][__FUNCTION__]) && count($_POST['cmd'][__FUNCTION__]) )
		{
			$this->object->moveAnswerDown( key($_POST['cmd'][__FUNCTION__]) );
			$this->object->saveToDb();
		}
		
		$this->editQuestion();
	}

	public function upkprim_answers()
	{
		if( isset($_POST['cmd'][__FUNCTION__]) && count($_POST['cmd'][__FUNCTION__]) )
		{
			$this->object->moveAnswerUp( key($_POST['cmd'][__FUNCTION__]) );
			$this->object->saveToDb();
		}

		$this->editQuestion();
	}

	protected function writePostData($upload = false)
	{
		$form = $this->buildEditForm();
		$form->setValuesByPost();
		
		if( $upload )
		{
			$answersInput = $form->getItemByPostVar('kprim_answers');
			$answersInput->setIgnoreMissingUploadsEnabled(true);
			
			if( !$answersInput->checkUploads($_POST[$answersInput->getPostVar()]) )
			{
				ilUtil::sendFailure($this->lng->txt("form_input_not_valid"));
				$this->editQuestion($form);
				return 1;
			}
			
			$answersInput->collectValidFiles();
		}
		elseif( !$form->checkInput() )
		{
			$this->editQuestion($form);
			return 1;
		}

		$this->writeQuestionGenericPostData();
		
		$this->writeQuestionSpecificPostData($form);
		$this->writeAnswerSpecificPostData($form);
		
		$this->saveTaxonomyAssignments();
		
		return 0;
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

	/**
	 * @param ilPropertyFormGUI $form
	 * @return ilPropertyFormGUI
	 */
	public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form)
	{
		// shuffle answers
		$shuffleAnswers = new ilCheckboxInputGUI($this->lng->txt( "shuffle_answers" ), "shuffle_answers_enabled");
		$shuffleAnswers->setChecked( $this->object->isShuffleAnswersEnabled() );
		$form->addItem($shuffleAnswers);

		if( !$this->object->getSelfAssessmentEditingMode() )
		{
			// answer mode (single-/multi-line)
			$answerType = new ilSelectInputGUI($this->lng->txt('answer_types'), 'answer_type');
			$answerType->setOptions($this->object->getAnswerTypeSelectOptions($this->lng));
			$answerType->setValue( $this->object->getAnswerType() );
			$form->addItem($answerType);
		}

		if( !$this->object->getSelfAssessmentEditingMode() && $this->object->isSingleLineAnswerType($this->object->getAnswerType()) )
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

		// option label
		$optionLabel = new ilRadioGroupInputGUI($this->lng->txt('option_label'), 'option_label');
		$optionLabel->setInfo($this->lng->txt('option_label_info'));
		$optionLabel->setRequired(true);
		$optionLabel->setValue($this->object->getOptionLabel());
		foreach($this->object->getValidOptionLabelsTranslated($this->lng) as $labelValue => $labelText)
		{
			$option = new ilRadioOption($labelText, $labelValue);
			$optionLabel->addOption($option);

			if( $this->object->isCustomOptionLabel($labelValue) )
			{
				$customLabelTrue = new ilTextInputGUI(
					$this->lng->txt('option_label_custom_true'), 'option_label_custom_true'
				);
				$customLabelTrue->setValue($this->object->getCustomTrueOptionLabel());
				$option->addSubItem($customLabelTrue);

				$customLabelFalse = new ilTextInputGUI(
					$this->lng->txt('option_label_custom_false'), 'option_label_custom_false'
				);
				$customLabelFalse->setValue($this->object->getCustomFalseOptionLabel());
				$option->addSubItem($customLabelFalse);
			}
		}
		$form->addItem($optionLabel);
		
		// points
		$points = new ilNumberInputGUI($this->lng->txt('points'), 'points');
		$points->setRequired(true);
		$points->setSize(3);
		$points->allowDecimals(true);
		$points->setMinValue(0);
		$points->setMinvalueShouldBeGreater(true);
		$points->setValue($this->object->getPoints());
		$form->addItem($points);

		// score partial solution
		$scorePartialSolution = new ilCheckboxInputGUI($this->lng->txt('score_partsol_enabled'), 'score_partsol_enabled');
		$scorePartialSolution->setInfo($this->lng->txt('score_partsol_enabled_info'));
		$scorePartialSolution->setChecked( $this->object->isScorePartialSolutionEnabled() );
		$form->addItem($scorePartialSolution);
		
		return $form;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function writeQuestionSpecificPostData(ilPropertyFormGUI $form)
	{
		$oldAnswerType = $this->object->getAnswerType();
		
		$this->object->setShuffleAnswersEnabled($form->getItemByPostVar('shuffle_answers_enabled')->getChecked());
		
		if( !$this->object->getSelfAssessmentEditingMode() )
		{
			$this->object->setAnswerType($form->getItemByPostVar('answer_type')->getValue());
		}
		else
		{
			$this->object->setAnswerType(assKprimChoice::ANSWER_TYPE_MULTI_LINE);
		}

		if( !$this->object->getSelfAssessmentEditingMode() && $this->object->isSingleLineAnswerType($oldAnswerType) )
		{
			$this->object->setThumbSize($form->getItemByPostVar('thumb_size')->getValue());
		}

		$this->object->setOptionLabel($form->getItemByPostVar('option_label')->getValue());

		if( $this->object->isCustomOptionLabel($this->object->getOptionLabel()) )
		{
			$this->object->setCustomTrueOptionLabel( strip_tags(
				$form->getItemByPostVar('option_label_custom_true')->getValue()
			));
			$this->object->setCustomFalseOptionLabel( strip_tags(
				$form->getItemByPostVar('option_label_custom_false')->getValue()
			));
		}
		
		$this->object->setPoints($form->getItemByPostVar('points')->getValue());
		
		$this->object->setScorePartialSolutionEnabled($form->getItemByPostVar('score_partsol_enabled')->getChecked());
	}

	/**
	 * @param ilPropertyFormGUI $form
	 * @return ilPropertyFormGUI
	 */
	public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form)
	{
		require_once 'Modules/TestQuestionPool/classes/class.ilKprimChoiceWizardInputGUI.php';
		$kprimAnswers = new ilKprimChoiceWizardInputGUI($this->lng->txt('answers'), 'kprim_answers');
		$kprimAnswers->setInfo($this->lng->txt('kprim_answers_info'));
		$kprimAnswers->setSize(64);
		$kprimAnswers->setMaxLength(1000);
		$kprimAnswers->setRequired(true);
		$kprimAnswers->setAllowMove(true);
		$kprimAnswers->setQuestionObject($this->object);
		if( !$this->object->getSelfAssessmentEditingMode() )
		{
			$kprimAnswers->setSingleline($this->object->isSingleLineAnswerType($this->object->getAnswerType()));
		}
		else
		{
			$kprimAnswers->setSingleline(false);
		}
		$kprimAnswers->setValues($this->object->getAnswers());
		$form->addItem($kprimAnswers);
		
		return $form;
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function writeAnswerSpecificPostData(ilPropertyFormGUI $form)
	{
		$answers = $form->getItemByPostVar('kprim_answers')->getValues();
		$files = $form->getItemByPostVar('kprim_answers')->getFiles();
		$this->object->handleFileUploads($answers, $files);
		$this->object->setAnswers($answers);
	}
	
	/**
	 * @param integer $active_id
	 * @param integer $pass
	 * @return string
	 */
	function getSpecificFeedbackOutput($active_id, $pass)
	{
		return ''; // question type supports inline answer specific feedback
	}

	/**
	 * @param integer		$active_id
	 * @param integer|null	$pass
	 * @param bool			$is_postponed
	 * @param bool			$use_post_solutions
	 * @param bool			$show_feedback
	 *
	 * @return string
	 */
	function getTestOutput(
		$active_id,
		$pass = NULL,
		$is_postponed = FALSE,
		$use_post_solutions = FALSE,
		$showInlineFeedback = FALSE
	)
	{
		// shuffle output
		$keys = $this->getParticipantsAnswerKeySequence();

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution[$solution_value["value1"]] = $solution_value["value2"];
			}
		}
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_kprim_output.html", TRUE, TRUE, "Modules/TestQuestionPool");

		foreach ($keys as $answer_id)
		{
			$answer = $this->object->getAnswer($answer_id);
			if (strlen($answer->getImageFile()))
			{
				if ($this->object->getThumbSize())
				{
					$template->setCurrentBlock("preview");
					$template->setVariable("URL_PREVIEW", $answer->getImageWebPath());
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
					$template->setVariable("ANSWER_IMAGE_URL", $answer->getThumbWebPath());
					list($width, $height, $type, $attr) = getimagesize($answer->getImageFsPath());
					$alt = $answer->getImageFile();
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
					$template->setVariable("ANSWER_IMAGE_URL", $answer->getImageWebPath());
					list($width, $height, $type, $attr) = getimagesize($answer->getImageFsPath());
					$alt = $answer->getImageFile();
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

			if( $showInlineFeedback )
			{
				$this->populateSpecificFeedbackInline($user_solution, $answer_id, $template);
			}

			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			$template->setVariable('VALUE_TRUE', 1);
			$template->setVariable('VALUE_FALSE', 0);

			if( isset($user_solution[$answer->getPosition()]) )
			{
				$tplVar = $user_solution[$answer->getPosition()] ? 'CHECKED_ANSWER_TRUE' : 'CHECKED_ANSWER_FALSE';
				$template->setVariable($tplVar, " checked=\"checked\"");
			}

			$template->parseCurrentBlock();
		}

		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));

		$template->setVariable("INSTRUCTIONTEXT", $this->object->getInstructionTextTranslation(
			$this->lng, $this->object->getOptionLabel()
		));
		
		$template->setVariable("OPTION_LABEL_TRUE", $this->object->getTrueOptionLabelTranslation(
			$this->lng, $this->object->getOptionLabel()
		));

		$template->setVariable("OPTION_LABEL_FALSE", $this->object->getFalseOptionLabelTranslation(
			$this->lng, $this->object->getOptionLabel()
		));
		
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	/**
	 * @param bool $show_question_only
	 * @param bool $showInlineFeedback
	 */
	public function getPreview($show_question_only = FALSE, $showInlineFeedback = false)
	{
		$user_solution = is_object($this->getPreviewSession()) ? (array)$this->getPreviewSession()->getParticipantsSolution() : array();
		// shuffle output
		$keys = $this->getParticipantsAnswerKeySequence();

		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_kprim_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->getAnswer($answer_id);
			if (strlen($answer->getImageFile()))
			{
				if ($this->object->getThumbSize())
				{
					$template->setCurrentBlock("preview");
					$template->setVariable("URL_PREVIEW", $answer->getImageWebPath());
					$template->setVariable("TEXT_PREVIEW", $this->lng->txt('preview'));
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.svg'));
					$template->setVariable("ANSWER_IMAGE_URL", $answer->getThumbWebPath());
					list($width, $height, $type, $attr) = getimagesize($answer->getImageFsPath());
					$alt = $answer->getImageFile();
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
					$template->setVariable("ANSWER_IMAGE_URL", $answer->getImageWebPath());
					list($width, $height, $type, $attr) = getimagesize($answer->getImageFsPath());
					$alt = $answer->getImageFile();
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

			if( $showInlineFeedback )
			{
				$this->populateSpecificFeedbackInline($user_solution, $answer_id, $template);
			}

			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			$template->setVariable('VALUE_TRUE', 1);
			$template->setVariable('VALUE_FALSE', 0);

			if( isset($user_solution[$answer->getPosition()]) )
			{
				$tplVar = $user_solution[$answer->getPosition()] ? 'CHECKED_ANSWER_TRUE' : 'CHECKED_ANSWER_FALSE';
				$template->setVariable($tplVar, " checked=\"checked\"");
			}
			
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		
		$template->setVariable("INSTRUCTIONTEXT", $this->object->getInstructionTextTranslation(
			$this->lng, $this->object->getOptionLabel()
		));
		
		$template->setVariable("OPTION_LABEL_TRUE", $this->object->getTrueOptionLabelTranslation(
			$this->lng, $this->object->getOptionLabel()
		));
		
		$template->setVariable("OPTION_LABEL_FALSE", $this->object->getFalseOptionLabelTranslation(
			$this->lng, $this->object->getOptionLabel()
		));
		
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		return $questionoutput;
	}

	/**
	 * @param $active_id
	 * @param null $pass
	 * @param bool $graphicalOutput
	 * @param bool $result_output
	 * @param bool $show_question_only
	 * @param bool $show_feedback
	 * @param bool $show_correct_solution
	 * @param bool $show_manual_scoring
	 * @param bool $show_question_text
	 */
	public function getSolutionOutput(
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
		$keys = $this->getParticipantsAnswerKeySequence();

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				$user_solution[$solution_value['value1']] = $solution_value['value2'];
			}
		}
		else
		{
			// take the correct solution instead of the user solution
			foreach ($this->object->getAnswers() as $answer)
			{
				$user_solution[$answer->getPosition()] = $answer->getCorrectness();
			}
		}

		// generate the question output
		$template = new ilTemplate("tpl.il_as_qpl_mc_kprim_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		
		foreach ($keys as $answer_id)
		{
			$answer = $this->object->getAnswer($answer_id);
			
			if (($active_id > 0) && (!$show_correct_solution))
			{
				if ($graphicalOutput)
				{
					// output of ok/not ok icons for user entered solutions

					if( $user_solution[$answer->getPosition()] == $answer->getCorrectness() )
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
			if (strlen($answer->getImageFile()))
			{
				$template->setCurrentBlock("answer_image");
				if ($this->object->getThumbSize())
				{
					$template->setVariable("ANSWER_IMAGE_URL", $answer->getThumbWebPath());
				}
				else
				{
					$template->setVariable("ANSWER_IMAGE_URL", $answer->getImageWebPath());
				}

				$template->setVariable("ANSWER_IMAGE_ALT", ilUtil::prepareFormOutput($answer->getImageFile()));
				$template->setVariable("ANSWER_IMAGE_TITLE", ilUtil::prepareFormOutput($answer->getImageFile()));
				$template->parseCurrentBlock();
			}

			if ($show_feedback)
			{
				$this->populateSpecificFeedbackInline($user_solution, $answer_id, $template);
			}
			
			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));

			if( isset($user_solution[$answer->getPosition()]) )
			{
				if( $user_solution[$answer->getPosition()] )
				{
					$template->setVariable("SOLUTION_IMAGE_TRUE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_checked.png")));
					$template->setVariable("SOLUTION_ALT_TRUE", $this->lng->txt("checked"));
					$template->setVariable("SOLUTION_IMAGE_FALSE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
					$template->setVariable("SOLUTION_ALT_FALSE", $this->lng->txt("unchecked"));
				}
				else
				{
					$template->setVariable("SOLUTION_IMAGE_TRUE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
					$template->setVariable("SOLUTION_ALT_TRUE", $this->lng->txt("unchecked"));
					$template->setVariable("SOLUTION_IMAGE_FALSE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_checked.png")));
					$template->setVariable("SOLUTION_ALT_FALSE", $this->lng->txt("checked"));
				}
			}
			else
			{
				$template->setVariable("SOLUTION_IMAGE_TRUE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
				$template->setVariable("SOLUTION_ALT_TRUE", $this->lng->txt("unchecked"));
				$template->setVariable("SOLUTION_IMAGE_FALSE", ilUtil::getHtmlPath(ilUtil::getImagePath("radiobutton_unchecked.png")));
				$template->setVariable("SOLUTION_ALT_FALSE", $this->lng->txt("unchecked"));
			}
			
			$template->parseCurrentBlock();
		}
		
		if ($show_question_text==true)
		{
			$questiontext = $this->object->getQuestion();
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));

			$template->setVariable("INSTRUCTIONTEXT", $this->object->getInstructionTextTranslation(
				$this->lng, $this->object->getOptionLabel()
			));
		}

		$template->setVariable("OPTION_LABEL_TRUE", $this->object->getTrueOptionLabelTranslation(
			$this->lng, $this->object->getOptionLabel()
		));

		$template->setVariable("OPTION_LABEL_FALSE", $this->object->getFalseOptionLabelTranslation(
			$this->lng, $this->object->getOptionLabel()
		));
		
		
		$questionoutput = $template->get();
		$feedback = ($show_feedback) ? $this->getGenericFeedbackOutput($active_id, $pass) : "";

		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");

		if( strlen($feedback) )
		{
			$solutiontemplate->setVariable("FEEDBACK", $this->object->prepareTextareaOutput( $feedback , true ));
		}
		
		$solutiontemplate->setVariable("SOLUTION_OUTPUT", $questionoutput);

		$solutionoutput = $solutiontemplate->get();
		
		if (!$show_question_only)
		{
			// get page object output
			$solutionoutput = '<div class="ilc_question_Standard">'.$solutionoutput."</div>";
		}
		return $solutionoutput;
	}

	protected function getParticipantsAnswerKeySequence()
	{
		if( !$this->object->isShuffleAnswersEnabled() )
		{
			return array_keys($this->object->getAnswers());
		}
		
		if (strcmp($_GET["activecommand"], "directfeedback") == 0)
		{
			if (is_array($_SESSION["choicekeys"])) $this->choiceKeys = $_SESSION["choicekeys"];
		}
		if (!is_array($this->choiceKeys))
		{
			$this->choiceKeys = array_keys($this->object->getAnswers());
			if ($this->object->getShuffle())
			{
				$this->choiceKeys = $this->object->pcArrayShuffle($this->choiceKeys);
			}
		}
		$_SESSION["choicekeys"] = $this->choiceKeys;
		return $this->choiceKeys;
	}
	
	private function populateSpecificFeedbackInline($user_solution, $answer_id, $template)
	{
		require_once 'Modules/TestQuestionPool/classes/feedback/class.ilAssConfigurableMultiOptionQuestionFeedback.php';
		
		if($this->object->getSpecificFeedbackSetting() == ilAssConfigurableMultiOptionQuestionFeedback::FEEDBACK_SETTING_CHECKED)
		{
			foreach($user_solution as $mc_solution)
			{
				if(strcmp($mc_solution, $answer_id) == 0)
				{
					$fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(), $answer_id);
					if(strlen($fb))
					{
						$template->setCurrentBlock("feedback");
						$template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
						$template->parseCurrentBlock();
					}
				}
			}
		}

		if($this->object->getSpecificFeedbackSetting() == ilAssConfigurableMultiOptionQuestionFeedback::FEEDBACK_SETTING_ALL)
		{
			$fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(), $answer_id);
			if(strlen($fb))
			{
				$template->setCurrentBlock("feedback");
				$template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
				$template->parseCurrentBlock();
			}
		}

		if($this->object->getSpecificFeedbackSetting() == ilAssConfigurableMultiOptionQuestionFeedback::FEEDBACK_SETTING_CORRECT)
		{
			$answer = $this->object->getAnswer($answer_id);

			if($answer->getPoints() > 0)
			{
				$fb = $this->object->feedbackOBJ->getSpecificAnswerFeedbackTestPresentation($this->object->getId(), $answer_id);
				if(strlen($fb))
				{
					$template->setCurrentBlock("feedback");
					$template->setVariable("FEEDBACK", $this->object->prepareTextareaOutput($fb, true));
					$template->parseCurrentBlock();
				}
			}
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
			$this->aggregateAnswers( $relevant_answers, $this->object->getAnswers() ) )->get();
		
		return '<pre>'.print_r($relevant_answers, 1).'</pre>';
	}
	
	public function renderAggregateView($aggregate)
	{
		$trueOptionLabel = $this->object->getTrueOptionLabelTranslation($this->lng, $this->object->getOptionLabel());
		$falseOptionLabel = $this->object->getFalseOptionLabelTranslation($this->lng, $this->object->getOptionLabel());
		
		$tpl = new ilTemplate('tpl.il_as_aggregated_kprim_answers_table.html', true, true, "Modules/TestQuestionPool");

		foreach( $aggregate as $lineData )
		{
			$tpl->setCurrentBlock('aggregaterow');
			$tpl->setVariable('OPTION', $lineData['answertext']);
			$tpl->setVariable('COUNT_TRUE', $lineData['count_true']);
			$tpl->setVariable('COUNT_FALSE', $lineData['count_false']);
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable('OPTION_HEAD', $this->lng->txt('answers'));
		$tpl->setVariable('COUNT_TRUE_HEAD', $trueOptionLabel);
		$tpl->setVariable('COUNT_FALSE_HEAD', $falseOptionLabel);

		return $tpl;
	}
	
	public function aggregateAnswers($rawSolutionData, $answers)
	{
		$aggregate = array();
		
		foreach( $answers as $answer )
		{
			$answerAgg = array(
				'answertext' => $answer->getAnswerText(), 'count_true' => 0, 'count_false' => 0
			);

			foreach( $rawSolutionData as $solutionRecord )
			{
				if( $solutionRecord['value1'] == $answer->getPosition() )
				{
					if( $solutionRecord['value2'] )
					{
						$answerAgg['count_true']++;
					}
					else
					{
						$answerAgg['count_false']++;
					}
				}
				
			}

			$aggregate[] = $answerAgg;
		}
		
		return $aggregate;
	}

}