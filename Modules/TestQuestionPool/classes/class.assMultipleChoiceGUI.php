<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Multiple choice question GUI representation
*
* The assMultipleChoiceGUI class encapsulates the GUI representation
* for multiple choice questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assMultipleChoiceGUI extends assQuestionGUI
{
	var $choiceKeys;
	
	/**
	* assMultipleChoiceGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assMultipleChoiceGUI object.
	*
	* @param integer $id The database id of a multiple choice question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assMultipleChoice.php";
		$this->object = new assMultipleChoice();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
	}

	function getCommand($cmd)
	{
		return $cmd;
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData($always = false)
	{
		$hasErrors = (!$always) ? $this->editQuestion(true) : false;
		if (!$hasErrors)
		{
			$this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
			$this->object->setComment(ilUtil::stripSlashes($_POST["comment"]));
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = ilUtil::stripSlashes($_POST["question"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
			$this->object->setQuestion($questiontext);
			$this->object->setShuffle($_POST["shuffle"]);
			$this->object->setEstimatedWorkingTime(
				ilUtil::stripSlashes($_POST["Estimated"]["hh"]),
				ilUtil::stripSlashes($_POST["Estimated"]["mm"]),
				ilUtil::stripSlashes($_POST["Estimated"]["ss"])
			);
			if ($this->getSelfAssessmentEditingMode())
			{
				$this->object->setNrOfTries($_POST['nr_of_tries']);
			}
			$this->object->setMultilineAnswerSetting($_POST["types"]);
			$this->object->setThumbSize((strlen($_POST["thumb_size"])) ? $_POST["thumb_size"] : "");

			// Delete all existing answers and create new answers from the form data
			$this->object->flushAnswers();
			if (!$this->object->getMultilineAnswerSetting())
			{
				foreach ($_POST['choice']['answer'] as $index => $answer)
				{
					$filename = $_POST['choice']['imagename'][$index];
					if (strlen($_FILES['choice']['name']['image'][$index]))
					{
						// upload image
						$filename = $this->object->createNewImageFileName($_FILES['choice']['name']['image'][$index]);
						$upload_result = $this->object->setImageFile($filename, $_FILES['choice']['tmp_name']['image'][$index]);
						if ($upload_result != 0)
						{
							$filename = "";
						}
					}
					$answertext = ilUtil::stripSlashes($answer, false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
					$this->object->addAnswer($answertext, $_POST['choice']['points'][$index], $_POST['choice']['points_unchecked'][$index], $index, $filename);
				}
			}
			else
			{
				foreach ($_POST['choice']['answer'] as $index => $answer)
				{
					$answertext = ilUtil::stripSlashes($answer, false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment"));
					$this->object->addAnswer($answertext, $_POST['choice']['points'][$index], $_POST['choice']['points_unchecked'][$index], $index);
				}
			}
			return 0;
		}
		else
		{
			return 1;
		}
	}

	/**
	* Creates an output of the edit form for the question
	*
	* @access public
	*/
	public function editQuestion($checkonly = FALSE)
	{
		$save = ((strcmp($this->ctrl->getCmd(), "save") == 0) || (strcmp($this->ctrl->getCmd(), "saveEdit") == 0)) ? TRUE : FALSE;
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$usegraphics = ($save) ? (($_POST['types']) ? false : true) : (($this->object->getMultilineAnswerSetting()) ? false : true);
		if ($usegraphics)
		{
			$form->setMultipart(TRUE);
		}
		else
		{
			$form->setMultipart(FALSE);
		}
		$form->setTableWidth("100%");
		$form->setId("assmultiplechoice");

		// title, author, description, question, working time (assessment mode)
		$this->addBasicQuestionFormProperties($form);

		// shuffle
		$shuffle = new ilCheckboxInputGUI($this->lng->txt("shuffle_answers"), "shuffle");
		$shuffle->setValue(1);
		$shuffle->setChecked($this->object->getShuffle());
		$shuffle->setRequired(FALSE);
		$form->addItem($shuffle);
	
		if ($this->object->getId())
		{
			$hidden = new ilHiddenInputGUI("", "ID");
			$hidden->setValue($this->object->getId());
			$form->addItem($hidden);
		}

		if (!$this->getSelfAssessmentEditingMode())
		{
			// Answer types
			$types = new ilSelectInputGUI($this->lng->txt("answer_types"), "types");
			$types->setRequired(false);
			$types->setValue(($this->object->getMultilineAnswerSetting()) ? 1 : 0);
			$types->setOptions(array(
				0 => $this->lng->txt('answers_singleline'),
				1 => $this->lng->txt('answers_multiline'),
			));
			$form->addItem($types);
		}

		if (($usegraphics) && (!$this->getSelfAssessmentEditingMode()))
		{
			// thumb size
			$thumb_size = new ilNumberInputGUI($this->lng->txt("thumb_size"), "thumb_size");
			$thumb_size->setMinValue(20);
			$thumb_size->setDecimals(0);
			$thumb_size->setSize(6);
			$thumb_size->setInfo($this->lng->txt('thumb_size_info'));
			$thumb_size->setValue($this->object->getThumbSize());
			$thumb_size->setRequired(false);
			$form->addItem($thumb_size);
		}

		// Choices
		include_once "./Modules/TestQuestionPool/classes/class.ilMultipleChoiceWizardInputGUI.php";
		$choices = new ilMultipleChoiceWizardInputGUI($this->lng->txt("answers"), "choice");
		if ($this->getSelfAssessmentEditingMode()) $choices->setHideImages(true);
		$choices->setRequired(true);
		$choices->setQuestionObject($this->object);
		$choices->setSingleline(($this->object->getMultilineAnswerSetting()) ? false : true);
		$choices->setAllowMove(false);
		if ($this->object->getAnswerCount() == 0) $this->object->addAnswer("", 0, 0, 0);
		$choices->setValues($this->object->getAnswers());
		$form->addItem($choices);

		$form->addCommandButton("save", $this->lng->txt("save"));
		if (!$this->getSelfAssessmentEditingMode()) $form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
	
		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$errors = !$form->checkInput();
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
		$this->object->addAnswer("", 0, 0, $position+1);
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

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions, $show_feedback); 
		$this->tpl->setVariable("QUESTION_OUTPUT", $test_output);
		$this->tpl->setVariable("FORMACTION", $formaction);
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
		$show_manual_scoring = FALSE
	)
	{
		// shuffle output
		$keys = $this->getChoiceKeys();

		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = array();
		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			foreach ($solutions as $idx => $solution_value)
			{
				array_push($user_solution, $solution_value["value1"]);
			}
		}
		else
		{
			// take the correct solution instead of the user solution
			foreach ($this->object->answers as $index => $answer)
			{
				$points_checked = $answer->getPointsChecked();
				$points_unchecked = $answer->getPointsUnchecked();
				if ($points_checked > $points_unchecked)
				{
					if ($points_checked > 0)
					{
						array_push($user_solution, $index);
					}
				}
			}
		}
		
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_mr_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
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
					$checked = FALSE;
					foreach ($user_solution as $mc_solution)
					{
						if (strcmp($mc_solution, $answer_id) == 0)
						{
							$checked = TRUE;
						}
					}
					if ($checked)
					{
						if ($answer->getPointsChecked() > $answer->getPointsUnchecked())
						{
							$ok = TRUE;
						}
						else
						{
							$ok = FALSE;
						}
					}
					else
					{
						if ($answer->getPointsChecked() > $answer->getPointsUnchecked())
						{
							$ok = FALSE;
						}
						else
						{
							$ok = TRUE;
						}
					}
					if ($ok)
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
						$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
						$template->parseCurrentBlock();
					}
					else
					{
						$template->setCurrentBlock("icon_ok");
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
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
				foreach ($user_solution as $mc_solution)
				{
					if (strcmp($mc_solution, $answer_id) == 0)
					{
						$fb = $this->object->getFeedbackSingleAnswer($answer_id);
						if (strlen($fb))
						{
							$template->setCurrentBlock("feedback");
							$template->setVariable("FEEDBACK", $fb);
							$template->parseCurrentBlock();
						}
					}
				}
			}
			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			$checked = FALSE;
			if ($result_output)
			{
				$pointschecked = $this->object->answers[$answer_id]->getPointsChecked();
				$pointsunchecked = $this->object->answers[$answer_id]->getPointsUnchecked();
				$resulttextchecked = ($pointschecked == 1) || ($pointschecked == -1) ? "%s " . $this->lng->txt("point") : "%s " . $this->lng->txt("points");
				$resulttextunchecked = ($pointsunchecked == 1) || ($pointsunchecked == -1) ? "%s " . $this->lng->txt("point") : "%s " . $this->lng->txt("points"); 
				$template->setVariable("RESULT_OUTPUT", sprintf("(" . $this->lng->txt("checkbox_checked") . " = $resulttextchecked, " . $this->lng->txt("checkbox_unchecked") . " = $resulttextunchecked)", $pointschecked, $pointsunchecked));
			}
			foreach ($user_solution as $mc_solution)
			{
				if (strcmp($mc_solution, $answer_id) == 0)
				{
					$template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_checked.gif")));
					$template->setVariable("SOLUTION_ALT", $this->lng->txt("checked"));
					$checked = TRUE;
				}
			}
			if (!$checked)
			{
				$template->setVariable("SOLUTION_IMAGE", ilUtil::getHtmlPath(ilUtil::getImagePath("checkbox_unchecked.gif")));
				$template->setVariable("SOLUTION_ALT", $this->lng->txt("unchecked"));
			}
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$feedback = ($show_feedback) ? $this->getAnswerFeedbackOutput($active_id, $pass) : "";
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
	
	function getPreview($show_question_only = FALSE)
	{
		// shuffle output
		$keys = $this->getChoiceKeys();

		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_mr_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
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
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
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
			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
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

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		// shuffle output
		$keys = $this->getChoiceKeys();

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
				array_push($user_solution, $solution_value["value1"]);
			}
		}
		// generate the question output
		include_once "./classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_mc_mr_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
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
					$template->setVariable("IMG_PREVIEW", ilUtil::getImagePath('enlarge.gif'));
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

			foreach ($user_solution as $mc_solution)
			{
				if (strcmp($mc_solution, $answer_id) == 0)
				{
					if ($show_feedback)
					{
						$feedback = $this->object->getFeedbackSingleAnswer($answer_id);
						if (strlen($feedback))
						{
							$template->setCurrentBlock("feedback");
							$template->setVariable("FEEDBACK", $feedback);
							$template->parseCurrentBlock();
						}
					}
				}
			}

			$template->setCurrentBlock("answer_row");
			$template->setVariable("ANSWER_ID", $answer_id);
			$template->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			foreach ($user_solution as $mc_solution)
			{
				if (strcmp($mc_solution, $answer_id) == 0)
				{
					$template->setVariable("CHECKED_ANSWER", " checked=\"checked\"");
				}
			}
			$template->parseCurrentBlock();
		}
		$questiontext = $this->object->getQuestion();
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
	}

	/**
	* Saves the feedback for a single choice question
	*
	* @access public
	*/
	function saveFeedback()
	{
		include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
		$this->object->saveFeedbackGeneric(0, ilUtil::stripSlashes($_POST["feedback_incomplete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		$this->object->saveFeedbackGeneric(1, ilUtil::stripSlashes($_POST["feedback_complete"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		foreach ($this->object->answers as $index => $answer)
		{
			$this->object->saveFeedbackSingleAnswer($index, ilUtil::stripSlashes($_POST["feedback_answer_$index"], false, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")));
		}
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
	}

	/**
	* Creates the output of the feedback page for a single choice question
	*
	* @access public
	*/
	function feedback()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "feedback", "tpl.il_as_qpl_mc_mr_feedback.html", "Modules/TestQuestionPool");
		foreach ($this->object->answers as $index => $answer)
		{
			$this->tpl->setCurrentBlock("feedback_answer");
			$this->tpl->setVariable("FEEDBACK_TEXT_ANSWER", $this->lng->txt("feedback"));
			$this->tpl->setVariable("ANSWER_TEXT", $this->object->prepareTextareaOutput($answer->getAnswertext(), TRUE));
			$this->tpl->setVariable("ANSWER_ID", $index);
			$this->tpl->setVariable("VALUE_FEEDBACK_ANSWER", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackSingleAnswer($index)), FALSE));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FEEDBACK_TEXT", $this->lng->txt("feedback"));
		$this->tpl->setVariable("FEEDBACK_COMPLETE", $this->lng->txt("feedback_complete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_COMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(1)), FALSE));
		$this->tpl->setVariable("FEEDBACK_INCOMPLETE", $this->lng->txt("feedback_incomplete_solution"));
		$this->tpl->setVariable("VALUE_FEEDBACK_INCOMPLETE", ilUtil::prepareFormOutput($this->object->prepareTextareaOutput($this->object->getFeedbackGeneric(0)), FALSE));
		$this->tpl->setVariable("FEEDBACK_ANSWERS", $this->lng->txt("feedback_answers"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		include_once "./Services/RTE/classes/class.ilRTE.php";
		$rtestring = ilRTE::_getRTEClassname();
		include_once "./Services/RTE/classes/class.$rtestring.php";
		$rte = new $rtestring();
		$rte->addPlugin("latex");
		$rte->addButton("latex"); 
		$rte->addButton("pastelatex");
		include_once "./classes/class.ilObject.php";
		$obj_id = $_GET["q_id"];
		$obj_type = ilObject::_lookupType($_GET["ref_id"], TRUE);
		$rte->addRTESupport($obj_id, $obj_type, "assessment");
	}

	/**
	* Sets the ILIAS tabs for this question type
	*
	* @access public
	*/
	function setQuestionTabs()
	{
		global $rbacsystem, $ilTabs;
		
		$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
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
				$ilTabs->addTarget("edit_content",
					$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "edit"),
					array("edit", "insert", "exec_pg"),
					"", "", $force_active);
			}
	
			// edit page
			$ilTabs->addTarget("preview",
				$this->ctrl->getLinkTargetByClass("ilPageObjectGUI", "preview"),
				array("preview"),
				"ilPageObjectGUI", "", $force_active);
		}
		$force_active = false;
		if ($rbacsystem->checkAccess('write', $_GET["ref_id"]))
		{
			$url = "";
			if ($classname) $url = $this->ctrl->getLinkTargetByClass($classname, "editQuestion");
			$force_active = false;
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "saveEdit", "addchoice", "removechoice", "removeimagechoice", "uploadchoice"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}
		
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("solution_hint",
				$this->ctrl->getLinkTargetByClass($classname, "suggestedsolution"),
				array("suggestedsolution", "saveSuggestedSolution", "outSolutionExplorer", "cancel", 
				"addSuggestedSolution","cancelExplorer", "linkChilds", "removeSuggestedSolution"
				),
				$classname, 
				""
			);
		}

		// Assessment of questions sub menu entry
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("statistics",
				$this->ctrl->getLinkTargetByClass($classname, "assessment"),
				array("assessment"),
				$classname, "");
		}
		
		if (($_GET["calling_test"] > 0) || ($_GET["test_ref_id"] > 0))
		{
			$ref_id = $_GET["calling_test"];
			if (strlen($ref_id) == 0) $ref_id = $_GET["test_ref_id"];
			$ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}
	
	/*
	 * Create the key index numbers for the array of choices
	 * 
	 * @return array
	 */
	function getChoiceKeys()
	{
		if (strcmp($_GET["activecommand"], "directfeedback") == 0)
		{
			if (is_array($_SESSION["choicekeys"])) $this->choiceKeys = $_SESSION["choicekeys"];
		}
		if (!is_array($this->choiceKeys))
		{
			$this->choiceKeys = array_keys($this->object->answers);
			if ($this->object->getShuffle())
			{
				$this->choiceKeys = $this->object->pcArrayShuffle($this->choiceKeys);
			}
		}
		$_SESSION["choicekeys"] = $this->choiceKeys;
		return $this->choiceKeys;
	}
}
?>
