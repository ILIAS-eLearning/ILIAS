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

require_once "class.SurveyNominalQuestion.php";

/**
* Nominal survey question GUI representation
*
* The SurveyNominalQuestionGUI class encapsulates the GUI representation
* for nominal survey question types.
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.SurveyNominalQuestionGUI.php
* @modulegroup   Survey
*/
class SurveyNominalQuestionGUI {
/**
* Question object
*
* A reference to the nominal question object
*
* @var object
*/
  var $object;
	
	var $tpl;
	var $lng;

/**
* SurveyNominalQuestionGUI constructor
*
* The constructor takes possible arguments an creates an instance of the SurveyNominalQuestionGUI object.
*
* @param integer $id The database id of a nominal question object
* @access public
*/
  function SurveyNominalQuestionGUI(
		$id = -1
  )

  {
		global $lng;
		global $tpl;
		
    $this->lng =& $lng;
    $this->tpl =& $tpl;
		
		$this->object = new SurveyNominalQuestion();
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
		return "qt_nominal";
	}

/**
* Creates an output of the edit form for the question
*
* Creates an output of the edit form for the question
*
* @access public
*/
  function showEditForm() {
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_nominal.html", true);
	  $this->tpl->addBlockFile("OTHER_QUESTION_DATA", "other_question_data", "tpl.il_svy_qpl_other_question_data.html", true);

    // output of existing single response answers
		for ($i = 0; $i < $this->object->getCategoryCount(); $i++) {
			$this->tpl->setCurrentBlock("deletebutton");
			$this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
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
		if (strlen($_POST["cmd"]["add"]) > 0) {
			// Create template for a new category
			$this->tpl->setCurrentBlock("categories");
			$this->tpl->setVariable("CATEGORY_ORDER", $this->object->getCategoryCount());
			$this->tpl->setVariable("TEXT_CATEGORY", $this->lng->txt("category"));
			$this->tpl->parseCurrentBlock();
		}

		// call to other question data
		$this->outOtherQuestionData();

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("QUESTION_ID", $this->object->getId());
		$this->tpl->setVariable("VALUE_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("VALUE_DESCRIPTION", $this->object->getDescription());
		$this->tpl->setVariable("VALUE_AUTHOR", $this->object->getAuthor());
		$this->tpl->setVariable("VALUE_QUESTION", $this->object->getQuestiontext());
		$this->tpl->setVariable("VALUE_ADD_CATEGORY", $this->lng->txt("add_category"));
		$this->tpl->setVariable("TXT_SR", $this->lng->txt("multiple_choice_single_response"));
		$this->tpl->setVariable("TXT_MR", $this->lng->txt("multiple_choice_multiple_response"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_QUESTION", $this->lng->txt("question"));
		$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt("questiontype"));
		$this->tpl->setVariable("SAVE",$this->lng->txt("save"));
		$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
		$this->tpl->setVariable("CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=questions&sel_question_types=qt_nominal");
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
			foreach ($this->question->materials as $key => $value) {
				$this->tpl->setVariable("MATERIAL_COUNTER", $i);
				$this->tpl->setVariable("MATERIAL_VALUE", $key);
				$this->tpl->setVariable("MATERIAL_FILE_VALUE", $value);
				$this->tpl->parseCurrentBlock();
				$i++;
			}
			$this->tpl->setVariable("UPLOADED_MATERIAL", $this->lng->txt("uploaded_material"));
			$this->tpl->setVariable("VALUE_MATERIAL_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("COLSPAN_MATERIAL", "colspan=\"4\" ");
			$this->tpl->parse("mainselect_block");
		}
		
		$this->tpl->setVariable("TEXT_MATERIAL", $this->lng->txt("material"));
		$this->tpl->setVariable("TEXT_MATERIAL_FILE", $this->lng->txt("material_file"));
		$this->tpl->setVariable("VALUE_MATERIAL_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("COLSPAN_MATERIAL", "colspan=\"4\" ");
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

		if (($result) and ($_POST["cmd"]["add"])) {
			// You cannot add answers before you enter the required data
      sendInfo($this->lng->txt("fill_out_all_required_fields_add_category"));
			$_POST["cmd"]["add"] = "";
		}

		// Check for blank fields before a new category field is inserted
		if ($_POST["cmd"]["add"]) {
			foreach ($_POST as $key => $value) {
	   		if (preg_match("/category_(\d+)/", $key, $matches)) {
					if (!$value) {
						$_POST["cmd"]["add"] = "";
						sendInfo($this->lng->txt("fill_out_all_category_fields"));
					}
			 	}
		  }
		}

    $this->object->setTitle(ilUtil::stripSlashes($_POST["title"]));
    $this->object->setAuthor(ilUtil::stripSlashes($_POST["author"]));
    $this->object->setDescription(ilUtil::stripSlashes($_POST["description"]));
    $this->object->setQuestiontext(ilUtil::stripSlashes($_POST["question"]));
    // adding materials uris
    $saved = $this->writeOtherPostData();

    // Delete all existing categories and create new categories from the form data
    $this->object->flushCategories();

    // Add all categories from the form into the object
		foreach ($_POST as $key => $value) {
			if (preg_match("/category_(\d+)/", $key, $matches)) {
				$this->object->addCategory($value);
			}
		}

    // After adding all categories from the form we have to check if the learner pressed a delete button
    foreach ($_POST as $key => $value) {
      // was one of the answers deleted
      if (preg_match("/delete_(\d+)/", $key, $matches)) {
        $this->object->removeCategory($matches[1]);
      }
    }

    // Set the question id from a hidden form parameter
    if ($_POST["id"] > 0)
      $this->object->setId($_POST["id"]);

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


}
?>
