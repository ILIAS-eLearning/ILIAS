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

require_once "./assessment/classes/class.assQuestion.php";

/**
* Basic GUI class for assessment questions
*
* The ASS_QuestionGUI class encapsulates basic GUI functions
* for assessment questions.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.assQuestionGUI.php
* @modulegroup   Assessment
*/
class ASS_QuestionGUI
{
	/**
	* Question object
	*
	* A reference to the matching question object
	*
	* @var object
	*/
	var $object;

	var $tpl;
	var $lng;
	var $error;
	
	/**
	* ASS_QuestionGUI constructor
	*
	* ASS_QuestionGUI constructor
	*
	* @access public
	*/
	function ASS_QuestionGUI()
	{
		global $lng, $tpl, $ilCtrl;


		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "q_id");

		$this->object = new ASS_Question();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

	function getCommand($cmd)
	{
		return $cmd;
	}


	/**
	* Returns the question type string
	*
	* Returns the question type string
	*
	* @result string The question type string
	* @access public
	*/
	function getQuestionType()
	{
		return "";
	}

	/**
	* needed for page editor compliance
	*/
	function getType()
	{
		return $this->getQuestionType();
	}

	/**
	* Sets the extra fields i.e. estimated working time of a question from a posted create/edit form
	*
	* Sets the extra fields i.e. estimated working time of a question from a posted create/edit form
	*
	* @access private
	*/
	function outOtherQuestionData()
	{
	}

	/**
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* Evaluates a posted edit form and writes the form data in the question object
	*
	* @return integer A positive value, if one of the required fields wasn't set, else 0
	* @access private
	*/
	function writePostData()
	{
	}

	/**
	* Creates the question output form for the learner
	*
	* Creates the question output form for the learner
	*
	* @access public
	*/
	function outWorkingForm($test_id = "", $is_postponed = false)
	{
	}

	/**
	* output assessment
	*/
	function assessment()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// catch feedback message
		sendInfo();

		//$this->setLocator();

