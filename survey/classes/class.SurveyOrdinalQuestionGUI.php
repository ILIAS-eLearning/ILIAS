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

require_once "./survey/classes/class.SurveyOrdinalQuestion.php";
require_once "./survey/classes/class.SurveyQuestionGUI.php";

/**
* Ordinal survey question GUI representation
*
* The SurveyOrdinalQuestionGUI class encapsulates the GUI representation
* for ordinal survey question types.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.SurveyOrdinalQuestionGUI.php
* @modulegroup   Survey
*/
class SurveyOrdinalQuestionGUI extends SurveyQuestionGUI {

/**
* SurveyOrdinalQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyOrdinalQuestionGUI object.
*
* @param integer $id The database id of a ordinal question object
* @access public
*/
  function SurveyOrdinalQuestionGUI(
		$id = -1
  )

  {
		$this->SurveyQuestionGUI();
		$this->object = new SurveyOrdinalQuestion();
		if ($id >= 0)
		{
			$this->object->loadFromDb($id);
		}
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
		return "qt_ordinal";
	}

/**
* Creates an output for the confirmation to delete categories
*
* Creates an output for the confirmation to delete categories
*
* @access public
*/
  function showDeleteCategoryForm() 
	{
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
	
/**
* Creates an output of the edit form for the question
*
* Creates an output of the edit form for the question
*
* @access public
*/
  function editQuestion() {
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_ordinal.html", true);
	  $this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_svy_qpl_other_question_data.html", true);
    // output of existing single response answers
		for ($i = 0; $i < $this->object->getCategoryCount(); $i++) {
			$this->tpl->setCurrentBlock("cat_selector");
			$this->tpl->setVariable("CATEGORY_ORDER", $i);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("categories");
			$category = $this->object->getCategory($i);
			$this->tpl->setVariable("CATEGORY_ORDER", $i);
			$this->tpl->setVariable("CATEGORY_NUMBER", $i+1);
			$this->tpl->setVariable("VALUE_CATEGORY", $category);
			$this->tpl->setVariable("TEXT_CATEGORY", $this->lng->txt("category"));
			$this->tpl->parseCurrentBlock();
		}
		if (strcmp($_POST["cmd"]["addCategory"], "") != 0) {
			// Create template for a new category
			$this->tpl->setCurrentBlock("categories");
			$this->tpl->setVariable("CATEGORY_ORDER", $this->object->getCategoryCount());
			$this->tpl->setVariable("TEXT_CATEGORY", $this->lng->txt("category"));
			$this->tpl->parseCurrentBlock();
		}

		// call to other question data
		$this->outOtherQuestionData();
		$internallinks = array(
			"lm" => $this->lng->txt("obj_lm"),
			"st" => $this->lng->txt("obj_st"),
			"pg" => $this->lng->txt("obj_pg"),
			"glo" => $this->lng->txt("glossary_term")
		);
		foreach ($internallinks as $key => $value)
		{
			$this->tpl->setCurrentBlock("internallink");
			$this->tpl->setVariable("TYPE_INTERNAL_LINK", $key);
			$this->tpl->setVariable("TEXT_INTERNAL_LINK", $value);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material"));
		if (count($this->object->material))
		{
			$href = SurveyQuestion::_getInternalLinkHref($this->object->material["internal_link"]);
			$this->tpl->setVariable("TEXT_VALUE_MATERIAL", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("material"). "</a> ");
			$this->tpl->setVariable("BUTTON_REMOVE_MATERIAL", $this->lng->txt("remove"));
			$this->tpl->setVariable("BUTTON_ADD_MATERIAL", $this->lng->txt("change"));
			$this->tpl->setVariable("VALUE_MATERIAL", $this->object->material["internal_link"]);
		}
		else
		{
			$this->tpl->setVariable("BUTTON_ADD_MATERIAL", $this->lng->txt("add"));
		}
		$this->tpl->setVariable("TEXT_ORIENTATION", $this->lng->txt("orientation"));
		switch ($this->object->getOrientation())
		{
			case 0:
				$this->tpl->setVariable("SELECTED_VERTICAL", " selected=\"selected\"");
				break;
			case 1:
				$this->tpl->setVariable("SELECTED_HORIZONTAL", " selected=\"selected\"");
				break;
			case 2:
				$this->tpl->setVariable("SELECTED_COMBOBOX", " selected=\"selected\"");
				break;
		}
		$this->tpl->setVariable("TXT_VERTICAL", $this->lng->txt("vertical"));
		$this->tpl->setVariable("TXT_HORIZONTAL", $this->lng->txt("horizontal"));
		$this->tpl->setVariable("TXT_COMBOBOX", $this->lng->txt("combobox"));
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("VALUE_DESCRIPTION", $this->object->getDescription());
		$this->tpl->setVariable("VALUE_AUTHOR", $this->object->getAuthor());
		$questiontext = $this->object->getQuestiontext();
		$questiontext = str_replace("<br />", "\n", $questiontext);
		$this->tpl->setVariable("VALUE_QUESTION", htmlspecialchars($questiontext));
		$this->tpl->setVariable("VALUE_SAVE_PHRASE", $this->lng->txt("save_phrase"));
		$this->tpl->setVariable("VALUE_ADD_PHRASE", $this->lng->txt("add_phrase"));
		$this->tpl->setVariable("VALUE_ADD_CATEGORY", $this->lng->txt("add_category"));
		$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
		$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("questiontype"));
		$this->tpl->setVariable("TEXT_OBLIGATORY", $this->lng->txt("obligatory"));
		if ($this->object->getObligatory())
		{
			$this->tpl->setVariable("CHECKED_OBLIGATORY", " checked=\"checked\"");
		}
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
  }

/**
* Creates the question output form for the learner
* 
* Creates the question output form for the learner
*
* @access public
*/
	function outWorkingForm($working_data = "", $question_title = 1, $error_message = "")
	{
		if (count($this->object->material))
		{
			$this->tpl->setCurrentBlock("material_ordinal");
			$href = SurveyQuestion::_getInternalLinkHref($this->object->material["internal_link"]);
			$this->tpl->setVariable("TEXT_MATERIAL", " <a href=\"$href\" target=\"content\">" . $this->lng->txt("material"). "</a> ");
			$this->tpl->parseCurrentBlock();
		}
		switch ($this->object->orientation)
		{
			case 0:
				// vertical orientation
				for ($i = 0; $i < $this->object->getCategoryCount(); $i++) {
					$category = $this->object->getCategory($i);
					$this->tpl->setCurrentBlock("ordinal_row");
					$this->tpl->setVariable("TEXT_ORDINAL", $category);
					$this->tpl->setVariable("VALUE_ORDINAL", $i);
					$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
					if (is_array($working_data))
					{
						if (strcmp($working_data[0]["value"], "") != 0)
						{
							if ($working_data[0]["value"] == $i)
							{
								$this->tpl->setVariable("CHECKED_ORDINAL", " checked=\"checked\"");
							}
						}
					}
					$this->tpl->parseCurrentBlock();
				}
				break;
			case 1:
				// horizontal orientation
				for ($i = 0; $i < $this->object->getCategoryCount(); $i++) 
				{
					$category = $this->object->getCategory($i);
					$this->tpl->setCurrentBlock("radio_col_ordinal");
					$this->tpl->setVariable("VALUE_ORDINAL", $i);
					$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
					if (is_array($working_data))
					{
						if (strcmp($working_data[0]["value"], "") != 0)
						{
							if ($working_data[0]["value"] == $i)
							{
								$this->tpl->setVariable("CHECKED_ORDINAL", " checked=\"checked\"");
							}
						}
					}
					$this->tpl->parseCurrentBlock();
				}
				for ($i = 0; $i < $this->object->getCategoryCount(); $i++) 
				{
					$category = $this->object->getCategory($i);
					$this->tpl->setCurrentBlock("text_col_ordinal");
					$this->tpl->setVariable("VALUE_ORDINAL", $i);
					$this->tpl->setVariable("TEXT_ORDINAL", $category);
					$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
					$this->tpl->parseCurrentBlock();
				}
				break;
			case 2:
				// combobox output
				for ($i = 0; $i < $this->object->getCategoryCount(); $i++) {
					$category = $this->object->getCategory($i);
					$this->tpl->setCurrentBlock("comborow");
					$this->tpl->setVariable("TEXT_ORDINAL", $category);
					$this->tpl->setVariable("VALUE_ORDINAL", $i);
					if (is_array($working_data))
					{
						if (strcmp($working_data[0]["value"], "") != 0)
						{
							if ($working_data[0]["value"] == $i)
							{
								$this->tpl->setVariable("SELECTED_ORDINAL", " selected=\"selected\"");
							}
						}
					}
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("combooutput");
				$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
				$this->tpl->setVariable("SELECT_OPTION", $this->lng->txt("select_option"));
				$this->tpl->setVariable("TEXT_SELECTION", $this->lng->txt("selection"));
				$this->tpl->parseCurrentBlock();
				break;
		}
		if ($question_title)
		{
			$this->tpl->setVariable("QUESTION_TITLE", $this->object->getTitle());
		}
		$this->tpl->setCurrentBlock("question_data_ordinal");
		if (strcmp($error_message, "") != 0)
		{
			$this->tpl->setVariable("ERROR_MESSAGE", "<p class=\"warning\">$error_message</p>");
		}
		$this->tpl->setVariable("QUESTIONTEXT", $this->object->getQuestiontext());
		if ($this->object->getObligatory())
		{
			$this->tpl->setVariable("OBLIGATORY_TEXT", $this->lng->txt("survey_question_obligatory"));
		}
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a preview of the question
*
* Creates a preview of the question
*
* @access private
*/
	function preview()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_preview.html", true);
		$this->tpl->addBlockFile("ORDINAL", "ordinal", "tpl.il_svy_out_ordinal.html", true);
		$this->outWorkingForm();
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
*
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
*
* @access private
*/
  function outOtherQuestionData() {
		if (!empty($this->object->materials)) {
			$this->tpl->setCurrentBlock("mainselect_block");
			$this->tpl->setCurrentBlock("select_block");
			foreach ($this->object->materials as $key => $value) {
				$this->tpl->setVariable("MATERIAL_VALUE", $key);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("materiallist_block");
			$i = 1;
			foreach ($this->object->materials as $key => $value) {
				$this->tpl->setVariable("MATERIAL_COUNTER", $i);
				$this->tpl->setVariable("MATERIAL_VALUE", $key);
				$this->tpl->setVariable("MATERIAL_FILE_VALUE", $value);
				$this->tpl->parseCurrentBlock();
				$i++;
			}
			$this->tpl->setVariable("UPLOADED_MATERIAL", $this->lng->txt("uploaded_material"));
			$this->tpl->setVariable("VALUE_MATERIAL_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("COLSPAN_MATERIAL", " colspan=\"3\"");
			$this->tpl->parse("mainselect_block");
		}
		
		$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material"));
		$this->tpl->setVariable("TEXT_MATERIAL_FILE", $this->lng->txt("material_file"));
		$this->tpl->setVariable("VALUE_MATERIAL_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("COLSPAN_MATERIAL", " colspan=\"3\"");
		$this->tpl->parseCurrentBlock();
	}

/**
* Evaluates a posted edit form and writes the form data in the question object
*
* Evaluates a posted edit form and writes the form data in the question object
*
* @return integer A positive value, if one of the required fields wasn't set, else 0
* @access private
*/
  function writePostData() {
    $result = 0;
    if ((!$_POST["title"]) or (!$_POST["author"]) or (!$_POST["question"]))
      $result = 1;

    // Set the question id from a hidden form parameter
    if ($_POST["id"] > 0)
      $this->object->setId($_POST["id"]);

    $this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
    $this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
    $this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
		$this->object->setOrientation($_POST["orientation"]);
		$this->object->setMaterial($_POST["material"]);
		$questiontext = ilUtil::stripSlashes($_POST["question"]);
		$questiontext = str_replace("\n", "<br />", $questiontext);
    $this->object->setQuestiontext($questiontext);
		if ($_POST["obligatory"])
		{
			$this->object->setObligatory(1);
		}
		else
		{
			$this->object->setObligatory(0);
		}
    // adding materials uris
    $saved = $this->writeOtherPostData();

    // Delete all existing categories and create new categories from the form data
    $this->object->flushCategories();

		$array1 = array();
    // Add all categories from the form into the object
		foreach ($_POST as $key => $value) {
			if (preg_match("/^category_(\d+)/", $key, $matches)) {
				array_push($array1, ilUtil::stripSlashes($value));
			}
		}
		$this->object->addCategoryArray($array1);
		
		if ($saved) {
			// If the question was saved automatically before an upload, we have to make
			// sure, that the state after the upload is saved. Otherwise the user could be
			// irritated, if he presses cancel, because he only has the question state before
			// the upload process.
			$this->object->saveToDb();
		}
    return $result;
  }

/**
* Sets the other data i.e. materials uris of a question from a posted create/edit form
*
* Sets the other data i.e. materials uris of a question from a posted create/edit form
*
* @return boolean Returns true, if the question had to be autosaved to get a question id for the save path of the material, otherwise returns false.
* @access private
*/
	function writeOtherPostData() {
		// Add all materials uris from the form into the object
		$saved = false;
		$this->object->flushMaterials();
		foreach ($_POST as $key => $value) {
			if (preg_match("/material_list_/", $key, $matches)) {
				$this->object->addMaterials($value, str_replace("material_list_", "", $key));
			}
		}
		if (!empty($_FILES['materialFile']['tmp_name'])) {
			if ($this->object->getId() <= 0) {
				$this->object->saveToDb();
				$saved = true;
				sendInfo($this->lng->txt("question_saved_for_upload"));
			}
			$this->object->setMaterialsFile($_FILES['materialFile']['name'], $_FILES['materialFile']['tmp_name'], $_POST[materialName]);
		}
	
		// Delete material if the delete button was pressed
		if ((strlen($_POST["cmd"]["deletematerial"]) > 0)&&(!empty($_POST[materialselect]))) {
			foreach ($_POST[materialselect] as $value) {
				$this->object->deleteMaterial($value);
			}
		}
		return $saved;
	}

	function setQuestionTabs()
	{
		$this->setQuestionTabsForClass("surveyordinalquestiongui");
	}
}
?>
