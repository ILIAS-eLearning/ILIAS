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

require_once "./survey/classes/class.SurveyNominalQuestionGUI.php";
require_once "./survey/classes/class.SurveyTextQuestionGUI.php";
require_once "./survey/classes/class.SurveyMetricQuestionGUI.php";
require_once "./survey/classes/class.SurveyOrdinalQuestionGUI.php";

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
	
	function originalSyncForm($question_object, $ref_id)
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
	
	/**
	* Creates an output for the confirmation to delete categories
	*
	* Creates an output for the confirmation to delete categories
	*
	* @access public
	*/
	function deleteCategory()
	{
		$result = $this->writePostData();
		if ($result == 0)
		{
			$delete_categories = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/chb_category_(\d+)/", $key, $matches)) {
					array_push($delete_categories, $matches[1]);
				}
			}
			if (count($delete_categories))
			{
				sendInfo($this->lng->txt("category_delete_confirm"));
				$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_confirm_remove_categories.html", true);
				$rowclass = array("tblrow1", "tblrow2");
				$counter = 0;
				foreach ($_POST as $key => $value) {
					if (preg_match("/chb_category_(\d+)/", $key, $matches)) {
						$this->tpl->setCurrentBlock("row");
						$this->tpl->setVariable("TXT_TITLE", $_POST["category_$matches[1]"]);
						$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("hidden");
						$this->tpl->setVariable("HIDDEN_NAME", $key);
						$this->tpl->setVariable("HIDDEN_VALUE", $value);
						$this->tpl->parseCurrentBlock();
						$counter++;
					}
				}
		
				// set the id to return to the selected question
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "id");
				$this->tpl->setVariable("HIDDEN_VALUE", $_POST["id"]);
				$this->tpl->parseCurrentBlock();
				
				$this->tpl->setCurrentBlock("adm_content");
				$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("category"));
				$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
				$this->tpl->setVariable("BTN_CONFIRM",$this->lng->txt("confirm"));
				$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				sendInfo($this->lng->txt("category_delete_select_none"));
				$this->editQuestion();
			}
		}
		else
		{
			// You cannot add answers before you enter the required data
			sendInfo($this->lng->txt("fill_out_all_required_fields_delete_category"));
			$this->editQuestion();
		}
	}
	
