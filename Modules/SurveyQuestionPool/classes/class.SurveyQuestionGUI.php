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

include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Basic class for all survey question types
*
* The SurveyQuestionGUI class defines and encapsulates basic methods and attributes
* for survey question types to be used for all parent classes.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyQuestionGUI 
{
/**
* Question object
*
* A reference to the metric question object
*
* @var object
*/
  var $object;
	var $tpl;
	var $lng;
	private $errormessages;

	/**
	* An array containing the cumulated results of the question for a given survey
	*
	* An array containing the cumulated results of the question for a given survey
	*
	* @var array
	*/
	var $cumulated;
	
/**
* SurveyQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyQuestion object.
*
* @param string $title A title string to describe the question
* @param string $description A description string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @access public
*/
	function SurveyQuestionGUI()
	{
		global $lng, $tpl, $ilCtrl;

		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, "q_id");
		$this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
		$this->cumulated = array();
		$this->errormessages = array();
	}

	function addErrorMessage($errormessage)
	{
		if (strlen($errormessage)) array_push($this->errormessages, $errormessage);
	}
	
	function outErrorMessages()
	{
		if (count($this->errormessages))
		{
			$out = implode("<br />", $this->errormessages);
			ilUtil::sendInfo($out);
		}
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
	static function &_getQuestionGUI($questiontype, $question_id = -1)
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		if ((!$questiontype) and ($question_id > 0))
		{
			$questiontype = SurveyQuestion::_getQuestiontype($question_id);
		}
		SurveyQuestion::_includeClass($questiontype, 1);
		$question_type_gui = $questiontype . "GUI";
		$question = new $question_type_gui($question_id);
		return $question;
	}
	
	function _getGUIClassNameForId($a_q_id)
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		$q_type = SurveyQuestion::_getQuestiontype($a_q_id);
		$class_name = SurveyQuestionGUI::_getClassNameForQType($q_type);
		return $class_name;
	}

	function _getClassNameForQType($q_type)
	{
		return $q_type;
	}
	
	function originalSyncForm()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_sync_original.html", "Modules/SurveyQuestionPool");
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
		$this->ctrl->redirect($this, "editQuestion");
		
		/*$_GET["ref_id"] = $_GET["calling_survey"];
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&ref_id=" . $_GET["calling_survey"] . "&cmd=questions");*/
	}

	function cancelSync()
	{
		$this->ctrl->redirect($this, "editQuestion");
		/*$_GET["ref_id"] = $_GET["calling_survey"];
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&ref_id=" . $_GET["calling_survey"] . "&cmd=questions");*/
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
			$ilUser->setPref("svy_lastquestiontype", $this->object->getQuestionType());
			$ilUser->writePref("svy_lastquestiontype", $this->object->getQuestionType());
			$this->object->saveToDb();
			$originalexists = $this->object->_questionExists($this->object->original_id);
			$this->ctrl->setParameter($this, "q_id", $this->object->getId());
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			if ($_GET["calling_survey"] && $originalexists && SurveyQuestion::_isWriteable($this->object->original_id, $ilUser->getId()))
			{
				$this->originalSyncForm();
				return;
			}
			elseif ($_GET["calling_survey"])
			{
				$_GET["ref_id"] = $_GET["calling_survey"];
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&ref_id=" . $_GET["calling_survey"] . "&cmd=questions");
				return;
			}
			elseif ($_GET["new_for_survey"] > 0)
			{
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "new_for_survey", $_GET["new_for_survey"]);
				$this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
				return;
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "new_for_survey", $_GET["new_for_survey"]);
				$this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
			}
		}
		$this->editQuestion();
	}
	
	function cancel()
	{
		if ($_GET["calling_survey"])
		{
			$_GET["ref_id"] = $_GET["calling_survey"];
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&cmd=questions&ref_id=".$_GET["calling_survey"]);
		}
		elseif ($_GET["new_for_survey"])
		{
			$_GET["ref_id"] = $_GET["new_for_survey"];
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::redirect("ilias.php?baseClass=ilObjSurveyGUI&cmd=questions&ref_id=".$_GET["new_for_survey"]);
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjsurveyquestionpoolgui", "questions");
		}
	}

