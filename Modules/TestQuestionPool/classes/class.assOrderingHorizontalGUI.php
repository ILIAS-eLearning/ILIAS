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
* The assOrderingHorizontalGUI class encapsulates the GUI representation
* for horizontal ordering questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @ilctrl_iscalledby assOrderingHorizontalGUI: ilObjQuestionPoolGUI
* */
class assOrderingHorizontalGUI extends assQuestionGUI
{
	/**
	* assOrderingHorizontalGUI constructor
	*
	* The constructor takes possible arguments an creates an instance of the assOrderingHorizontalGUI object.
	*
	* @param integer $id The database id of a single choice question object
	* @access public
	*/
	function __construct($id = -1)
	{
		parent::__construct();
		include_once "./Modules/TestQuestionPool/classes/class.assOrderingHorizontal.php";
		$this->object = new assOrderingHorizontal();
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
			$this->object->setTitle($_POST["title"]);
			$this->object->setAuthor($_POST["author"]);
			$this->object->setComment($_POST["comment"]);
			if ($this->getSelfAssessmentEditingMode())
			{
				$this->object->setNrOfTries($_POST['nr_of_tries']);
			}

			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$questiontext = $_POST["question"];
			$this->object->setQuestion($questiontext);
			$this->object->setPoints($_POST["points"]);
			// adding estimated working time
			$this->object->setEstimatedWorkingTime(
				$_POST["Estimated"]["hh"],
				$_POST["Estimated"]["mm"],
				$_POST["Estimated"]["ss"]
			);
			$this->object->setTextSize($_POST["textsize"]);
			$this->object->setOrderText($_POST["ordertext"]);
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
		$form->setId("orderinghorizontal");

		$this->addBasicQuestionFormProperties($form);

		// ordertext
		$ordertext = new ilTextAreaInputGUI($this->lng->txt("ordertext"), "ordertext");
		$ordertext->setValue($this->object->prepareTextareaOutput($this->object->getOrderText()));
		$ordertext->setRequired(TRUE);
		$ordertext->setInfo(sprintf($this->lng->txt("ordertext_info"), $this->object->separator));
		$ordertext->setRows(10);
		$ordertext->setCols(80);
		$form->addItem($ordertext);
		// textsize
		$textsize = new ilNumberInputGUI($this->lng->txt("textsize"), "textsize");
		$textsize->setValue($this->object->getTextSize());
		$textsize->setInfo($this->lng->txt("textsize_info"));
		$textsize->setSize(6);
		$textsize->setMinValue(10);
		$textsize->setRequired(FALSE);
		$form->addItem($textsize);
		// points
		$points = new ilNumberInputGUI($this->lng->txt("points"), "points");

		// mbecker: Fix for mantis bug 7866: Predefined values schould make sense.
		// This implements a default value of "1" for this question type.
		if ($this->object->getPoints == null)
		{
			$points->setValue("1");			
		} 
		else 
		{
			$points->setValue($this->object->getPoints());
		}
		//$points->setValue($this->object->getPoints());
		$points->setRequired(TRUE);
		$points->setSize(3);
		$points->setMinValue(0.0);
		$points->setMinvalueShouldBeGreater(true);
		$form->addItem($points);

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
		$template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_output_solution.html", TRUE, TRUE, "Modules/TestQuestionPool");

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
		$template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
		$elements = $this->object->getRandomOrderingElements();
		foreach ($elements as $id => $element)
		{
			$template->setCurrentBlock("element");
			$template->setVariable("ELEMENT_ID", "e$id");
			$template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
			$template->parseCurrentBlock();
		}
		if ($this->object->textsize >= 10) echo $template->setVariable("STYLE", " style=\"font-size: " . $this->object->textsize . "%;\"");
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
		$questionoutput = $template->get();
		if (!$show_question_only)
		{
			// get page object output
			$questionoutput = $this->getILIASPage($questionoutput);
		}
		include_once "./Services/YUI/classes/class.ilYuiUtil.php";
		ilYuiUtil::initDragDropAnimation();
		$this->tpl->addJavascript("./Modules/TestQuestionPool/templates/default/orderinghorizontal.js");
		return $questionoutput;
	}

	function getTestOutput($active_id, $pass = NULL, $is_postponed = FALSE, $use_post_solutions = FALSE, $show_feedback = FALSE)
	{
		// generate the question output
		$template = new ilTemplate("tpl.il_as_qpl_orderinghorizontal_output.html",TRUE, TRUE, "Modules/TestQuestionPool");
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
		if (strlen($_SESSION['qst_selection']))
		{
			$this->object->moveRight($_SESSION['qst_selection'], $active_id, $pass);
			unset($_SESSION['qst_selection']);
			$solutions =& $this->object->getSolutionValues($active_id, $pass);
			if (count($solutions) == 1)
			{
				$elements = split("{::}", $solutions[0]["value1"]);
			}
		}
		if (count($solutions) == 0)
		{
			$_SESSION['qst_ordering_horizontal_elements'] = $elements;
		}
		else
		{
			unset($_SESSION['qst_ordering_horizontal_elements']);
		}
		$idx = 0;
		foreach ($elements as $id => $element)
		{
			$template->setCurrentBlock("element");
			$template->setVariable("ELEMENT_ID", "e_" . $this->object->getId() . "_$id");
			$template->setVariable("ELEMENT_VALUE", ilUtil::prepareFormOutput($element));
			$this->ctrl->setParameterByClass('iltestoutputgui', 'qst_selection', $idx);
			$idx++;
			$url = $this->ctrl->getLinkTargetByClass('iltestoutputgui', 'gotoQuestion');
			$template->setVariable("MOVE_RIGHT", $url);
			$template->setVariable("TEXT_MOVE_RIGHT", $this->lng->txt('move_right'));
			$template->setVariable("RIGHT_IMAGE", ilUtil::getImagePath('nav_arr_R.gif'));
			$template->parseCurrentBlock();
		}
		if ($this->object->textsize >= 10) echo $template->setVariable("STYLE", " style=\"font-size: " . $this->object->textsize . "%;\"");
		$template->setVariable("VALUE_ORDERRESULT", ' value="' . join($elements, '{::}') . '"');
		$template->setVariable("QUESTIONTEXT", $this->object->prepareTextareaOutput($this->object->getQuestion(), TRUE));
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
			$commands = $_POST["cmd"];
			if (is_array($commands))
			{
				foreach ($commands as $key => $value)
				{
					if (preg_match("/^suggestrange_.*/", $key, $matches))
					{
						$force_active = true;
					}
				}
			}
			// edit question properties
			$ilTabs->addTarget("edit_properties",
				$url,
				array("editQuestion", "save", "saveEdit", "originalSyncForm"),
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
