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
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output);
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
		
		header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		exit();
	}

  function propertiesObject()
  {
    $add_parameter = $this->get_add_parameter();
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
		$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		$this->getTemplateFile("edit");

		foreach ($data["fields"] as $key => $val)
    {
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
      if ($this->prepare_output)
			{
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setVariable("FORMACTION", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("obj_qpl") . ": " . $this->object->getTitle());
		$this->tpl->setVariable("TITLE", $this->object->getTitle());
		$this->tpl->setVariable("DESC", $this->object->getDescription());
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
    $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
  }

  function out_preview_page($question_id) {
    $question_gui =& new ASS_QuestionGUI();
    $question =& $question_gui->create_question("", $question_id);
    $question_gui->out_preview();
  }

  function set_question_form($type, $edit = "") {
    $question_gui =& new ASS_QuestionGUI();
    $question =& $question_gui->create_question($type, $edit);
    $question_gui->set_edit_template();
  }
  
  function get_add_parameter() 
  {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }
  

  function questionsObject()
  {
    global $rbacsystem;
    $type = ($_POST["sel_question_types"]) ? $_POST["sel_question_types"] : $_GET["sel_question_types"];
    if ($_GET["preview"]) {
      $this->out_preview_page($_GET["preview"]);
      return;
    }
    if ($_GET["edit"]) {
      $this->set_question_form($type, $_GET["edit"]);
      return;
    }
    if (($_POST["cmd"]["create"]) and ($type)) {
      $this->set_question_form($type);
      return;
    }
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
          header("location:" . $_SERVER["PHP_SELF"] . $add_parameter . "&edit=" . $checked_questions[0]);
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
      $this->tpl->setVariable("EDIT", $this->lng->txt("edit"));
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
            $this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&edit=$data->question_id\">$data->title</a>");
          } else {
            $this->tpl->setVariable("QUESTION_TITLE", $data->title);
          }
          $this->tpl->setVariable("PREVIEW", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&preview=$data->question_id\">" . $this->lng->txt("preview") . "</a>");
          $this->tpl->setVariable("QUESTION_COMMENT", $data->comment);
          $this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
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
  }
 

} // END class.ilObjQuestionPoolGUI
?>