		$title = $this->lng->txt("qpl_assessment_of_questions");
		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		//$question =& $this->object->createQuestion("", $_GET["edit"]);
		$total_of_answers = $this->object->getTotalAnswers();
		$counter = 0;
		$color_class = array("tblrow1", "tblrow2");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_assessment_of_questions.html", true);
		if (!$total_of_answers)
		{
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ASSESSMENT", $this->lng->txt("qpl_assessment_no_assessment_of_questions"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("qpl_assessment_total_of_answers"));
			$this->tpl->setVariable("TXT_VALUE", $total_of_answers);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("qpl_assessment_total_of_right_answers"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", $this->object->_getTotalRightAnswers($_GET["q_id"]) * 100.0) . " %");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_QUESTION_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* Sets the other data i.e. estimated working time of a question from a posted create/edit form
	*
	* Sets the other data i.e. estimated working time of a question from a posted create/edit form
	*
	* @return boolean Returns true, if the question had to be autosaved
	* @access private
	*/
	function writeOtherPostData($result = 0)
	{
		$this->object->setEstimatedWorkingTime(
			ilUtil::stripSlashes($_POST["Estimated"][h]),
			ilUtil::stripSlashes($_POST["Estimated"][m]),
			ilUtil::stripSlashes($_POST["Estimated"][s])
		);

		$saved = false;
		return $saved;
	}

	/**
	* Creates a question gui representation
	*
	* Creates a question gui representation and returns the alias to the question gui
	* note: please do not use $this inside this method to allow static calls
	*
	* @param string $question_type The question type as it is used in the language database
	* @param integer $question_id The database ID of an existing question to load it into ASS_QuestionGUI
	* @return object The alias to the question object
	* @access public
	*/
	function &_getQuestionGUI($question_type, $question_id = -1)
	{
		if ((!$question_type) and ($question_id > 0))
		{
			$question_type = ASS_Question::getQuestionTypeFromDb($question_id);
// echo ":".$question_type;
		}
		switch ($question_type)
		{
			case "qt_multiple_choice_sr":
				$question =& new ASS_MultipleChoiceGUI();
				$question->object->set_response(RESPONSE_SINGLE);
				break;

			case "qt_multiple_choice_mr":
				$question =& new ASS_MultipleChoiceGUI();
				$question->object->set_response(RESPONSE_MULTIPLE);
				break;

			case "qt_cloze":
				$question =& new ASS_ClozeTestGUI();
				break;

			case "qt_matching":
				$question =& new ASS_MatchingQuestionGUI();
				break;

			case "qt_ordering":
				$question =& new ASS_OrderingQuestionGUI();
				break;

			case "qt_imagemap":
				$question =& new ASS_ImagemapQuestionGUI();
				break;

			case "qt_javaapplet":
				$question =& new ASS_JavaAppletGUI();
				break;
		}
		if ($question_id > 0)
		{
			$question->object->loadFromDb($question_id);
		}

		return $question;
	}

	function _getGUIClassNameForId($a_q_id)
	{
		$q_type =  ASS_Question::getQuestionTypeFromDb($a_q_id);
		$class_name = ASS_QuestionGUI::_getClassNameForQType($q_type);
		return $class_name;
	}

	function _getClassNameForQType($q_type)
	{
		switch ($q_type)
		{
			case "qt_multiple_choice_sr":
				return "ASS_MultipleChoiceGUI";
				break;

			case "qt_multiple_choice_mr":
				return "ASS_MultipleChoiceGUI";
				break;

			case "qt_cloze":
				return "ASS_ClozeTestGUI";
				break;

			case "qt_matching":
				return "ASS_MatchingQuestionGUI";
				break;

			case "qt_ordering":
				return "ASS_OrderingQuestionGUI";
				break;

			case "qt_imagemap":
				return "ASS_ImagemapQuestionGUI";
				break;

			case "qt_javaapplet":
				return "ASS_JavaAppletGUI";
				break;
		}

	}

	/**
	* Creates a question gui representation
	*
	* Creates a question gui representation and returns the alias to the question gui
	*
	* @param string $question_type The question type as it is used in the language database
	* @param integer $question_id The database ID of an existing question to load it into ASS_QuestionGUI
	* @return object The alias to the question object
	* @access public
	*/
	function &createQuestionGUI($question_type, $question_id = -1)
	{
		$this->question =& ASS_QuestionGUI::_getQuestionGUI($question_type, $question_id);
	}

	/**
	* get question template
	*/
	function getQuestionTemplate($q_type)
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		// set screen title (Edit/Create Question)
		if ($this->object->id > 0)
		{
			$title = $this->lng->txt("edit") . " " . $this->lng->txt($q_type);
		}
		else
		{
			$title = $this->lng->txt("create_new") . " " . $this->lng->txt($q_type);
			$this->tpl->setVariable("HEADER", $title);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_question.html", true);
	}

	/**
	* output question page
	*/
	function outQuestionPage($a_temp_var, $a_postponed = false, $test_id = "")
	{
		$postponed = "";
		if ($a_postponed)
		{
			$postponed = " (" . $this->lng->txt("postponed") . ")";
		}

		include_once("content/classes/Pages/class.ilPageObjectGUI.php");
		$this->lng->loadLanguageModule("content");
		$page =& new ilPageObject("qpl", $this->object->getId());
		$page_gui =& new ilPageObjectGUI($page);
		$page_gui->setQuestionXML($this->object->to_xml(false, false, true, $test_id));
		$page_gui->setTemplateTargetVar($a_temp_var);
		$page_gui->setFileDownloadLink("test.php?cmd=downloadFile".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFullscreenLink("test.php?cmd=fullscreen".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setSourcecodeDownloadScript("test.php?ref_id=".$_GET["ref_id"]);
		$page_gui->setOutputMode("presentation");
		//$page_gui->setHeader($this->object->getTitle());
		$page_gui->setPresentationTitle($this->object->getTitle().$postponed);
		return $page_gui->presentation();
	}

	/**
	* cancel action
	*/
	function cancel()
	{
		if ($_GET["q_id"] > 0)
		{
			$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $_GET["q_id"]);
			$this->ctrl->redirectByClass("ilpageobjectgui", "view");
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
		}
	}

	/**
	* save question
	*/
	function saveEdit()
	{
		$this->writePostData();
		$this->object->saveToDb();
		if ($_GET["test_ref_id"] == "")
		{
			$_GET["q_id"] = $this->object->getId();
			$this->editQuestion();
			if (strcmp($_SESSION["info"], "") != 0)
			{
				sendInfo($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), false);
			}
			else
			{
				sendInfo($this->lng->txt("msg_obj_modified"), false);
			}
			$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $this->object->getId());
			$this->ctrl->redirectByClass("ilpageobjectgui", "view");
		}
		else
		{
			require_once ("assessment/classes/class.ilObjTest.php");
			$_GET["ref_id"] = $_GET["test_ref_id"];
			$test =& new ilObjTest($_GET["test_ref_id"], true);
			$test->insertQuestion($this->object->getId());
			ilUtil::redirect("test.php?cmd=questions&ref_id=".$_GET["test_ref_id"]);
		}
	}

	/**
	* save question
	*/
	function save()
	{
		$old_id = $_GET["q_id"];
		$this->writePostData();
		$this->object->saveToDb();
		if ($_GET["test_ref_id"] == "")
		{
			$_GET["q_id"] = $this->object->getId();
			if ($_GET["q_id"] !=  $old_id)
			{
				// first save
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
				$this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
			}
			$this->editQuestion();
			if (strcmp($_SESSION["info"], "") != 0)
			{
				sendInfo($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), false);
			}
			else
			{
				sendInfo($this->lng->txt("msg_obj_modified"), false);
			}
//			$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $this->object->getId());
//			$this->ctrl->redirectByClass("ilpageobjectgui", "view");
		}
		else
		{
			require_once ("assessment/classes/class.ilObjTest.php");
			$_GET["ref_id"] = $_GET["test_ref_id"];
			$test =& new ilObjTest($_GET["test_ref_id"], true);
			$test->insertQuestion($this->object->getId());
			ilUtil::redirect("test.php?cmd=questions&ref_id=".$_GET["test_ref_id"]);
		}
	}

