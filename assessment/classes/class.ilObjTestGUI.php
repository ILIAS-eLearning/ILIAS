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

class ilObjTestGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjTestGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{
    global $lng;
	  $lng->loadLanguageModule("assessment");
		$this->type = "tst";
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
		$this->object->save_to_db();

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
		
		$this->object->set_test_type($data["test_type"]);
		$this->object->setTitle($data["title"]);
		$this->object->setDescription($data["description"]);
		$this->object->set_author($data["author"]);
		$this->object->set_introduction($data["introduction"]);
		$this->object->set_sequence_settings($data["sequence_settings"]);
		$this->object->set_score_reporting($data["score_reporting"]);
		//$this->object->set_reporting_date($data["reporting_date"]);
		$this->object->set_nr_of_tries($data["nr_of_tries"]);
		$this->object->set_processing_time($data["processing_time"]);
		$this->object->set_starting_time($data["starting_time"]);

    $add_parameter = $this->get_add_parameter();
    if ($_POST["cmd"]["save"]) {
			$this->updateObject();
      header("location: ". $this->getReturnLocation("cancel","/ilias3/repository.php?ref_id=15"));
			exit();
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
		$this->tpl->setVariable("TEXT_PROCESSING_TIME", $this->lng->txt("tst_processing_time"));
		$this->tpl->setVariable("VALUE_PROCESSING_TIME", $data["processing_time"]);
		$this->tpl->setVariable("TEXT_STARTING_TIME", $this->lng->txt("tst_starting_time"));
		$this->tpl->setVariable("VALUE_STARTING_TIME", $data["starting_time"]);
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("APPLY", $this->lng->txt("apply"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
    $this->tpl->parseCurrentBlock();
		return;
    $add_parameter = $this->get_add_parameter();
    if ($_POST["cmd"]["save"]) {
      $this->updateObject();
      return;
    }
    if ($_POST["cmd"]["cancel"]) {
      sendInfo($this->lng->txt("msg_cancel"),true);
      header("location: ". $this->getReturnLocation("cancel","adm_object.php?ref_id=".$this->ref_id));
      exit();
    }
    $data = array();
		$data["fields"] = array();
    if ($_SESSION["error_post_vars"]) {
      $data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
      $data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
    } else {
      $data["fields"]["title"] = $this->object->getTitle();
      $data["fields"]["desc"] = $this->object->getDescription();
    }
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
		$this->tpl->setVariable("TITLE", $data["fields"]["title"]);
		$this->tpl->setVariable("DESC", $data["fields"]["desc"]);
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
    $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
  }

	function questionsObject() {
	}
	
	function editMetaObject() {
	}
	
	function marksObject() {
    $add_parameter = $this->get_add_parameter();

		if ($_POST["cmd"]["new_simple"]) {
			$this->object->mark_schema->create_simple_schema("failed", "failed", 0, "passed", "passed", 50);
		} else {
			$this->object->mark_schema->flush();
			foreach ($_POST as $key => $value) {
				if (preg_match("/mark_short_(\d+)/", $key, $matches)) {
					$this->object->mark_schema->add_mark_step($_POST["mark_short_$matches[1]"], $_POST["mark_official_$matches[1]"], $_POST["mark_percentage_$matches[1]"]);
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
			$ilias_locator->navigate($i++,$row["title"], ILIAS_HTTP_PATH . "/" . $scriptname."?ref_id=".$row["child"],"bottom");
		}

		if (isset($_GET["obj_id"]))
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);
			$ilias_locator->navigate($i++,$obj_data->getTitle(),$scriptname."?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"],"bottom");
		}
    $ilias_locator->output(true);
	}
} // END class.ilObjTestGUI

?>
