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


/**
* Class ilObjSurveyGUI
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package survey
*/

require_once "classes/class.ilObjectGUI.php";
require_once "classes/class.ilMetaDataGUI.php";
require_once "classes/class.ilUtil.php";

class ilObjSurveyGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSurveyGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
    global $lng;
		$this->type = "svy";
		$lng->loadLanguageModule("survey");
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		$this->setTabTargetScript("survey.php");
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
		
		header("Location:".$this->getReturnLocation("save","survey.php?".$this->link_params));
		exit();
	}

	function updateObject() {
		$this->update = $this->object->update();
		$this->object->saveToDb();
		sendInfo($this->lng->txt("msg_obj_modified"),true);
	}

/**
* Returns the GET parameters for the survey object URLs
*
* Returns the GET parameters for the survey object URLs
*
* @access public
*/
  function getAddParameter() 
  {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }
	
	function writePropertiesFormData()
	{
		$this->object->setAuthor($_POST["author"]);
		$this->object->setStatus($_POST["status"]);
		$this->object->setEvaluationAccess($_POST["evaluation_access"]);
		$this->object->setStartDate(sprintf("%04d-%02d-%02d", $_POST["start_date"]["y"], $_POST["start_date"]["m"], $_POST["start_date"]["d"]));
		$this->object->setStartDateEnabled($_POST["checked_start_date"]);
		$this->object->setEndDate(sprintf("%04d-%02d-%02d", $_POST["end_date"]["y"], $_POST["end_date"]["m"], $_POST["end_date"]["d"]));
		$this->object->setEndDateEnabled($_POST["checked_end_date"]);
		$this->object->setIntroduction($_POST["introduction"]);
	}
	
