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
* Class ilObjQuestionPoolGUI
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

require_once "classes/class.ilObjectGUI.php";
require_once "assessment/classes/class.assQuestionGUI.php";
require_once "assessment/classes/class.ilObjQuestionPool.php";
require_once "classes/class.ilMetaDataGUI.php";

class ilObjQuestionPoolGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjQuestionPoolGUI($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
    global $lng;
		$this->type = "qpl";
    $lng->loadLanguageModule("assessment");
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

  function out_preview_page($question_id) {
    $question_gui =& new ASS_QuestionGUI();
    $question =& $question_gui->create_question("", $question_id);
    $question_gui->out_preview();
  }

/**
* Cancels actions editing this question
*
* Cancels actions editing this question
*
* @access private
*/
  function cancel_action($question_id = "") {
		if ($_SESSION["test_id"])
		{
			if ($question_id) {
				$add_question = "&add=$question_id";
			}
	    header("location:" . "test.php" . "?ref_id=" . $_SESSION["test_id"] . "&cmd=questions$add_question");
		} 
			else
		{
	    header("location:" . $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=questions");
		}
  }

/**
* Creates the create/edit template form of a question
*
* Creates the create/edit template form of a question and fills it with
* that data of the question.
*
* @access public
*/
  function set_question_form($type, $edit = "") {
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// catch feedback message
		sendInfo();
    $question_gui =& new ASS_QuestionGUI();
    $question =& $question_gui->create_question($type, $edit);
    if ($_POST["id"] > 0) {
      // First of all: Load question data from database
      $question->load_from_db($_POST["id"]);
			$edit = $_POST["id"];
    }

		$this->setLocator("", "", "", $question->title);
    $missing_required_fields = 0;

    if (strlen($_POST["cmd"]["cancel"]) > 0) {
      // Cancel
      $this->cancel_action();
      exit();
    }

    $question->set_ref_id($_GET["ref_id"]);
    $question_type = $question_gui->get_question_type($question);

    if ($question->id > 0) {
      $title = $this->lng->txt("edit") . " " . $this->lng->txt($question_type);
    } else {
      $title = $this->lng->txt("create_new") . " " . $this->lng->txt($question_type);
    }

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_question.html", true);

    if (!$_GET["edit"]) {
      $missing_required_fields = $question_gui->set_question_data_from_template($question_type);
    }
    if (strlen($_POST["cmd"]["save"]) > 0) {
      // Save and back to question pool
      if (!$missing_required_fields) {
        $question->save_to_db();
        $this->cancel_action($question->get_id());
        exit();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
      }
    }
    if (strlen($_POST["cmd"]["apply"]) > 0) {
      // Save and continue editing
      if (!$missing_required_fields) {
        $question->save_to_db();
      } else {
        sendInfo($this->lng->txt("fill_out_all_required_fields"));
      }
    }

    $question_gui->set_template_from_question_data($question_type);

    $this->tpl->setCurrentBlock("adm_content");
    $this->tpl->parseCurrentBlock();
  }

  function assessmentObject() 
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_qpl_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// catch feedback message
		sendInfo();

		$this->setLocator();

		$title = $this->lng->txt("qpl_assessment_of_questions");
		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

    $question_gui =& new ASS_QuestionGUI();
    $question =& $question_gui->create_question("", $_GET["edit"]);
		$total_of_answers = $this->object->get_total_answers($_GET["edit"]);		
		$counter = 0;
		$color_class = array("tblrow1", "tblrow2");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_qpl_assessment_of_questions.html", true);
		if (!$total_of_answers) {
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ASSESSMENT", $this->lng->txt("qpl_assessment_no_assessment_of_questions"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("qpl_assessment_total_of_answers"));
			$this->tpl->setVariable("TXT_VALUE", $total_of_answers);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("qpl_assessment_total_of_right_answers"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", $this->object->get_total_right_answers($_GET["edit"]) * 100.0) . " %");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
    $this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_QUESTION_TITLE", $question->get_title());
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
    $this->tpl->parseCurrentBlock();
  }
  
  function get_add_parameter() 
  {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }
  
	function questionObject() 
	{
    $type = $_GET["sel_question_types"];
    $this->set_question_form($type, $_GET["edit"]);
	}

  function questionsObject()
  {
    global $rbacsystem;
    $type = $_GET["sel_question_types"];
    if ($_GET["preview"]) {
      $this->out_preview_page($_GET["preview"]);
      return;
    }

		if ($_GET["create"]) 
		{
			// create a new question out of a test
			$this->set_question_form($_GET["create"]);
			return;
		}
		
    if ($_POST["cmd"]["create"]) {
      $this->set_question_form($_POST["sel_question_types"]);
      return;
    }

		// reset test_id SESSION variable
		$_SESSION["test_id"] = "";
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.qpl_questions.html", true);
    $this->tpl->addBlockFile("CREATE_QUESTION", "create_question", "tpl.il_as_create_new_question.html", true);
    $this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_as_qpl_action_buttons.html", true);
    $this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_as_qpl_filter_questions.html", true);

    $add_parameter = $this->get_add_parameter();

    // create an array of all checked checkboxes
    $checked_questions = array();
    foreach ($_POST as $key => $value) {
      if (preg_match("/cb_(\d+)/", $key, $matches)) {
        array_push($checked_questions, $matches[1]);
      }
    }
    
    if (strlen($_POST["cmd"]["edit"]) > 0) {
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
    
    if (strlen($_POST["cmd"]["delete"]) > 0) {
      // delete button was pressed
      if (count($checked_questions) > 0) {
        if ($rbacsystem->checkAccess('edit', $this->ref_id)) {
          foreach ($checked_questions as $key => $value) {
            $this->object->delete_question($value);
          }
        } else {
          sendInfo($this->lng->txt("qpl_delete_rbac_error"));
        }
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_delete_select_none"));
      }
    }
    
    if (strlen($_POST["cmd"]["duplicate"]) > 0) {
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
        // here comes the export routine call for qti export
      } elseif (count($checked_questions) == 0) {
        sendInfo($this->lng->txt("qpl_export_select_none"));
      }
    }
    
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
      "comment" => $this->lng->txt("description"),
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
            $where = " AND qpl_questions.title LIKE " . $this->ilias->db->db->quote("%" . $_POST["filter_text"] . "%");
            break;
          case "comment":
            $where = " AND qpl_questions.comment LIKE " . $this->ilias->db->db->quote("%" . $_POST["filter_text"] . "%");
            break;
          case "author":
            $where = " AND qpl_questions.author LIKE " . $this->ilias->db->db->quote("%" . $_POST["filter_text"] . "%");
            break;
        }
      }
    }
  
  // create edit buttons & table footer
  
//    if ($this->view_mode == VIEW_MODE_STANDARD) {
      $this->tpl->setCurrentBlock("standard");
//      $this->tpl->setVariable("EDIT", $this->lng->txt("edit"));
      $this->tpl->setVariable("DELETE", $this->lng->txt("delete"));
      $this->tpl->setVariable("DUPLICATE", $this->lng->txt("duplicate"));
      $this->tpl->setVariable("EXPORT", $this->lng->txt("export"));
      $this->tpl->parseCurrentBlock();
//    } elseif ($this->view_mode == VIEW_MODE_QUESTION_SELECTION) {
//      $this->tpl->setCurrentBlock("selection");
//      $this->tpl->setVariable("INSERT", "Insert");
//      $this->tpl->parseCurrentBlock();
//    }
    
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
          case "comment":
            $order = " ORDER BY comment $value";
            $img_comment = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
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
        }
      }
    }

    // display all questions in the question pool
    $query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND qpl_questions.ref_fi = " . $_GET["ref_id"] . " $where$order";
    $query_result = $this->ilias->db->query($query);
    $colors = array("tblrow1", "tblrow2");
    $counter = 0;
    if ($query_result->numRows() > 0)
    {
      while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
      {
        if (($data->private != 1) or ($data->owner == $this->ilias->account->id)) {
          $this->tpl->setVariable("QUESTION_ID", $data->question_id);
          if ($rbacsystem->checkAccess('edit', $this->ref_id)) {
            $this->tpl->setVariable("EDIT", "[<a href=\"" . $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=question&edit=$data->question_id\">" . $this->lng->txt("edit") . "</a>]");
          }
          $this->tpl->setVariable("QUESTION_TITLE", "<strong>$data->title</strong>");
          $this->tpl->setVariable("PREVIEW", "[<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&preview=$data->question_id\">" . $this->lng->txt("preview") . "</a>]");
          $this->tpl->setVariable("QUESTION_COMMENT", $data->comment);
          $this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
					$this->tpl->setVariable("QUESTION_ASSESSMENT", "<a href=\"" . $_SERVER["PHP_SELF"] . "?ref_id=" . $_GET["ref_id"] . "&cmd=assessment&edit=$data->question_id" . "\"><img src=\"" . ilUtil::getImagePath("assessment.gif", true) . "\" alt=\"" . $this->lng->txt("qpl_assessment_of_questions") . "\" title=\"" . $this->lng->txt("qpl_assessment_of_questions") . "\" boder=\"0\" /></a>");
          $this->tpl->setVariable("QUESTION_AUTHOR", $data->author);
          $this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data->created), "date"));
          $this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data->TIMESTAMP), "date"));
          $this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
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
    
    // "create question" form
    $this->tpl->setCurrentBlock("QTypes");
    $query = "SELECT * FROM qpl_question_type ORDER BY question_type_id";
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

    // define the sort column parameters
    $sort = array(
      "title" => $_GET["sort"]["title"],
      "comment" => $_GET["sort"]["comment"],
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
    $this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[comment]=" . $sort["comment"] . "\">" . $this->lng->txt("description") . "</a>$img_comment");
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
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @param	scriptanme that is used for linking; if not set adm_object.php is used
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="repository.php", $question_title = "")
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
		if (!($scriptname))
		{
			$scriptname = "repository.php";
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

		foreach ($path as $key => $row)
		{
			if (strcmp($row["title"], "ILIAS") == 0) {
				$row["title"] = $this->lng->txt("repository");
			}
			if ($this->ref_id == $row["child"]) {
				$param = "&cmd=questions";
				$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/assessment/questionpool.php" . "?ref_id=".$row["child"] . $param,"bottom");
				switch ($_GET["cmd"]) {
					case "question":
						$id = $_GET["edit"];
						if (!$id) {
							$id = $_POST["id"];
						}
						if ($question_title) {
							$ilias_locator->navigate($i++, $question_title, ILIAS_HTTP_PATH . "/assessment/questionpool.php" . "?ref_id=".$row["child"] . "&cmd=question&edit=$id","bottom");
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
    $ilias_locator->output(true);
	}


} // END class.ilObjQuestionPoolGUI
?>
