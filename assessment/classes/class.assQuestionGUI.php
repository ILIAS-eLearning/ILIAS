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

require_once "class.assQuestion.php";

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
class ASS_QuestionGUI {
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

/**
* ASS_QuestionGUI constructor
*
* ASS_QuestionGUI constructor
*
* @access public
*/
  function ASS_QuestionGUI(
  )

  {
		global $lng;
		global $tpl;

    $this->lng =& $lng;
    $this->tpl =& $tpl;

		$this->object = new ASS_Question();
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
* Creates an output of the edit form for the question
*
* Creates an output of the edit form for the question
*
* @access public
*/
  function showEditForm() 
	{
  }

/**
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
*
* Sets the extra fields i.e. estimated working time and material of a question from a posted create/edit form
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
* Creates a preview of the question
*
* Creates a preview of the question
*
* @access private
*/
	function outPreviewForm()
	{
	}

/**
* Sets the other data i.e. materials uris of a question from a posted create/edit form
*
* Sets the other data i.e. materials uris of a question from a posted create/edit form
*
* @return boolean Returns true, if the question had to be autosaved to get a question id for the save path of the material, otherwise returns false.
* @access private
*/
	function writeOtherPostData($result = 0) {
		$this->object->setEstimatedWorkingTime(
			ilUtil::stripSlashes($_POST["Estimated"][h]),
			ilUtil::stripSlashes($_POST["Estimated"][m]),
			ilUtil::stripSlashes($_POST["Estimated"][s])
		);

		// Add all materials uris from the form into the object
		$saved = false;
		$this->object->flushMaterials();
		foreach ($_POST as $key => $value) 
		{
			if (preg_match("/material_list_/", $key, $matches)) 
			{
				$this->object->add_materials($value, str_replace("material_list_", "", $key));
			}
		}
		if (!empty($_FILES['materialFile']['tmp_name']) and ($_POST["cmd"]["uploadingMaterial"])) 
		{
			if (($_POST["id"] > 0) or ($result != 1)) 
			{
				if ($this->object->getId() <= 0) 
				{
					$this->object->saveToDb();
					$saved = true;
					sendInfo($this->lng->txt("question_saved_for_upload"));
				}
				$this->object->setMaterialsFile($_FILES['materialFile']['name'], $_FILES['materialFile']['tmp_name'], $_POST[materialName]);
			}
			else
			{
				if ($_POST["cmd"]["uploadingMaterial"])
				{
					sendInfo($this->lng->txt("fill_out_all_required_fields_upload_material"));
				}
			}
		}

		// Delete material if the delete button was pressed
		if ((strlen($_POST["cmd"]["deletematerial"]) > 0)&&(!empty($_POST[materialselect]))) 
		{
			foreach ($_POST[materialselect] as $value) 
			{
				$this->object->deleteMaterial($value);
			}
		}
		return $saved;
	}


}
?>