/**
* Creates the properties form for the survey object
*
* Creates the properties form for the survey object
*
* @access public
*/
  function propertiesObject()
  {
		global $rbacsystem;
				
    $add_parameter = $this->getAddParameter();
		if ($_POST["cmd"]["save"] or $_POST["cmd"]["apply"])
		{
			$this->writePropertiesFormData();
		}
    if ($_POST["cmd"]["save"]) {
			$this->updateObject();
			$path = $this->tree->getPathFull($this->object->getRefID());
      header("location: ". $this->getReturnLocation("cancel","/ilias3/repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			exit();
    }
    if ($_POST["cmd"]["apply"]) {
			$this->updateObject();
    }
    if ($_POST["cmd"]["cancel"]) {
      sendInfo($this->lng->txt("msg_cancel"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
      header("location: ". $this->getReturnLocation("cancel","/ilias3/repository.php?ref_id=" . $path[count($path) - 2]["child"]));
      exit();
    }

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_properties.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $add_parameter);
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TITLE", $this->object->getTitle());
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("VALUE_AUTHOR", $this->object->getAuthor());
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("VALUE_DESCRIPTION", $this->object->getDescription());
		$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("introduction"));
		$this->tpl->setVariable("VALUE_INTRODUCTION", $this->object->getIntroduction());
		$this->tpl->setVariable("TEXT_STATUS", $this->lng->txt("status"));
		$this->tpl->setVariable("TEXT_START_DATE", $this->lng->txt("start_date"));
		$this->tpl->setVariable("VALUE_START_DATE", ilUtil::makeDateSelect("start_date", $this->object->getStartYear(), $this->object->getStartMonth(), $this->object->getStartDay()));
		$this->tpl->setVariable("TEXT_END_DATE", $this->lng->txt("end_date"));
		$this->tpl->setVariable("VALUE_END_DATE", ilUtil::makeDateSelect("end_date", $this->object->getEndYear(), $this->object->getEndMonth(), $this->object->getEndDay()));
		$this->tpl->setVariable("TEXT_EVALUATION_ACCESS", $this->lng->txt("evaluation_access"));
		$this->tpl->setVariable("VALUE_OFFLINE", $this->lng->txt("offline"));
		$this->tpl->setVariable("VALUE_ONLINE", $this->lng->txt("online"));
		$this->tpl->setVariable("TEXT_ENABLED", $this->lng->txt("enabled"));
		$this->tpl->setVariable("VALUE_OFF", $this->lng->txt("off"));
		$this->tpl->setVariable("VALUE_ON", $this->lng->txt("on"));
		if ($this->object->getEndDateEnabled())
		{
			$this->tpl->setVariable("CHECKED_END_DATE", " checked=\"checked\"");
		}
		if ($this->object->getStartDateEnabled())
		{
			$this->tpl->setVariable("CHECKED_START_DATE", " checked=\"checked\"");
		}
		
		if ($this->object->getEvaluationAccess() == EVALUATION_ACCESS_ON)
		{
			$this->tpl->setVariable("SELECTED_ON", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_OFF", " selected=\"selected\"");
		}
		if ($this->object->getStatus() == STATUS_ONLINE)
		{
			$this->tpl->setVariable("SELECTED_ONLINE", " selected=\"selected\"");
		}
		else
		{
			$this->tpl->setVariable("SELECTED_OFFLINE", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
    if ($rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
    $this->tpl->parseCurrentBlock();
  }
	
/**
* Creates the questionbrowser to select questions from question pools
*
* Creates the questionbrowser to select questions from question pools
*
* @access public
*/
	function questionBrowser() {
    global $rbacsystem;

    $add_parameter = $this->getAddParameter() . "&insert_question=1";

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questionbrowser.html", true);
    $this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_svy_qpl_action_buttons.html", true);
    $this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_svy_qpl_filter_questions.html", true);

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
            $where = " AND survey_questions.title LIKE " . $this->ilias->db->quote("%" . $_POST["filter_text"] . "%");
            break;
          case "description":
            $where = " AND survey_questions.description LIKE " . $this->ilias->db->quote("%" . $_POST["filter_text"] . "%");
            break;
          case "author":
            $where = " AND survey_questions.author LIKE " . $this->ilias->db->quote("%" . $_POST["filter_text"] . "%");
            break;
        }
      }
    }

  // create edit buttons & table footer
		$this->tpl->setCurrentBlock("selection");
		$this->tpl->setVariable("INSERT", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();

    $this->tpl->setCurrentBlock("Footer");
    $this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
    $this->tpl->parseCurrentBlock();

    $this->tpl->setCurrentBlock("QTab");

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
            $order = " ORDER BY question_type_id $value";
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
					case "qpl":
						$order = " ORDER BY ref_fi $value";
            $img_qpl = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
        }
      }
    }

    // display all questions in accessable question pools
    $query = "SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id" . " $where$order";
    $query_result = $this->ilias->db->query($query);
    $colors = array("tblrow1", "tblrow2");
    $counter = 0;
		$questionpools =& $this->object->getQuestionpoolTitles();
    if ($query_result->numRows() > 0)
    {
			$existing_questions =& $this->object->getExistingQuestions();
      while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
      {
        if (($rbacsystem->checkAccess("read", $data->ref_fi)) and (!in_array($data->question_id, $existing_questions))) {
					if ($data->complete) {
						// make only complete questions selectable
	          $this->tpl->setVariable("QUESTION_ID", $data->question_id);
					}
          $this->tpl->setVariable("QUESTION_TITLE", "<strong>$data->title</strong>");
          $this->tpl->setVariable("PREVIEW", "[<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&preview=$data->question_id\">" . $this->lng->txt("preview") . "</a>]");
          $this->tpl->setVariable("QUESTION_COMMENT", $data->description);
          $this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
          $this->tpl->setVariable("QUESTION_AUTHOR", $data->author);
          $this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data->created), "date"));
          $this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data->TIMESTAMP), "date"));
          $this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
          $this->tpl->setVariable("QUESTION_POOL", $questionpools[$data->ref_fi]);
          $this->tpl->parseCurrentBlock();
          $counter++;
        }
      }
    }

    // if there are no questions, display a message
    if ($counter == 0) {
      $this->tpl->setCurrentBlock("Emptytable");
      $this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
      $this->tpl->parseCurrentBlock();
    }

    // define the sort column parameters
    $sort = array(
      "title" => $_GET["sort"]["title"],
      "description" => $_GET["sort"]["description"],
      "type" => $_GET["sort"]["type"],
      "author" => $_GET["sort"]["author"],
      "created" => $_GET["sort"]["created"],
      "updated" => $_GET["sort"]["updated"],
			"qpl" => $_GET["sort"]["qpl"]
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
    $this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[description]=" . $sort["description"] . "\">" . $this->lng->txt("description") . "</a>$img_description");
    $this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>$img_type");
    $this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>$img_author");
    $this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>$img_created");
    $this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>$img_updated");
		$this->tpl->setVariable("QUESTION_POOL", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[qpl]=" . $sort["qpl"] . "\">" . $this->lng->txt("obj_qpl") . "</a>$img_qpl");
    $this->tpl->setVariable("BUTTON_BACK", $this->lng->txt("back"));
    $this->tpl->setVariable("FORM_ACTION", $_SERVER["PHP_SELF"] . $add_parameter);
    $this->tpl->parseCurrentBlock();
	}

