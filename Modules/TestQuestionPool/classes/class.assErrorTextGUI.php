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
* The assErrorTextGUI class encapsulates the GUI representation
* for error text questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @ilctrl_iscalledby assErrorTextGUI: ilObjQuestionPoolGUI
* */
class assErrorTextGUI extends assQuestionGUI
{
	/**
	* assErrorTextGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assOrderingHorizontalGUI object.
	*
	* @param integer $id The database id of a single choice question object
	* @access public
	*/
	function __construct($id = -1)
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
			$this->object->setPoints(ilUtil::stripSlashes($_POST["points"]));
			// adding estimated working time
			$this->writeOtherPostData();
			$this->object->setTextSize(ilUtil::stripSlashes($_POST["textsize"]));
			$this->object->setErrorText(ilUtil::stripSlashes($_POST["errortext"]));
			
			$this->object->flushErrorData();
			if (is_array($_POST['errordata']['key']))
			{
				foreach ($_POST['errordata']['key'] as $idx => $val)
				{
					$this->object->addErrorData(ilUtil::stripSlashes($val), ilUtil::stripSlashes($_POST['errordata']['value'][$idx]), ilUtil::stripSlashes($_POST['errordata']['points'][$idx]));
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
		$form->setMultipart(FALSE);
		$form->setTableWidth("100%");
		$form->setId("orderinghorizontal");

		// title
		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setValue($this->object->getTitle());
		$title->setRequired(TRUE);
		$form->addItem($title);
		// author
		$author = new ilTextInputGUI($this->lng->txt("author"), "author");
		$author->setValue($this->object->getAuthor());
		$author->setRequired(TRUE);
		$form->addItem($author);
		// description
		$description = new ilTextInputGUI($this->lng->txt("description"), "comment");
		$description->setValue($this->object->getComment());
		$description->setRequired(FALSE);
		$form->addItem($description);
		// questiontext
		$question = new ilTextAreaInputGUI($this->lng->txt("question"), "question");
		$question->setValue($this->object->prepareTextareaOutput($this->object->getQuestion()));
		$question->setRequired(TRUE);
		$question->setRows(10);
		$question->setCols(80);
		$question->setUseRte(TRUE);
		$question->addPlugin("latex");
		$question->addButton("latex");
		$question->addButton("pastelatex");
		$question->setRTESupport($this->object->getId(), "qpl", "assessment");
		$form->addItem($question);
		// duration
		$duration = new ilDurationInputGUI($this->lng->txt("working_time"), "Estimated");
		$duration->setShowHours(TRUE);
		$duration->setShowMinutes(TRUE);
		$duration->setShowSeconds(TRUE);
		$ewt = $this->object->getEstimatedWorkingTime();
		$duration->setHours($ewt["h"]);
		$duration->setMinutes($ewt["m"]);
		$duration->setSeconds($ewt["s"]);
		$duration->setRequired(FALSE);
		$form->addItem($duration);
		
		if ($this->object->getId())
		{
			$hidden = new ilHiddenInputGUI("", "ID");
			$hidden->setValue($this->object->getId());
			$form->addItem($hidden);
		}
		// errortext
		$errortext = new ilTextAreaInputGUI($this->lng->txt("errortext"), "errortext");
		$errortext->setValue($this->object->prepareTextareaOutput($this->object->getErrorText()));
		$errortext->setRequired(TRUE);
		$errortext->setInfo($this->lng->txt("errortext_info"));
		$errortext->setRows(10);
		$errortext->setCols(80);
		$form->addItem($errortext);

		// textsize
		$textsize = new ilNumberInputGUI($this->lng->txt("textsize"), "textsize");
		$textsize->setValue(strlen($this->object->getTextSize()) ? $this->object->getTextSize() : 100.0);
		$textsize->setInfo($this->lng->txt("textsize_errortext_info"));
		$textsize->setSize(6);
		$textsize->setSuffix("%");
		$textsize->setMinValue(10);
		$textsize->setRequired(true);
		$form->addItem($textsize);
		
		if (count($this->object->getErrorData()))
		{
			$header = new ilFormSectionHeaderGUI();
			$header->setTitle($this->lng->txt("errors_section"));
			$form->addItem($header);

			include_once "./Modules/TestQuestionPool/classes/class.ilErrorTextWizardInputGUI.php";
			$errordata = new ilErrorTextWizardInputGUI($this->lng->txt("errors"), "errordata");
			$values = array();
			$errordata->setKeyName($this->lng->txt('text_wrong'));
			$errordata->setValueName($this->lng->txt('text_correct'));
			$errordata->setValues($this->object->getErrorData());
			$form->addItem($errordata);
		}

		$form->addCommandButton("analyze", $this->lng->txt('analyze_errortext'));
		$form->addCommandButton("save", $this->lng->txt("save"));
		$form->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
		
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
	* Parse the error text
	*/
	public function analyze()
	{
		$this->writePostData(true);
		$this->object->setErrorData($this->object->getErrorsFromText(ilUtil::stripSlashes($_POST['errortext'])));
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
		// get the solution of the user for the active pass or from the last pass if allowed
		$template = new ilTemplate("tpl.il_as_qpl_errortext_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");

		$solutionvalue = "";
		if (($active_id > 0) && (!$show_correct_solution))
		{
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			if (strlen($solutions[0]["value1"]))
			{
				$elements = split("{::}", $solutions[0]["value1"]);
				foreach ($elements as $id => $element)
				{
					$template->setCurrentBlock("element");
					$template->setVariable("ELEMENT_ID", "e$id");
					$template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
					$template->parseCurrentBlock();
				}
			}
			$solutionvalue = str_replace("{::}", " ", $solutions[0]["value1"]);
		}
		else
		{
			$elements = $this->object->getOrderingElements();
			foreach ($elements as $id => $element)
			{
				$template->setCurrentBlock("element");
				$template->setVariable("ELEMENT_ID", "e$id");
				$template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
				$template->parseCurrentBlock();
			}
			$solutionvalue = join($this->object->getOrderingElements(), " ");
		}

		if (($active_id > 0) && (!$show_correct_solution))
		{
			$reached_points = $this->object->getReachedPoints($active_id, $pass);
			if ($graphicalOutput)
			{
				// output of ok/not ok icons for user entered solutions
				if ($reached_points == $this->object->getMaximumPoints())
				{
					$template->setCurrentBlock("icon_ok");
					$template->setVariable("ICON_OK", ilUtil::getImagePath("icon_ok.gif"));
					$template->setVariable("TEXT_OK", $this->lng->txt("answer_is_right"));
					$template->parseCurrentBlock();
				}
				else
				{
					$template->setCurrentBlock("icon_ok");
					if ($reached_points > 0)
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_mostly_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_not_correct_but_positive"));
					}
					else
					{
						$template->setVariable("ICON_NOT_OK", ilUtil::getImagePath("icon_not_ok.gif"));
						$template->setVariable("TEXT_NOT_OK", $this->lng->txt("answer_is_wrong"));
					}
					$template->parseCurrentBlock();
				}
			}
		}
		else
		{
			$reached_points = $this->object->getPoints();
		}

		if ($result_output)
		{
			$resulttext = ($reached_points == 1) ? "(%s " . $this->lng->txt("point") . ")" : "(%s " . $this->lng->txt("points") . ")"; 
			$template->setVariable("RESULT_OUTPUT", sprintf($resulttext, $reached_points));
		}
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
//		$template->setVariable("SOLUTION_TEXT", ilUtil::prepareFormOutput($solutionvalue));
		if ($this->object->textsize >= 10) echo $template->setVariable("STYLE", " style=\"font-size: " . $this->object->textsize . "%;\"");

		$questionoutput = $template->get();
		$solutiontemplate = new ilTemplate("tpl.il_as_tst_solution_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
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
		$template = new ilTemplate("tpl.il_as_qpl_errortext_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		if ($this->object->getTextSize() >= 10) echo $template->setVariable("STYLE", " style=\"font-size: " . $this->object->getTextSize() . "%;\"");
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->question, TRUE));
		$template->setVariable("ERRORTEXT", str_replace("\n", "<br />", $this->object->createErrorTextOutput()));
		$template->setVariable("ERRORTEXT_ID", uniqid());
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initElementSelection();
		$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/errortext.js");
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		// generate the question output
		$template = new ilTemplate("tpl.il_as_qpl_errortext_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$elements = $this->object->getRandomOrderingElements();
		
		if ($active_id)
		{
			$solutions = NULL;
			include_once "./Modules/Test/classes/class.ilObjTest.php";
			if (!ilObjTest::_getUsePreviousAnswers($active_id, true))
			{
				if (is_null($pass)) $pass = ilObjTest::_getPass($active_id);
			}
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			if (count($solutions) == 1)
			{
				$elements = split("{::}", $solutions[0]["value1"]);
			}
		}
		
		foreach ($elements as $id => $element)
		{
			$template->setCurrentBlock("element");
			$template->setVariable("ELEMENT_ID", "e$id");
			$template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
			$template->parseCurrentBlock();
		}
		if ($this->object->textsize >= 10) echo $template->setVariable("STYLE", " style=\"font-size: " . $this->object->textsize . "%;\"");
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->question, TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDragDropAnimation();
		$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/orderinghorizontal.js");
		$questionoutput = $template->get();
		$pageoutput = $this->outQuestionPage("", $is_postponed, $active_id, $questionoutput);
		return $pageoutput;
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
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "saveEdit", "analyze"),
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
}
?>
