<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once "./survey/classes/class.SurveyNominalQuestionGUI.php";
require_once "./survey/classes/class.SurveyTextQuestionGUI.php";
require_once "./survey/classes/class.SurveyMetricQuestionGUI.php";
require_once "./survey/classes/class.SurveyOrdinalQuestionGUI.php";
require_once "./classes/class.ilObjectGUI.php";
require_once "./classes/class.ilMetaDataGUI.php";

/**
* Class ilObjSurveyQuestionPoolGUI
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

class ilObjSurveyQuestionPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSurveyQuestionPoolGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
    		global $lng;
		$this->type = "spl";
		$lng->loadLanguageModule("survey");
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		if (!defined("ILIAS_MODULE"))
		{
			$this->setTabTargetScript("adm_object.php");
		}
		else
		{
			$this->setTabTargetScript("questionpool.php");
		}
		if ($a_prepare_output) {
			$this->prepareOutput();
		}
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff
			
		// always send a message
		sendInfo($this->lng->txt("object_added"),true);
		
		$returnlocation = "questionpool.php";
		if (!defined("ILIAS_MODULE"))
		{
			$returnlocation = "adm_object.php";
		}
		header("Location:".$this->getReturnLocation("save","$returnlocation?".$this->link_params));
		exit();
	}
	
  function getAddParameter() 
  {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }
  
/**
* Cancels any action and displays the question browser
*
* Cancels any action and displays the question browser
*
* @param string $question_id Sets the id of a newly created question for a calling survey
* @access public
*/
	function cancelAction($question_id = "") 
	{
		if ($_SESSION["survey_id"])
		{
			if ($question_id) {
				$add_question = "&browsetype=1&add=$question_id";
			}
	    header("location:" . "survey.php" . "?ref_id=" . $_SESSION["survey_id"] . "&cmd=questions$add_question");
		} 
			else
		{
			header("location:" . $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=questions");
		}
	}
	
/**
* Creates a confirmation form to delete questions from the question pool
*
* Creates a confirmation form to delete questions from the question pool
*
* @param array $checked_questions An array with the id's of the questions checked for deletion
* @access public
*/
	function deleteQuestionsForm($checked_questions)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_confirm_delete_questions.html", true);
		$whereclause = join($checked_questions, " OR survey_question.question_id = ");
		$whereclause = " AND (survey_question.question_id = " . $whereclause . ")";
		$query = "SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id$whereclause ORDER BY survey_question.title";
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		if ($query_result->numRows() > 0)
		{
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if (in_array($data->question_id, $checked_questions))
				{
					$this->tpl->setCurrentBlock("row");
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->setVariable("TXT_TITLE", $data->title);
					$this->tpl->setVariable("TXT_DESCRIPTION", $data->description);
					$this->tpl->setVariable("TXT_TYPE", $this->lng->txt($data->type_tag));
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "1");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Creates a confirmation form to paste copied questions in the question pool
*
* Creates a confirmation form to paste copied questions in the question pool
*
* @param array $copied_questions An array with the id's of the copied questions
* @access public
*/
	function pasteQuestionsForm($copied_questions)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_confirm_paste_questions.html", true);
		$questions_info =& $this->object->getQuestionsInfo($copied_questions);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($questions_info as $data)
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->setVariable("TXT_TITLE", $data->title);
			$this->tpl->setVariable("TXT_DESCRIPTION", $data->description);
			$this->tpl->setVariable("TXT_TYPE", $this->lng->txt($data->type_tag));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		foreach ($questions_info as $data)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$data->question_id");
			$this->tpl->setVariable("HIDDEN_VALUE", $data->question_id);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Displays a preview of a question
*
* Displays a preview of a question
*
* @param string $question_id The database id of the question
* @access public
*/
	function outPreviewForm($question_id)
	{
		$questiontype = $this->object->getQuestiontype($question_id);
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
		$question->object->loadFromDb($question_id);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_preview.html", true);
		$question->outPreviewForm();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("FORM_ACTION", $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}

/**
* Displays a form to edit/create a survey question
*
* Displays a form to edit/create a survey question
*
* @param string $questiontype The questiontype of the question
* @access public
*/
	function editQuestionForm($questiontype)
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_svy_qpl_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		if (!$questiontype)
		{
			$questiontype = $this->object->getQuestiontype($_GET["edit"]);
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
		$limit_error = 0;
		if ($_GET["edit"] > 0)
		{
			$question->object->loadFromDb($_GET["edit"]);
		}
		if ($_POST["cmd"]["cancel_delete"] or $_POST["cmd"]["confirm_delete"] or $_POST["cmd"]["confirm_savephrase"] or $_POST["cmd"]["select_phrase"] or $_POST["cmd"]["cancel_savephrase"] or $_POST["cmd"]["cancel_standard_numbers"] or $_POST["cmd"]["add_standard_numbers"] or $_POST["cmd"]["cancel_viewphrase"])
		{ 
			$question->object->loadFromDb($_POST["id"]);
			if ($_POST["cmd"]["add_standard_numbers"])
			{
				if ((strcmp($_POST["lower_limit"], "") == 0) or (strcmp($_POST["upper_limit"], "") == 0))
				{
					sendInfo($this->lng->txt("missing_upper_or_lower_limit"));
					$limit_error = 1;
				}
				else if ((int)$_POST["upper_limit"] <= (int)$_POST["lower_limit"])
				{
					sendInfo($this->lng->txt("upper_limit_must_be_greater"));
					$limit_error = 1;
				}
				else
				{
					$question->object->addStandardNumbers($_POST["lower_limit"], $_POST["upper_limit"]);
				}
			}
			if ($_POST["cmd"]["select_phrase"])
			{
				if (strcmp($this->object->getPhrase($_POST["phrases"]), "dp_standard_numbers") != 0)
				{
					$question->object->addPhrase($_POST["phrases"]);
				}
			}
		}
		$question->object->setObjId($this->object->getId());

		if ($_POST["cmd"]["delete"])
		{
			if ($question->canRemoveCategories())
			{
				sendInfo($this->lng->txt("category_delete_confirm"));
			}
			else
			{
				sendInfo($this->lng->txt("category_delete_select_none"));
				$_POST["cmd"]["delete"] = "";
			}
		}
		
    if (strlen($_POST["cmd"]["cancel"]) > 0) {
      // Cancel
      $this->cancelAction();
      exit();
    }

    $question_type = $question->getQuestionType();
    if ((!$_GET["edit"]) and (!$_POST["cmd"]["create"]) and (!$_POST["cmd"]["confirm_delete"]) and (!$_POST["cmd"]["cancel_delete"]) and (!$_POST["cmd"]["cancel_savephrase"]) and (!$_POST["cmd"]["cancel_viewphrase"]) and (!$_POST["cmd"]["select_phrase"]) and (!$_POST["cmd"]["confirm_savephrase"]) and (!$_POST["cmd"]["add_standard_numbers"]) and (!$_POST["cmd"]["cancel_standard_numbers"])) {
      $missing_required_fields = $question->writePostData();
    }

		// catch feedback message
		sendInfo();

		$this->setLocator("", "", "", $question->object->getTitle());

		if ($_POST["cmd"]["confirm_delete"]) 
		{
			$question->removeCategories();
		}
		
		if ($_POST["cmd"]["confirm_savephrase"]) 
		{
			if (!$_POST["phrase_title"])
			{
				$savephrase_error = 1;
				sendInfo($this->lng->txt("qpl_savephrase_empty"));
			}
			if ((!$savephrase_error) and ($question->object->phraseExists($_POST["phrase_title"])))
			{
				$savephrase_error = 1;
				sendInfo($this->lng->txt("qpl_savephrase_exists"));
			}
			if (!$savephrase_error)
			{
				$question->saveNewPhrase();
			}
		}
		
    if (strlen($_POST["cmd"]["save"]) > 0) {
      // Save and continue editing
      if (!$missing_required_fields) {
        $question->object->saveToDb();
				if ($_SESSION["survey_id"])
				{
					$this->cancelAction($question->object->getId());
					exit;
				}
				sendInfo($this->lng->txt("msg_obj_modified"));
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
      }
    }
		
    if ($question->object->getId() > 0) {
      $title = $this->lng->txt("edit") . " " . $this->lng->txt($question_type);
    } else {
      $title = $this->lng->txt("create_new") . " " . $this->lng->txt($question_type);
    }
		$this->tpl->setVariable("HEADER", $title);

		if ($_POST["cmd"]["delete"])
		{
      if (!$missing_required_fields) {
        $question->object->saveToDb();
				$question->showDeleteCategoryForm();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
				$question->showEditForm();
      }
		}
		else if ($_POST["cmd"]["add_phrase"])
		{
      if (!$missing_required_fields) {
        $question->object->saveToDb();
				$question->showAddPhraseForm();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
				$question->showEditForm();
      }
		}
		else if (($_POST["cmd"]["select_phrase"]) or ($limit_error == 1))
		{
			if ((strcmp($this->object->getPhrase($_POST["phrases"]), "dp_standard_numbers") == 0) or ($limit_error == 1))
			{
				$question->showStandardNumbersForm();
				return;
			}
			else
			{
				$question->showEditForm();
			}
		}
		else if (($_POST["cmd"]["save_phrase"]) or ($savephrase_error))
		{
			$checked_categories = 0;
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/chb_category_(\d+)/", $key))
				{
					$checked_categories++;
				}
			}
			if ($checked_categories < 2)
			{
        sendInfo($this->lng->txt("save_phrase_categories_not_checked"));
				$question->showEditForm();
				return;
			}
      if (!$missing_required_fields) {
        if (!$savephrase_error)
				{
					$question->object->saveToDb();
				}
				$question->showSavePhraseForm();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
				$question->showEditForm();
      }
		}
		else
		{
			$question->showEditForm();
		}
	}
	
/**
* Displays the definition form for a question block
*
* Displays the definition form for a question block
*
* @access public
*/
	function defineQuestionblock()
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_define_questionblock.html", true);
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/cb_(\d+)/", $key, $matches))
			{
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("HIDDEN_NAME", "cb_$matches[1]");
				$this->tpl->setVariable("HIDDEN_VALUE", $matches[1]);
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setCurrentBlock("obligatory");
		$this->tpl->setVariable("TEXT_OBLIGATORY", $this->lng->txt("obligatory"));
		$this->tpl->setVariable("CHECKED_OBLIGATORY", " checked=\"checked\"");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("DEFINE_QUESTIONBLOCK_HEADING", $this->lng->txt("define_questionblock"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}


/**
* Creates a confirmation form to delete personal phases from the database
*
* Creates a confirmation form to delete personal phases from the database
*
* @param array $checked_phrases An array with the id's of the phrases checked for deletion
* @access public
*/
	function deletePhrasesForm($checked_phrases)
	{
		sendInfo();
		$ordinal = new SurveyOrdinalQuestion();
		$phrases =& $ordinal->getAvailablePhrases(1);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_confirm_delete_phrases.html", true);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($checked_phrases as $id)
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter++ % 2]);
			$this->tpl->setVariable("PHRASE_TITLE", $phrases[$id]["title"]);
			$categories =& $ordinal->getCategoriesForPhrase($id);
			$this->tpl->setVariable("PHRASE_CONTENT", join($categories, ", "));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "phrase_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "1");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_PHRASE_TITLE", $this->lng->txt("phrase"));
		$this->tpl->setVariable("TEXT_PHRASE_CONTENT", $this->lng->txt("categories"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Displays a form to manage the user created phrases
	*
	* @access	public
	*/
  function phrasesObject()
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			if ($_POST["cmd"]["delete_phrase"])
			{
				$phrases = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/phrase_(\d+)/", $key, $matches))
					{
						array_push($phrases, $matches[1]);
					}
				}
				if (count($phrases))
				{
					sendInfo($this->lng->txt("qpl_confirm_delete_phrases"));
					$this->deletePhrasesForm($phrases);
					return;
				}
				else
				{
					sendInfo($this->lng->txt("qpl_delete_phrase_select_none"));
				}
			}
			if ($_POST["cmd"]["confirm_delete"])
			{
				$phrases = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/phrase_(\d+)/", $key, $matches))
					{
						array_push($phrases, $matches[1]);
					}
				}
				$this->object->deletePhrases($phrases);
				sendInfo($this->lng->txt("qpl_phrases_deleted"));
			}
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_phrases.html", true);
			$ordinal = new SurveyOrdinalQuestion();
			$phrases =& $ordinal->getAvailablePhrases(1);
			if (count($phrases))
			{
				$colors = array("tblrow1", "tblrow2");
				$counter = 0;
				foreach ($phrases as $phrase_id => $phrase_array)
				{
					$this->tpl->setCurrentBlock("phraserow");
					$this->tpl->setVariable("PHRASE_ID", $phrase_id);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter++ % 2]);
					$this->tpl->setVariable("PHRASE_TITLE", $phrase_array["title"]);
					$categories =& $ordinal->getCategoriesForPhrase($phrase_id);
					$this->tpl->setVariable("PHRASE_CONTENT", join($categories, ", "));
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("Footer");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				$this->tpl->setVariable("TEXT_DELETE", $this->lng->txt("delete"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("Emptytable");
				$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_user_phrases_defined"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("INTRODUCTION_MANAGE_PHRASES", $this->lng->txt("introduction_manage_phrases"));
			$this->tpl->setVariable("TEXT_PHRASE_TITLE", $this->lng->txt("phrase"));
			$this->tpl->setVariable("TEXT_PHRASE_CONTENT", $this->lng->txt("categories"));
			$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			sendInfo($this->lng->txt("cannot_manage_phrases"));
		}
	}
	
	/**
	* Displays the question browser
	* @access	public
	*/
  function questionsObject()
  {
    global $rbacsystem;

    if ($_GET["preview"]) {
      $this->outPreviewForm($_GET["preview"]);
      return;
    }

		if ($_GET["create"]) 
		{
			// create a new question from a survey
			$this->editQuestionForm($_GET["create"]);
			return;
		}
		
    $type = $_GET["sel_question_types"];
		if (!$type) {
			$type = $_POST["sel_question_types"];
		}
    if (($_POST["cmd"]["create"]) or ($_GET["sel_question_types"]) or ($_GET["edit"])) {
      $this->editQuestionForm($type);
      return;
    }

		// reset survey_id SESSION variable (only needed to create new questions from a question pool)
		$_SESSION["survey_id"] = "";
		
    $add_parameter = $this->getAddParameter();

    // create an array of all checked checkboxes
    $checked_questions = array();
    foreach ($_POST as $key => $value) {
      if (preg_match("/cb_(\d+)/", $key, $matches)) {
        array_push($checked_questions, $matches[1]);
      }
    }
    
/*    if (strlen($_POST["cmd"]["edit"]) > 0) {
      // edit button was pressed
      if (count($checked_questions) > 1) {
        sendInfo($this->lng->txt("qpl_edit_select_multiple"));
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_edit_select_none"));
      } else {
        if ($rbacsystem->checkAccess('edit', $this->ref_id)) {
          header("location:" . $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=question" . "&edit=" . $checked_questions[0]);
          exit();
        } else {
          sendInfo($this->lng->txt("qpl_edit_rbac_error"));
        }
      }
    }
*/    
		if ($_POST["cmd"]["questionblock"])
		{
			$questionblock = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/cb_(\d+)/", $key, $matches))
				{
					array_push($questionblock, $value);
				}
			}
			if (count($questionblock) < 2)
			{
        sendInfo($this->lng->txt("qpl_define_questionblock_select_missing"));
			}
			else
			{
				$this->defineQuestionblock();
				return;
			}
		}
		
		if ($_POST["cmd"]["save_questionblock"])
		{
			if ($_POST["title"])
			{
				$questionblock = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/cb_(\d+)/", $key, $matches))
					{
						array_push($questionblock, $value);
					}
				}
//				$this->object->createQuestionblock($_POST["title"], $_POST["obligatory"], $questionblock);
			}
		}

		if ($_POST["cmd"]["unfold"])
		{
			$unfoldblocks = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/cb_qb_(\d+)/", $key, $matches))
				{
					array_push($unfoldblocks, $matches[1]);
				}
			}
			if (count($unfoldblocks))
			{
//				$this->object->unfoldQuestionblocks($unfoldblocks);
			}
			else
			{
        sendInfo($this->lng->txt("qpl_unfold_select_none"));
			}
		}
		
    if (strlen($_POST["cmd"]["delete"]) > 0) {
      // delete button was pressed
      if (count($checked_questions) > 0) {
        if ($rbacsystem->checkAccess('edit', $this->ref_id)) {
					sendInfo($this->lng->txt("qpl_confirm_delete_questions"));
					$this->deleteQuestionsForm($checked_questions);
					return;
				} else {
          sendInfo($this->lng->txt("qpl_delete_rbac_error"));
        }
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_delete_select_none"));
      }
    }
    
		if (strlen($_POST["cmd"]["confirm_delete"]) > 0)
		{
			// delete questions after confirmation
			sendInfo($this->lng->txt("qpl_questions_deleted"));
			$checked_questions = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/id_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
			}
      foreach ($checked_questions as $key => $value) {
        $this->object->removeQuestion($value);
      }
		}
		
		if (strlen($_POST["cmd"]["duplicate"]) > 0) {
      // duplicate button was pressed
      if (count($checked_questions) > 0) {
        foreach ($checked_questions as $key => $value) {
					$this->object->duplicateQuestion($value);
        }
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_duplicate_select_none"));
      }
    }
  
		if (strlen($_POST["cmd"]["copy"]) > 0) {
      // copy button was pressed
      if (count($checked_questions) > 0) {
				$_SESSION["spl_copied_questions"] = join($checked_questions, ",");
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_copy_select_none"));
				$_SESSION["spl_copied_questions"] = "";
      }
    }
  
		if (strlen($_POST["cmd"]["paste"]) > 0) {
      // paste button was pressed
			if (strcmp($_SESSION["spl_copied_questions"], "") != 0)
			{
				$copied_questions = split("/,/", $_SESSION["spl_copied_questions"]);
				sendInfo($this->lng->txt("qpl_past_questions_confirmation"));
				$this->pasteQuestionsForm($copied_questions);
				return;
			}
    }
  
		if (strlen($_POST["cmd"]["confirm_paste"]) > 0)
		{
			// paste questions after confirmation
			sendInfo($this->lng->txt("qpl_questions_pasted"));
			$checked_questions = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/id_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
			}
      foreach ($checked_questions as $key => $value) {
        $this->object->paste($value);
      }
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_questions.html", true);
	  if ($rbacsystem->checkAccess('write', $this->ref_id)) {
  	  $this->tpl->addBlockFile("CREATE_QUESTION", "create_question", "tpl.il_svy_qpl_create_new_question.html", true);
	    $this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_svy_qpl_action_buttons.html", true);
		}
    $this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_svy_qpl_filter_questions.html", true);

    // create filter form
    $filter_fields = array(
      "title" => $this->lng->txt("title"),
      "description" => $this->lng->txt("description"),
      "author" => $this->lng->txt("author"),
    );
    $this->tpl->setCurrentBlock("filterrow");
    foreach ($filter_fields as $key => $value) {
      $this->tpl->setVariable("VALUE_FILTER_TYPE", "$key");
      $this->tpl->setVariable("NAME_FILTER_TYPE", "$value");
      if (!$_POST["cmd"]["reset"]) {
        if (strcmp($_POST["sel_filter_type"], $key) == 0) {
          $this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
        }
      }
      $this->tpl->parseCurrentBlock();
    }
    
    $this->tpl->setCurrentBlock("filter_questions");
    $this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
    $this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
    if (!$_POST["cmd"]["reset"]) {
      $this->tpl->setVariable("VALUE_FILTER_TEXT", $_POST["filter_text"]);
    }
    $this->tpl->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
    $this->tpl->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
    $this->tpl->parseCurrentBlock();
    
  // create edit buttons & table footer
  if ($rbacsystem->checkAccess('write', $this->ref_id)) {
      $this->tpl->setCurrentBlock("standard");
      $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
      $this->tpl->setVariable("DUPLICATE", $this->lng->txt("duplicate"));
      $this->tpl->setVariable("COPY", $this->lng->txt("copy"));
      $this->tpl->setVariable("PASTE", $this->lng->txt("paste"));
			if (strcmp($_SESSION["spl_copied_questions"], "") == 0)
			{
	      $this->tpl->setVariable("PASTE_DISABLED", " disabled=\"disabled\"");
			}
      $this->tpl->setVariable("QUESTIONBLOCK", $this->lng->txt("define_questionblock"));
      $this->tpl->setVariable("UNFOLD", $this->lng->txt("unfold"));
      $this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("Footer");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
			$this->tpl->parseCurrentBlock();
	}    
    
		if ($_POST["cmd"]["reset"])
		{
			$_POST["filter_text"] = "";
		}
		$startrow = 0;
		if ($_GET["prevrow"])
		{
			$startrow = $_GET["prevrow"];
		}		
		if ($_GET["nextrow"])
		{
			$startrow = $_GET["nextrow"];
		}
		if ($_GET["startrow"])
		{
			$startrow = $_GET["startrow"];
		}
		if (!$_GET["sort"])
		{
			// default sort order
			$_GET["sort"] = array("title" => "ASC");
		}
		$table = $this->object->getQuestionsTable($_GET["sort"], $_POST["filter_text"], $_POST["sel_filter_type"], $startrow);
    $colors = array("tblrow1", "tblrow2");
    $counter = 0;
		$last_questionblock_id = 0;
		$editable = $rbacsystem->checkAccess('write', $this->ref_id);
		foreach ($table["rows"] as $data)
		{
			$this->tpl->setCurrentBlock("checkable");
			$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
			$this->tpl->parseCurrentBlock();
			if ($data["complete"] == 0)
			{
				$this->tpl->setCurrentBlock("qpl_warning");
				$this->tpl->setVariable("IMAGE_WARNING", ilUtil::getImagePath("warning.png"));
				$this->tpl->setVariable("ALT_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->setVariable("TITLE_WARNING", $this->lng->txt("warning_question_not_complete"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("QTab");
			if ($editable) {
				$this->tpl->setVariable("EDIT", "[<a href=\"" . $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=questions&edit=" . $data["question_id"] . "\">" . $this->lng->txt("edit") . "</a>]");
			}
			$this->tpl->setVariable("QUESTION_TITLE", "<strong>" . $data["title"] . "</strong>");
			//$this->lng->txt("preview")
			$this->tpl->setVariable("PREVIEW", "[<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&preview=" . $data["question_id"] . "\">" . $this->lng->txt("preview") . "</a>]");
			$this->tpl->setVariable("QUESTION_DESCRIPTION", $data["description"]);
			$this->tpl->setVariable("QUESTION_PREVIEW", $this->lng->txt("preview"));
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
			$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
			$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["created"]), "date"));
			$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["TIMESTAMP"]), "date"));
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
			$this->tpl->parseCurrentBlock();
			$counter++;
    }
    
		if ($table["rowcount"] > count($table["rows"]))
		{
			$nextstep = $table["nextrow"] + $table["step"];
			if ($nextstep > $table["rowcount"])
			{
				$nextstep = $table["rowcount"];
			}
			$sort = "";
			if (is_array($_GET["sort"]))
			{
				$key = key($_GET["sort"]);
				$sort = "&sort[$key]=" . $_GET["sort"]["$key"];
			}
			$counter = 1;
			for ($i = 0; $i < $table["rowcount"] - 1; $i += $table["step"])
			{
				$this->tpl->setCurrentBlock("pages");
				if ($table["startrow"] == $i)
				{
					$this->tpl->setVariable("PAGE_NUMBER", "<strong>$counter</strong>");
				}
				else
				{
					$this->tpl->setVariable("PAGE_NUMBER", "<a href=\"" . $_SERVER['PHP_SELF'] . $add_parameter . "$sort&nextrow=$i" . "\">$counter</a>");
				}
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
			$this->tpl->setCurrentBlock("navigation_bottom");
			$this->tpl->setVariable("TEXT_ITEM", $this->lng->txt("item"));
			$this->tpl->setVariable("TEXT_ITEM_START", $table["startrow"] + 1);
			$this->tpl->setVariable("TEXT_ITEM_END", $table["startrow"] + $table["step"]);
			$this->tpl->setVariable("TEXT_OF", strtolower($this->lng->txt("of")));
			$this->tpl->setVariable("TEXT_ITEM_COUNT", $table["rowcount"]);
			$this->tpl->setVariable("TEXT_PREVIOUS", $this->lng->txt("previous"));
			$this->tpl->setVariable("TEXT_NEXT", $this->lng->txt("next"));
			$this->tpl->setVariable("HREF_PREV_ROWS", $_SERVER['PHP_SELF'] . $add_parameter . "$sort&prevrow=" . $table["prevrow"]);
			$this->tpl->setVariable("HREF_NEXT_ROWS", $_SERVER['PHP_SELF'] . $add_parameter . "$sort&nextrow=" . $table["nextrow"]);
			$this->tpl->parseCurrentBlock();
		}

    // if there are no questions, display a message
    if ($counter == 0) {
      $this->tpl->setCurrentBlock("Emptytable");
      $this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
      $this->tpl->parseCurrentBlock();
    }
    
	  if ($rbacsystem->checkAccess('write', $this->ref_id)) {
			// "create question" form
			$this->tpl->setCurrentBlock("QTypes");
			$query = "SELECT * FROM survey_questiontype ORDER BY questiontype_id";
			$query_result = $this->ilias->db->query($query);
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->tpl->setVariable("QUESTION_TYPE_ID", $data->type_tag);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("CreateQuestion");
			$this->tpl->setVariable("QUESTION_ADD", $this->lng->txt("create"));
			$this->tpl->setVariable("ACTION_QUESTION_ADD", $_SERVER["PHP_SELF"] . $add_parameter);
			$this->tpl->parseCurrentBlock();
		}
    // define the sort column parameters
    $sort = array(
      "title" => $_GET["sort"]["title"],
      "description" => $_GET["sort"]["description"],
      "type" => $_GET["sort"]["type"],
      "author" => $_GET["sort"]["author"],
      "created" => $_GET["sort"]["created"],
      "updated" => $_GET["sort"]["updated"]
    );
    foreach ($sort as $key => $value) {
      if (strcmp($value, "ASC") == 0) {
        $sort[$key] = "DESC";
      } else {
        $sort[$key] = "ASC";
      }
    }
    
    $this->tpl->setCurrentBlock("adm_content");
    // create table header
    $this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[title]=" . $sort["title"] . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
    $this->tpl->setVariable("QUESTION_DESCRIPTION", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[description]=" . $sort["description"] . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["description"]);
    $this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>" . $table["images"]["type"]);
    $this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>" . $table["images"]["author"]);
    $this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
    $this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
    $this->tpl->setVariable("BUTTON_CANCEL", $this->lng->txt("cancel"));
    $this->tpl->setVariable("ACTION_QUESTION_FORM", $_SERVER["PHP_SELF"] . $add_parameter);
    $this->tpl->parseCurrentBlock();
  }

	function editMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			"questionpool.php?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
	}
	
		function saveMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save($_POST["meta_section"]);
		ilUtil::redirect("questionpool.php?ref_id=".$_GET["ref_id"]);
	}

	// called by administration
	function chooseMetaSectionObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "questionpool.php?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_REQUEST["meta_section"]);
	}

	// called by editor
	function chooseMetaSection()
	{
		$this->chooseMetaSectionObject("questionpool.php?ref_id=".
			$this->object->getRefId());
	}

	function addMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "questionpool.php?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_name = $_POST["meta_name"] ? $_POST["meta_name"] : $_GET["meta_name"];
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		if ($meta_index == "")
			$meta_index = 0;
		$meta_path = $_POST["meta_path"] ? $_POST["meta_path"] : $_GET["meta_path"];
		$meta_section = $_POST["meta_section"] ? $_POST["meta_section"] : $_GET["meta_section"];
		if ($meta_name != "")
		{
			$meta_gui->meta_obj->add($meta_name, $meta_path, $meta_index);
		}
		else
		{
			sendInfo($this->lng->txt("meta_choose_element"), true);
		}
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $meta_section);
	}

	function addMeta()
	{
		$this->addMetaObject("questionpool.php?ref_id=".
			$this->object->getRefId());
	}

	function deleteMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "questionpool.php?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		$meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $meta_index);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_GET["meta_section"]);
	}

	function deleteMeta()
	{
		$this->deleteMetaObject("questionpool.php?ref_id=".
			$this->object->getRefId());
	}
	
	function updateObject() {
		$this->update = $this->object->updateMetaData();
		sendInfo($this->lng->txt("msg_obj_modified"), true);
	}

	/**
	* show permissions of current node
	*
	* @access	public
	*/
	function permObject()
	{
		global $rbacsystem, $rbacreview;

		static $num = 0;

		if (!$rbacsystem->checkAccess("edit_permission", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
			exit();
		}

		// only display superordinate roles; local roles with other scope are not displayed
		$parentRoles = $rbacreview->getParentRoleIds($this->object->getRefId());

		$data = array();

		// GET ALL LOCAL ROLE IDS
		$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());

		$local_roles = array();

		if ($role_folder)
		{
			$local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);
		}

		foreach ($parentRoles as $key => $r)
		{
			if ($r["obj_id"] == SYSTEM_ROLE_ID)
			{
				unset($parentRoles[$key]);
				continue;
			}

			if (!in_array($r["obj_id"],$local_roles))
			{
				$data["check_inherit"][] = ilUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
			}
			else
			{
				$r["link"] = true;

				// don't display a checkbox for local roles AND system role
				if ($rbacreview->isAssignable($r["obj_id"],$role_folder["ref_id"]))
				{
					$data["check_inherit"][] = "&nbsp;";
				}
				else
				{
					// linked local roles with stopped inheritance
					$data["check_inherit"][] = ilUtil::formCheckBox(1,"stop_inherit[]",$r["obj_id"]);
				}
			}

			$data["roles"][] = $r;
		}

		$ope_list = getOperationList($this->object->getType());

		// BEGIN TABLE_DATA_OUTER
		foreach ($ope_list as $key => $operation)
		{
			$opdata = array();

			$opdata["name"] = $operation["operation"];

			$colspan = count($parentRoles) + 1;

			foreach ($parentRoles as $role)
			{
				$checked = $rbacsystem->checkPermission($this->object->getRefId(), $role["obj_id"],$operation["operation"],$_GET["parent"]);
				$disabled = false;

				// Es wird eine 2-dim Post Variable bergeben: perm[rol_id][ops_id]
				$box = ilUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"],$disabled);
				$opdata["values"][] = $box;
			}

			$data["permission"][] = $opdata;
		}

		/////////////////////
		// START DATA OUTPUT
		/////////////////////

		$this->getTemplateFile("perm");
		$this->tpl->setCurrentBlock("tableheader");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("permission_settings"));
		$this->tpl->setVariable("COLSPAN", $colspan);
		$this->tpl->setVariable("TXT_OPERATION", $this->lng->txt("operation"));
		$this->tpl->setVariable("TXT_ROLES", $this->lng->txt("roles"));
		$this->tpl->parseCurrentBlock();

		$num = 0;

		foreach($data["roles"] as $role)
		{
			// BLOCK ROLENAMES
			if ($role["link"])
			{
				$this->tpl->setCurrentBlock("ROLELINK_OPEN");
				$this->tpl->setVariable("LINK_ROLE_RULESET","questionpool.php?ref_id=".$role_folder["ref_id"]."&obj_id=".$role["obj_id"]."&cmd=perm");
				$this->tpl->setVariable("TXT_ROLE_RULESET",$this->lng->txt("edit_perm_ruleset"));
				$this->tpl->parseCurrentBlock();

				$this->tpl->touchBlock("ROLELINK_CLOSE");
			}

			$this->tpl->setCurrentBlock("ROLENAMES");
			$this->tpl->setVariable("ROLE_NAME",$role["title"]);
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			if ($this->objDefinition->stopInheritance($this->type))
			{
				$this->tpl->setCurrentBLock("CHECK_INHERIT");
				$this->tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$num]);
				$this->tpl->parseCurrentBlock();
			}

			$num++;
		}

		// save num for required column span and the end of parsing
		$colspan = $num + 1;
		$num = 0;

		// offer option 'stop inheritance' only to those objects where this option is permitted
		if ($this->objDefinition->stopInheritance($this->type))
		{
			$this->tpl->setCurrentBLock("STOP_INHERIT");
			$this->tpl->setVariable("TXT_STOP_INHERITANCE", $this->lng->txt("stop_inheritance"));
			$this->tpl->parseCurrentBlock();
		}

		foreach ($data["permission"] as $ar_perm)
		{
			foreach ($ar_perm["values"] as $box)
			{
				// BEGIN TABLE CHECK PERM
				$this->tpl->setCurrentBlock("CHECK_PERM");
				$this->tpl->setVariable("CHECK_PERMISSION",$box);
				$this->tpl->parseCurrentBlock();
				// END CHECK PERM
			}

			// BEGIN TABLE DATA OUTER
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW",$css_row);
			$this->tpl->setVariable("PERMISSION", $this->lng->txt($this->object->getType()."_".$ar_perm["name"]));
			$this->tpl->parseCurrentBlock();
			// END TABLE DATA OUTER
		}

		// ADD LOCAL ROLE - Skip that until I know how it works with the module folder
		if (false)
		// if ($this->object->getRefId() != ROLE_FOLDER_ID and $rbacsystem->checkAccess('create_role',$this->object->getRefId()))
		{
			$this->tpl->setCurrentBlock("LOCAL_ROLE");

			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
			}

			$this->tpl->setVariable("FORMACTION_LR",$this->getFormAction("addRole", "questionpool.php?ref_id=".$_GET["ref_id"]."&cmd=addRole"));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("role_add_local"));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("addRole"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->parseCurrentBlock();
		}

		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION",
		$this->getFormAction("permSave","questionpool.php?".$this->link_params."&cmd=permSave"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("COL_ANZ",$colspan);
		$this->tpl->parseCurrentBlock();
	}

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @param	scriptanme that is used for linking; if not set adm_object.php is used
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="repository.php", $question_title = "")
	{
		$ilias_locator = new ilLocatorGUI(false);
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		if (!($a_id))
		{
			$a_id = $_GET["ref_id"];
		}
		if (!($scriptname))
		{
			$scriptname = "repository.php";
		}
		$path = $a_tree->getPathFull($a_id);
		//check if object isn't in tree, this is the case if parent_parent is set
		// TODO: parent_parent no longer exist. need another marker
		if ($a_parent_parent)
		{
			//$subObj = getObject($a_ref_id);
			$subObj =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj->getTitle())
				);
		}

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (isset($_GET["obj_id"]))
		{
			$modifier = 0;
		}

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		if (!defined("ILIAS_MODULE")) {
			foreach ($path as $key => $row)
			{
				$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/adm_object.php?ref_id=".$row["child"],"target=\"bottom\"");
			}
		} else {
			foreach ($path as $key => $row)
			{
				if (strcmp($row["title"], "ILIAS") == 0) {
					$row["title"] = $this->lng->txt("repository");
				}
				if ($this->ref_id == $row["child"]) {
					$param = "&cmd=questions";
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/survey/questionpool.php" . "?ref_id=".$row["child"] . $param,"target=\"bottom\"");
					switch ($_GET["cmd"]) {
						case "questions":
							$id = $_GET["edit"];
							if (!$id) {
								$id = $_POST["id"];
							}
							if ($question_title) {
								if ($id > 0)
								{
									$ilias_locator->navigate($i++, $question_title, ILIAS_HTTP_PATH . "/survey/questionpool.php" . "?ref_id=".$row["child"] . "&cmd=questions&edit=$id","target=\"bottom\"");
								}
							}
							break;
					}
				} else {
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/" . $scriptname."?ref_id=".$row["child"],"target=\"bottom\"");
				}
			}
	
			if (isset($_GET["obj_id"]))
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
				$ilias_locator->navigate($i++,$obj_data->getTitle(),$scriptname."?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"],"target=\"bottom\"");
			}
		}
		$ilias_locator->output(true);
	}
	
} // END class.ilObjSurveyQuestionPoolGUI
?>
