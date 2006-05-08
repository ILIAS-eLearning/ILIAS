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

include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* Basic GUI class for assessment questions
*
* The ASS_QuestionGUI class encapsulates basic GUI functions
* for assessment questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
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
	var $errormessage;
	
	/**
	 * sequence number in test
	 */
	
	var $sequence_no;
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

		include_once "./assessment/classes/class.assQuestion.php";
		$this->errormessage = $this->lng->txt("fill_out_all_required_fields");
		$this->object = new ASS_Question();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd("editQuestion");
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
		include_once "./assessment/classes/class.assQuestion.php";
		if ((!$question_type) and ($question_id > 0))
		{
			$question_type = ASS_Question::getQuestionTypeFromDb($question_id);
// echo ":".$question_type;
		}
		switch ($question_type)
		{
			case "qt_multiple_choice_sr":
				include_once "./assessment/classes/class.assSingleChoiceGUI.php";
				$question =& new ASS_SingleChoiceGUI();
				break;

			case "qt_multiple_choice_mr":
				include_once "./assessment/classes/class.assMultipleChoiceGUI.php";
				$question =& new ASS_MultipleChoiceGUI();
				$question->object->setResponse(RESPONSE_MULTIPLE);
				break;

			case "qt_cloze":
				include_once "./assessment/classes/class.assClozeTestGUI.php";
				$question =& new ASS_ClozeTestGUI();
				break;

			case "qt_matching":
				include_once "./assessment/classes/class.assMatchingQuestionGUI.php";
				$question =& new ASS_MatchingQuestionGUI();
				break;

			case "qt_numeric":
				include_once "./assessment/classes/class.assNumericGUI.php";
				$question =& new ASS_NumericGUI();
				break;

			case "qt_textsubset":
				include_once "./assessment/classes/class.assTextSubsetGUI.php";
				$question =& new ASS_TextSubsetGUI();
				break;

			case "qt_ordering":
				include_once "./assessment/classes/class.assOrderingQuestionGUI.php";
				$question =& new ASS_OrderingQuestionGUI();
				break;

			case "qt_imagemap":
				include_once "./assessment/classes/class.assImagemapQuestionGUI.php";
				$question =& new ASS_ImagemapQuestionGUI();
				break;

			case "qt_javaapplet":
				include_once "./assessment/classes/class.assJavaAppletGUI.php";
				$question =& new ASS_JavaAppletGUI();
				break;
			case "qt_text":
				include_once "./assessment/classes/class.assTextQuestionGUI.php";
				$question =& new ASS_TextQuestionGUI();
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
		include_once "./assessment/classes/class.assQuestion.php";
		include_once "./assessment/classes/class.assQuestionGUI.php";
		$q_type =  ASS_Question::getQuestionTypeFromDb($a_q_id);
		$class_name = ASS_QuestionGUI::_getClassNameForQType($q_type);
		return $class_name;
	}

	function _getClassNameForQType($q_type)
	{
		switch ($q_type)
		{
			case "qt_multiple_choice_sr":
				return "ASS_SingleChoiceGUI";
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

			case "qt_numeric":
				return "ASS_NumericGUI";
				break;

			case "qt_textsubset":
				return "ASS_TextSubsetGUI";
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

			case "qt_text":
				return "ASS_TextQuestionGUI";
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
		include_once "./assessment/classes/class.assQuestionGUI.php";
		$this->question =& ASS_QuestionGUI::_getQuestionGUI($question_type, $question_id);
	}

	/**
	* get question template
	*/
	function getQuestionTemplate($q_type)
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
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

		include_once("content/classes/Pages/class.ilPageObject.php");
		include_once("content/classes/Pages/class.ilPageObjectGUI.php");
		$this->lng->loadLanguageModule("content");
		$page =& new ilPageObject("qpl", $this->object->getId());
		$page_gui =& new ilPageObjectGUI($page);
		$page_gui->setQuestionXML($this->object->to_xml(false, false, true, $test_id, $force_image_references = true));
		$page_gui->setTemplateTargetVar($a_temp_var);
		$page_gui->setFileDownloadLink("ilias.php?baseClass=ilObjTestGUI&cmd=downloadFile".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFullscreenLink("ilias.php?baseClass=ilObjTestGUI&cmd=fullscreen".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilObjTestGUI&ref_id=".$_GET["ref_id"]);
		$page_gui->setOutputMode("presentation");
		//$page_gui->setHeader($this->object->getTitle());
		include_once "./assessment/classes/class.ilObjTest.php";
		$maxpoints = " (".$this->object->getMaximumPoints()." ".$this->lng->txt("points").")";
		if (ilObjTest::_getHideTitlePoints($test_id))
		{
			$maxpoints = "";
		}
		if (!$a_postponed && is_numeric($this->sequence_no))
			$page_gui->setPresentationTitle($this->lng->txt("question")." ".$this->sequence_no." - ".$this->object->getTitle().$postponed.$maxpoints);
		else 
			$page_gui->setPresentationTitle($this->object->getTitle().$postponed.$maxpoints);
		return $page_gui->presentation();
	}
	
	/**
	* cancel action
	*/
	function cancel()
	{
		if ($_GET["calling_test"])
		{
			$_GET["ref_id"] = $_GET["calling_test"];
			ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["calling_test"]);
		}
		elseif ($_GET["test_ref_id"])
		{
			$_GET["ref_id"] = $_GET["test_ref_id"];
			ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["test_ref_id"]);
		}
		else
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
	}

	function originalSyncForm()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_tst_sync_original.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BUTTON_YES", $this->lng->txt("yes"));
		$this->tpl->setVariable("BUTTON_NO", $this->lng->txt("no"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TEXT_SYNC", $this->lng->txt("confirm_sync_questions"));
		$this->tpl->parseCurrentBlock();
	}
	
	function sync()
	{
		$original_id = $this->object->original_id;
		if ($original_id)
		{
			$this->object->syncWithOriginal();
		}
		$_GET["ref_id"] = $_GET["calling_test"];
		ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["calling_test"]);
	}

	function cancelSync()
	{
		$_GET["ref_id"] = $_GET["calling_test"];
		ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["calling_test"]);
	}
		
	/**
	* save question
	*/
	function saveEdit()
	{
		global $ilUser;

		$result = $this->writePostData();
		if ($result == 0)
		{
			$ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
			$ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
			$this->object->saveToDb();
			$originalexists = $this->object->_questionExists($this->object->original_id);
			include_once "./assessment/classes/class.assQuestion.php";
			if ($_GET["calling_test"] && $originalexists && ASS_Question::_isWriteable($this->object->original_id, $ilUser->getId()))
			{
				$this->originalSyncForm();
			}
			elseif ($_GET["calling_test"])
			{
				$_GET["ref_id"] = $_GET["calling_test"];
				ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["calling_test"]);
				return;
			}
			elseif ($_GET["test_ref_id"])
			{
				include_once ("./assessment/classes/class.ilObjTest.php");
				$_GET["ref_id"] = $_GET["test_ref_id"];
				$test =& new ilObjTest($_GET["test_ref_id"], true);
				$test->insertQuestion($this->object->getId());
				ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["test_ref_id"]);
			}
			else
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
		}
		else
		{
      sendInfo($this->lng->txt("fill_out_all_required_fields"));
			$this->editQuestion();
		}
	}

	/**
	* save question
	*/
	function save()
	{
		global $ilUser;
		
		$old_id = $_GET["q_id"];
		$result = $this->writePostData();
		if ($result == 0)
		{
			$ilUser->setPref("tst_lastquestiontype", $this->object->getQuestionType());
			$ilUser->writePref("tst_lastquestiontype", $this->object->getQuestionType());
			$this->object->saveToDb();
			$originalexists = $this->object->_questionExists($this->object->original_id);
			include_once "./assessment/classes/class.assQuestion.php";
			if ($_GET["calling_test"] && $originalexists && ASS_Question::_isWriteable($this->object->original_id, $ilUser->getId()))
			{
				$this->originalSyncForm();
			}
			elseif ($_GET["calling_test"])
			{
				$_GET["ref_id"] = $_GET["calling_test"];
				ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["calling_test"]);
				return;
			}
			elseif ($_GET["test_ref_id"])
			{
				include_once ("./assessment/classes/class.ilObjTest.php");
				$_GET["ref_id"] = $_GET["test_ref_id"];
				$test =& new ilObjTest($_GET["test_ref_id"], true);
				$test->insertQuestion($this->object->getId());
				ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["test_ref_id"]);
			}
			else
			{
				$_GET["q_id"] = $this->object->getId();
				if ($_GET["q_id"] !=  $old_id)
				{
					// first save
					$this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
					$this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
					sendInfo($this->lng->txt("msg_obj_modified"), true);
					$this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
				}
				if (strcmp($_SESSION["info"], "") != 0)
				{
					sendInfo($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), false);
				}
				else
				{
					sendInfo($this->lng->txt("msg_obj_modified"), false);
				}
				$this->editQuestion();
	//			$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $this->object->getId());
	//			$this->ctrl->redirectByClass("ilpageobjectgui", "view");
			}
		}
		else
		{
      sendInfo($this->getErrorMessage());
			$this->editQuestion();
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
		unset($_SESSION["subquestion_index"]);
		unset($_SESSION["link_new_type"]);
		$this->editQuestion();
	}
	
	function addSuggestedSolution()
	{
		global $tree;

		include_once("./assessment/classes/class.ilSolutionExplorer.php");
		switch ($_POST["internalLinkType"])
		{
			case "lm":
				$_SESSION["link_new_type"] = "lm";
				$_SESSION["search_link_type"] = "lm";
				break;
			case "glo":
				$_SESSION["link_new_type"] = "glo";
				$_SESSION["search_link_type"] = "glo";
				break;
			case "st":
				$_SESSION["link_new_type"] = "lm";
				$_SESSION["search_link_type"] = "st";
				break;
			case "pg":
				$_SESSION["link_new_type"] = "lm";
				$_SESSION["search_link_type"] = "pg";
				break;
			default:
				if (!$_SESSION["link_new_type"])
				{
					$_SESSION["link_new_type"] = "lm";
				}
				break;
		}

		sendInfo($this->lng->txt("select_object_to_link"));
		
		$exp = new ilSolutionExplorer($this->ctrl->getLinkTarget($this,'addSuggestedSolution'), get_class($this));

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
		$this->object->saveToDb();
		$this->editQuestion();
	}
	
	function addPG()
	{
		$subquestion_index = 0;
		if ($_SESSION["subquestion_index"] >= 0)
		{
			$subquestion_index = $_SESSION["subquestion_index"];
		}
		$this->object->setSuggestedSolution("il__pg_" . $_GET["pg"], $subquestion_index);
		unset($_SESSION["subquestion_index"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		sendInfo($this->lng->txt("suggested_solution_added_successfully"));
		$this->editQuestion();
	}
	
	function addST()
	{
		$subquestion_index = 0;
		if ($_SESSION["subquestion_index"] >= 0)
		{
			$subquestion_index = $_SESSION["subquestion_index"];
		}
		$this->object->setSuggestedSolution("il__st_" . $_GET["st"], $subquestion_index);
		unset($_SESSION["subquestion_index"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		sendInfo($this->lng->txt("suggested_solution_added_successfully"));
		$this->editQuestion();
	}

	function addGIT()
	{
		$subquestion_index = 0;
		if ($_SESSION["subquestion_index"] >= 0)
		{
			$subquestion_index = $_SESSION["subquestion_index"];
		}
		$this->object->setSuggestedSolution("il__git_" . $_GET["git"], $subquestion_index);
		unset($_SESSION["subquestion_index"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		sendInfo($this->lng->txt("suggested_solution_added_successfully"));
		$this->editQuestion();
	}
	
	function linkChilds()
	{
		switch ($_SESSION["search_link_type"])
		{
			case "pg":
				include_once "./content/classes/class.ilLMPageObject.php";
				include_once("./content/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($_GET["source_id"], true);
				$pages = ilLMPageObject::getPageList($cont_obj->getId());
				$_GET["q_id"] = $this->object->getId();
				$this->tpl->setVariable("HEADER", $this->object->getTitle());
				$this->getQuestionTemplate($_GET["sel_question_types"]);
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				$this->tpl->addBlockFile("LINK_SELECTION", "link_selection", "tpl.il_as_qpl_internallink_selection.html", true);
				foreach($pages as $page)
				{
					if($page["type"] == $_SESSION["search_link_type"])
					{
						$this->tpl->setCurrentBlock("linktable_row");
						$this->tpl->setVariable("TEXT_LINK", $page["title"]);
						$this->tpl->setVariable("TEXT_ADD", $this->lng->txt("add"));
						$this->tpl->setVariable("LINK_HREF", $this->ctrl->getLinkTargetByClass(get_class($this), "add" . strtoupper($page["type"])) . "&" . $page["type"] . "=" . $page["obj_id"]);
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						$this->tpl->parseCurrentBlock();
						$counter++;
					}
				}
				$this->tpl->setCurrentBlock("link_selection");
				$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
				$this->tpl->setVariable("TEXT_LINK_TYPE", $this->lng->txt("obj_" . $_SESSION["search_link_type"]));
				$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
				$this->tpl->parseCurrentBlock();
				break;
			case "st":
				$_GET["q_id"] = $this->object->getId();
				$this->tpl->setVariable("HEADER", $this->object->getTitle());
				$this->getQuestionTemplate($_GET["sel_question_types"]);
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				include_once("./content/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($_GET["source_id"], true);
				// get all chapters
				$ctree =& $cont_obj->getLMTree();
				$nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
				$this->tpl->addBlockFile("LINK_SELECTION", "link_selection", "tpl.il_as_qpl_internallink_selection.html", true);
				foreach($nodes as $node)
				{
					if($node["type"] == $_SESSION["search_link_type"])
					{
						$this->tpl->setCurrentBlock("linktable_row");
						$this->tpl->setVariable("TEXT_LINK", $node["title"]);
						$this->tpl->setVariable("TEXT_ADD", $this->lng->txt("add"));
						$this->tpl->setVariable("LINK_HREF", $this->ctrl->getLinkTargetByClass(get_class($this), "add" . strtoupper($node["type"])) . "&" . $node["type"] . "=" . $node["obj_id"]);
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						$this->tpl->parseCurrentBlock();
						$counter++;
					}
				}
				$this->tpl->setCurrentBlock("link_selection");
				$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
				$this->tpl->setVariable("TEXT_LINK_TYPE", $this->lng->txt("obj_" . $_SESSION["search_link_type"]));
				$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
				$this->tpl->parseCurrentBlock();
				break;
			case "glo":
				$_GET["q_id"] = $this->object->getId();
				$this->tpl->setVariable("HEADER", $this->object->getTitle());
				$this->getQuestionTemplate($_GET["sel_question_types"]);
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				$this->tpl->addBlockFile("LINK_SELECTION", "link_selection", "tpl.il_as_qpl_internallink_selection.html", true);
				include_once "./content/classes/class.ilObjGlossary.php";
				$glossary =& new ilObjGlossary($_GET["source_id"], true);
				// get all glossary items
				$terms = $glossary->getTermList();
				foreach($terms as $term)
				{
					$this->tpl->setCurrentBlock("linktable_row");
					$this->tpl->setVariable("TEXT_LINK", $term["term"]);
					$this->tpl->setVariable("TEXT_ADD", $this->lng->txt("add"));
					$this->tpl->setVariable("LINK_HREF", $this->ctrl->getLinkTargetByClass(get_class($this), "addGIT") . "&git=" . $term["id"]);
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("link_selection");
				$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
				$this->tpl->setVariable("TEXT_LINK_TYPE", $this->lng->txt("glossary_term"));
				$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
				$this->tpl->parseCurrentBlock();
				break;
			case "lm":
				$subquestion_index = 0;
				if ($_SESSION["subquestion_index"] >= 0)
				{
					$subquestion_index = $_SESSION["subquestion_index"];
				}
				$this->object->setSuggestedSolution("il__lm_" . $_GET["source_id"], $subquestion_index);
				unset($_SESSION["subquestion_index"]);
				unset($_SESSION["link_new_type"]);
				unset($_SESSION["search_link_type"]);
				sendInfo($this->lng->txt("suggested_solution_added_successfully"));
				$this->editQuestion();
				break;
		}
	}
	
	function replaceInputElements  ($gap_idx, $solution, $output, $before="[", $after="]") {		
		#echo htmlentities ($output)."<br>";
		#echo htmlentities ($gap_idx)."<br>";
		$before = "<span class=\"textanswer\">".$before;
		$after  = $after . "</span>";
		$output = preg_replace ("/(<input[^>]*".$gap_idx."[^>]*>)/" , $before.$solution.$after, $output);
		#echo htmlentities ($output)."<br>";		
		return $output;	
	}

	/*function replaceInputElements  ($gap_idx, $solution, $output, $before="", $after="") {		
		#echo htmlentities ($output)."<br>";
		#echo htmlentities ($gap_idx)."<br>";
		$before="<span class=\"textanswer\">[";
		$after="]</span>";
		$output = preg_replace ("/(<input[^>]*".$gap_idx."[^>]*>)/" , $before.$solution.$after, $output);
		#echo htmlentities ($output)."<br>";		
		return $output;	
	}*/
	
	function replaceSelectElements ($gap_idx, $repl_str, $output)//, $before="", $after="") {
	{
		#echo htmlentities ($output)."<br>";
		#echo htmlentities ($gap_idx)."<br>";
		#echo htmlentities ($repl_str)."<br>";
		$before="<span class=\"textanswer\">[";
		$after="]</span>";	
	
		$select_pattern = "/<select[^>]*name=\"$gap_idx\".*?[^>]*>.*?<\/select>/";
		#echo  htmlentities ($select_pattern)."<br>";
		// to extract the display value we need the according select statement 
		if (preg_match($select_pattern, $output, $matches)) {
			// got it, now we are trying to get the value
			#echo "<br><br>".htmlentities ($matches[0]);
			$value_pattern = "/<option[^>]*".$repl_str."[^>]*>(.*?)<\/option>/";												
			if (preg_match($value_pattern, $matches[0], $matches))
				$output = preg_replace ($select_pattern, $before.$matches[1].$after, $output);
/*			else 
				$output = preg_replace ($select_pattern, $before.$after, $output);*/
		}
		return $output;	
	}
	
	function removeFormElements ($output) {
		$output = preg_replace ("/(<input[^>]*>)/" ,"[]", $output);			
		$output = preg_replace ("/<select[^>]*>.*?<\/select>/s" ,"[]", $output);		
		return $output;	
	}
	
	function setSequenceNumber ($nr) {
		$this->sequence_no = $nr;
	}
	
	function getSequenceNumber () {
		return $this->sequence_no;
	}
	
	function getErrorMessage()
	{
		return $this->errormessage;
	}
	
	function setErrorMessage($errormessage)
	{
		$this->errormessage = $errormessage;
	}
}
?>