/**
* Creates a confirmation form to insert questions into the survey
*
* Creates a confirmation form to insert questions into the survey
*
* @access public
*/
	function insertQuestionsForm($checked_questions)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_insert_questions.html", true);
		$where = "";
		foreach ($checked_questions as $id) {
			$where .= sprintf(" OR survey_question.question_id = %s", $this->ilias->db->quote($id));
		}
		$where = preg_replace("/^ OR /", "", $where);
		$where = "($where)";
    $query = "SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND $where";
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
					$this->tpl->setVariable("TEXT_TITLE", $data->title);
					$this->tpl->setVariable("TEXT_DESCRIPTION", $data->description);
					$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt($data->type_tag));
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
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}

/**
* Creates a confirmation form to remove questions from the survey
*
* Creates a confirmation form to remove questions from the survey
*
* @param array $checked_questions An array containing the id's of the questions to be removed
* @param array $checked_questionblocks An array containing the id's of the question blocks to be removed
* @access public
*/
	function removeQuestionsForm($checked_questions, $checked_questionblocks)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_remove_questions.html", true);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$surveyquestions =& $this->object->getSurveyQuestions();
		foreach ($surveyquestions as $question_id => $data)
		{
			if (in_array($data["question_id"], $checked_questions) or (in_array($data["questionblock_id"], $checked_questionblocks)))
			{
				$this->tpl->setCurrentBlock("row");
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("TEXT_TITLE", $data["title"]);
				$this->tpl->setVariable("TEXT_DESCRIPTION", $data["description"]);
				$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $data["questionblock_title"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}
		foreach ($checked_questions as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "$id");
			$this->tpl->parseCurrentBlock();
		}
		foreach ($checked_questionblocks as $id)
		{
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "id_qb_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "$id");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TEXT_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $this->lng->txt("questionblock"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
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
* Creates the questions form for the survey object
*
* Creates the questions form for the survey object
*
* @access public
*/
	function questionsObject() {
		global $rbacsystem;

    $add_parameter = $this->getAddParameter();

		if ($_POST["cmd"]["insert_before"] or $_POST["cmd"]["insert_after"])
		{
			// get all questions to move
			$move_questions = array();
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^move_(\d+)$/", $key, $matches))
				{
					array_push($move_questions, $value);
				}
			}
			// get insert point
			$insert_id = -1;
			foreach ($_POST as $key => $value)
			{
				if (preg_match("/^cb_(\d+)$/", $key, $matches))
				{
					if ($insert_id < 0)
					{
						$insert_id = $matches[1];
					}
				}
			}
			if ($insert_id <= 0)
			{
				sendInfo($this->lng->txt("no_target_selected_for_move"));
			}
			else
			{
				$insert_mode = 1;
				if ($_POST["cmd"]["insert_before"])
				{
					$insert_mode = 0;
				}
				$this->object->moveQuestions($move_questions, $insert_id, $insert_mode);
			}
		}
		
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
				$this->object->unfoldQuestionblocks($unfoldblocks);
			}
			else
			{
        sendInfo($this->lng->txt("qpl_unfold_select_none"));
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
				$this->object->createQuestionblock($_POST["title"], $questionblock);
			}
		}
		
