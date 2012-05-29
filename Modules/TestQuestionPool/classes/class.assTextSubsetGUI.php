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
* The assTextSubsetGUI class encapsulates the GUI representation
* for multiple choice questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author		Björn Heyser <bheyser@databay.de>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assTextSubsetGUI extends assQuestionGUI
{
	/**
	* assTextSubsetGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assTextSubsetGUI object.
	*
	* @param integer $id The database id of a text subset question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assTextSubset.php";
		$this->object = new assTextSubset();
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
			$this->object->setTitle($_POST["title"]);
			$this->object->setAuthor($_POST["author"]);
			$this->object->setComment($_POST["comment"]);
			if ($this->getSelfAssessmentEditingMode())
			{
				$this->object->setNrOfTries($_POST['nr_of_tries']);
			}
			
			// mbecker: fix for 8407
			$this->object->setEstimatedWorkingTime(
				$_POST["Estimated"]["hh"],
				$_POST["Estimated"]["mm"],
				$_POST["Estimated"]["ss"]
			);
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = $_POST["question"];
			$this->object->setQuestion($questiontext);
			$this->object->setCorrectAnswers($_POST["correctanswers"]);
			$this->object->setTextRating($_POST["text_rating"]);
			// Delete all existing answers and create new answers from the form data
			$this->object->flushAnswers();
			foreach ($_POST['answers']['answer'] as $index => $answer)
			{
				$answertext = $answer;
				$this->object->addAnswer($answertext, $_POST['answers']['points'][$index], $index);
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
		$save = $this->isSaveCommand();
		$this->getQuestionTemplate();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->outQuestionType());
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("asstextsubset");

		$this->addBasicQuestionFormProperties($form);

		// number of requested answers
		$correctanswers = new ilNumberInputGUI($this->lng->txt("nr_of_correct_answers"), "correctanswers");
		$correctanswers->setMinValue(1);
		$correctanswers->setDecimals(0);
		$correctanswers->setSize(3);
		$correctanswers->setValue($this->object->getCorrectAnswers());
		$correctanswers->setRequired(true);
		$form->addItem($correctanswers);

		// maximum available points
		$points = new ilNumberInputGUI($this->lng->txt("maximum_points"), "points");
		$points->setMinValue(0.25);
		$points->setSize(6);
		$points->setDisabled(true);
		$points->setValue($this->object->getMaximumPoints());
		$points->setRequired(false);
		$form->addItem($points);

		// text rating
		$textrating = new ilSelectInputGUI($this->lng->txt("text_rating"), "text_rating");
		$text_options = array(
			"ci" => $this->lng->txt("cloze_textgap_case_insensitive"),
			"cs" => $this->lng->txt("cloze_textgap_case_sensitive")
		);
		if (!$this->getSelfAssessmentEditingMode())
		{
			$text_options["l1"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "1");
			$text_options["l2"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "2");
			$text_options["l3"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "3");
			$text_options["l4"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "4");
			$text_options["l5"] = sprintf($this->lng->txt("cloze_textgap_levenshtein_of"), "5");
		}
		$textrating->setOptions($text_options);
		$textrating->setValue($this->object->getTextRating());
		$form->addItem($textrating);

		// Choices
		include_once "./Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php";
		$choices = new ilAnswerWizardInputGUI($this->lng->txt("answers"), "answers");
		$choices->setRequired(true);
		$choices->setQuestionObject($this->object);
		$choices->setSingleline(true);
		$choices->setAllowMove(false);
		if ($this->object->getAnswerCount() == 0) $this->object->addAnswer("", 0, 0);
		$choices->setValues($this->object->getAnswers());
		$form->addItem($choices);

		$this->addQuestionFormCommandButtons($form);
	
		$errors = false;
	
		if ($save)
		{
			$form->setValuesByPost();
			$points->setValue($this->object->getMaximumPoints());
			$errors = !$form->checkInput();
			$form->setValuesByPost(); // again, because checkInput now performs the whole stripSlashes handling and we need this if we don't want to have duplication of backslashes
			if ($errors) $checkonly = false;
		}

		if (!$checkonly) $this->tpl->setVariable("QUESTION_DATA", $form->getHTML());
		return $errors;
	}

	/**
	* Add a new answer
	*/
	public function addanswers()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['addanswers']);
		$this->object->addAnswer("", 0, $position+1);
		$this->editQuestion();
	}

	/**
	* Remove an answer
	*/
	public function removeanswers()
	{
		$this->writePostData(true);
		$position = key($_POST['cmd']['removeanswers']);
		$this->object->deleteAnswer($position);
		$this->editQuestion();
	}

	function outQuestionForTest($formaction, $active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		$test_output = $this->getTestOutput($active_id, $pass, $is_postponed, $use_post_solutions); 
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
			$rank = array();
			foreach ($this->object->answers as $answer)
			{
				if ($answer->getPoints() > 0)
				{
					if (!is_array($rank[$answer->getPoints()]))
					{
						$rank[$answer->getPoints()] = array();
					}
					array_push($rank[$answer->getPoints()], $answer->getAnswertext());
				}
			}
			krsort($rank, SORT_NUMERIC);
			foreach ($rank as $index => $bestsolutions)
			{
				array_push($solutions, array("value1" => join(",", $bestsolutions), "points" => $index));
			}
		}
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_textsubset_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$available_answers =& $this->object->getAvailableAnswers();
		for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++)
		{
			if ((!$test_id) && (strcmp($solutions[$i]["value1"], "") == 0))
			{
			}
			else
			{
				if (($active_id > 0) && (!$show_correct_solution))
				{
					if ($graphicalOutput)
					{
						// output of ok/not ok icons for user entered solutions
						$index = $this->object->isAnswerCorrect($available_answers, $solutions[$i]["value1"]);
						$correct = FALSE;
						if ($index !== FALSE)
						{
							unset($available_answers[$index]);
							$correct = TRUE;
						}
						if ($correct)
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
				$template->setCurrentBlock("textsubset_row");
				$template->setVariable("SOLUTION", $solutions[$i]["value1"]);
				$template->setVariable("COUNTER", $i+1);
				if ($result_output)
				{
					$points = $solutions[$i]["points"];
					$resulttext = ($points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
					$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $points));
				}
				$template->parseCurrentBlock();
			}
		}
		$questiontext = $this->object->getQuestion();
		if ($show_question_text==true)
		{
			$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($questiontext, TRUE));
		}
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
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_textsubset_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$width = $this->object->getMaxTextboxWidth();
		for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++)
		{
			$template->setCurrentBlock("textsubset_row");
			$template->setVariable("COUNTER", $i+1);
			$template->setVariable("TEXTFIELD_ID", sprintf("%02d", $i+1));
			$template->setVariable("TEXTFIELD_SIZE", $width);
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

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE)
	{
		// get the solution of the user for the active pass or from the last pass if allowed
		$user_solution = "";
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
		
		// generate the question output
		include_once "./Services/UICore/classes/class.ilTemplate.php";
		$template = new ilTemplate("tpl.il_as_qpl_textsubset_output.html", TRUE, TRUE, "Modules/TestQuestionPool");
		$width = $this->object->getMaxTextboxWidth();
		for ($i = 0; $i < $this->object->getCorrectAnswers(); $i++)
		{
			$template->setCurrentBlock("textsubset_row");
			foreach ($solutions as $idx => $solution_value)
			{
				if ($idx == $i)
				{
					$template->setVariable("TEXTFIELD_VALUE", " value=\"" . $solution_value["value1"]."\"");
				}
			}
			$template->setVariable("COUNTER", $i+1);
			$template->setVariable("TEXTFIELD_ID", sprintf("%02d", $i+1));
			$template->setVariable("TEXTFIELD_SIZE", $width);
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
		$errors = $this->feedback(true);
		$this->object->saveFeedbackGeneric(0, $_POST["feedback_incomplete"]);
		$this->object->saveFeedbackGeneric(1, $_POST["feedback_complete"]);
		$this->object->cleanupMediaObjectUsage();
		parent::saveFeedback();
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
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "saveEdit", "addanswers", "removeanswers", "originalSyncForm"),
				$classname, "", $force_active);
		}

		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("feedback",
				$this->ctrl->getLinkTargetByClass($classname, "feedback"),
				array("feedback", "saveFeedback"),
				$classname, "");
		}

		// add tab for question hint within common class assQuestionGUI
		$this->addTab_QuestionHints($ilTabs);
		
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

                        global $___test_express_mode;

                        if (!$_GET['test_express_mode'] && !$___test_express_mode) {
                            $ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), "ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=$ref_id");
                        }
                        else {
                            $link = ilTestExpressPage::getReturnToPageLink();
                            $ilTabs->setBackTarget($this->lng->txt("backtocallingtest"), $link);
                        }
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("qpl"), $this->ctrl->getLinkTargetByClass("ilobjquestionpoolgui", "questions"));
		}
	}
}
?>
