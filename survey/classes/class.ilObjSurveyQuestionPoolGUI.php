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

require_once "class.SurveyNominalQuestionGUI.php";
require_once "class.SurveyTextQuestionGUI.php";
require_once "class.SurveyMetricQuestionGUI.php";
require_once "class.SurveyOrdinalQuestionGUI.php";

/**
* Class ilObjSurveyQuestionPoolGUI
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

require_once "classes/class.ilObjectGUI.php";
require_once "classes/class.ilMetaDataGUI.php";

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
		$this->setTabTargetScript("questionpool.php");
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
		
		header("Location:".$this->getReturnLocation("save","questionpool.php?".$this->link_params));
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
				$add_question = "&add=$question_id";
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
		if ($_GET["edit"])
		{
			$question->object->loadFromDb($_GET["edit"]);
		}
		if ($_POST["cmd"]["cancel_delete"] or $_POST["cmd"]["confirm_delete"] or $_POST["cmd"]["select_phrase"])
		{ 
			$question->object->loadFromDb($_POST["id"]);
			if ($_POST["cmd"]["select_phrase"])
			{
				$question->object->addPhrase($_POST["phrases"]);
			}
		}
		$question->object->setRefId($_GET["ref_id"]);

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
    if ((!$_GET["edit"]) and (!$_POST["cmd"]["create"]) and (!$_POST["cmd"]["confirm_delete"]) and (!$_POST["cmd"]["cancel_delete"]) and (!$_POST["cmd"]["select_phrase"])) {
      $missing_required_fields = $question->writePostData();
    }

		// catch feedback message
		sendInfo();

		$this->setLocator("", "", "", $question->object->getTitle());

		if ($_POST["cmd"]["confirm_delete"]) 
		{
			$question->removeCategories();
		}
		
    if (strlen($_POST["cmd"]["save"]) > 0) {
      // Save and back to question pool
      if (!$missing_required_fields) {
        $question->object->saveToDb();
	      $this->cancelAction($question->object->getId());
        exit();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
      }
    }
    if (strlen($_POST["cmd"]["apply"]) > 0) {
      // Save and continue editing
      if (!$missing_required_fields) {
        $question->object->saveToDb();
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
		else if ($_POST["cmd"]["view_phrase"])
		{
			$question->showAddPhraseForm();
		}
		else
		{
			$question->showEditForm();
		}
	}
	
	/**
	* Displays the question browser
	* @access	public
	*/
  function questionsObject()
  {
    global $rbacsystem;

/*    if ($_GET["preview"]) {
      $this->out_preview_page($_GET["preview"]);
      return;
    }
*/
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
  /*  if (strlen($_POST["cmd"]["duplicate"]) > 0) {
      // duplicate button was pressed
      if (count($checked_questions) > 0) {
        foreach ($checked_questions as $key => $value) {
          $question_gui =& new ASS_QuestionGUI();
          $question =& $question_gui->create_question("", $value);
          $question_gui->question->duplicate();
        }
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_duplicate_select_none"));
      }
    }
    
    if (strlen($_POST["cmd"]["export"]) > 0) {
      // export button was pressed
      if (count($checked_questions) > 0) {
				foreach ($checked_questions as $key => $value) {
					$question_gui =& new ASS_QuestionGUI();
					$question =& $question_gui->create_question("", $value);
					$xml .= $question_gui->question->to_xml();
				}
				if (count($checked_questions) > 1)
				{
					$xml = preg_replace("/<\/questestinterop>\s*<.xml.*?>\s*<questestinterop>/", "", $xml);

				}
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
				// force downloading of the xml file: use octet-stream instead of text/xml
				header ("Content-type: application/octet-stream");
				header ("Content-Disposition: attachment; filename=qti_export.xml" );
 				print $xml;
				exit();
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_export_select_none"));
      }
    }
*/
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_questions.html", true);
	  if ($rbacsystem->checkAccess('write', $this->ref_id)) {
  	  $this->tpl->addBlockFile("CREATE_QUESTION", "create_question", "tpl.il_svy_qpl_create_new_question.html", true);
	    $this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_svy_qpl_action_buttons.html", true);
		}
    $this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_svy_qpl_filter_questions.html", true);

    
/*    if (strlen($_POST["cmd"]["insert"]) > 0) {
      // insert button was pressed
      if (count($checked_questions) > 0) {
        foreach ($_POST as $key => $value) {
          if (preg_match("/cb_(\d+)/", $key, $matches)) {
            $this->insert_question_in_test($matches[1], $_GET["test"]);
          }
        }       
        header("location:il_as_test_composer.php?edit=" . $_GET["test"] . "&tab=questions");
      } elseif (count($checked_questions) == 0) {
        sendInfo("Please check at least one question to insert it into your test");
      }
    }
*/    
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
    
    if (!$_POST["cmd"]["reset"]) {
      if (strlen($_POST["filter_text"]) > 0) {
        switch($_POST["sel_filter_type"]) {
          case "title":
            $where = " AND survey_question.title LIKE " . $this->ilias->db->quote("%" . $_POST["filter_text"] . "%");
            break;
          case "description":
            $where = " AND survey_question.description LIKE " . $this->ilias->db->quote("%" . $_POST["filter_text"] . "%");
            break;
          case "author":
            $where = " AND survey_question.author LIKE " . $this->ilias->db->quote("%" . $_POST["filter_text"] . "%");
            break;
        }
      }
    }
  
  // create edit buttons & table footer
  if ($rbacsystem->checkAccess('write', $this->ref_id)) {
      $this->tpl->setCurrentBlock("standard");
      $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
      $this->tpl->setVariable("DUPLICATE", $this->lng->txt("duplicate"));
      $this->tpl->setVariable("QUESTIONBLOCK", $this->lng->txt("define_questionblock"));
      $this->tpl->setVariable("UNFOLD", $this->lng->txt("unfold"));
      $this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("Footer");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
			$this->tpl->parseCurrentBlock();
	}    
    
    // build sort order for sql query
    if (count($_GET["sort"])) {
      foreach ($_GET["sort"] as $key => $value) {
        switch($key) {
          case "title":
            $order = " ORDER BY title $value";
            $img_title = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "description":
            $order = " ORDER BY description $value";
            $img_description = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "type":
            $order = " ORDER BY questiontype_fi $value";
            $img_type = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "author":
            $order = " ORDER BY author $value";
            $img_author = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "created":
            $order = " ORDER BY created $value";
            $img_created = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "updated":
            $order = " ORDER BY TIMESTAMP $value";
            $img_updated = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
        }
      }
		}

    // display all questions in the question pool
    $query = "SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.ref_fi = " . $_GET["ref_id"] . " $where$order";
    $query_result = $this->ilias->db->query($query);
    $colors = array("tblrow1", "tblrow2");
    $counter = 0;
		$last_questionblock_id = 0;
		$editable = $rbacsystem->checkAccess('write', $this->ref_id);
    if ($query_result->numRows() > 0)
    {
      while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
      {
				$this->tpl->setCurrentBlock("checkable");
				$this->tpl->setVariable("QUESTION_ID", $data->question_id);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("QTab");
				if ($editable) {
					$this->tpl->setVariable("EDIT", "[<a href=\"" . $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=questions&edit=$data->question_id\">" . $this->lng->txt("edit") . "</a>]");
				}
				$this->tpl->setVariable("QUESTION_TITLE", "<strong>$data->title</strong>");
				//$this->lng->txt("preview")
				$this->tpl->setVariable("PREVIEW", "[<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&preview=$data->question_id\">" . $this->lng->txt("preview") . "</a>]");
				$this->tpl->setVariable("QUESTION_DESCRIPTION", $data->description);
				$this->tpl->setVariable("QUESTION_PREVIEW", $this->lng->txt("preview"));
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data->author);
				$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data->created), "date"));
				$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data->TIMESTAMP), "date"));
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->parseCurrentBlock();
				$counter++;
      }
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
    $this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[title]=" . $sort["title"] . "\">" . $this->lng->txt("title") . "</a>$img_title");
    $this->tpl->setVariable("QUESTION_DESCRIPTION", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[description]=" . $sort["description"] . "\">" . $this->lng->txt("description") . "</a>$img_description");
    $this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>$img_type");
    $this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>$img_author");
    $this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>$img_created");
    $this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>$img_updated");
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
				$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/adm_object.php?ref_id=".$row["child"],"bottom");
			}
		} else {
			foreach ($path as $key => $row)
			{
				if (strcmp($row["title"], "ILIAS") == 0) {
					$row["title"] = $this->lng->txt("repository");
				}
				if ($this->ref_id == $row["child"]) {
					$param = "&cmd=questions";
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/survey/questionpool.php" . "?ref_id=".$row["child"] . $param,"bottom");
					switch ($_GET["cmd"]) {
						case "questions":
							$id = $_GET["edit"];
							if (!$id) {
								$id = $_POST["id"];
							}
							if ($question_title) {
								$ilias_locator->navigate($i++, $question_title, ILIAS_HTTP_PATH . "/survey/questionpool.php" . "?ref_id=".$row["child"] . "&cmd=questions&edit=$id","bottom");
							}
							break;
					}
				} else {
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/" . $scriptname."?ref_id=".$row["child"],"bottom");
				}
			}
	
			if (isset($_GET["obj_id"]))
			{
				$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
				$ilias_locator->navigate($i++,$obj_data->getTitle(),$scriptname."?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"],"bottom");
			}
		}
		$ilias_locator->output(true);
	}
	
} // END class.ilObjSurveyQuestionPoolGUI
?>