/*		if ($_POST["cmd"]["create_question_execute"])
		{
			$_SESSION["test_id"] = $this->object->getRefId();
			header("Location:questionpool.php?ref_id=" . $_POST["sel_qpl"] . "&cmd=questions&create=" . $_POST["sel_question_types"]);
			exit();
		}

		if ($_GET["add"])
		{
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
			$total = $this->object->evalTotalPersons();
			if ($total) {
				// the test was executed previously
				sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			} else {
				sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->insertQuestions($selected_array);
			return;
		}
*/
		if (($_POST["cmd"]["insert_question"]) or ($_GET["insert_question"])) {
			$show_questionbrowser = true;
			if ($_POST["cmd"]["insert"]) {
				// insert selected questions into test
				$selected_array = array();
				foreach ($_POST as $key => $value) {
					if (preg_match("/cb_(\d+)/", $key, $matches)) {
						array_push($selected_array, $matches[1]);
					}
				}
				if (!count($selected_array)) {
					sendInfo($this->lng->txt("tst_insert_missing_question"));
				} else {
//					$total = $this->object->evalTotalPersons();
//					if ($total) {
						// the test was executed previously
//						sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
//					} else {
						sendInfo($this->lng->txt("ask_insert_questions"));
//					}
					$this->insertQuestionsForm($selected_array);
					return;
				}
			}
			if ($_POST["cmd"]["back"]) {
				$show_questionbrowser = false;
			}
			if ($show_questionbrowser) {
				$this->questionBrowser();
				return;
			}
		}
		/*
		if ($_POST["cmd"]["create_question"]) {
			//header("location:il_as_question_composer.php?sel_question_types=" . $_POST["sel_question_types"]);
		}
*/
		if (strlen($_POST["cmd"]["confirm_insert"]) > 0)
		{
			// insert questions from test after confirmation
			foreach ($_POST as $key => $value) {
				if (preg_match("/id_(\d+)/", $key, $matches)) {
					$this->object->insertQuestion($matches[1]);
				}
			}
			$this->object->saveCompletionStatus();
			sendInfo($this->lng->txt("questions_inserted"));
		}

		if (strlen($_POST["cmd"]["confirm_remove"]) > 0)
		{
			// remove questions from test after confirmation
			sendInfo($this->lng->txt("questions_removed"));
			$checked_questions = array();
			$checked_questionblocks = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/id_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
				if (preg_match("/id_qb_(\d+)/", $key, $matches)) {
					array_push($checked_questionblocks, $matches[1]);
				}
			}
			$this->object->removeQuestions($checked_questions, $checked_questionblocks);
			$this->object->saveCompletionStatus();
		}

		if (strlen($_POST["cmd"]["remove"]) > 0) {
			$checked_questions = array();
			$checked_questionblocks = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/cb_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
				if (preg_match("/cb_qb_(\d+)/", $key, $matches))
				{
					array_push($checked_questionblocks, $matches[1]);
				}
			}
			if (count($checked_questions) + count($checked_questionblocks) > 0) {
//				$total = $this->object->evalTotalPersons();
//				if ($total) {
					// the test was executed previously
//					sendInfo(sprintf($this->lng->txt("remove_questions_and_results"), $total));
//				} else {
					sendInfo($this->lng->txt("remove_questions"));
//				}
				$this->removeQuestionsForm($checked_questions, $checked_questionblocks);
				return;
			} else {
				sendInfo($this->lng->txt("no_question_selected_for_removal"));
			}
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_svy_questions.html", true);

		$survey_questions =& $this->object->getSurveyQuestions();
		$questionblock_titles =& $this->object->getQuestionblockTitles();
		$questionpools =& $this->object->getQuestionpoolTitles();
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		if (count($survey_questions) > 0)
		{
			foreach ($survey_questions as $question_id => $data)
			{
				if (($data["questionblock_id"] > 0) and ($data["questionblock_id"] != $last_questionblock_id))
				{
					$this->tpl->setCurrentBlock("block");
					$this->tpl->setVariable("TEXT_QUESTIONBLOCK", $this->lng->txt("questionblock") . ": " . $data["questionblock_title"]);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("QTab");
					$this->tpl->setVariable("QUESTION_ID", "qb_" . $data["questionblock_id"]);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				if (!$data["questionblock_id"])
				{
					$this->tpl->setCurrentBlock("checkable");
					$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
					$this->tpl->parseCurrentBlock();
					$counter++;
				}
				$this->tpl->setCurrentBlock("QTab");
				$this->tpl->setVariable("QUESTION_TITLE", $data["title"]);
				$this->tpl->setVariable("QUESTION_COMMENT", $data["description"]);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
				$this->tpl->setVariable("QUESTION_POOL", $questionpools[$data["ref_fi"]]);
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->parseCurrentBlock();
				$last_questionblock_id = $data["questionblock_id"];
			}
		}

		$checked_move = 0;
		if ($_POST["cmd"]["move"])
		{
			foreach ($_POST as $key => $value) 
			{
				if (preg_match("/cb_(\d+)/", $key, $matches))
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
				sendInfo($this->lng->txt("select_target_position_for_move_question"));
				$this->tpl->setCurrentBlock("move_buttons");
				$this->tpl->setVariable("INSERT_BEFORE", $this->lng->txt("insert_before"));
				$this->tpl->setVariable("INSERT_AFTER", $this->lng->txt("insert_after"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				sendInfo($this->lng->txt("no_question_selected_for_move"));
			}
		}
		

		if ($counter == 0) {
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		} else {
	    if ($rbacsystem->checkAccess('write', $this->ref_id)) {
				$this->tpl->setCurrentBlock("QFooter");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				$this->tpl->setVariable("REMOVE", $this->lng->txt("remove_question"));
				$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
				$this->tpl->setVariable("QUESTIONBLOCK", $this->lng->txt("define_questionblock"));
				$this->tpl->setVariable("UNFOLD", $this->lng->txt("unfold"));
				$this->tpl->parseCurrentBlock();
			}
		}

    if ($rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->tpl->setCurrentBlock("QTypes");
			$query = "SELECT * FROM survey_questiontype";
			$query_result = $this->ilias->db->query($query);
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->tpl->setVariable("QUESTION_TYPE_ID", $data->type_tag);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_POOL", $this->lng->txt("spl"));

    if ($rbacsystem->checkAccess('write', $this->ref_id)) {
			$this->tpl->setVariable("BUTTON_INSERT_QUESTION", $this->lng->txt("browse_for_questions"));
			$this->tpl->setVariable("TEXT_CREATE_NEW", " " . strtolower($this->lng->txt("or")) . " " . $this->lng->txt("create_new"));
			$this->tpl->setVariable("BUTTON_CREATE_QUESTION", $this->lng->txt("create"));
		}
		
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
	function setLocator($a_tree = "", $a_id = "", $scriptname="repository.php")
	{
//		global $ilias_locator;
	  $ilias_locator = new ilLocatorGUI(false);
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		if (!($a_id))
		{
			$a_id = $_GET["ref_id"];
		}

		//$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

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
					if ($_GET["cmd"]) {
						$param = "&cmd=" . $_GET["cmd"];
					} else {
						$param = "";
					}
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/survey/survey.php" . "?ref_id=".$row["child"] . $param,"bottom");
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
    $ilias_locator->output();
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
				$this->tpl->setVariable("LINK_ROLE_RULESET","../adm_object.php?ref_id=".$role_folder["ref_id"]."&obj_id=".$role["obj_id"]."&cmd=perm");
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

			$this->tpl->setVariable("FORMACTION_LR",$this->getFormAction("addRole", "../adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=addRole"));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("role_add_local"));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("addRole"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->parseCurrentBlock();
		}

		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION",
		$this->getFormAction("permSave","../adm_object.php?".$this->link_params."&cmd=permSave"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("COL_ANZ",$colspan);
		$this->tpl->parseCurrentBlock();
	}

	/**
	* save permissions
	*
	* @access	public
	*/
	function permSaveObject()
	{
		global $rbacsystem, $rbacreview, $rbacadmin;

		// first save the new permission settings for all roles
		$rbacadmin->revokePermission($this->ref_id);

		if (is_array($_POST["perm"]))
		{
			foreach ($_POST["perm"] as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms,$this->ref_id);
			}
		}

		// update object data entry (to update last modification date)
		$this->object->update();

		// get rolefolder data if a rolefolder already exists
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->ref_id);
		$rolf_id = $rolf_data["child"];

		if ($_POST["stop_inherit"])
		{
			// rolefolder doesn't exists, so create one
			if (empty($rolf_id))
			{
				// create a local role folder
				$rfoldObj = $this->object->createRoleFolder();

				// set rolf_id again from new rolefolder object
				$rolf_id = $rfoldObj->getRefId();
			}

			// CHECK ACCESS 'write' of role folder
			if (!$rbacsystem->checkAccess('write',$rolf_id))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
			}

			foreach ($_POST["stop_inherit"] as $stop_inherit)
			{
				$roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_id);

				// create role entries for roles with stopped inheritance
				if (!in_array($stop_inherit,$roles_of_folder))
				{
					$parentRoles = $rbacreview->getParentRoleIds($rolf_id);
					$rbacadmin->copyRolePermission($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_id,$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id,'n');
				}
			}// END FOREACH
		}// END STOP INHERIT
		elseif 	(!empty($rolf_id))
		{
			// TODO: this feature doesn't work at the moment
			// ok. if the rolefolder is not empty, delete the local roles
			//if (!empty($roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_data["ref_id"])));
			//{
				//foreach ($roles_of_folder as $obj_id)
				//{
					//$rolfObj =& $this->ilias->obj_factory->getInstanceByRefId($rolf_data["child"]);
					//$rolfObj->delete();
					//unset($rolfObj);
				//}
			//}
		}

		sendinfo($this->lng->txt("saved_successfully"),true);

		ilUtil::redirect($this->getReturnLocation("permSave","survey/survey.php?ref_id=".$_GET["ref_id"]."&cmd=perm"));

	}

	function editMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit("ADM_CONTENT", "adm_content",
			"survey.php?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
	}

		function saveMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save($_POST["meta_section"]);
		if (!strcmp($_POST["meta_section"], "General")) {
			//$this->updateObject();
		}
		ilUtil::redirect("survey.php?ref_id=".$_GET["ref_id"]);
	}

	// called by administration
	function chooseMetaSectionObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "survey.php?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_REQUEST["meta_section"]);
	}

	// called by editor
	function chooseMetaSection()
	{
		$this->chooseMetaSectionObject("survey.php?ref_id=".
			$this->object->getRefId());
	}

	function addMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "survey.php?ref_id=".$_GET["ref_id"];
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
		$this->addMetaObject("survey.php?ref_id=".
			$this->object->getRefId());
	}

	function deleteMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "survey.php?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		$meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $meta_index);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_GET["meta_section"]);
	}

	function deleteMeta()
	{
		$this->deleteMetaObject("survey.php?ref_id=".
			$this->object->getRefId());
	}

} // END class.ilObjSurveyGUI
?>
