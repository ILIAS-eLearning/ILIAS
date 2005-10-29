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

include_once "./survey/classes/class.SurveyNominalQuestionGUI.php";
include_once "./survey/classes/class.SurveyTextQuestionGUI.php";
include_once "./survey/classes/class.SurveyMetricQuestionGUI.php";
include_once "./survey/classes/class.SurveyOrdinalQuestionGUI.php";

/**
* Basic class for all survey question types
*
* The SurveyQuestionGUI class defines and encapsulates basic methods and attributes
* for survey question types to be used for all parent classes.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.SurveyQuestionGUI.php
* @modulegroup   survey
*/
class SurveyQuestionGUI {
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
	function &_getQuestionGUI($questiontype, $question_id = -1)
	{
		if (!$questiontype)
		{
			$questiontype = SurveyQuestion::_getQuestiontype($question_id);
		}
		switch ($questiontype)
		{
			case "qt_nominal":
				$question = new SurveyNominalQuestionGUI();
				break;
			case "qt_ordinal":
				$question = new SurveyOrdinalQuestionGUI();
				break;
			case "qt_metric":
				$question = new SurveyMetricQuestionGUI();
				break;
			case "qt_text":
				$question = new SurveyTextQuestionGUI();
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
		$q_type = SurveyQuestion::_getQuestiontype($a_q_id);
		$class_name = SurveyQuestionGUI::_getClassNameForQType($q_type);
		return $class_name;
	}

	function _getClassNameForQType($q_type)
	{
		switch ($q_type)
		{
			case "qt_nominal":
				return "SurveyNominalQuestionGUI";
				break;

			case "qt_ordinal":
				return "SurveyOrdinalQuestionGUI";
				break;

			case "qt_metric":
				return "SurveyMetricQuestionGUI";
				break;

			case "qt_text":
				return "SurveyTextQuestionGUI";
				break;
		}
	}
	
	function originalSyncForm()
	{
//		$this->tpl->setVariable("HEADER", $this->object->getTitle());
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_sync_original.html", true);
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
		$_GET["ref_id"] = $_GET["calling_survey"];
		ilUtil::redirect("survey.php?ref_id=" . $_GET["calling_survey"] . "&cmd=questions");
	}

	function cancelSync()
	{
		$_GET["ref_id"] = $_GET["calling_survey"];
		ilUtil::redirect("survey.php?ref_id=" . $_GET["calling_survey"] . "&cmd=questions");
	}
		
	/**
	* save question
	*/
	function save()
	{
		$old_id = $_GET["q_id"];
		$result = $this->writePostData();
		if ($result == 0)
		{
			$this->object->saveToDb();
			$originalexists = $this->object->_questionExists($this->object->original_id);
			$_GET["q_id"] = $this->object->getId();
			if ($_GET["calling_survey"] && $originalexists)
			{
				$this->originalSyncForm();
				return;
			}
			elseif ($_GET["calling_survey"] && !$originalexists)
			{
				$_GET["ref_id"] = $_GET["calling_survey"];
				ilUtil::redirect("survey.php?ref_id=" . $_GET["calling_survey"] . "&cmd=questions");
				return;
			}
			elseif ($_GET["new_for_survey"] > 0)
			{
				ilUtil::redirect("survey.php?cmd=questions&ref_id=" . $_GET["new_for_survey"] . "&new_id=".$_GET["q_id"]);
				return;
			}
			else
			{
				sendInfo($this->lng->txt("msg_obj_modified"), true);
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "q_id", $this->object->getId());
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "sel_question_types", $_GET["sel_question_types"]);
				$this->ctrl->setParameterByClass($_GET["cmdClass"], "new_for_survey", $_GET["new_for_survey"]);
				$this->ctrl->redirectByClass($_GET["cmdClass"], "editQuestion");
			}
		}
		else
		{
      sendInfo($this->lng->txt("fill_out_all_required_fields"));
		}
		$this->editQuestion();
	}
	
	function cancel()
	{
		if ($_GET["calling_survey"])
		{
			$_GET["ref_id"] = $_GET["calling_survey"];
			ilUtil::redirect("survey.php?cmd=questions&ref_id=".$_GET["calling_survey"]);
		}
		elseif ($_GET["new_for_survey"])
		{
			$_GET["ref_id"] = $_GET["new_for_survey"];
			ilUtil::redirect("survey.php?cmd=questions&ref_id=".$_GET["new_for_survey"]);
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
	function cancelDeleteCategory() {
		$this->ctrl->redirect($this, "editQuestion");
	}

	function addMaterial()
	{
		global $tree;

		include_once("./survey/classes/class.ilMaterialExplorer.php");
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
		
		$exp = new ilMaterialExplorer($this->ctrl->getLinkTarget($this,'addMaterial'), get_class($this));

		$exp->setExpand($_GET["expand"] ? $_GET["expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'addMaterial'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->cur_ref_id);
		$exp->addFilter($_SESSION["link_new_type"]);
		$exp->setSelectableType($_SESSION["link_new_type"]);

		// build html-output
		$exp->setOutput(0);

		$this->tpl->addBlockFile("ADM_CONTENT", "explorer", "tpl.il_svy_qpl_explorer.html", true);
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
		sendInfo($this->lng->txt("material_added_successfully"));
		$this->editQuestion();
	}
	
	function addST()
	{
		$this->object->setMaterial("il__st_" . $_GET["st"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		sendInfo($this->lng->txt("material_added_successfully"));
		$this->editQuestion();
	}

	function addGIT()
	{
		$this->object->setMaterial("il__git_" . $_GET["git"]);
		unset($_SESSION["link_new_type"]);
		unset($_SESSION["search_link_type"]);
		sendInfo($this->lng->txt("material_added_successfully"));
		$this->editQuestion();
	}
	
	function linkChilds()
	{
		switch ($_SESSION["search_link_type"])
		{
			case "pg":
			case "st":
				$_GET["q_id"] = $this->object->getId();
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				include_once("./content/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($_GET["source_id"], true);
				// get all chapters
				$ctree =& $cont_obj->getLMTree();
				$nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
				$this->tpl->addBlockFile("ADM_CONTENT", "link_selection", "tpl.il_svy_qpl_internallink_selection.html", true);
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
				$color_class = array("tblrow1", "tblrow2");
				$counter = 0;
				$this->tpl->addBlockFile("ADM_CONTENT", "link_selection", "tpl.il_svy_qpl_internallink_selection.html", true);
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
				$this->object->setMaterial("il__lm_" . $_GET["source_id"]);
				unset($_SESSION["link_new_type"]);
				unset($_SESSION["search_link_type"]);
				sendInfo($this->lng->txt("material_added_successfully"));
				$this->editQuestion();
				break;
		}
	}

	function setQuestionTabsForClass($guiclass)
	{
		global $rbacsystem;

		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		$tabs_gui->setSubTabs();
		
		$this->ctrl->setParameterByClass("$guiclass", "sel_question_types", $this->getQuestionType());
		$this->ctrl->setParameterByClass("$guiclass", "q_id", $_GET["q_id"]);

		if ($_GET["q_id"])
		{
			$tabs_gui->addTarget("preview",
			$this->ctrl->getLinkTargetByClass("$guiclass", "preview"), "preview",
			"$guiclass");
		}
		
		if ($rbacsystem->checkAccess('edit', $_GET["ref_id"])) {
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTargetByClass("$guiclass", "editQuestion"), 
				array("editQuestion", "cancelExplorer", "linkChilds", "addGIT", "addST",
				"addPG", "addPhrase",
				"editQuestion", "addMaterial", "removeMaterial", "save", "cancel"
				),
				"$guiclass");
		}
		
		switch ($guiclass)
		{
			case "surveynominalquestiongui":
			case "surveyordinalquestiongui":
				$tabs_gui->addTarget("categories",
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
				break;
		}
		
		$this->tpl->setVariable("SUB_TABS", $tabs_gui->getHTML());

    if ($this->object->getId() > 0) {
      $title = $this->lng->txt("edit") . " &quot;" . $this->object->getTitle() . "&quot";
    } else {
      $title = $this->lng->txt("create_new") . " " . $this->lng->txt($this->getQuestionType());
    }
		$this->tpl->setVariable("HEADER", $title);
//		echo "<br>end setQuestionTabs<br>";
	}

/**
* Adds a category to the question
*
* Adds a category to the question
*
* @access private
*/
	function addCategory()
	{
		$result = $this->writeCategoryData();
		if ($result == false)
		{
			sendInfo($this->lng->txt("fill_out_all_category_fields"));
		}
		$_SESSION["spl_modified"] = true;
		$this->categories($result);
	}
	
/**
* Saves the categories
*
* Saves the categories
*
* @access private
*/
	function saveCategories()
	{
		$this->writeCategoryData(true);
		$_SESSION["spl_modified"] = false;
		sendInfo($this->lng->txt("saved_successfully"), true);
		$this->ctrl->redirect($this, "categories");
	}

/**
* Recreates the categories from the POST data
*
* Recreates the categories from the POST data and
* saves it (optionally) to the database.
*
* @param boolean $save If set to true the POST data will be saved to the database
* @access private
*/
	function writeCategoryData($save = false)
	{
    // Delete all existing categories and create new categories from the form data
    $this->object->categories->flushCategories();
		$complete = true;
		$array1 = array();
    // Add all categories from the form into the object
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/^category_(\d+)/", $key, $matches)) 
			{
				$array1[$matches[1]] = ilUtil::stripSlashes($value);
				if (strlen($array1[$matches[1]]) == 0) $complete = false;
			}
		}
		$this->object->categories->addCategoryArray($array1);
		if ($save)
		{	
			$this->object->saveCategoriesToDb();
		}
		return $complete;
	}
	
/**
* Removes one or more categories
*
* Removes one or more categories
*
* @access private
*/
	function deleteCategory()
	{
		$this->writeCategoryData();
		$nothing_selected = true;
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]))
			{
				$nothing_selected = false;
				$this->object->categories->removeCategories($_POST["chb_category"]);
			}
		}
		if ($nothing_selected) sendInfo($this->lng->txt("category_delete_select_none"));
		$_SESSION["spl_modified"] = true;
		$this->categories();
	}
	
/**
* Selects one or more categories for moving
*
* Selects one or more categories for moving
*
* @access private
*/
	function moveCategory()
	{
		$this->writeCategoryData();
		$nothing_selected = true;
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]))
			{
				$nothing_selected = false;
				sendInfo($this->lng->txt("select_target_position_for_move"));
				$_SESSION["spl_move"] = $_POST["chb_category"];
			}
		}
		if ($nothing_selected) sendInfo($this->lng->txt("no_category_selected_for_move"));
		$this->categories();
	}
	