	/**
	* apply changes
	*/
	function apply()
	{
		$this->writePostData();
		$this->object->saveToDb();
		$_GET["q_id"] = $this->object->getId();
		$this->editQuestion();
	}
	
	function cancelExplorer()
	{
		$this->editQuestion();
	}
	
	function addSuggestedSolution($subquestion_index = 0)
	{
		global $tree;

		require_once("./assessment/classes/class.ilSolutionExplorer.php");

		$_SESSION["link_new_type"] = "lm";

		sendInfo($this->lng->txt("select_object_to_link"));
		
		$exp = new ilSolutionExplorer($this->ctrl->getLinkTarget($this,'addSuggestedSolution'), get_class($this), $subquestion_index);

		$exp->setExpand($_GET["expand"] ? $_GET["expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'addSuggestedSolution'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->cur_ref_id);
		$exp->addFilter($_SESSION["link_new_type"]);
		$exp->setSelectableType($_SESSION["link_new_type"]);

		// build html-output
		$exp->setOutput(0);

		$this->tpl->addBlockFile("EXPLORER", "explorer", "tpl.il_as_qpl_explorer.html", true);
		$this->tpl->setVariable("EXPLORER_TREE",$exp->getOutput());
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	function removeSuggestedSolution()
	{
		$this->object->suggested_solutions = array();
		$this->editQuestion();
	}
	
	function linkChilds()
	{
		$this->object->setSuggestedSolution("il__lm_" . $_GET["source_id"], 0);
		$this->editQuestion();
	}
}
?>