/**
* Cancels the form adding a phrase
*
* Cancels the form adding a phrase
*
* @access public
*/
	function cancelDeleteCategory() 
	{
		$this->ctrl->redirect($this, "editQuestion");
	}

	function addMaterial()
	{
		global $tree;

		if ($_POST["cmd"]["addMaterial"])
		{
			if ($this->writePostData() == 1)
			{
				return $this->editQuestion();
			}
			else
			{
				$this->object->saveToDb();
				$this->ctrl->setParameter($this, "q_id", $this->object->getId());
			}
		}
		include_once("./Modules/SurveyQuestionPool/classes/class.ilMaterialExplorer.php");
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
		
		$exp = new ilMaterialExplorer($this->ctrl->getLinkTarget($this,'addMaterial'), get_class($this));

		$exp->setExpand($_GET["expand"] ? $_GET["expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'addMaterial'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->cur_ref_id);
		$exp->addFilter($_SESSION["link_new_type"]);
		$exp->setSelectableType($_SESSION["link_new_type"]);

		// build html-output
		$exp->setOutput(0);

		$this->tpl->addBlockFile("ADM_CONTENT", "explorer", "tpl.il_svy_qpl_explorer.html", "Modules/SurveyQuestionPool");
		$this->tpl->setVariable("EXPLORER_TREE",$exp->getOutput());
		$this->tpl->setVariable("BUTTON_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
	function removeMaterial()
	{
		$this->object->material = array();
		$this->object->saveToDb();
		$this->editQuestion();
	}
	
	function cancelExplorer()
	{
		unset($_SESSION["link_new_type"]);
		$this->editQuestion();
	}
		
	function addPG()
	{
		$this->object->setMaterial("il__pg_" . $_GET["pg"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		ilUtil::sendSuccess($this->lng->txt("material_added_successfully"));
		$this->editQuestion();
	}
	
	function addST()
	{
		$this->object->setMaterial("il__st_" . $_GET["st"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		ilUtil::sendSuccess($this->lng->txt("material_added_successfully"));
		$this->editQuestion();
	}

	function addGIT()
	{
		$this->object->setMaterial("il__git_" . $_GET["git"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		ilUtil::sendSuccess($this->lng->txt("material_added_successfully"));
		$this->editQuestion();
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
				$this->ctrl->setParameter($this, "q_id", $this->object->getId());
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				$this->tpl->addBlockFile("ADM_CONTENT", "link_selection", "tpl.il_svy_qpl_internallink_selection.html", "Modules/SurveyQuestionPool");
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
				$this->ctrl->setParameter($this, "q_id", $this->object->getId());
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				include_once("./Modules/LearningModule/classes/class.ilObjContentObjectGUI.php");
				$cont_obj_gui =& new ilObjContentObjectGUI("", $_GET["source_id"], true);
				$cont_obj = $cont_obj_gui->object;
				// get all chapters
				$ctree =& $cont_obj->getLMTree();
				$nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
				$this->tpl->addBlockFile("ADM_CONTENT", "link_selection", "tpl.il_svy_qpl_internallink_selection.html", "Modules/SurveyQuestionPool");
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
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				$this->tpl->addBlockFile("ADM_CONTENT", "link_selection", "tpl.il_svy_qpl_internallink_selection.html", "Modules/SurveyQuestionPool");
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
				$this->object->setMaterial("il__lm_" . $_GET["source_id"]);
				unset($_SESSION["link_new_type"]);
				unset($_SESSION["search_link_type"]);
				ilUtil::sendSuccess($this->lng->txt("material_added_successfully"));
				$this->editQuestion();
				break;
		}
	}


	/**
	* Creates a HTML representation of the question
	*
	* Creates a HTML representation of the question
	*
	* @access private
	*/
	function getPrintView($question_title = 1, $show_questiontext = 1)
	{
		return "";
	}

	function setQuestionTabsForClass($guiclass)
	{
		global $rbacsystem,$ilTabs;
		$this->ctrl->setParameterByClass("$guiclass", "sel_question_types", $this->getQuestionType());
		$this->ctrl->setParameterByClass("$guiclass", "q_id", $_GET["q_id"]);

		if (($_GET["calling_survey"] > 0) || ($_GET["new_for_survey"] > 0))
		{
			$ref_id = $_GET["calling_survey"];
			if (!strlen($ref_id)) $ref_id = $_GET["new_for_survey"];
			$addurl = "";
			if (strlen($_GET["new_for_survey"]))
			{
				$addurl = "&new_id=" . $_GET["q_id"];
			}
			$ilTabs->setBackTarget($this->lng->txt("menubacktosurvey"), "ilias.php?baseClass=ilObjSurveyGUI&ref_id=$ref_id&cmd=questions" . $addurl);
		}
		else
		{
			$ilTabs->setBackTarget($this->lng->txt("spl"), $this->ctrl->getLinkTargetByClass("ilObjSurveyQuestionPoolGUI", "questions"));
		}
		if ($_GET["q_id"])
		{
			$ilTabs->addTarget("preview",
									 $this->ctrl->getLinkTargetByClass("$guiclass", "preview"), "preview",
									 "$guiclass");
		}
		if ($rbacsystem->checkAccess('edit', $_GET["ref_id"])) {
			$ilTabs->addTarget("edit_properties",
									 $this->ctrl->getLinkTargetByClass("$guiclass", "editQuestion"), 
									 array("editQuestion", "cancelExplorer", "linkChilds", "addGIT", "addST",
											 "addPG",
											 "editQuestion", "addMaterial", "removeMaterial", "save", "cancel"
										 ),
									 "$guiclass");
		}

		switch ($guiclass)
		{
			case "surveyordinalquestiongui":
				if ($this->object->getId() > 0) 
				{
					$ilTabs->addTarget("categories",
											 $this->ctrl->getLinkTargetByClass("$guiclass", "categories"), 
											 array("categories", "addCategory", "insertBeforeCategory",
													 "insertAfterCategory", "moveCategory", "deleteCategory",
													 "saveCategories", "savePhrase", "addPhrase",
													 "savePhrase", "addSelectedPhrase", "cancelViewPhrase", "confirmSavePhrase",
													 "cancelSavePhrase",
													 "confirmDeleteCategory", "cancelDeleteCategory"
												 ),
											 $guiclass
					);
				}
				break;
		}
		
		if ($this->object->getId() > 0) 
		{
			$title = $this->lng->txt("edit") . " &quot;" . $this->object->getTitle() . "&quot";
		} 
		else 
		{
			$title = $this->lng->txt("create_new") . " " . $this->lng->txt($this->getQuestionType());
		}

		$this->tpl->setVariable("HEADER", $title);
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
* Creates a the cumulated results row for the question
*
* Creates a the cumulated results row for the question
*
* @return string HTML text with the cumulated results
* @access private
*/
	function getCumulatedResultRow($counter, $css_class, $survey_id)
	{
		// overwrite in parent classes
		return "";
	}
	
	function editQuestion()
	{
		$this->outErrorMessages();
	}
}
?>