/**
* Removes selected categories from the question
*
* Removes selected categories from the question
*
* @access public
*/
	function confirmDeleteCategory() {
		$delete_categories = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/chb_category_(\d+)/", $key, $matches)) {
				array_push($delete_categories, $matches[1]);
			}
		}
		if (count($delete_categories))
		{
			$this->object->removeCategories($delete_categories);
		}
		$this->object->saveToDb();
		$this->ctrl->redirect($this, "editQuestion");
	}

	function moveCategory()
	{
		$result = $this->writePostData();
		if ($result == 0)
		{
			$checked_move = 0;
			foreach ($_POST as $key => $value) 
			{
				if (preg_match("/chb_category_(\d+)/", $key, $matches))
				{
					$checked_move++;
					$this->tpl->setCurrentBlock("move");
					$this->tpl->setVariable("MOVE_COUNTER", $matches[1]);
					$this->tpl->setVariable("MOVE_VALUE", $matches[1]);
					$this->tpl->parseCurrentBlock();
				}
			}
			if ($checked_move)
			{
				$this->tpl->setCurrentBlock("move_buttons");
				$this->tpl->setVariable("INSERT_BEFORE", $this->lng->txt("insert_before"));
				$this->tpl->setVariable("INSERT_AFTER", $this->lng->txt("insert_after"));
				$this->tpl->parseCurrentBlock();
				sendInfo($this->lng->txt("select_target_position_for_move"));
			}
			else
			{
				sendInfo($this->lng->txt("no_category_selected_for_move"));
			}
		}
		else
		{
			// You cannot add answers before you enter the required data
			sendInfo($this->lng->txt("fill_out_all_required_fields_move_category"));
		}
		$this->editQuestion();
	}
	
	function addCategory()
	{
		$result = $this->writePostData();
		if ($result == 0)
		{
			// Check for blank fields before a new category field is inserted
			foreach ($_POST as $key => $value) {
				if (preg_match("/category_(\d+)/", $key, $matches)) {
					if (!$value) {
						$_POST["cmd"]["addCategory"] = "";
						sendInfo($this->lng->txt("fill_out_all_category_fields"));
					}
				}
			}
		}
		else
		{
			sendInfo($this->lng->txt("fill_out_all_required_fields_add_category"));
			$_POST["cmd"]["addCategory"] = "";
		}
		$this->editQuestion();
	}
	
	function insertBeforeCategory()
	{
		$result = $this->writePostData();
		$array1 = array();
		$array2 = array();
	
		// Move selected categories
		$move_categories = array();
		$selected_category = -1;
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^move_(\d+)$/", $key, $matches))
			{
				array_push($move_categories, $value);
				array_push($array2, ilUtil::stripSlashes($_POST["category_$value"]));
			}
			if (preg_match("/^chb_category_(\d+)/", $key, $matches))
			{
				if ($selected_category < 0)
				{
					// take onley the first checked category (if more categories are checked)
					$selected_category = $matches[1];
				}
			}
		}

    // Add all categories from the form into the object
		foreach ($_POST as $key => $value) {
			if (preg_match("/^category_(\d+)/", $key, $matches)) {
				if (!in_array($matches[1], $move_categories) or ($selected_category < 0))
				{
					array_push($array1, ilUtil::stripSlashes($value));
				}
			}
		}

		if ($selected_category >= 0)
		{
			// Delete all existing categories and create new categories from the form data
			$this->object->flushCategories();
			$array_pos = array_search($_POST["category_$selected_category"], $array1);
			$part1 = array_slice($array1, 0, $array_pos);
			$part2 = array_slice($array1, $array_pos);
			$array1 = array_merge($part1, $array2, $part2);
			$this->object->addCategoryArray($array1);
			$this->object->saveToDb();
		}
		$this->editQuestion();
	}
	
	function insertAfterCategory()
	{
		$result = $this->writePostData();
		$array1 = array();
		$array2 = array();
	
		// Move selected categories
		$move_categories = array();
		$selected_category = -1;
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/^move_(\d+)$/", $key, $matches))
			{
				array_push($move_categories, $value);
				array_push($array2, ilUtil::stripSlashes($_POST["category_$value"]));
			}
			if (preg_match("/^chb_category_(\d+)/", $key, $matches))
			{
				if ($selected_category < 0)
				{
					// take onley the first checked category (if more categories are checked)
					$selected_category = $matches[1];
				}
			}
		}

    // Add all categories from the form into the object
		foreach ($_POST as $key => $value) {
			if (preg_match("/^category_(\d+)/", $key, $matches)) {
				if (!in_array($matches[1], $move_categories) or ($selected_category < 0))
				{
					array_push($array1, ilUtil::stripSlashes($value));
				}
			}
		}

		if ($selected_category >= 0)
		{
			// Delete all existing categories and create new categories from the form data
			$this->object->flushCategories();
			$array_pos = array_search($_POST["category_$selected_category"], $array1);
			$part1 = array_slice($array1, 0, $array_pos + 1);
			$part2 = array_slice($array1, $array_pos + 1);
			$array1 = array_merge($part1, $array2, $part2);
			$this->object->addCategoryArray($array1);
			$this->object->saveToDb();
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
* Creates an output for the addition of phrases
*
* Creates an output for the addition of phrases
*
* @access public
*/
  function addPhrase($hasError = false) 
	{
		if (!$hasError)
		{
			$this->object->saveToDb();
			$result = $this->writePostData();
			if ($result > 0)
			{
				sendInfo($this->lng->txt("fill_out_all_required_fields"));
				$this->editQuestion();
				return;
			}
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_addphrase.html", true);

		// set the id to return to the selected question
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "id");
		$this->tpl->setVariable("HIDDEN_VALUE", $this->object->getId());
		$this->tpl->parseCurrentBlock();

		$phrases =& $this->object->getAvailablePhrases();
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($phrases as $phrase_id => $phrase_array)
		{
			$this->tpl->setCurrentBlock("phraserow");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter++ % 2]);
			$this->tpl->setVariable("PHRASE_VALUE", $phrase_id);
			$this->tpl->setVariable("PHRASE_NAME", $phrase_array["title"]);
			$categories =& $this->object->getCategoriesForPhrase($phrase_id);
			$this->tpl->setVariable("PHRASE_CONTENT", join($categories, ","));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TEXT_PHRASE", $this->lng->txt("phrase"));
		$this->tpl->setVariable("TEXT_CONTENT", $this->lng->txt("categories"));
		$this->tpl->setVariable("TEXT_ADD_PHRASE", $this->lng->txt("add_phrase"));
		$this->tpl->setVariable("TEXT_INTRODUCTION",$this->lng->txt("add_phrase_introduction"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Cancels the form adding a phrase
*
* Cancels the form adding a phrase
*
* @access public
*/
	function cancelViewPhrase() {
		$this->ctrl->redirect($this, "editQuestion");
	}

/**
* Adds a selected phrase
*
* Adds a selected phrase
*
* @access public
*/
	function addSelectedPhrase() {
		if (strcmp($_POST["phrases"], "") == 0)
		{
			sendInfo($this->lng->txt("select_phrase_to_add"));
			$this->addPhrase(true);
		}
		else
		{
			if (strcmp($this->object->getPhrase($_POST["phrases"]), "dp_standard_numbers") != 0)
			{
				$this->object->addPhrase($_POST["phrases"]);
			}
			else
			{
				$this->addStandardNumbers();
				return;
			}
			$this->editQuestion();
		}
	}

/**
* Creates an output for the addition of standard numbers
*
* Creates an output for the addition of standard numbers
*
* @access public
*/
  function addStandardNumbers() 
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_addphrase_standard_numbers.html", true);

		// set the id to return to the selected question
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "id");
		$this->tpl->setVariable("HIDDEN_VALUE", $this->object->getId());
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_ADD_LIMITS", $this->lng->txt("add_limits_for_standard_numbers"));
		$this->tpl->setVariable("TEXT_LOWER_LIMIT",$this->lng->txt("lower_limit"));
		$this->tpl->setVariable("TEXT_UPPER_LIMIT",$this->lng->txt("upper_limit"));
		$this->tpl->setVariable("VALUE_LOWER_LIMIT", $_POST["lower_limit"]);
		$this->tpl->setVariable("VALUE_UPPER_LIMIT", $_POST["upper_limit"]);
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt("add_phrase"));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Cancels the form adding standard numbers
*
* Cancels the form adding standard numbers
*
* @access public
*/
	function cancelStandardNumbers() {
		$this->ctrl->redirect($this, "editQuestion");
	}

/**
* Insert standard numbers to the question
*
* Insert standard numbers to the question
*
* @access public
*/
	function insertStandardNumbers() {
		if ((strcmp($_POST["lower_limit"], "") == 0) or (strcmp($_POST["upper_limit"], "") == 0))
		{
			sendInfo($this->lng->txt("missing_upper_or_lower_limit"));
			$this->addStandardNumbers();
		}
		else if ((int)$_POST["upper_limit"] <= (int)$_POST["lower_limit"])
		{
			sendInfo($this->lng->txt("upper_limit_must_be_greater"));
			$this->addStandardNumbers();
		}
		else
		{
			$this->object->addStandardNumbers($_POST["lower_limit"], $_POST["upper_limit"]);
			$this->editQuestion();
		}
	}

/**
* Creates an output to save a phrase
*
* Creates an output to save a phrase
*
* @access public
*/
  function savePhrase($hasError = false) 
	{
		if (!$hasError)
		{
			$result = $this->writePostData();
		}
		else
		{
			$result = 0;
		}

		$categories_checked = 0;
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/chb_category_(\d+)/", $key, $matches)) 
			{
				$categories_checked++;
			}
		}

		if ($categories_checked == 0)
		{
			sendInfo($this->lng->txt("check_category_to_save_phrase"));
			$this->editQuestion();
			return;
		}
		
		if ($result == 0)
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_savephrase.html", true);
			$rowclass = array("tblrow1", "tblrow2");
			$counter = 0;
			foreach ($_POST as $key => $value) {
				if (preg_match("/chb_category_(\d+)/", $key, $matches)) {
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("TXT_TITLE", $_POST["category_$matches[1]"]);
					$this->tpl->setVariable("COLOR_CLASS", $rowclass[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("hidden");
					$this->tpl->setVariable("HIDDEN_NAME", $key);
					$this->tpl->setVariable("HIDDEN_VALUE", $value);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("hidden");
					$this->tpl->setVariable("HIDDEN_NAME", "category_$matches[1]");
					$this->tpl->setVariable("HIDDEN_VALUE", $_POST["category_$matches[1]"]);
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
			}
	
			// set the id to return to the selected question
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id");
			$this->tpl->setVariable("HIDDEN_VALUE", $_POST["id"]);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("SAVE_PHRASE_INTRODUCTION", $this->lng->txt("save_phrase_introduction"));
			$this->tpl->setVariable("TEXT_PHRASE_TITLE", $this->lng->txt("enter_phrase_title"));
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("category"));
			$this->tpl->setVariable("VALUE_PHRASE_TITLE", $_POST["phrase_title"]);
			$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("BTN_CONFIRM",$this->lng->txt("confirm"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			sendInfo($this->lng->txt("fill_out_all_required_fields_save_phrase"));
		}
	}

/**
* Cancels the form saving a phrase
*
* Cancels the form saving a phrase
*
* @access public
*/
	function cancelSavePhrase() {
		$this->ctrl->redirect($this, "editQuestion");
	}

/**
* Save a new phrase to the database
*
* Save a new phrase to the database
*
* @access public
*/
	function confirmSavePhrase() {
		if (!$_POST["phrase_title"])
		{
			sendInfo($this->lng->txt("qpl_savephrase_empty"));
			$this->savePhrase(true);
			return;
		}
		if ($this->object->phraseExists($_POST["phrase_title"]))
		{
			sendInfo($this->lng->txt("qpl_savephrase_exists"));
			$this->savePhrase(true);
			return;
		}

		$save_categories = array();
		foreach ($_POST as $key => $value) {
			if (preg_match("/chb_category_(\d+)/", $key, $matches)) {
				array_push($save_categories, $matches[1]);
			}
		}
		if (count($save_categories))
		{
			$this->object->savePhrase($save_categories, $_POST["phrase_title"]);
			sendInfo($this->lng->txt("phrase_saved"));
		}
		$this->editQuestion();
	}

	function setQuestionTabsForClass($guiclass)
	{
		global $rbacsystem;

		include_once "./classes/class.ilTabsGUI.php";
		$tabs_gui =& new ilTabsGUI();
		
		$this->ctrl->setParameterByClass("$guiclass", "sel_question_types", $this->getQuestionType());
		$this->ctrl->setParameterByClass("$guiclass", "q_id", $_GET["q_id"]);

		if ($rbacsystem->checkAccess('edit', $_GET["ref_id"])) {
			$tabs_gui->addTarget("properties",
				$this->ctrl->getLinkTargetByClass("$guiclass", "editQuestion"), "editQuestion",
				"$guiclass");
		}
		
		if ($_GET["q_id"])
		{
			$tabs_gui->addTarget("preview",
			$this->ctrl->getLinkTargetByClass("$guiclass", "preview"), "preview",
			"$guiclass");
		}

		if ($_GET["calling_survey"])
		{
			$tabs_gui->addTarget("menubacktosurvey",
				"survey.php?cmd=questions&ref_id=".$_GET["calling_survey"], "questions",
				"ilObjSurveyQuestionPoolGUI");
		}
		elseif ($_GET["new_for_survey"])
		{
			$tabs_gui->addTarget("menubacktosurvey",
				"survey.php?cmd=questions&new_id=".$this->object->getId() . "&ref_id=".$_GET["new_for_survey"], "questions",
				"ilObjSurveyQuestionPoolGUI");
		}
		else
		{		
			$tabs_gui->addTarget("menuback",
				$this->ctrl->getLinkTargetByClass("ilobjsurveyquestionpoolgui", "questions"), "questions",
				"ilObjSurveyQuestionPoolGUI");
		}
		
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

    if ($this->object->getId() > 0) {
      $title = $this->lng->txt("edit") . " &quot;" . $this->object->getTitle() . "&quot";
    } else {
      $title = $this->lng->txt("create_new") . " " . $this->lng->txt($this->getQuestionType());
    }
		$this->tpl->setVariable("HEADER", $title);
//		echo "<br>end setQuestionTabs<br>";
	}
}
?>
