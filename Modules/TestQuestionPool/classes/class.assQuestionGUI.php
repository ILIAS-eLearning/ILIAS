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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Basic GUI class for assessment questions
*
* The assQuestionGUI class encapsulates basic GUI functions
* for assessment questions.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assQuestionGUI
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
	 * question count in test
	 */
	var $question_count;
	
	/**
	* assQuestionGUI constructor
	*
	* assQuestionGUI constructor
	*
	* @access public
	*/
	function assQuestionGUI()
	{
		global $lng, $tpl, $ilCtrl;


		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "q_id");

		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$this->errormessage = $this->lng->txt("fill_out_all_required_fields");
		$this->object = new assQuestion();
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
		$est_working_time = $this->object->getEstimatedWorkingTime();
		$this->tpl->setVariable("TEXT_WORKING_TIME", $this->lng->txt("working_time"));
		$this->tpl->setVariable("TIME_FORMAT", $this->lng->txt("time_format"));
		$this->tpl->setVariable("VALUE_WORKING_TIME", ilUtil::makeTimeSelect("Estimated", false, $est_working_time[h], $est_working_time[m], $est_working_time[s]));
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
	* output assessment
	*/
	function assessment()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", "Modules/TestQuestionPool");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// catch feedback message
		ilUtil::sendInfo();

		$total_of_answers = $this->object->getTotalAnswers();
		$counter = 0;
		$color_class = array("tblrow1", "tblrow2");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_assessment_of_questions.html", "Modules/TestQuestionPool");
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

		$instances =& $this->object->getInstances();
		$counter = 0;
		foreach ($instances as $instance)
		{
			if (is_array($instance["refs"]))
			{
				foreach ($instance["refs"] as $ref_id)
				{
					$this->tpl->setCurrentBlock("references");
					$this->tpl->setVariable("GOTO", "./goto.php?target=tst_" . $ref_id);
					$this->tpl->setVariable("TEXT_GOTO", $this->lng->txt("perma_link"));
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->tpl->setCurrentBlock("instance_row");
			$this->tpl->setVariable("TEST_TITLE", $instance["title"]);
			$this->tpl->setVariable("TEST_AUTHOR", $instance["author"]);
			$this->tpl->setVariable("QUESTION_ID", $instance["question_id"]);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("instances");
		$this->tpl->setVariable("TEXT_TEST_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_TEST_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_TEST_LOCATION", $this->lng->txt("location"));
		$this->tpl->setVariable("INSTANCES_TITLE", $this->lng->txt("question_instances_title"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_QUESTION_TITLE", $this->lng->txt("question_cumulated_statistics"));
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
	* @param integer $question_id The database ID of an existing question to load it into assQuestionGUI
	* @return object The alias to the question object
	* @access public
	*/
	function &_getQuestionGUI($question_type, $question_id = -1)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		if ((!$question_type) and ($question_id > 0))
		{
			$question_type = assQuestion::getQuestionTypeFromDb($question_id);
		}
		if (strlen($question_type) == 0) return NULL;
		$question_type_gui = $question_type . "GUI";
		assQuestion::_includeClass($question_type, 1);
		$question =& new $question_type_gui();
		if ($question_id > 0)
		{
			$question->object->loadFromDb($question_id);
		}
		return $question;
	}
	
	function _getGUIClassNameForId($a_q_id)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$q_type =  assQuestion::getQuestionTypeFromDb($a_q_id);
		$class_name = assQuestionGUI::_getClassNameForQType($q_type);
		return $class_name;
	}

	function _getClassNameForQType($q_type)
	{
		return $q_type . "GUI";
	}

	/**
	* Creates a question gui representation
	*
	* Creates a question gui representation and returns the alias to the question gui
	*
	* @param string $question_type The question type as it is used in the language database
	* @param integer $question_id The database ID of an existing question to load it into assQuestionGUI
	* @return object The alias to the question object
	* @access public
	*/
	function &createQuestionGUI($question_type, $question_id = -1)
	{
		include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
		$this->question =& assQuestionGUI::_getQuestionGUI($question_type, $question_id);
	}

	/**
	* get question template
	*/
	function getQuestionTemplate()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", "Modules/TestQuestionPool");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_question.html", "Modules/TestQuestionPool");
	}

	/**
	* Returns the ILIAS Page around a question
	*
	* Returns the ILIAS Page around a question
	*
	* @return string The ILIAS page content
	* @access public
	*/
	function getILIASPage()
	{
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
		//$page =& new ilPageObject("qpl", $this->object->getId());
		$page_gui =& new ilPageObjectGUI("qpl", $this->object->getId());
		$page_gui->setTemplateTargetVar($a_temp_var);
		$page_gui->setEnabledInternalLinks(false);
		$page_gui->setFileDownloadLink("ilias.php?baseClass=ilObjTestGUI&cmd=downloadFile".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFullscreenLink("ilias.php?baseClass=ilObjTestGUI&cmd=fullscreen".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilObjTestGUI&ref_id=".$_GET["ref_id"]);
		$page_gui->setOutputMode("presentation");
		$page_gui->setPresentationTitle("");
		return $page_gui->presentation();
	}

	/**
	* output question page
	*/
	function outQuestionPage($a_temp_var, $a_postponed = false, $active_id = "")
	{
		$postponed = "";
		if ($a_postponed)
		{
			$postponed = " (" . $this->lng->txt("postponed") . ")";
		}

		include_once("./Services/COPage/classes/class.ilPageObject.php");
		include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
		$this->lng->loadLanguageModule("content");
		//$page =& new ilPageObject("qpl", $this->object->getId());
		$page_gui =& new ilPageObjectGUI("qpl", $this->object->getId());
		$page_gui->setTemplateTargetVar($a_temp_var);
		$page_gui->setFileDownloadLink("ilias.php?baseClass=ilObjTestGUI&cmd=downloadFile".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setFullscreenLink("ilias.php?baseClass=ilObjTestGUI&cmd=fullscreen".
			"&amp;ref_id=".$_GET["ref_id"]);
		$page_gui->setSourcecodeDownloadScript("ilias.php?baseClass=ilObjTestGUI&ref_id=".$_GET["ref_id"]);
		$page_gui->setOutputMode("presentation");

		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$title_output = ilObjTest::_getTitleOutput($active_id);
		switch ($title_output)
		{
			case 1:
				$page_gui->setPresentationTitle(sprintf($this->lng->txt("tst_position"), $this->getSequenceNumber(), $this->getQuestionCount())." - ".$this->object->getTitle().$postponed);
				break;
			case 2:
				$page_gui->setPresentationTitle(sprintf($this->lng->txt("tst_position"), $this->getSequenceNumber(), $this->getQuestionCount()).$postponed);
				break;
			case 0:
			default:
				$maxpoints = $this->object->getMaximumPoints();
				if ($maxpoints == 1)
				{
					$maxpoints = " (".$maxpoints." ".$this->lng->txt("point").")";
				}
				else
				{
					$maxpoints = " (".$maxpoints." ".$this->lng->txt("points").")";
				}
				$page_gui->setPresentationTitle(sprintf($this->lng->txt("tst_position"), $this->getSequenceNumber(), $this->getQuestionCount())." - ".$this->object->getTitle().$postponed.$maxpoints);
				break;
		}
		$presentation = $page_gui->presentation();
		if (strlen($maxpoints)) $presentation = str_replace($maxpoints, "<em>$maxpoints</em>", $presentation);
		return $presentation;
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
				$this->ctrl->redirectByClass("ilpageobjectgui", "edit");
			}
			else
			{
				$this->ctrl->redirectByClass("ilobjquestionpoolgui", "questions");
			}
		}
	}

	function originalSyncForm($return_to = "")
	{
		if (strlen($return_to))
		{
			$this->ctrl->setParameter($this, "return_to", $return_to);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_sync_original.html", "Modules/TestQuestionPool");
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
		if (strlen($_GET["return_to"]))
		{
			$this->ctrl->redirect($this, $_GET["return_to"]);
		}
		else
		{
			$_GET["ref_id"] = $_GET["calling_test"];
			ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["calling_test"]);
		}
	}

	function cancelSync()
	{
		if (strlen($_GET["return_to"]))
		{
			$this->ctrl->redirect($this, $_GET["return_to"]);
		}
		else
		{
			$_GET["ref_id"] = $_GET["calling_test"];
			ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["calling_test"]);
		}
	}
		
	/**
	* Saves the feedback for a single choice question
	*
	* Saves the feedback for a single choice question
	*
	* @access public
	*/
	function saveFeedback()
	{
		global $ilUser;
		
		$originalexists = $this->object->_questionExists($this->object->original_id);
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		if ($_GET["calling_test"] && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId()))
		{
			$this->originalSyncForm("feedback");
		}
		else
		{
			$this->feedback();
		}
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
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			if ($_GET["calling_test"] && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId()))
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
				include_once ("./Modules/Test/classes/class.ilObjTest.php");
				$_GET["ref_id"] = $_GET["test_ref_id"];
				$test =& new ilObjTest($_GET["test_ref_id"], true);
				$test->insertQuestion($this->object->getId());
				ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["test_ref_id"]);
			}
			else
			{
				$this->ctrl->setParameter($this, "q_id", $this->object->getId());
				$this->editQuestion();
				if (strcmp($_SESSION["info"], "") != 0)
				{
					ilUtil::sendInfo($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), false);
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), false);
				}
				$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $this->object->getId());
				$this->ctrl->redirectByClass("ilpageobjectgui", "edit");
			}
		}
		else
		{
      ilUtil::sendInfo($this->lng->txt("fill_out_all_required_fields"));
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
			include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
			if ($_GET["calling_test"] && $originalexists && assQuestion::_isWriteable($this->object->original_id, $ilUser->getId()))
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
				include_once ("./Modules/Test/classes/class.ilObjTest.php");
				$_GET["ref_id"] = $_GET["test_ref_id"];
				$test =& new ilObjTest($_GET["test_ref_id"], true);
				$test->insertQuestion($this->object->getId());
				ilUtil::redirect("ilias.php?baseClass=ilObjTestGUI&cmd=questions&ref_id=".$_GET["test_ref_id"]);
			}
			else
			{
				if ($this->object->getId() !=  $old_id)
				{
					// first save
					$this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
					$this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
					ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
					$this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
				}
				if (strcmp($_SESSION["info"], "") != 0)
				{
					ilUtil::sendInfo($_SESSION["info"] . "<br />" . $this->lng->txt("msg_obj_modified"), false);
				}
				else
				{
					ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), false);
				}
				$this->editQuestion();
	//			$this->ctrl->setParameterByClass("ilpageobjectgui", "q_id", $this->object->getId());
	//			$this->ctrl->redirectByClass("ilpageobjectgui", "view");
			}
		}
		else
		{
      ilUtil::sendInfo($this->getErrorMessage());
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
		$this->ctrl->setParameter($this, "q_id", $this->object->getId());
		$this->editQuestion();
	}
	
	function cancelExplorer()
	{
		unset($_SESSION["subquestion_index"]);
		unset($_SESSION["link_new_type"]);
		$this->editQuestion();
	}
	
	/**
	* Handler for cmd[addSuggestedSolution] to add a suggested solution for the question
	*
	* Handler for cmd[addSuggestedSolution] to add a suggested solution for the question
	*
	* @access public
	*/
	function addSuggestedSolution()
	{
		global $tree;

		include_once("./Modules/TestQuestionPool/classes/class.ilSolutionExplorer.php");
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

		ilUtil::sendInfo($this->lng->txt("select_object_to_link"));
		
		$exp = new ilSolutionExplorer($this->ctrl->getLinkTarget($this,'addSuggestedSolution'), get_class($this));

		$exp->setExpand($_GET["expand"] ? $_GET["expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'addSuggestedSolution'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->cur_ref_id);
		$exp->addFilter($_SESSION["link_new_type"]);
		$exp->setSelectableType($_SESSION["link_new_type"]);

		// build html-output
		$exp->setOutput(0);

		$this->tpl->addBlockFile("EXPLORER", "explorer", "tpl.il_as_qpl_explorer.html", "Modules/TestQuestionPool");
		$this->tpl->setVariable("EXPLORER_TREE",$exp->getOutput());
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
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
		ilUtil::sendInfo($this->lng->txt("suggested_solution_added_successfully"));
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
		ilUtil::sendInfo($this->lng->txt("suggested_solution_added_successfully"));
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
		ilUtil::sendInfo($this->lng->txt("suggested_solution_added_successfully"));
		$this->editQuestion();
	}
	
	/**
	* get context path in content object tree
	*
	* @param	int		$a_endnode_id		id of endnode
	* @param	int		$a_startnode_id		id of startnode
	*/
	function getContextPath($cont_obj, $a_endnode_id, $a_startnode_id = 1)
	{
		$path = "";

		$tmpPath = $cont_obj->getLMTree()->getPathFull($a_endnode_id, $a_startnode_id);

		// count -1, to exclude the learning module itself
		for ($i = 1; $i < (count($tmpPath) - 1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}

			$path .= $tmpPath[$i]["title"];
		}

		return $path;
	}

	function linkChilds()
	{
		switch ($_SESSION["search_link_type"])
		{
			case "pg":
				include_once "./Modules/LearningModule/classes/class.ilLMPageObject.php";
				include_once("./Modules/LearningModule/classes/class.ilObjContentObjectGUI.php");
				$cont_obj_gui =& new ilObjContentObjectGUI("", $_GET["source_id"], true);
				$cont_obj = $cont_obj_gui->object;
				$pages = ilLMPageObject::getPageList($cont_obj->getId());
				$shownpages = array();
				$tree = $cont_obj->getLMTree();
				$chapters = $tree->getSubtree($tree->getNodeData($tree->getRootId()));
				$this->ctrl->setParameter($this, "q_id", $this->object->getId());
				$this->tpl->setVariable("HEADER", $this->object->getTitle());
				$this->getQuestionTemplate();
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				$this->tpl->addBlockFile("LINK_SELECTION", "link_selection", "tpl.il_as_qpl_internallink_selection.html", "Modules/TestQuestionPool");
				foreach ($chapters as $chapter)
				{
					$chapterpages = $tree->getChildsByType($chapter["obj_id"], "pg");
					foreach ($chapterpages as $page)
					{
						if($page["type"] == $_SESSION["search_link_type"])
						{
							array_push($shownpages, $page["obj_id"]);
							$this->tpl->setCurrentBlock("linktable_row");
							$this->tpl->setVariable("TEXT_LINK", $page["title"]);
							$this->tpl->setVariable("TEXT_ADD", $this->lng->txt("add"));
							$this->tpl->setVariable("LINK_HREF", $this->ctrl->getLinkTargetByClass(get_class($this), "add" . strtoupper($page["type"])) . "&" . $page["type"] . "=" . $page["obj_id"]);
							$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
							if ($tree->isInTree($page["obj_id"]))
							{
								$path_str = $this->getContextPath($cont_obj, $page["obj_id"]);
							}
							else
							{
								$path_str = "---";
							}
							$this->tpl->setVariable("TEXT_DESCRIPTION", ilUtil::prepareFormOutput($path_str));
							$this->tpl->parseCurrentBlock();
							$counter++;
						}
					}
				}
				foreach ($pages as $page)
				{
					if (!in_array($page["obj_id"], $shownpages))
					{
						$this->tpl->setCurrentBlock("linktable_row");
						$this->tpl->setVariable("TEXT_LINK", $page["title"]);
						$this->tpl->setVariable("TEXT_ADD", $this->lng->txt("add"));
						$this->tpl->setVariable("LINK_HREF", $this->ctrl->getLinkTargetByClass(get_class($this), "add" . strtoupper($page["type"])) . "&" . $page["type"] . "=" . $page["obj_id"]);
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						$path_str = "---";
						$this->tpl->setVariable("TEXT_DESCRIPTION", ilUtil::prepareFormOutput($path_str));
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
				$this->ctrl->setParameter($this, "q_id", $this->object->getId());
				$this->tpl->setVariable("HEADER", $this->object->getTitle());
				$this->getQuestionTemplate();
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				include_once("./Modules/LearningModule/classes/class.ilObjContentObjectGUI.php");
				$cont_obj_gui =& new ilObjContentObjectGUI("", $_GET["source_id"], true);
				$cont_obj = $cont_obj_gui->object;
				// get all chapters
				$ctree =& $cont_obj->getLMTree();
				$nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
				$this->tpl->addBlockFile("LINK_SELECTION", "link_selection", "tpl.il_as_qpl_internallink_selection.html", "Modules/TestQuestionPool");
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
				$this->ctrl->setParameter($this, "q_id", $this->object->getId());
				$this->tpl->setVariable("HEADER", $this->object->getTitle());
				$this->getQuestionTemplate();
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				$this->tpl->addBlockFile("LINK_SELECTION", "link_selection", "tpl.il_as_qpl_internallink_selection.html", "Modules/TestQuestionPool");
				include_once "./Modules/Glossary/classes/class.ilObjGlossary.php";
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
				ilUtil::sendInfo($this->lng->txt("suggested_solution_added_successfully"));
				$this->editQuestion();
				break;
		}
	}
	
	function setSequenceNumber($nr) 
	{
		$this->sequence_no = $nr;
	}
	
	function getSequenceNumber() 
	{
		return $this->sequence_no;
	}
	
	function setQuestionCount($a_question_count)
	{
		$this->question_count = $a_question_count;
	}
	
	function getQuestionCount()
	{
		return $this->question_count;
	}
	
	function getErrorMessage()
	{
		return $this->errormessage;
	}
	
	function setErrorMessage($errormessage)
	{
		$this->errormessage = $errormessage;
	}

	function addErrorMessage($errormessage)
	{
		$this->errormessage .= ((strlen($this->errormessage)) ? "<br />" : "") . $errormessage;
	}
	
	function outAdditionalOutput()
	{
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
		return $this->object->getQuestionType();
	}

	/**
	* Returns the answer specific feedback depending on the results of the question
	*
	* Returns the answer specific feedback depending on the results of the question
	*
	* @param integer $active_id Active ID of the user
	* @param integer $pass Active pass
	* @result string HTML Code with the answer specific feedback
	* @access public
	*/
	function getAnswerFeedbackOutput($active_id, $pass)
	{
		$output = "";
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$manual_feedback = ilObjTest::getManualFeedback($active_id, $this->object->getId(), $pass);
		if (strlen($manual_feedback))
		{
			return $manual_feedback;
		}
		$correct_feedback = $this->object->getFeedbackGeneric(1);
		$incorrect_feedback = $this->object->getFeedbackGeneric(0);
		if (strlen($correct_feedback.$incorrect_feedback))
		{
			$reached_points = $this->object->calculateReachedPoints($active_id);
			$max_points = $this->object->getMaximumPoints();
			if ($reached_points == $max_points)
			{
				$output = $correct_feedback;
			}
			else
			{
				$output = $incorrect_feedback;
			}
		}
		return $this->object->prepareTextareaOutput($output, TRUE);
	}

	/**
	* Creates the output of the feedback page for a question
	*
	* Creates the output of the feedback page for a question
	*
	* @access public
	*/
	function feedback()
	{
		// overwrite in parent classes
	}
}
?>