/**
* Inserts categories which are selected for moving before the selected category
*
* Inserts categories which are selected for moving before the selected category
*
* @access private
*/
	function insertBeforeCategory()
	{
		$result = $this->writeCategoryData();
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]) == 1)
			{
				// one entry is selected, moving is allowed
				$this->object->categories->removeCategories($_SESSION["spl_move"]);
				$newinsertindex = $this->object->categories->getCategoryIndex($_POST["category_".$_POST["chb_category"][0]]);
				if ($newinsertindex === false) $newinsertindex = 0;
				$move_categories = $_SESSION["spl_move"];
				natsort($move_categories);
				foreach (array_reverse($move_categories) as $index)
				{
					$this->object->categories->addCategoryAtPosition($_POST["category_$index"], $newinsertindex);
				}
			}
		}
		$_SESSION["spl_modified"] = true;
		unset($_SESSION["spl_move"]);
		$this->categories();
	}
	
/**
* Inserts categories which are selected for moving before the selected category
*
* Inserts categories which are selected for moving before the selected category
*
* @access private
*/
	function insertAfterCategory()
	{
		$result = $this->writeCategoryData();
		if (array_key_exists("chb_category", $_POST))
		{
			if (count($_POST["chb_category"]) == 1)
			{
				// one entry is selected, moving is allowed
				$this->object->categories->removeCategories($_SESSION["spl_move"]);
				$newinsertindex = $this->object->categories->getCategoryIndex($_POST["category_".$_POST["chb_category"][0]]);
				if ($newinsertindex === false) $newinsertindex = 0;
				$move_categories = $_SESSION["spl_move"];
				natsort($move_categories);
				foreach (array_reverse($move_categories) as $index)
				{
					$this->object->categories->addCategoryAtPosition($_POST["category_$index"], $newinsertindex+1);
				}
			}
		}
		$_SESSION["spl_modified"] = true;
		unset($_SESSION["spl_move"]);
		$this->categories();
	}
}
?>
