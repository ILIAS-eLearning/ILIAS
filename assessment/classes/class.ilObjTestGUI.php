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
* Class ilObjTestGUI
*
* @author Helmut Schottmüller <hschottm@tzi.de>
* $Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

require_once "classes/class.ilObjectGUI.php";
require_once "class.assQuestionGUI.php";

class ilObjTestGUI extends ilObjectGUI
{
	var $sequence;
	
	/**
	* Constructor
	* @access public
	*/
	function ilObjTestGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{
    global $lng;
	  $lng->loadLanguageModule("assessment");
		$this->type = "tst";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		$this->setTabTargetScript("test.php");
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
		
		header("Location:".$this->getReturnLocation("save","test.php?".$this->link_params));
		exit();
	}
	
	function updateObject() {
		$this->update = $this->object->update();
		$this->object->save_to_db();
		sendInfo($this->lng->txt("msg_obj_modified"),true);
	}
	
  function get_add_parameter() 
  {
    return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
  }  

  function propertiesObject()
  {
		if ($_POST["cmd"]["save"] or $_POST["cmd"]["apply"]) {
			// Check the values the user entered in the form
			$data["sel_test_types"] = ilUtil::stripSlashes($_POST["sel_test_types"]);
			$data["title"] = ilUtil::stripSlashes($_POST["title"]);
			$data["description"] = ilUtil::stripSlashes($_POST["description"]);
			$data["author"] = ilUtil::stripSlashes($_POST["author"]);
			$data["introduction"] = ilUtil::stripSlashes($_POST["introduction"]);
			$data["sequence_settings"] = ilUtil::stripSlashes($_POST["sequence_settings"]);
			$data["score_reporting"] = ilUtil::stripSlashes($_POST["score_reporting"]);
			$data["reporting_date"] = ilUtil::stripSlashes($_POST["reporting_date"]);
			$data["nr_of_tries"] = ilUtil::stripSlashes($_POST["nr_of_tries"]);
			$data["processing_time"] = ilUtil::stripSlashes($_POST["processing_time"]);
			$data["starting_time"] = ilUtil::stripSlashes($_POST["starting_time"]);
		} else {
			$data["sel_test_types"] = $this->object->get_test_type();
			$data["title"] = $this->object->getTitle();
			$data["description"] = $this->object->getDescription();
			$data["author"] = $this->object->get_author();
			$data["introduction"] = $this->object->get_introduction();
			$data["sequence_settings"] = $this->object->get_sequence_settings();
			$data["score_reporting"] = $this->object->get_score_reporting();
			$data["reporting_date"] = $this->object->get_reporting_date();
			$data["nr_of_tries"] = $this->object->get_nr_of_tries();
			$data["processing_time"] = $this->object->get_processing_time();
			$data["starting_time"] = $this->object->get_starting_time();
		}
		$this->object->set_test_type($data["sel_test_types"]);
		$this->object->setTitle($data["title"]);
		$this->object->setDescription($data["description"]);
		$this->object->set_author($data["author"]);
		$this->object->set_introduction($data["introduction"]);
		$this->object->set_sequence_settings($data["sequence_settings"]);
		$this->object->set_score_reporting($data["score_reporting"]);
		//$this->object->set_reportin g_date($data["reporting_date"]);
		$this->object->set_nr_of_tries($data["nr_of_tries"]);
		$this->object->set_processing_time($data["processing_time"]);
		$this->object->set_starting_time($data["starting_time"]);
    $add_parameter = $this->get_add_parameter();
    if ($_POST["cmd"]["save"]) {
			$this->updateObject();
      header("location: ". $this->getReturnLocation("cancel","/ilias3/repository.php?ref_id=15"));
			exit();
    }
    if ($_POST["cmd"]["apply"]) {
			$this->updateObject();
    }
    if ($_POST["cmd"]["cancel"]) {
      sendInfo($this->lng->txt("msg_cancel"),true);
      header("location: ". $this->getReturnLocation("cancel","/ilias3/repository.php?ref_id=15"));
      exit();
    }
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_properties.html", true);
		$this->tpl->setCurrentBlock("test_types");
		foreach ($this->object->test_types as $key => $value) {
			$this->tpl->setVariable("VALUE_TEST_TYPE", $key);
			$this->tpl->setVariable("TEXT_TEST_TYPE", $this->lng->txt($value));
			if ($data["sel_test_types"] == $key) {
				$this->tpl->setVariable("SELECTED_TEST_TYPE", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
    $this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_PROPERTIES", $_SERVER['PHP_SELF'] . $add_parameter);
		$this->tpl->setVariable("HEADING_GENERAL", $this->lng->txt("tst_general_properties"));
		$this->tpl->setVariable("TEXT_TEST_TYPES", $this->lng->txt("tst_types"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("VALUE_TITLE", $data["title"]);
		$this->tpl->setVariable("TEXT_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("VALUE_AUTHOR", $data["author"]);
		$this->tpl->setVariable("TEXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("VALUE_DESCRIPTION", $data["description"]);
		$this->tpl->setVariable("TEXT_INTRODUCTION", $this->lng->txt("tst_introduction"));
		$this->tpl->setVariable("VALUE_INTRODUCTION", $data["introduction"]);
		$this->tpl->setVariable("HEADING_SEQUENCE", $this->lng->txt("tst_sequence_properties"));
		$this->tpl->setVariable("TEXT_SEQUENCE", $this->lng->txt("tst_sequence"));
		$this->tpl->setVariable("SEQUENCE_FIXED", $this->lng->txt("tst_sequence_fixed"));
		$this->tpl->setVariable("SEQUENCE_POSTPONE", $this->lng->txt("tst_sequence_postpone"));
		if ($data["sequence_settings"] == 0) {
			$this->tpl->setVariable("SELECTED_FIXED", " selected=\"selected\"");
		} elseif ($data["sequence_settings"] == 1) {
			$this->tpl->setVariable("SELECTED_POSTPONE", " selected=\"selected\"");
		}
		$this->tpl->setVariable("HEADING_SCORE", $this->lng->txt("tst_score_reporting"));
		$this->tpl->setVariable("TEXT_SCORE_TYPE", $this->lng->txt("tst_score_type"));
		$this->tpl->setVariable("VALUE_SCORE_DATE", $data["reporting_date"]);
		$this->tpl->setVariable("REPORT_AFTER_QUESTION", $this->lng->txt("tst_report_after_question"));
		$this->tpl->setVariable("REPORT_AFTER_TEST", $this->lng->txt("tst_report_after_test"));
		if ($data["score_reporting"] == 0) {
			$this->tpl->setVariable("SELECTED_QUESTION", " selected=\"selected\"");
		} elseif ($data["score_reporting"] == 1) {
			$this->tpl->setVariable("SELECTED_TEST", " selected=\"selected\"");
		}
		$this->tpl->setVariable("TEXT_SCORE_DATE", $this->lng->txt("tst_score_reporting_date"));
		$this->tpl->setVariable("HEADING_SESSION", $this->lng->txt("tst_session_settings"));
		$this->tpl->setVariable("TEXT_NR_OF_TRIES", $this->lng->txt("tst_nr_of_tries"));
		$this->tpl->setVariable("VALUE_NR_OF_TRIES", $data["nr_of_tries"]);
		$this->tpl->setVariable("COMMENT_NR_OF_TRIES", $this->lng->txt("0_unlimited"));
		$this->tpl->setVariable("TEXT_PROCESSING_TIME", $this->lng->txt("tst_processing_time"));
		$this->tpl->setVariable("VALUE_PROCESSING_TIME", $data["processing_time"]);
		$this->tpl->setVariable("TEXT_STARTING_TIME", $this->lng->txt("tst_starting_time"));
		$this->tpl->setVariable("VALUE_STARTING_TIME", $data["starting_time"]);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
    $this->tpl->parseCurrentBlock();
  }
	
	function questionBrowser() {
    global $rbacsystem;
		
    $add_parameter = $this->get_add_parameter() . "&insert_question=1";

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questionbrowser.html", true);
    $this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_as_qpl_action_buttons.html", true);
    $this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_as_qpl_filter_questions.html", true);

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
					case "qpl":
						$order = " ORDER BY ref_fi $value";
            $img_qpl = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
        }
      }
    }

    // display all questions in accessable question pools
    $query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id" . " $where$order";
    $query_result = $this->ilias->db->query($query);
    $colors = array("tblrow1", "tblrow2");
    $counter = 0;
		$questionpools =& $this->object->get_qpl_titles();
    if ($query_result->numRows() > 0)
    {
			$existing_questions =& $this->object->get_existing_questions();
      while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
      {
        if (($rbacsystem->checkAccess("read", $data->ref_fi)) and (!in_array($data->question_id, $existing_questions))) {
          $this->tpl->setVariable("QUESTION_ID", $data->question_id);
          //if ($rbacsystem->checkAccess('edit', $this->ref_id)) {
          //  $this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&edit=$data->question_id\">$data->title</a>");
          //} else {
            $this->tpl->setVariable("QUESTION_TITLE", $data->title);
          //}
          $this->tpl->setVariable("PREVIEW", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&preview=$data->question_id\">" . $this->lng->txt("preview") . "</a>");
          $this->tpl->setVariable("QUESTION_COMMENT", $data->comment);
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
      "comment" => $_GET["sort"]["comment"],
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
    $this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[comment]=" . $sort["comment"] . "\">" . $this->lng->txt("description") . "</a>$img_comment");
    $this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>$img_type");
    $this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>$img_author");
    $this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>$img_created");
    $this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>$img_updated");
		$this->tpl->setVariable("QUESTION_POOL", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&sort[qpl]=" . $sort["qpl"] . "\">" . $this->lng->txt("obj_qpl") . "</a>$img_qpl");
    $this->tpl->setVariable("BUTTON_BACK", $this->lng->txt("back"));
    $this->tpl->setVariable("ACTION_QUESTION_FORM", $_SERVER["PHP_SELF"] . $add_parameter);
    $this->tpl->parseCurrentBlock();
	}

	function questionsObject() {
    $add_parameter = $this->get_add_parameter();

		if ($_GET["up"] > 0) {
			$this->object->question_move_up($_GET["up"]);
		}
		if ($_GET["down"] > 0) {
			$this->object->question_move_down($_GET["down"]);
		}
		if (($_POST["cmd"]["insert_question"]) or ($_GET["insert_question"])) {
			$show_questionbrowser = true;
			if ($_POST["cmd"]["insert"]) {
				// insert selected questions into test
				$selected_counter = 0;
				foreach ($_POST as $key => $value) {
					if (preg_match("/cb_(\d+)/", $key, $matches)) {
						$this->object->insert_question($matches[1]);
						$selected_counter++;
					}
				}
				if (!$selected_counter) {
					sendInfo($this->lng->txt("tst_insert_missing_question"));
				} else {
					$show_questionbrowser = false;
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
		if ($_POST["cmd"]["create_question"]) {
			//header("location:il_as_question_composer.php?sel_question_types=" . $_POST["sel_question_types"]);
		}
		if (strlen($_POST["cmd"]["remove"]) > 0) {
			$checked_questions = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/cb_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
			}
			if (count($checked_questions) > 0) {
				foreach ($checked_questions as $key => $value) {
					$this->object->remove_question($value);
				}
			} elseif (count($checked_questions) == 0) {
				sendInfo($this->lng->txt("tst_no_question_selected_for_removal"));
			}
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", true);
    $this->tpl->addBlockFile("A_BUTTONS", "question_buttons", "tpl.il_as_tst_question_buttons.html", true);

		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type, tst_test_question WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY sequence",
			$this->ilias->db->db->quote($this->object->get_test_id())
		);
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		if ($query_result->numRows() > 0)
		{
			while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->tpl->setCurrentBlock("QTab");
				$this->tpl->setVariable("QUESTION_ID", $data->question_id);
				if ($data->owner == $this->ilias->account->id) {
					$this->tpl->setVariable("QUESTION_TITLE", $data->title);
				} else {
					$this->tpl->setVariable("QUESTION_TITLE", $data->title);
				}
				$this->tpl->setVariable("QUESTION_SEQUENCE", $this->lng->txt("tst_sequence"));
				$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&up=$data->question_id\"><img src=\"" . ilUtil::getImagePath("up.gif", true) . "\" alt=\"Up\" border=\"0\" /></a>");
				$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&down=$data->question_id\"><img src=\"" . ilUtil::getImagePath("down.gif", true) . "\" alt=\"Down\" border=\"0\" /></a>");
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
		if ($counter == 0) {
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("tst_no_questions_available"));
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("QFooter");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
			$this->tpl->setVariable("REMOVE", $this->lng->txt("remove_question"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("QTypes");
		$query = "SELECT * FROM qpl_question_type";
		$query_result = $this->ilias->db->query($query);
		while ($data = $query_result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->tpl->setVariable("QUESTION_TYPE_ID", $data->type_tag);
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_CREATED", $this->lng->txt("tst_question_create_date"));
		$this->tpl->setVariable("QUESTION_UPDATED", $this->lng->txt("tst_question_last_update"));
		$this->tpl->setVariable("BUTTON_INSERT_QUESTION", $this->lng->txt("tst_browse_for_questions"));
		$this->tpl->setVariable("BUTTON_CREATE_QUESTION", $this->lng->txt("create"));
		$this->tpl->setVariable("TEXT_CREATE_NEW", $this->lng->txt("create_new"));
		$this->tpl->parseCurrentBlock();
	}
	
	function editMetaObject() {
	}
	
	function marksObject() {
    $add_parameter = $this->get_add_parameter();

		if ($_POST["cmd"]["new_simple"]) {
			$this->object->mark_schema->create_simple_schema("failed", "failed", 0, 0, "passed", "passed", 50, 1);
		} elseif (count($_POST)) {
			$this->object->mark_schema->flush();
			foreach ($_POST as $key => $value) {
				if (preg_match("/mark_short_(\d+)/", $key, $matches)) {
					$this->object->mark_schema->add_mark_step($_POST["mark_short_$matches[1]"], $_POST["mark_official_$matches[1]"], $_POST["mark_percentage_$matches[1]"], $_POST["cb_passed_$matches[1]"]);
				}
			}
			if ($_POST["cmd"]["new"]) {
				$this->object->mark_schema->add_mark_step();
			} elseif ($_POST["cmd"]["delete"]) {
				$delete_mark_steps = array();
				foreach ($_POST as $key => $value) {
					if (preg_match("/cb_(\d+)/", $key, $matches)) {
						array_push($delete_mark_steps, $matches[1]);
					}
				}
				if (count($delete_mark_steps)) {
					$this->object->mark_schema->delete_mark_steps($delete_mark_steps);
				} else {
					sendInfo($this->lng->txt("tst_delete_missing_mark"));
				}
			}
			$this->object->mark_schema->sort();
		}

		if ($_POST["cmd"]["save"]) {
			$this->object->mark_schema->save_to_db($this->object->get_test_id());
		}

		if ($_POST["cmd"]["apply"]) {
			$this->object->mark_schema->save_to_db($this->object->get_test_id());
		}

		if ($_POST["cmd"]["cancel"]) {
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_marks.html", true);
		$marks = $this->object->mark_schema->mark_steps;
		$rows = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($marks as $key => $value) {
			$this->tpl->setCurrentBlock("markrow");
			$this->tpl->setVariable("MARK_SHORT", $value->get_short_name());
			$this->tpl->setVariable("MARK_OFFICIAL", $value->get_official_name());
			$this->tpl->setVariable("MARK_PERCENTAGE", sprintf("%.2f", $value->get_minimum_level()));
			$this->tpl->setVariable("MARK_PASSED", strtolower($this->lng->txt("tst_mark_passed")));
			$this->tpl->setVariable("MARK_ID", "$key");
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			if ($value->get_passed()) {
				$this->tpl->setVariable("MARK_PASSED_CHECKED", " checked=\"checked\"");
			}
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($marks) == 0) {
			$this->tpl->setCurrentBlock("Emptyrow");
			$this->tpl->setVariable("EMPTY_ROW", $this->lng->txt("tst_no_marks_defined"));
			$this->tpl->setVariable("ROW_CLASS", $rows[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("Footer");
			$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
			$this->tpl->setVariable("BUTTON_EDIT", $this->lng->txt("edit"));
			$this->tpl->setVariable("BUTTON_DELETE", $this->lng->txt("delete"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_MARKS", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("HEADER_SHORT", $this->lng->txt("tst_mark_short_form"));
		$this->tpl->setVariable("HEADER_OFFICIAL", $this->lng->txt("tst_mark_official_form"));
		$this->tpl->setVariable("HEADER_PERCENTAGE", $this->lng->txt("tst_mark_minimum_level"));
		$this->tpl->setVariable("HEADER_PASSED", $this->lng->txt("tst_mark_passed"));
		$this->tpl->setVariable("BUTTON_NEW", $this->lng->txt("tst_mark_create_new_mark_step"));
		$this->tpl->setVariable("BUTTON_NEW_SIMPLE", $this->lng->txt("tst_mark_create_simple_mark_schema"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}
	
	function runObject() {
    $add_parameter = $this->get_add_parameter();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_tst_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();
		
		if ($_POST["cmd"]["next"] or $_POST["cmd"]["previous"] or $_POST["cmd"]["postpone"]) {
			// save question solution
			$question_gui = new ASS_QuestionGui();
			$question_gui->create_question("", $this->object->get_question_id_from_active_user_sequence($_GET["sequence"]));
		}

		$this->sequence = $_GET["sequence"];
		if ($_POST["cmd"]["next"]) {
			$this->sequence++;
		} elseif (($_POST["cmd"]["previous"]) and ($this->sequence != 0)) {
			$this->sequence--;
		}
		$this->setLocator();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		if (!$this->sequence) {
			// show introduction page
			$active = $this->object->get_active_test_user();
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_introduction.html", true);
			$this->tpl->setCurrentBlock("info_row");
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_type") . ":");
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($this->object->test_types[$this->object->get_test_type()]));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("description") . ":");
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->object->getDescription());
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_sequence") . ":");
			if ($this->object->get_sequence_settings() == TEST_FIXED_SEQUENCE) {
				$seq_setting = "tst_sequence_fixed";
			} else {
				$seq_setting = "tst_sequence_postpone";
			}
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($seq_setting));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_score_reporting") . ":");
			if ($this->object->get_score_reporting() == REPORT_AFTER_QUESTION) {
				$score_reporting = "tst_report_after_question";
			} else {
				$score_reporting = "tst_report_after_test";
			}
			$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($score_reporting));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_nr_of_tries") . ":");
			$num_of = $this->object->get_nr_of_tries();
			if (!$num_of) {
				$num_of = $this->lng->txt("unlimited");
			}
			$this->tpl->setVariable("TEXT_INFO_COL2", $num_of);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("info");
			$this->tpl->parseCurrentBlock();
			if ($active) {
				$this->tpl->setVariable("BTN_START", $this->lng->txt("tst_resume_test"));
			} else {
				$this->tpl->setVariable("BTN_START", $this->lng->txt("tst_start_test"));
			}
			$this->tpl->setCurrentBlock("adm_content");
			$introduction = $this->object->get_introduction();
			$introduction = preg_replace("/0n/i", "<br />", $introduction);
			$this->tpl->setVariable("TEXT_INTRODUCTION", $introduction);
			$seq = 1;
			if ($active) {
				$seq = $active->lastindex;
			}
			$this->tpl->setVariable("FORMACTION", $_SERVER['PHP_SELF'] . "$add_parameter&sequence=$seq");
			$this->tpl->parseCurrentBlock();
		} else {
			if ($this->sequence <= $this->object->get_question_count()) {
				// show next/previous question
				$postpone = "";
				if ($_POST["cmd"]["postpone"]) {
					$postpone = $this->sequence;
				}
				$this->object->set_active_test_user($this->sequence, $postpone);
				$question_gui = new ASS_QuestionGui();
				$question_gui->create_question("", $this->object->get_question_id_from_active_user_sequence($this->sequence));
				if ($this->sequence == $this->object->get_question_count()) {
					$finish = true;
				} else {
					$finish = false;
				}
				$postpone = false;
				if ($this->object->get_sequence_settings() == TEST_POSTPONE) {
					$postpone = true;
				}
				$active = $this->object->get_active_test_user();
				$question_gui->out_working_question($this->sequence, $finish, $this->object->get_test_id(), $active);
			} else {
				// finish test
				$this->object->set_active_test_user(1, "", true);
				$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish.html", true);
				$this->tpl->setCurrentBlock("adm_content");
				$this->tpl->setVariable("TEXT_FINISH", $this->lng->txt("tst_finished"));
				$this->tpl->parseCurrentBlock();
			}
		}
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
				$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/assessment/test.php" . "?ref_id=".$row["child"] . $param,"bottom");
				if ($this->sequence) {
					$ilias_locator->navigate($i++, $this->object->get_question_title($this->sequence), ILIAS_HTTP_PATH . "/assessment/test.php" . "?ref_id=".$row["child"] . $param . "&sequence=" . $this->sequence,"bottom");
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
    $ilias_locator->output();
	}
} // END class.ilObjTestGUI

?>
