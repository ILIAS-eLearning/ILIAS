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
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version		$Id$
*
* @extends ilObjectGUI
* @package ilias-core
* @package assessment
*/

require_once "./assessment/classes/class.ilObjQuestionPool.php";
require_once "./classes/class.ilObjectGUI.php";
require_once "./classes/class.ilMetaDataGUI.php";
require_once "./assessment/classes/class.assQuestionGUI.php";
require_once './classes/Spreadsheet/Excel/Writer.php';

define ("TYPE_XLS", "excel");
define ("TYPE_SPSS", "csv");

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
		if (!defined("ILIAS_MODULE"))
		{
			$this->setTabTargetScript("adm_object.php");
		}
		else
		{
			$this->setTabTargetScript("test.php");
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

		$returnlocation = "test.php";
		if (!defined("ILIAS_MODULE"))
		{
			$returnlocation = "adm_object.php";
		}
		header("Location:".$this->getReturnLocation("save","$returnlocation?".$this->link_params));
		exit();
	}

	function updateObject()
	{
		$this->update = $this->object->update();
		$this->object->saveToDb();
		sendInfo($this->lng->txt("msg_obj_modified"),true);
	}

	function getAddParameter()
	{
		return "?ref_id=" . $_GET["ref_id"] . "&cmd=" . $_GET["cmd"];
	}

	function exportObject()
	{
		global $rbacsystem;
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			if ($_POST["cmd"]["export"])
			{
				ilUtil::deliverData($this->object->to_xml(), $this->object->getTitle() . ".xml");
			}
			$add_parameter = $this->getAddParameter();
			if (!defined("ILIAS_MODULE"))
			{
				define("ILIAS_MODULE", "assessment");
			}
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_export.html", true);
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("FORMACTION", $add_parameter);
			$this->tpl->setVariable("BUTTON_EXPORT", $this->lng->txt("export"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			sendInfo("cannot_export_test");
		}
	}
	
	function propertiesObject()
	{
		global $rbacsystem;
		$deleteuserdata = false;
		if ($_POST["cmd"]["save"])
		{
			// Check the values the user entered in the form
			$data["sel_test_types"] = ilUtil::stripSlashes($_POST["sel_test_types"]);
			if ($data["sel_test_types"] != $this->object->getTestType())
			{
				$deleteuserdata = true;
			}
			//$data["title"] = ilUtil::stripSlashes($_POST["title"]);
			//$data["description"] = ilUtil::stripSlashes($_POST["description"]);
			$data["author"] = ilUtil::stripSlashes($_POST["author"]);
			$data["introduction"] = ilUtil::stripSlashes($_POST["introduction"]);
			$data["sequence_settings"] = ilUtil::stripSlashes($_POST["sequence_settings"]);
			$data["score_reporting"] = ilUtil::stripSlashes($_POST["score_reporting"]);
			$data["nr_of_tries"] = ilUtil::stripSlashes($_POST["nr_of_tries"]);
			$data["processing_time"] = ilUtil::stripSlashes($_POST["processing_time"]);
			if (!$_POST["chb_starting_time"])
			{
				$data["starting_time"] = "";
			}
			else
			{
				$data["starting_time"] = sprintf("%04d%02d%02d%02d%02d%02d",
					$_POST["starting_date"]["y"],
					$_POST["starting_date"]["m"],
					$_POST["starting_date"]["d"],
					$_POST["starting_time"]["h"],
					$_POST["starting_time"]["m"],
					0
				);
			}
			if (!$_POST["chb_ending_time"])
			{
				$data["ending_time"] = "";
			}
			else
			{
				$data["ending_time"] = sprintf("%04d%02d%02d%02d%02d%02d",
					$_POST["ending_date"]["y"],
					$_POST["ending_date"]["m"],
					$_POST["ending_date"]["d"],
					$_POST["ending_time"]["h"],
					$_POST["ending_time"]["m"],
					0
				);
			}

			if ($_POST["chb_processing_time"])
			{
				$data["enable_processing_time"] = "1";
			}
			else
			{
				$data["enable_processing_time"] = "0";
			}

			if ($data["enable_processing_time"])
			{
				$data["processing_time"] = sprintf("%02d:%02d:%02d",
					$_POST["processing_time"]["h"],
					$_POST["processing_time"]["m"],
					$_POST["processing_time"]["s"]
				);
			}
			else
			{
				$proc_time = $this->object->getEstimatedWorkingTime();
				$data["processing_time"] = sprintf("%02d:%02d:%02d",
					$proc_time["h"],
					$proc_time["m"],
					$proc_time["s"]
				);
			}

			if (!$_POST["chb_reporting_date"])
			{
				$data["reporting_date"] = "";
			}
			else
			{
				$data["reporting_date"] = sprintf("%04d%02d%02d%02d%02d%02d",
					$_POST["reporting_date"]["y"],
					$_POST["reporting_date"]["m"],
					$_POST["reporting_date"]["d"],
					$_POST["reporting_time"]["h"],
					$_POST["reporting_time"]["m"],
					0
				);
			}

		}
		else
		{
			$data["sel_test_types"] = $this->object->getTestType();
			$data["author"] = $this->object->getAuthor();
			$data["introduction"] = $this->object->getIntroduction();
			$data["sequence_settings"] = $this->object->getSequenceSettings();
			$data["score_reporting"] = $this->object->getScoreReporting();
			$data["reporting_date"] = $this->object->getReportingDate();
			$data["nr_of_tries"] = $this->object->getNrOfTries();
			$data["enable_processing_time"] = $this->object->getEnableProcessingTime();
			$data["processing_time"] = $this->object->getProcessingTime();
			if ((int)substr($data["processing_time"], 0, 2) + (int)substr($data["processing_time"], 3, 2) + (int)substr($data["processing_time"], 6, 2) == 0)
			{
				$proc_time = $this->object->getEstimatedWorkingTime();
				$data["processing_time"] = sprintf("%02d:%02d:%02d",
					$proc_time["h"],
					$proc_time["m"],
					$proc_time["s"]
				);
			}
			$data["starting_time"] = $this->object->getStartingTime();
			$data["ending_time"] = $this->object->getEndingTime();
		}
		$data["title"] = $this->object->getTitle();
		$data["description"] = $this->object->getDescription();
		$this->object->setTestType($data["sel_test_types"]);
		$this->object->setTitle($data["title"]);
		$this->object->setDescription($data["description"]);
		$this->object->setAuthor($data["author"]);
		$this->object->setIntroduction($data["introduction"]);
		$this->object->setSequenceSettings($data["sequence_settings"]);
		$this->object->setScoreReporting($data["score_reporting"]);
		$this->object->setReportingDate($data["reporting_date"]);
		$this->object->setNrOfTries($data["nr_of_tries"]);
		$this->object->setStartingTime($data["starting_time"]);
		$this->object->setEndingTime($data["ending_time"]);
		$this->object->setProcessingTime($data["processing_time"]);
		$this->object->setEnableProcessingTime($data["enable_processing_time"]);
		$add_parameter = $this->getAddParameter();
		if ($_POST["cmd"]["save"])
		{
			$this->updateObject();
			if ($deleteuserdata)
			{
				$this->object->removeAllTestEditings();
				sendInfo($this->lng->txt("tst_type_changed"));
			}
			else
			{
				sendInfo($this->lng->txt("msg_obj_modified"));
			}
		}
		if ($_POST["cmd"]["cancel"])
		{
			sendInfo($this->lng->txt("msg_cancel"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			header("location: ". $this->getReturnLocation("cancel","../repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			exit();
		}

		if ($data["sel_test_types"] == TYPE_ASSESSMENT)
		{
			$this->tpl->setCurrentBlock("starting_time");
			$this->tpl->setVariable("TEXT_STARTING_TIME", $this->lng->txt("tst_starting_time"));
			if (!$data["starting_time"])
			{
				$date_input = ilUtil::makeDateSelect("starting_date");
				$time_input = ilUtil::makeTimeSelect("starting_time");
			}
			else
			{
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $data["starting_time"], $matches);
				$date_input = ilUtil::makeDateSelect("starting_date", $matches[1], sprintf("%d", $matches[2]), sprintf("%d", $matches[3]));
				$time_input = ilUtil::makeTimeSelect("starting_time", true, sprintf("%d", $matches[4]), sprintf("%d", $matches[5]), sprintf("%d", $matches[6]));
			}
			$this->tpl->setVariable("TXT_ENABLED", $this->lng->txt("enabled"));
			if ($data["starting_time"])
			{
				$this->tpl->setVariable("CHECKED_STARTING_TIME", " checked=\"checked\"");
			}
			$this->tpl->setVariable("INPUT_STARTING_TIME", $this->lng->txt("date") . ": " . $date_input . $this->lng->txt("time") . ": " . $time_input);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("ending_time");
			$this->tpl->setVariable("TEXT_ENDING_TIME", $this->lng->txt("tst_ending_time"));
			if (!$data["ending_time"])
			{
				$date_input = ilUtil::makeDateSelect("ending_date");
				$time_input = ilUtil::makeTimeSelect("ending_time");
			}
			else
			{
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $data["ending_time"], $matches);
				$date_input = ilUtil::makeDateSelect("ending_date", $matches[1], sprintf("%d", $matches[2]), sprintf("%d", $matches[3]));
				$time_input = ilUtil::makeTimeSelect("ending_time", true, sprintf("%d", $matches[4]), sprintf("%d", $matches[5]), sprintf("%d", $matches[6]));
			}
			$this->tpl->setVariable("TXT_ENABLED", $this->lng->txt("enabled"));
			if ($data["ending_time"])
			{
				$this->tpl->setVariable("CHECKED_ENDING_TIME", " checked=\"checked\"");
			}
			$this->tpl->setVariable("INPUT_ENDING_TIME", $this->lng->txt("date") . ": " . $date_input . $this->lng->txt("time") . ": " . $time_input);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("reporting_date");
			$this->tpl->setVariable("TEXT_SCORE_DATE", $this->lng->txt("tst_score_reporting_date"));
			if (!$data["reporting_date"])
			{
				$date_input = ilUtil::makeDateSelect("reporting_date");
				$time_input = ilUtil::makeTimeSelect("reporting_time");
			} else {
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $data["reporting_date"], $matches);
				$date_input = ilUtil::makeDateSelect("reporting_date", $matches[1], sprintf("%d", $matches[2]), sprintf("%d", $matches[3]));
				$time_input = ilUtil::makeTimeSelect("reporting_time", true, sprintf("%d", $matches[4]), sprintf("%d", $matches[5]), sprintf("%d", $matches[6]));
			}
			$this->tpl->setVariable("TXT_ENABLED", $this->lng->txt("enabled"));
			if ($data["reporting_date"]) {
				$this->tpl->setVariable("CHECKED_REPORTING_DATE", " checked=\"checked\"");
			}
			$this->tpl->setVariable("INPUT_REPORTING_DATE", $this->lng->txt("date") . ": " . $date_input . $this->lng->txt("time") . ": " . $time_input);
			$this->tpl->parseCurrentBlock();
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
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("SUBMIT_TYPE", $this->lng->txt("change"));
		}
		$this->tpl->setVariable("HEADING_GENERAL", $this->lng->txt("tst_general_properties"));
		$this->tpl->setVariable("TEXT_TEST_TYPES", $this->lng->txt("tst_types"));
		$this->tpl->setVariable("TEST_TYPE_COMMENT", $this->lng->txt("tst_type_comment"));
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
		$this->tpl->setVariable("REPORT_AFTER_QUESTION", $this->lng->txt("tst_report_after_question"));
		$this->tpl->setVariable("REPORT_AFTER_TEST", $this->lng->txt("tst_report_after_test"));
		if ($data["sel_test_types"] == TYPE_ASSESSMENT) {
			$this->tpl->setVariable("SELECTED_TEST", " selected=\"selected\"");
			$this->tpl->setVariable("DISABLE_SCORE_REPORTING", " disabled=\"disabled\"");
		} else {
			if ($data["score_reporting"] == 0) {
				$this->tpl->setVariable("SELECTED_QUESTION", " selected=\"selected\"");
			} elseif ($data["score_reporting"] == 1) {
				$this->tpl->setVariable("SELECTED_TEST", " selected=\"selected\"");
			}
		}
		$this->tpl->setVariable("HEADING_SESSION", $this->lng->txt("tst_session_settings"));
		$this->tpl->setVariable("TEXT_NR_OF_TRIES", $this->lng->txt("tst_nr_of_tries"));
		$this->tpl->setVariable("VALUE_NR_OF_TRIES", $data["nr_of_tries"]);
		$this->tpl->setVariable("COMMENT_NR_OF_TRIES", $this->lng->txt("0_unlimited"));
		$this->tpl->setVariable("TEXT_PROCESSING_TIME", $this->lng->txt("tst_processing_time"));
		$time_input = ilUtil::makeTimeSelect("processing_time", false, substr($data["processing_time"], 0, 2), substr($data["processing_time"], 3, 2), substr($data["processing_time"], 6, 2));
		$this->tpl->setVariable("MAX_PROCESSING_TIME", $time_input . " (hh:mm:ss)");
		if ($data["enable_processing_time"]) {
			$this->tpl->setVariable("CHECKED_PROCESSING_TIME", " checked=\"checked\"");
		}


		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* download file
	*/
	function downloadFileObject()
	{
		$file = explode("_", $_GET["file_id"]);
		require_once("classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}

	/**
	* download source code paragraph
	*/
	function download_paragraphObject()
	{
		require_once("content/classes/Pages/class.ilPageObject.php");
		$pg_obj =& new ilPageObject("qpl", $_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
		exit;
	}


	function questionBrowser()
	{
		global $rbacsystem;

		$add_parameter = $this->getAddParameter() . "&insert_question=1";

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questionbrowser.html", true);
		$this->tpl->addBlockFile("A_BUTTONS", "a_buttons", "tpl.il_as_qpl_action_buttons.html", true);
		$this->tpl->addBlockFile("FILTER_QUESTION_MANAGER", "filter_questions", "tpl.il_as_tst_filter_questions.html", true);

		$questionpools =& $this->object->get_qpl_titles();

		$filter_type = $_GET["sel_filter_type"];
		if (!$filter_type)
		{
			$filter_type = $_POST["sel_filter_type"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_type = "";
		}
		$add_parameter .= "&sel_filter_type=$filter_type";

		$filter_text = $_GET["filter_text"];
		if (!$filter_text)
		{
			$filter_text = $_POST["filter_text"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_text = "";
		}
		$add_parameter .= "&filter_text=$filter_text";

		$filter_fields = array(
			"title" => $this->lng->txt("title"),
			"comment" => $this->lng->txt("description"),
			"author" => $this->lng->txt("author"),
		);
		$this->tpl->setCurrentBlock("filterrow");
		foreach ($filter_fields as $key => $value) {
			$this->tpl->setVariable("VALUE_FILTER_TYPE", "$key");
			$this->tpl->setVariable("NAME_FILTER_TYPE", "$value");
			if (strcmp($_POST["cmd"]["resetFilter"], "") == 0) {
				if (strcmp($filter_type, $key) == 0) {
					$this->tpl->setVariable("VALUE_FILTER_SELECTED", " selected=\"selected\"");
				}
			}
			$this->tpl->parseCurrentBlock();
		}

		$filter_question_type = $_GET["sel_question_type"];
		if (!$filter_question_type)
		{
			$filter_question_type = $_POST["sel_question_type"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_question_type = "";
		}
		$add_parameter .= "&sel_question_type=$filter_question_type";

		$questiontypes =& $this->object->_getQuestiontypes();
		foreach ($questiontypes as $key => $value)
		{
			$this->tpl->setCurrentBlock("questiontype_row");
			$this->tpl->setVariable("VALUE_QUESTION_TYPE", $value);
			$this->tpl->setVariable("TEXT_QUESTION_TYPE", $this->lng->txt($value));
			if (strcmp($filter_question_type, $value) == 0)
			{
				$this->tpl->setVariable("SELECTED_QUESTION_TYPE", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$filter_questionpool = $_GET["sel_questionpool"];
		if (!$filter_questionpool)
		{
			$filter_questionpool = $_POST["sel_questionpool"];
		}
		if (strcmp($_POST["cmd"]["resetFilter"], "") != 0)
		{
			$filter_questionpool = "";
		}
		$add_parameter .= "&sel_questionpool=$filter_questionpool";
		
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("questionpool_row");
			$this->tpl->setVariable("VALUE_QUESTIONPOOL", $key);
			$this->tpl->setVariable("TEXT_QUESTIONPOOL", $value);
			if (strcmp($filter_questionpool, $key) == 0)
			{
				$this->tpl->setVariable("SELECTED_QUESTIONPOOL", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("filter_questions");
		$this->tpl->setVariable("SHOW_QUESTION_TYPES", $this->lng->txt("filter_show_question_types"));
		$this->tpl->setVariable("TEXT_ALL_QUESTION_TYPES", $this->lng->txt("filter_all_question_types"));
		$this->tpl->setVariable("SHOW_QUESTIONPOOLS", $this->lng->txt("filter_show_questionpools"));
		$this->tpl->setVariable("TEXT_ALL_QUESTIONPOOLS", $this->lng->txt("filter_all_questionpools"));
		$this->tpl->setVariable("FILTER_TEXT", $this->lng->txt("filter"));
		$this->tpl->setVariable("TEXT_FILTER_BY", $this->lng->txt("by"));
		if (strcmp($_POST["cmd"]["resetFilter"], "") == 0) 
		{
			$this->tpl->setVariable("VALUE_FILTER_TEXT", $filter_text);
		}
		$this->tpl->setVariable("VALUE_SUBMIT_FILTER", $this->lng->txt("set_filter"));
		$this->tpl->setVariable("VALUE_RESET_FILTER", $this->lng->txt("reset_filter"));
		$this->tpl->parseCurrentBlock();

		// create edit buttons & table footer
		$this->tpl->setCurrentBlock("selection");
		$this->tpl->setVariable("INSERT", $this->lng->txt("insert"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("Footer");
		$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("QTab");

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
		$table = $this->object->getQuestionsTable($_GET["sort"], $filter_text, $filter_type, $startrow, 1, $filter_question_type, $filter_questionpool);
		// display all questions in accessable question pools
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$existing_questions =& $this->object->getExistingQuestions();
		foreach ($table["rows"] as $data)
		{
			if (($rbacsystem->checkAccess("write", $data["ref_id"])) and (!in_array($data["question_id"], $existing_questions)))
			{
				if ($data["complete"])
				{
					// make only complete questions selectable
					$this->tpl->setVariable("QUESTION_ID", $data["question_id"]);
				}
				$this->tpl->setVariable("QUESTION_TITLE", "<strong>" . $data["title"] . "</strong>");
				$this->tpl->setVariable("PREVIEW", "[<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&preview=" . $data["question_id"] . "\">" . $this->lng->txt("preview") . "</a>]");
				$this->tpl->setVariable("QUESTION_COMMENT", $data["comment"]);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data["type_tag"]));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data["author"]);
				$this->tpl->setVariable("QUESTION_CREATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["created"]), "date"));
				$this->tpl->setVariable("QUESTION_UPDATED", ilFormat::formatDate(ilFormat::ftimestamp2dateDB($data["TIMESTAMP"]), "date"));
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter % 2]);
				$this->tpl->setVariable("QUESTION_POOL", $questionpools[$data["obj_fi"]]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
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
		$this->tpl->setVariable("QUESTION_TITLE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[title]=" . $sort["title"] . "\">" . $this->lng->txt("title") . "</a>" . $table["images"]["title"]);
		$this->tpl->setVariable("QUESTION_COMMENT", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[comment]=" . $sort["comment"] . "\">" . $this->lng->txt("description") . "</a>". $table["images"]["comment"]);
		$this->tpl->setVariable("QUESTION_TYPE", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[type]=" . $sort["type"] . "\">" . $this->lng->txt("question_type") . "</a>" . $table["images"]["type"]);
		$this->tpl->setVariable("QUESTION_AUTHOR", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[author]=" . $sort["author"] . "\">" . $this->lng->txt("author") . "</a>" . $table["images"]["author"]);
		$this->tpl->setVariable("QUESTION_CREATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[created]=" . $sort["created"] . "\">" . $this->lng->txt("create_date") . "</a>" . $table["images"]["created"]);
		$this->tpl->setVariable("QUESTION_UPDATED", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&startrow=" . $table["startrow"] . "&sort[updated]=" . $sort["updated"] . "\">" . $this->lng->txt("last_update") . "</a>" . $table["images"]["updated"]);
		$this->tpl->setVariable("BUTTON_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->parseCurrentBlock();
	}

	function removeQuestions($checked_questions)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_remove_questions.html", true);
		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type, tst_test_question WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY sequence",
			$this->ilias->db->quote($this->object->getTestId())
		);
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
					$this->tpl->setVariable("TXT_DESCRIPTION", $data->comment);
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
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}

	function insertQuestions($checked_questions)
	{
		sendInfo();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_insert_questions.html", true);
		$where = "";
		foreach ($checked_questions as $id)
		{
			$where .= sprintf(" OR qpl_questions.question_id = %s", $this->ilias->db->quote($id));
		}
		$where = preg_replace("/^ OR /", "", $where);
		$where = "($where)";
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE ISNULL(qpl_questions.original_id) AND qpl_questions.question_type_fi = qpl_question_type.question_type_id AND $where";
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
					$this->tpl->setVariable("TXT_DESCRIPTION", $data->comment);
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
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("TXT_DESCRIPTION", $this->lng->txt("description"));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $this->getAddParameter());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates a new questionpool and returns the reference id
	*
	* Creates a new questionpool and returns the reference id
	*
	* @return integer Reference id of the newly created questionpool
	* @access	public
	*/
	function createQuestionPool($name = "dummy")
	{
		global $tree;
		$parent_ref = $tree->getParentId($this->object->getRefId());
		$qpl = new ilObjQuestionPool();
		$qpl->setType("qpl");
		$qpl->setTitle($name);
		$qpl->setDescription("");
		$qpl->create();
		$qpl->createReference();
		$qpl->putInTree($parent_ref);
		$qpl->setPermissions($parent_ref);
		return $qpl->getRefId();
	}

	function questionpoolSelect()
	{
		global $ilUser;
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_qpl_select.html", true);
		$questionpools =& $this->object->getAvailableQuestionpools();
		if (count($questionpools) == 0)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_QPL", "");
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			foreach ($questionpools as $key => $value)
			{
				$this->tpl->setCurrentBlock("option");
				$this->tpl->setVariable("VALUE_OPTION", $key);
				$this->tpl->setVariable("TEXT_OPTION", $value);
				$this->tpl->parseCurrentBlock();
			}
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $_SERVER["PHP_SELF"] . $add_parameter);

		if (count($questionpools) == 0)
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_enter_questionpool"));
		}
		else
		{
			$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_select_questionpool"));
		}
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

	function randomSelect()
	{
		global $ilUser;
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_select.html", true);
		$questionpools =& $this->object->getAvailableQuestionpools();
		$this->tpl->setCurrentBlock("option");
		$this->tpl->setVariable("VALUE_OPTION", "0");
		$this->tpl->setVariable("TEXT_OPTION", $this->lng->txt("all_available_question_pools"));
		$this->tpl->parseCurrentBlock();
		foreach ($questionpools as $key => $value)
		{
			$this->tpl->setCurrentBlock("option");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			$this->tpl->setVariable("TEXT_OPTION", $value);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("hidden");
		$this->tpl->setVariable("HIDDEN_NAME", "sel_question_types");
		$this->tpl->setVariable("HIDDEN_VALUE", $_POST["sel_question_types"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("TXT_QPL_SELECT", $this->lng->txt("tst_random_select_questionpool"));
		$this->tpl->setVariable("TXT_NR_OF_QUESTIONS", $this->lng->txt("tst_random_nr_of_questions"));
		$this->tpl->setVariable("BTN_SUBMIT", $this->lng->txt("submit"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

	function randomQuestionOffer()
	{
		$question_array = $this->object->randomSelectQuestions($_POST["nr_of_questions"], $_POST["sel_qpl"]);
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_random_question_offer.html", true);
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionpools =& $this->object->get_qpl_titles();
		foreach ($question_array as $question_id)
		{
			$dataset = $this->object->getQuestionDataset($question_id);
			$this->tpl->setCurrentBlock("QTab");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->setVariable("QUESTION_TITLE", $dataset->title);
			$this->tpl->setVariable("QUESTION_COMMENT", $dataset->comment);
			$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($dataset->type_tag));
			$this->tpl->setVariable("QUESTION_AUTHOR", $dataset->author);
			$this->tpl->setVariable("QUESTION_POOL", $questionpools[$dataset->obj_fi]);
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($question_array) == 0)
		{
			$this->tpl->setCurrentBlock("Emptytable");
			$this->tpl->setVariable("TEXT_NO_QUESTIONS_AVAILABLE", $this->lng->txt("no_questions_available"));
			$this->tpl->parseCurrentBlock();
		}
			else
		{
			$this->tpl->setCurrentBlock("Selectionbuttons");
			$this->tpl->setVariable("BTN_YES", $this->lng->txt("random_accept_sample"));
			$this->tpl->setVariable("BTN_NO", $this->lng->txt("random_another_sample"));
			$this->tpl->parseCurrentBlock();
		}
		$chosen_questions = join($question_array, ",");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORM_ACTION", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_POOL", $this->lng->txt("qpl"));
		$this->tpl->setVariable("VALUE_CHOSEN_QUESTIONS", $chosen_questions);
		$this->tpl->setVariable("VALUE_QUESTIONPOOL_SELECTION", $_POST["sel_qpl"]);
		$this->tpl->setVariable("VALUE_NR_OF_QUESTIONS", $_POST["nr_of_questions"]);
		$this->tpl->setVariable("TEXT_QUESTION_OFFER", $this->lng->txt("tst_question_offer"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

	function questionsObject()
	{
		global $rbacsystem;
		$add_parameter = $this->getAddParameter();

		if ($_GET["up"] > 0)
		{
			$this->object->questionMoveUp($_GET["up"]);
		}
		if ($_GET["down"] > 0)
		{
			$this->object->questionMoveDown($_GET["down"]);
		}
		if ($_POST["cmd"]["create_question"])
		{
			$this->questionpoolSelect();
			return;
		}

		if ($_POST["cmd"]["randomselect"])
		{
			$this->randomSelect();
			return;
		}

		if ($_POST["cmd"]["random_select_questions"])
		{
			$this->randomQuestionOffer();
			return;
		}

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


		if ($_POST["cmd"]["random_select_yes"])
		{
			$selected_array = split(",", $_POST["chosen_questions"]);
			if (!count($selected_array))
			{
				sendInfo($this->lng->txt("tst_insert_missing_question"));
			}
			else
			{
				$total = $this->object->evalTotalPersons();
				if ($total)
				{
					// the test was executed previously
					sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
				}
				else
				{
					sendInfo($this->lng->txt("tst_insert_questions"));
				}
				$this->insertQuestions($selected_array);
				return;
			}
		}

		if ($_POST["cmd"]["create_question_execute"])
		{
			$qpl_ref_id = $_POST["sel_qpl"];
			if ((strcmp($_POST["txt_qpl"], "") == 0) && (strcmp($qpl_ref_id, "") == 0))
			{
				sendInfo($this->lng->txt("questionpool_not_entered"));
				$this->questionpoolSelect();
				return;
			}
			else
			{
				$_SESSION["test_id"] = $this->object->getRefId();
				if (strcmp($_POST["txt_qpl"], "") != 0)
				{
					// create a new question pool and return the reference id
					$qpl_ref_id = $this->createQuestionPool($_POST["txt_qpl"]);
				}
	//			header("Location:questionpool.php?ref_id=" . $_POST["sel_qpl"] . "&cmd=questions&create=" . $_POST["sel_question_types"]);
				header("Location:questionpool.php?ref_id=" . $qpl_ref_id . "&cmd=createQuestionForTest&test_ref_id=".$_GET["ref_id"]."&sel_question_types=" . $_POST["sel_question_types"]);
				exit();
			}
		}

		if ($_GET["add"])
		{
			$selected_array = array();
			array_push($selected_array, $_GET["add"]);
			$total = $this->object->evalTotalPersons();
			if ($total)
			{
				// the test was executed previously
				sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
			}
			else
			{
				sendInfo($this->lng->txt("tst_insert_questions"));
			}
			$this->insertQuestions($selected_array);
			return;
		}

		if (($_POST["cmd"]["insert_question"]) or ($_GET["insert_question"]))
		{
			$show_questionbrowser = true;
			if ($_POST["cmd"]["insert"])
			{
				// insert selected questions into test
				$selected_array = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/cb_(\d+)/", $key, $matches))
					{
						array_push($selected_array, $matches[1]);
					}
				}
				if (!count($selected_array))
				{
					sendInfo($this->lng->txt("tst_insert_missing_question"));
				}
				else
				{
					$total = $this->object->evalTotalPersons();
					if ($total)
					{
						// the test was executed previously
						sendInfo(sprintf($this->lng->txt("tst_insert_questions_and_results"), $total));
					}
					else
					{
						sendInfo($this->lng->txt("tst_insert_questions"));
					}
					$this->insertQuestions($selected_array);
					return;
				}
			}
			if ($_POST["cmd"]["back"])
			{
				$show_questionbrowser = false;
			}
			if ($show_questionbrowser)
			{
				$this->questionBrowser();
				return;
			}
		}

		if (strlen($_POST["cmd"]["confirm_insert"]) > 0)
		{
			// insert questions from test after confirmation
			foreach ($_POST as $key => $value) {
				if (preg_match("/id_(\d+)/", $key, $matches)) {
					$this->object->insertQuestion($matches[1]);
				}
			}
			$this->object->saveCompleteStatus();
			sendInfo($this->lng->txt("tst_questions_inserted"));
		}

		if (strlen($_POST["cmd"]["confirm_remove"]) > 0)
		{
			// remove questions from test after confirmation
			sendInfo($this->lng->txt("tst_questions_removed"));
			$checked_questions = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/id_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
			}
			foreach ($checked_questions as $key => $value) {
				$this->object->removeQuestion($value);
			}
			$this->object->saveCompleteStatus();
		}

		if (strlen($_POST["cmd"]["remove"]) > 0) {
			$checked_questions = array();
			foreach ($_POST as $key => $value) {
				if (preg_match("/cb_(\d+)/", $key, $matches)) {
					array_push($checked_questions, $matches[1]);
				}
			}
			if (count($checked_questions) > 0) {
				$total = $this->object->evalTotalPersons();
				if ($total) {
					// the test was executed previously
					sendInfo(sprintf($this->lng->txt("tst_remove_questions_and_results"), $total));
				} else {
					sendInfo($this->lng->txt("tst_remove_questions"));
				}
				$this->removeQuestions($checked_questions);
				return;
			} elseif (count($checked_questions) == 0) {
				sendInfo($this->lng->txt("tst_no_question_selected_for_removal"));
			}
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_questions.html", true);
		$this->tpl->addBlockFile("A_BUTTONS", "question_buttons", "tpl.il_as_tst_question_buttons.html", true);

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


		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type, tst_test_question WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY sequence",
			$this->ilias->db->quote($this->object->getTestId())
		);
		$query_result = $this->ilias->db->query($query);
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		$questionpools =& $this->object->get_qpl_titles();
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

				if ($rbacsystem->checkAccess("write", $this->ref_id)) {
					if ($data->question_id != $this->object->questions[1])
					{
						$this->tpl->setVariable("BUTTON_UP", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&up=$data->question_id\"><img src=\"" . ilUtil::getImagePath("a_up.gif") . "\" alt=\"" . $this->lng->txt("up") . "\" border=\"0\" /></a>");
					}
					if ($data->question_id != $this->object->questions[count($this->object->questions)])
					{
						$this->tpl->setVariable("BUTTON_DOWN", "<a href=\"" . $_SERVER["PHP_SELF"] . "$add_parameter&down=$data->question_id\"><img src=\"" . ilUtil::getImagePath("a_down.gif") . "\" alt=\"" . $this->lng->txt("down") . "\" border=\"0\" /></a>");
					}
				}
				$this->tpl->setVariable("QUESTION_COMMENT", $data->comment);
				$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt($data->type_tag));
				$this->tpl->setVariable("QUESTION_AUTHOR", $data->author);
				$this->tpl->setVariable("QUESTION_POOL", $questionpools[$data->obj_fi]);
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
			if ($rbacsystem->checkAccess("write", $this->ref_id)) {
				$this->tpl->setCurrentBlock("QFooter");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				$this->tpl->setVariable("REMOVE", $this->lng->txt("remove_question"));
				$this->tpl->setVariable("MOVE", $this->lng->txt("move"));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
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
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_QUESTION_FORM", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("QUESTION_COMMENT", $this->lng->txt("description"));
		$this->tpl->setVariable("QUESTION_TYPE", $this->lng->txt("tst_question_type"));
		$this->tpl->setVariable("QUESTION_AUTHOR", $this->lng->txt("author"));
		$this->tpl->setVariable("QUESTION_POOL", $this->lng->txt("qpl"));

		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("BUTTON_INSERT_QUESTION", $this->lng->txt("tst_browse_for_questions"));
			$this->tpl->setVariable("TEXT_CREATE_NEW", " " . strtolower($this->lng->txt("or")) . " " . $this->lng->txt("create_new"));
			$this->tpl->setVariable("BUTTON_CREATE_QUESTION", $this->lng->txt("create"));
			$this->tpl->setVariable("TXT_OR", $this->lng->txt("or"));
			$this->tpl->setVariable("TEXT_RANDOM_SELECT", $this->lng->txt("random_selection"));
		}

		$this->tpl->parseCurrentBlock();
	}

	function editMetaObject()
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$meta_gui =& new ilMetaDataGUI();
			$meta_gui->setObject($this->object);
			$meta_gui->edit("ADM_CONTENT", "adm_content",
				"test.php?ref_id=".$_GET["ref_id"]."&cmd=saveMeta");
		}
	}

		function saveMetaObject()
	{
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->save($_POST["meta_section"]);
		if (!strcmp($_POST["meta_section"], "General")) {
			//$this->updateObject();
		}
		ilUtil::redirect("test.php?ref_id=".$_GET["ref_id"]);
	}

	// called by administration
	function chooseMetaSectionObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "test.php?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_REQUEST["meta_section"]);
	}

	// called by editor
	function chooseMetaSection()
	{
		$this->chooseMetaSectionObject("test.php?ref_id=".
			$this->object->getRefId());
	}

	function addMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "test.php?ref_id=".$_GET["ref_id"];
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
		$this->addMetaObject("test.php?ref_id=".
			$this->object->getRefId());
	}

	function deleteMetaObject($a_script = "",
		$a_templ_var = "ADM_CONTENT", $a_templ_block = "adm_content")
	{
		if ($a_script == "")
		{
			$a_script = "test.php?ref_id=".$_GET["ref_id"];
		}
		$meta_gui =& new ilMetaDataGUI();
		$meta_gui->setObject($this->object);
		$meta_index = $_POST["meta_index"] ? $_POST["meta_index"] : $_GET["meta_index"];
		$meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $meta_index);
		$meta_gui->edit($a_templ_var, $a_templ_block, $a_script, $_GET["meta_section"]);
	}

	function deleteMeta()
	{
		$this->deleteMetaObject("test.php?ref_id=".
			$this->object->getRefId());
	}

	function takenObject() {
	}

	function marksObject() {
		global $rbacsystem;
		$add_parameter = $this->getAddParameter();
		if ($_POST["cmd"]["new_simple"]) {
			$this->object->mark_schema->create_simple_schema($this->lng->txt("failed_short"), $this->lng->txt("failed_official"), 0, 0, $this->lng->txt("passed_short"), $this->lng->txt("passed_official"), 50, 1);
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
	
		if (count($_POST))
		{
			$this->object->ects_grades["A"] = $_POST["ects_grade_a"];
			$this->object->ects_grades["B"] = $_POST["ects_grade_b"];
			$this->object->ects_grades["C"] = $_POST["ects_grade_c"];
			$this->object->ects_grades["D"] = $_POST["ects_grade_d"];
			$this->object->ects_grades["E"] = $_POST["ects_grade_e"];
		}
		
		if ($_POST["cmd"]["save"]) {
			if ($_POST["chbECTS"] && ((strcmp($_POST["ects_grade_a"], "") == 0) or (strcmp($_POST["ects_grade_b"], "") == 0) or (strcmp($_POST["ects_grade_c"], "") == 0) or (strcmp($_POST["ects_grade_d"], "") == 0) or (strcmp($_POST["ects_grade_e"], "") == 0)))
			{
				sendInfo($this->lng->txt("ects_fill_out_all_values"), true);
			}
			elseif (($_POST["ects_grade_a"] > 100) or ($_POST["ects_grade_a"] < 0))
			{
				sendInfo($this->lng->txt("ects_range_error_a"), true);
			}
			elseif (($_POST["ects_grade_b"] > 100) or ($_POST["ects_grade_b"] < 0))
			{
				sendInfo($this->lng->txt("ects_range_error_b"), true);
			}
			elseif (($_POST["ects_grade_c"] > 100) or ($_POST["ects_grade_c"] < 0))
			{
				sendInfo($this->lng->txt("ects_range_error_c"), true);
			}
			elseif (($_POST["ects_grade_d"] > 100) or ($_POST["ects_grade_d"] < 0))
			{
				sendInfo($this->lng->txt("ects_range_error_d"), true);
			}
			elseif (($_POST["ects_grade_e"] > 100) or ($_POST["ects_grade_e"] < 0))
			{
				sendInfo($this->lng->txt("ects_range_error_e"), true);
			}
			else 
			{
				$this->object->mark_schema->saveToDb($this->object->getTestId());
				$this->object->saveCompleteStatus();
				if ($this->object->getReportingDate())
				{
					$fxpercent = "";
					if ($_POST["chbUseFX"])
					{
						$fxpercent = ilUtil::stripSlashes($_POST["percentFX"]);
					}
					$this->object->saveECTSStatus($_POST["chbECTS"], $fxpercent, $this->object->ects_grades["A"], $this->object->ects_grades["B"], $this->object->ects_grades["C"], $this->object->ects_grades["D"], $this->object->ects_grades["E"]);
				}
				sendInfo($this->lng->txt("msg_obj_modified"), true);
			}
		}

		if ($_POST["cmd"]["cancel"]) {
			sendInfo($this->lng->txt("msg_cancel"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			header("location: ". $this->getReturnLocation("cancel","../repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			exit();
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
			if ($rbacsystem->checkAccess("write", $this->ref_id)) {
				$this->tpl->setCurrentBlock("Footer");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"\">");
				$this->tpl->setVariable("BUTTON_EDIT", $this->lng->txt("edit"));
				$this->tpl->setVariable("BUTTON_DELETE", $this->lng->txt("delete"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		if ($this->object->getReportingDate())
		{
			$this->tpl->setCurrentBlock("ects");
			if ($this->object->ects_output)
			{
				$this->tpl->setVariable("CHECKED_ECTS", " checked=\"checked\"");
			}
			$this->tpl->setVariable("TEXT_OUTPUT_ECTS_GRADES", $this->lng->txt("ects_output_of_ects_grades"));
			$this->tpl->setVariable("TEXT_ALLOW_ECTS_GRADES", $this->lng->txt("ects_allow_ects_grades"));
			$this->tpl->setVariable("TEXT_USE_FX", $this->lng->txt("ects_use_fx_grade"));
			if (preg_match("/\d+/", $this->object->ects_fx))
			{
				$this->tpl->setVariable("CHECKED_FX", " checked=\"checked\"");
				$this->tpl->setVariable("VALUE_PERCENT_FX", sprintf("value=\"%s\" ", $this->object->ects_fx));
			}
			$this->tpl->setVariable("TEXT_PERCENT", $this->lng->txt("ects_use_fx_grade_part2"));
			$this->tpl->setVariable("ECTS_GRADE", $this->lng->txt("ects_grade"));
			$this->tpl->setVariable("PERCENTILE", $this->lng->txt("percentile"));
			$this->tpl->setVariable("ECTS_GRADE_A", "A - " . $this->lng->txt("ects_grade_a_short"));
			$this->tpl->setVariable("VALUE_GRADE_A", $this->object->ects_grades["A"]);
			$this->tpl->setVariable("ECTS_GRADE_B", "B - " . $this->lng->txt("ects_grade_b_short"));
			$this->tpl->setVariable("VALUE_GRADE_B", $this->object->ects_grades["B"]);
			$this->tpl->setVariable("ECTS_GRADE_C", "C - " . $this->lng->txt("ects_grade_c_short"));
			$this->tpl->setVariable("VALUE_GRADE_C", $this->object->ects_grades["C"]);
			$this->tpl->setVariable("ECTS_GRADE_D", "D - " . $this->lng->txt("ects_grade_d_short"));
			$this->tpl->setVariable("VALUE_GRADE_D", $this->object->ects_grades["D"]);
			$this->tpl->setVariable("ECTS_GRADE_E", "E - " . $this->lng->txt("ects_grade_e_short"));
			$this->tpl->setVariable("VALUE_GRADE_E", $this->object->ects_grades["E"]);
			
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("ACTION_MARKS", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->setVariable("HEADER_SHORT", $this->lng->txt("tst_mark_short_form"));
		$this->tpl->setVariable("HEADER_OFFICIAL", $this->lng->txt("tst_mark_official_form"));
		$this->tpl->setVariable("HEADER_PERCENTAGE", $this->lng->txt("tst_mark_minimum_level"));
		$this->tpl->setVariable("HEADER_PASSED", $this->lng->txt("tst_mark_passed"));
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->tpl->setVariable("BUTTON_NEW", $this->lng->txt("tst_mark_create_new_mark_step"));
			$this->tpl->setVariable("BUTTON_NEW_SIMPLE", $this->lng->txt("tst_mark_create_simple_mark_schema"));
			$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
			$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		}
		$this->tpl->parseCurrentBlock();
	}

	function runObject()
	{
		global $ilUser;

		if ($_POST["cmd"]["cancelTest"])
		{
			sendInfo($this->lng->txt("test_cancelled"), true);
			$path = $this->tree->getPathFull($this->object->getRefID());
			header("location: ". $this->getReturnLocation("cancel","../repository.php?ref_id=" . $path[count($path) - 2]["child"]));
			exit();
		}
		
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_tst_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();
		$maxprocessingtimereached = 0;
		if (($this->object->getEnableProcessingTime()) and ($this->object->getCompleteWorkingTime($ilUser->id) > $this->object->getProcessingTimeInSeconds()))
		{
			// maximum processing time reached
			$maxprocessingtimereached = 1;
		}

		$directfeedback = 0;
		// catch feedback message
		sendInfo();

		if ($_POST["cmd"]["next"] or $_POST["cmd"]["previous"] or $_POST["cmd"]["postpone"] or $_POST["cmd"]["directfeedback"] or isset($_GET["selImage"]))
		{
			// set new finish time for test
			if ($_SESSION["active_time_id"])
			{
				$this->object->updateWorkingTime($_SESSION["active_time_id"]);
			}
			if (($this->object->getEnableProcessingTime()) and ($this->object->getCompleteWorkingTime($ilUser->id) > $this->object->getProcessingTimeInSeconds()))
			{
				// maximum processing time reached
				$maxprocessingtimereached = 1;
			}

			// save question solution
			$saveResult = false;
			if (!($this->object->endingTimeReached() and ($this->object->getTestType() == TYPE_ASSESSMENT)) and (!$maxprocessingtimereached))
			{
				// but only if the ending time is not reached
				$question_gui = $this->object->createQuestionGUI("", $this->object->getQuestionIdFromActiveUserSequence($_GET["sequence"]));
				$saveResult = $question_gui->object->saveWorkingData($this->object->getTestId());

				if ($_POST["cmd"]["directfeedback"])
				{
					$directfeedback = 1;
				}
			}
		}
		if ($_POST["cmd"]["start"] or $_POST["cmd"]["resume"])
		{
			// create new time dataset and set start time
			$active_time_id = $this->object->startWorkingTime($ilUser->id);
			$_SESSION["active_time_id"] = $active_time_id;
			if ($_POST["chb_javascript"])
			{
				$ilUser->setPref("tst_javascript", 1);
				$ilUser->writePref("tst_javascript", 1);
			}
			else
			{
				$ilUser->setPref("tst_javascript", 0);
				$ilUser->writePref("tst_javascript", 0);
			}
		}

		$this->sequence = $_GET["sequence"];
		if ($_POST["cmd"]["deleteresults"] or $_POST["cmd"]["canceldeleteresults"] or $_POST["cmd"]["confirmdeleteresults"])
		{
			// reset sequence. it is not needed for test reset
			$this->sequence = "";
		}
		if (($_POST["cmd"]["next"]) and $saveResult)
		{
			$this->sequence++;
		}
		elseif (($_POST["cmd"]["previous"]) and ($this->sequence != 0) and ($saveResult))
		{
			$this->sequence--;
		}
		$this->setLocator();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		if ($_POST["cmd"]["confirmdeleteresults"])
		{
			$this->object->deleteResults($ilUser->id);
			sendInfo("tst_confirm_delete_results");
		}
		
		if ($_POST["cmd"]["deleteresults"])
		{
			$this->confirmDeleteResults();
			return;
		}
		
		if ($_GET["evaluation"])
		{
			$this->outEvaluationForm();
			return;
		}

		if (($_POST["cmd"]["showresults"]) or ($_GET["sortres"]))
		{
			$this->outTestResults();
			return;
		}

		if (!$this->sequence)
		{
			// show introduction page
			$this->outIntroductionPage($maxprocessingtimereached);
		}
		else
		{
			if ($this->object->endingTimeReached() and ($this->object->getTestType() == TYPE_ASSESSMENT))
			{
				sendInfo(sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
				$this->object->setActiveTestUser(1, "", true);
				if (!$this->object->canViewResults()) 
				{
					$this->outIntroductionPage($maxprocessingtimereached);
				}
				else
				{
					$this->outTestResults();
				}
				return;
			}
			if ($maxprocessingtimereached)
			{
				sendInfo($this->lng->txt("detail_max_processing_time_reached"));
				$this->object->setActiveTestUser(1, "", true);
				if (!$this->object->canViewResults()) 
				{
					$this->outIntroductionPage($maxprocessingtimereached);
				}
				else
				{
					$this->outTestResults();
				}
				return;
			}
			$user_question_order =& $this->object->getAllQuestionsForActiveUser();
			if ($this->sequence <= $this->object->getQuestionCount())
			{
				if ($this->object->getScoreReporting() == REPORT_AFTER_QUESTION)
				{
					$this->tpl->setCurrentBlock("direct_feedback");
					$this->tpl->setVariable("TEXT_DIRECT_FEEDBACK", $this->lng->txt("direct_feedback"));
					$this->tpl->parseCurrentBlock();
				}
				
				// show next/previous question
				$postpone = "";
				if ($_POST["cmd"]["postpone"])
				{
					$postpone = $this->sequence;
				}
				$this->object->setActiveTestUser($this->sequence, $postpone);
				if ($this->sequence == $this->object->getQuestionCount())
				{
					$finish = true;
				}
				else
				{
					$finish = false;
				}

				$postpone = false;

				if ($this->object->getSequenceSettings() == TEST_POSTPONE)
				{
					$postpone = true;
				}
				$active = $this->object->getActiveTestUser();
				$this->tpl->setCurrentBlock("percentage");
				$this->tpl->setVariable("PERCENTAGE", (int)(($this->sequence / count($user_question_order))*200));
				$this->tpl->setVariable("PERCENTAGE_VALUE", (int)(($this->sequence / count($user_question_order))*100));
				$this->tpl->setVariable("HUNDRED_PERCENT", "200");
				$this->tpl->setVariable("TEXT_COMPLETED", $this->lng->txt("completed") . ": ");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("cancel_test");
				$this->tpl->setVariable("BUTTON_CANCELTEST", $this->lng->txt("cancel_test"));
				$this->tpl->parseCurrentBlock();

				if ($this->object->getEnableProcessingTime())
				{
					$this->tpl->setCurrentBlock("enableprocessingtime");
					$working_time = $this->object->getCompleteWorkingTime($ilUser->id);
					$processing_time = $this->object->getProcessingTimeInSeconds();
					$time_seconds = $working_time;
					$time_hours    = floor($time_seconds/3600);
					$time_seconds -= $time_hours   * 3600;
					$time_minutes  = floor($time_seconds/60);
					$time_seconds -= $time_minutes * 60;
					$this->tpl->setVariable("USER_WORKING_TIME", $this->lng->txt("tst_time_already_spent") . ": " . sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
					$time_seconds = $processing_time;
					$time_hours    = floor($time_seconds/3600);
					$time_seconds -= $time_hours   * 3600;
					$time_minutes  = floor($time_seconds/60);
					$time_seconds -= $time_minutes * 60;
					$this->tpl->setVariable("MAXIMUM_PROCESSING_TIME", $this->lng->txt("tst_processing_time") . ": " . sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
					$this->tpl->parseCurrentBlock();
				}


				$this->outWorkingForm($this->sequence, $finish, $this->object->getTestId(), $active, $postpone, $user_question_order, $directfeedback);

			}
			else
			{
				// finish test
				$this->object->setActiveTestUser(1, "", true);
				if (!$this->object->canViewResults()) 
				{
					$this->outIntroductionPage($maxprocessingtimereached);
				}
				else
				{
					$this->outTestResults();
				}
			}
		}
	}

	/**
	* Creates the introduction page for a test
	*
	* Creates the introduction page for a test
	*
	* @access public
	*/
	function outIntroductionPage($maxprocessingtimereached = 0)
	{
		global $ilUser;
		
		$add_parameter = $this->getAddParameter();
		$active = $this->object->getActiveTestUser();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_introduction.html", true);
		$this->tpl->setCurrentBlock("info_row");
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_type") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($this->object->test_types[$this->object->getTestType()]));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("description") . ":");
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->object->getDescription());
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_sequence") . ":");
		if ($this->object->getSequenceSettings() == TEST_FIXED_SEQUENCE)
		{
			$seq_setting = "tst_sequence_fixed";
		}
		else
		{
			$seq_setting = "tst_sequence_postpone";
		}
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($seq_setting));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_score_reporting") . ":");
		if ($this->object->getScoreReporting() == REPORT_AFTER_QUESTION) {
			$score_reporting = "tst_report_after_question";
		} else {
			$score_reporting = "tst_report_after_test";
		}
		$this->tpl->setVariable("TEXT_INFO_COL2", $this->lng->txt($score_reporting));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_nr_of_tries") . ":");
		$num_of = $this->object->getNrOfTries();
		if (!$num_of) {
			$num_of = $this->lng->txt("unlimited");
		}
		$this->tpl->setVariable("TEXT_INFO_COL2", $num_of);
		$this->tpl->parseCurrentBlock();
		if ($num_of != 1)
		{
			// display number of tries of the user
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_nr_of_tries_of_user") . ":");
			$tries = $active->tries;
			if (!$tries)
			{
				$tries = $this->lng->txt("tst_no_tries");
			}
			$this->tpl->setVariable("TEXT_INFO_COL2", $tries);
			$this->tpl->parseCurrentBlock();
		}

		if ($this->object->getEnableProcessingTime())
		{
	 		$working_time = $this->object->getCompleteWorkingTime($ilUser->id);
			$processing_time = $this->object->getProcessingTimeInSeconds();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_processing_time") . ":");
			$time_seconds = $processing_time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$this->tpl->setVariable("TEXT_INFO_COL2", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setVariable("TEXT_INFO_COL1", $this->lng->txt("tst_time_already_spent") . ":");
			$time_seconds = $working_time;
			$time_hours    = floor($time_seconds/3600);
			$time_seconds -= $time_hours   * 3600;
			$time_minutes  = floor($time_seconds/60);
			$time_seconds -= $time_minutes * 60;
			$this->tpl->setVariable("TEXT_INFO_COL2", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
			$this->tpl->parseCurrentBlock();
		}


		$this->tpl->setCurrentBlock("info");
		$this->tpl->setVariable("TEXT_USE_JAVASCRIPT", $this->lng->txt("tst_use_javascript"));
		if ($ilUser->prefs["tst_javascript"])
		{
			$this->tpl->setVariable("CHECKED_JAVASCRIPT", "checked=\"checked\" ");
		}
		$this->tpl->parseCurrentBlock();
		$seq = 1;
		if ($active) {
			$seq = $active->lastindex;
		}
		$add_sequence = "&sequence=$seq";
		$test_disabled = false;
		if ($active) {
			$this->tpl->setCurrentBlock("resume");
			$this->tpl->setVariable("BTN_RESUME", $this->lng->txt("tst_resume_test"));
			if ((($active->tries >= $this->object->getNrOfTries()) and ($this->object->getNrOfTries() != 0)) or $maxprocessingtimereached) {
				$this->tpl->setVariable("DISABLED", " disabled");
				$test_disabled = true;
				$add_sequence = "";
			}
			else
			{
				// if resume is active it is possible to reset the test
				$this->tpl->setCurrentBlock("delete_results");
				$this->tpl->setVariable("BTN_DELETERESULTS", $this->lng->txt("tst_delete_results"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("results");
			if (($active->tries < 1) or (!$this->object->canViewResults())) {
				$this->tpl->setVariable("DISABLED", " disabled");
			}
			$this->tpl->setVariable("BTN_RESULTS", $this->lng->txt("tst_show_results"));
			$this->tpl->parseCurrentBlock();
			if (!$this->object->canViewResults()) {
				$this->tpl->setCurrentBlock("report_date_not_reached");
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->object->getReportingDate(), $matches);
				$reporting_date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]));
				$this->tpl->setVariable("RESULT_DATE_NOT_REACHED", sprintf($this->lng->txt("report_date_not_reached"), $reporting_date));
				$this->tpl->parseCurrentBlock();
			}
		}
		else
		{
			if (($this->object->startingTimeReached() and (!$this->object->endingTimeReached())) or (!$this->object->getTestType == TYPE_ASSESSMENT))
			{
				$this->tpl->setCurrentBlock("start");
				$this->tpl->setVariable("BTN_START", $this->lng->txt("tst_start_test"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("startingtime");
				$this->tpl->setVariable("IMAGE_STARTING_TIME", ilUtil::getImagePath("time.gif", true));
				if (!$this->object->startingTimeReached())
				{
					$this->tpl->setVariable("ALT_STARTING_TIME_NOT_REACHED", $this->lng->txt("starting_time_not_reached"));
					$this->tpl->setVariable("TEXT_STARTING_TIME_NOT_REACHED", sprintf($this->lng->txt("detail_starting_time_not_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getStartingTime())));
				}
				else
				{
					$this->tpl->setVariable("ALT_STARTING_TIME_NOT_REACHED", $this->lng->txt("ending_time_reached"));
					$this->tpl->setVariable("TEXT_STARTING_TIME_NOT_REACHED", sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->object->getEndingTime())));
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setCurrentBlock("adm_content");
		if ($test_disabled)
		{
			if ($maxprocessingtimereached)
			{
				sendInfo($this->lng->txt("detail_max_processing_time_reached"));
			}
			else
			{
				$this->tpl->setVariable("MAXIMUM_NUMBER_OF_TRIES_REACHED", $this->lng->txt("maximum_nr_of_tries_reached"));
			}
		}
		$introduction = $this->object->getIntroduction();
		$introduction = preg_replace("/\n/i", "<br />", $introduction);
		$this->tpl->setVariable("TEXT_INTRODUCTION", $introduction);
		$this->tpl->setVariable("FORMACTION", $_SERVER['PHP_SELF'] . "$add_parameter$add_sequence");
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Creates the learners output of a question
	*
	* Creates the learners output of a question
	*
	* @access public
	*/
	function outWorkingForm($sequence = 1, $finish = false, $test_id, $active, $postpone_allowed, $user_question_order, $directfeedback = 0)
	{
		global $ilUser;
		
		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
		ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
		ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$question_gui = $this->object->createQuestionGUI("", $this->object->getQuestionIdFromActiveUserSequence($sequence));
		if ($ilUser->prefs["tst_javascript"])
		{
			$question_gui->object->setOutputType(OUTPUT_JAVASCRIPT);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_preview.html", true);

		$is_postponed = false;
		if ($active)
		{
			if (!preg_match("/(^|\D)" . $question_gui->object->getId() . "($|\D)/", $active->postponed) and !($active->postponed == $question_gui->object->getId()))
			{
				$is_postponed = false;
			}
			else
			{
				$is_postponed = true;
			}
		}

//		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_question_output.html", true);
		$formaction = $_SERVER["PHP_SELF"] . $this->getAddParameter() . "&sequence=$sequence";

		// output question
		switch ($question_gui->getQuestionType())
		{
			case "qt_imagemap":
				$question_gui->outWorkingForm($test_id, $is_postponed, $directfeedback, $formaction);
				$info =& $question_gui->object->getReachedInformation($ilUser->id, $test_id);
				if (strcmp($info[0]["value"], "") != 0)
				{
					$formaction .= "&selImage=" . $info[0]["value"];
				}
				break;

			default:
				$question_gui->outWorkingForm($test_id, $is_postponed, $directfeedback);
				break;
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $formaction);

		if ($sequence == 1)
		{
			$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_introduction"));
		}
		else
		{
			$this->tpl->setVariable("BTN_PREV", "&lt;&lt; " . $this->lng->txt("save_previous"));
		}

		if ($finish)
		{
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_finish") . " &gt;&gt;");
		}
		else
		{
			$this->tpl->setVariable("BTN_NEXT", $this->lng->txt("save_next") . " &gt;&gt;");
		}

		if ($postpone_allowed)
		{
			if (!$is_postponed)
			{
				$this->tpl->setVariable("BTN_POSTPONE", $this->lng->txt("postpone"));
			}
		}

		$this->tpl->parseCurrentBlock();
	}


	function outEvaluationForm()
	{
		global $ilUser;

		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$test_id = $this->object->getTestId();
		$question_gui = $this->object->createQuestionGUI("", $_GET["evaluation"]);
//		$this->tpl->addBlockFile("RESULT_DESCRIPTION", "result_description", "tpl.il_as_tst_result_table.html", true);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_evaluation.html", true);
		$formaction = $_SERVER["PHP_SELF"] . $this->getAddParameter() . "&sequence=$sequence";
		switch ($question_gui->getQuestionType())
		{
			case "qt_imagemap":
				$question_gui->outWorkingForm($test_id, "", 1, $formaction);
				break;
			case "qt_javaapplet":
				$question_gui->outWorkingForm("", "", 0);
				break;
			default:
				$question_gui->outWorkingForm($test_id, "", 1);
		}
//		$this->tpl->setCurrentBlock("result_description");
//		$question_gui->outUserSolution($ilUser->id, $this->object->getTestId());
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $_SERVER["PHP_SELF"] . $this->getAddParameter());
		$this->tpl->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
		$this->tpl->parseCurrentBlock();
	}

	function printEvaluationObject()
	{
		$tpl = new ilTemplate("./assessment/templates/default/tpl.il_as_tst_eval_statistical_evaluation_preview.html", true, true);
		$row_classes = array("tblrow1", "tblrow2");
		foreach ($_SESSION["print_eval"] as $key => $value)
		{
			if ($key == 0)
			{
				for ($i = 0; $i < count($value); $i++)
				{
					if ($i < count($value) - $_SESSION["print_eval"]["nr_of_questions"])
					{
						$tpl->setCurrentBlock("titlecol");
						$tpl->setVariable("TXT_TITLE", $value[$i]);
						$tpl->parseCurrentBlock();
						if ($i == 0)
						{
							$tpl->setCurrentBlock("qtitlecol");
							$tpl->setVariable("TXT_QTITLE", $value[$i]);
							$tpl->parseCurrentBlock();
						}
					}
					else
					{
						$tpl->setCurrentBlock("qtitlecol");
						$tpl->setVariable("TXT_QTITLE", $value[$i]);
						$tpl->parseCurrentBlock();
					}
				}
			}
			else if (preg_match("/\d+/", $key))
			{
				for ($i = 0; $i < count($value); $i++)
				{
					if ($i < count($value) - $_SESSION["print_eval"]["nr_of_questions"])
					{
						$tpl->setCurrentBlock("datacol");
						$tpl->setVariable("TXT_DATA", $value[$i]);
						$tpl->parseCurrentBlock();
						if ($i == 0)
						{
							$tpl->setCurrentBlock("qdatacol");
							$tpl->setVariable("TXT_QDATA", $value[$i]);
							$tpl->parseCurrentBlock();
						}
					}
					else
					{
						$tpl->setCurrentBlock("qdatacol");
						$tpl->setVariable("TXT_QDATA", $value[$i]);
						$tpl->parseCurrentBlock();
					}
				}
				$tpl->setCurrentBlock("row");
				$tpl->setVariable("ROW_CLASS", $row_classes[$key % 2]);
				$tpl->parseCurrentBlock();
				$tpl->setCurrentBlock("qrow");
				$tpl->setVariable("ROW_CLASS", $row_classes[$key % 2]);
				$tpl->parseCurrentBlock();
			}
		}
		
		$tpl->setCurrentBlock("question_block");
		$tpl->setVariable("TXT_QUESTIONS",  $this->lng->txt("ass_questions"));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("heading");
		$tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation") . " " . $this->lng->txt("of") . " " . $this->object->getTitle());
		$tpl->setVariable("PRINT_CSS", "./templates/default/print.css");
		$tpl->parseCurrentBlock();
		$tpl->show();
		exit();
	}
	
	function eval_statObject()
	{
		global $ilUser;
		if (!$_POST["export_type"])
		{
			$_POST["export_type"] = TYPE_PRINT;
		}

		$testname = preg_replace("/\s/", "_", $this->object->getTitle());
		switch ($_POST["export_type"])
		{
			case TYPE_XLS:
				// Creating a workbook
				$workbook = new Spreadsheet_Excel_Writer();

				// sending HTTP headers
				$workbook->send("$testname.xls");

				// Creating a worksheet
				$format_bold =& $workbook->addFormat();
				$format_bold->setBold();
				$format_percent =& $workbook->addFormat();
				$format_percent->setNumFormat("0.00%");
				$format_datetime =& $workbook->addFormat();
				$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
				$format_title =& $workbook->addFormat();
				$format_title->setBold();
				$format_title->setColor('black');
				$format_title->setPattern(1);
				$format_title->setFgColor('silver');
				$worksheet =& $workbook->addWorksheet();
				break;
			case TYPE_SPSS:
			case TYPE_PRINT:
				$csvfile = array();
				break;
		}
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_tst_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();
		$this->setLocator();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_statistical_evaluation.html", true);

		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		if ($_POST["cmd"]["stat_all_users"] or $_POST["cmd"]["stat_selected_users"] or $_POST["cmd"]["expEvalData"])
		{
			if ($_POST["cmd"]["stat_all_users"] or $_POST["cmd"]["stat_selected_users"])
			{
				$eval_statistical_settings = array(
					"qworkedthrough" => $_POST["chb_result_qworkedthrough"],
					"pworkedthrough" => $_POST["chb_result_pworkedthrough"],
					"timeofwork" => $_POST["chb_result_timeofwork"],
					"atimeofwork" => $_POST["chb_result_atimeofwork"],
					"firstvisit" => $_POST["chb_result_firstvisit"],
					"lastvisit" => $_POST["chb_result_lastvisit"],
					"resultspoints" => $_POST["chb_result_resultspoints"],
					"resultsmarks" => $_POST["chb_result_resultsmarks"],
					"distancemedian" => $_POST["chb_result_distancemedian"]
				);
				$this->object->evalSaveStatisticalSettings($eval_statistical_settings, $ilUser->id);
			}
			else
			{
				$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
				$eval_statistical_settings = array(
					"qworkedthrough" => $user_settings["qworkedthrough"],
					"pworkedthrough" => $user_settings["pworkedthrough"],
					"timeofwork" => $user_settings["timeofwork"],
					"atimeofwork" => $user_settings["atimeofwork"],
					"firstvisit" => $user_settings["firstvisit"],
					"lastvisit" => $user_settings["lastvisit"],
					"resultspoints" => $user_settings["resultspoints"],
					"resultsmarks" => $user_settings["resultsmarks"],
					"distancemedian" => $user_settings["distancemedian"]
				);
			}
			// bild title columns
			$this->tpl->setCurrentBlock("titlecol");
			$name_column = $this->lng->txt("name");
			if ($this->object->getTestType() == TYPE_SELF_ASSESSMENT)
			{
				$name_column = $this->lng->txt("counter");
			}
			$this->tpl->setVariable("TXT_TITLE", $name_column);
			$this->tpl->parseCurrentBlock();
			$column = 0;
			$csvrow = array();
			switch ($_POST["export_type"])
			{
				case TYPE_XLS:
					$worksheet->write(0, $column++, $name_column, $format_title);
					break;
				case TYPE_SPSS:
				case TYPE_PRINT:
					array_push($csvrow, $name_column);
					break;
			}
			$char = "A";
			if ($eval_statistical_settings["qworkedthrough"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_qworkedthrough") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_qworkedthrough"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_qworkedthrough"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_qworkedthrough"));
						break;
				}
				$char++;
			}
			if ($eval_statistical_settings["pworkedthrough"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_pworkedthrough") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_pworkedthrough"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_pworkedthrough"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_pworkedthrough"));
						break;
				}
				$char++;
			}
			if ($eval_statistical_settings["timeofwork"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_timeofwork") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_timeofwork"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_timeofwork"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_timeofwork"));
						break;
				}
				$char++;
			}
			if ($eval_statistical_settings["atimeofwork"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_atimeofwork") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_atimeofwork"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_atimeofwork"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_atimeofwork"));
						break;
				}
				$char++;
			}
			if ($eval_statistical_settings["firstvisit"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_firstvisit") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_firstvisit"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_firstvisit"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_firstvisit"));
						break;
				}
				$char++;
			}
			if ($eval_statistical_settings["lastvisit"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_lastvisit") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_lastvisit"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_lastvisit"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_lastvisit"));
						break;
				}
				$char++;
			}
			if ($eval_statistical_settings["resultspoints"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_resultspoints") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_resultspoints"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_resultspoints"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_resultspoints"));
						break;
				}
				$char++;
			}
			if ($eval_statistical_settings["resultsmarks"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_resultsmarks") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_resultsmarks"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_resultsmarks"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_resultsmarks"));
						break;
				}
				$char++;
				
				if ($this->object->ects_output)
				{
					$this->tpl->setCurrentBlock("titlecol");
					$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("ects_grade") . "\">$char</div>");
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("legendrow");
					$this->tpl->setVariable("TXT_SYMBOL", $char);
					$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("ects_grade"));
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write(0, $column++, $this->lng->txt("ects_grade"), $format_title);
							break;
						case TYPE_SPSS:
						case TYPE_PRINT:
							array_push($csvrow, $this->lng->txt("ects_grade"));
							break;
					}
					$char++;
				}
			}
			if ($eval_statistical_settings["distancemedian"]) {
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_mark_median") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char++);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_mark_median"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_rank_participant") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char++);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_rank_participant"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_rank_median") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char++);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_rank_median"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_total_participants") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char++);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_total_participants"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("titlecol");
				$this->tpl->setVariable("TXT_TITLE", "<div title=\"" . $this->lng->txt("tst_stat_result_median") . "\">$char</div>");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("legendrow");
				$this->tpl->setVariable("TXT_SYMBOL", $char++);
				$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("tst_stat_result_median"));
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_mark_median"), $format_title);
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_rank_participant"), $format_title);
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_rank_median"), $format_title);
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_total_participants"), $format_title);
						$worksheet->write(0, $column++, $this->lng->txt("tst_stat_result_median"), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->lng->txt("tst_stat_result_mark_median"));
						array_push($csvrow, $this->lng->txt("tst_stat_result_rank_participant"));
						array_push($csvrow, $this->lng->txt("tst_stat_result_rank_median"));
						array_push($csvrow, $this->lng->txt("tst_stat_result_total_participants"));
						array_push($csvrow, $this->lng->txt("tst_stat_result_median"));
						break;
				}
			}
			for ($i = 1; $i <= count($this->object->questions); $i++)
			{
				if ($i == 1)
				{
					$this->tpl->setCurrentBlock("questions_titlecol");
					$this->tpl->setVariable("TXT_TITLE", $name_column);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("questions_titlecol");
				$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("question_short") . " " . $i);
				$this->tpl->parseCurrentBlock();
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write(0, $column++, $this->object->getQuestionTitle($i), $format_title);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $this->object->getQuestionTitle($i));
						break;
				}
			}
			switch ($_POST["export_type"])
			{
				case TYPE_SPSS:
				case TYPE_PRINT:
					array_push($csvfile, $csvrow);
					break;
			}
			if ($_POST["cmd"]["stat_all_users"] or $_POST["stat_all_users"]) {
				$selected_users =& $this->object->evalTotalPersonsArray();
				$this->tpl->setCurrentBlock("hidden");
				$this->tpl->setVariable("NAME_HIDDEN", "stat_all_users");
				$this->tpl->setVariable("VALUE_HIDDEN", "1");
				$this->tpl->parseCurrentBlock();
			} else {
				$sel_users =& $this->object->evalTotalPersonsArray();
				$selected_users = array();
				foreach ($_POST as $key => $value)
				{
					if (preg_match("/chb_user_(\d+)/", $key, $matches)) {
						$selected_users[$matches[1]] = $sel_users[$matches[1]];
						$this->tpl->setCurrentBlock("hidden");
						$this->tpl->setVariable("NAME_HIDDEN", "chb_user_$matches[1]");
						$this->tpl->setVariable("VALUE_HIDDEN", "$matches[1]");
						$this->tpl->parseCurrentBlock();
					}
				}
			}
			$row = 0;
			$question_legend = false;
			$evaluation_array = array();
			foreach ($selected_users as $key => $value) {
				// receive array with statistical information on the test for a specific user
				$stat_eval =& $this->object->evalStatistical($key);
				$evaluation_array[$key] = $stat_eval;
			}

			require_once "./classes/class.ilStatistics.php";
			
			// calculate the median
			$median_array =& $this->object->getTotalPointsArray();
			$statistics = new ilStatistics();
			$statistics->setData($median_array);
			$median = $statistics->median();
			$passed_statistics = new ilStatistics();
			$passed_array =& $this->object->getTotalPointsPassedArray();
			$passed_statistics->setData($passed_array);
			$ects_percentiles = array
				(
					"A" => $passed_statistics->quantile($this->object->ects_grades["A"]),
					"B" => $passed_statistics->quantile($this->object->ects_grades["B"]),
					"C" => $passed_statistics->quantile($this->object->ects_grades["C"]),
					"D" => $passed_statistics->quantile($this->object->ects_grades["D"]),
					"E" => $passed_statistics->quantile($this->object->ects_grades["E"])
				);
			$evalcounter = 1;
			foreach ($evaluation_array as $key => $stat_eval)
			{
				$csvrow = array();
				if (!$question_legend)
				{
					$i = 1;
					foreach ($stat_eval as $key1 => $value1)
					{
						if (preg_match("/\d+/", $key1))
						{
							$this->tpl->setCurrentBlock("legendrow");
							$this->tpl->setVariable("TXT_SYMBOL", $this->lng->txt("question_short") . " " . $i);
							$this->tpl->setVariable("TXT_MEANING", $this->object->getQuestionTitle($value1["nr"]));
							$this->tpl->parseCurrentBlock();
							$i++;
						}
					}
					$question_legend = true;
				}
				$this->tpl->setCurrentBlock("datacol");
				$username = $evalcounter++; 
				if ($this->object->getTestType() != TYPE_SELF_ASSESSMENT)
				{
					$username = $selected_users[$key];
				}
				$this->tpl->setVariable("TXT_DATA", $username);
				$column = 0;
				$row++;
				switch ($_POST["export_type"])
				{
					case TYPE_XLS:
						$worksheet->write($row, $column++, $username);
						break;
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvrow, $username);
						break;
				}
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->parseCurrentBlock();
				if ($eval_statistical_settings["qworkedthrough"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $stat_eval["qworkedthrough"]);
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $stat_eval["qworkedthrough"]);
							break;
					case TYPE_SPSS:
					case TYPE_PRINT:
							array_push($csvrow, $stat_eval["qworkedthrough"]);
							break;
					}
				}
				if ($eval_statistical_settings["pworkedthrough"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", sprintf("%2.2f", $stat_eval["pworkedthrough"] * 100.0) . " %");
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $stat_eval["pworkedthrough"], $format_percent);
							break;
						case TYPE_SPSS:
							array_push($csvrow, $stat_eval["pworkedthrough"]);
							break;
						case TYPE_PRINT:
							array_push($csvrow, sprintf("%2.2f", $stat_eval["pworkedthrough"] * 100.0) . " %");
							break;
					}
				}
				if ($eval_statistical_settings["timeofwork"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$time = $stat_eval["timeofwork"];
					$time_seconds = $time;
					$time_hours    = floor($time_seconds/3600);
					$time_seconds -= $time_hours   * 3600;
					$time_minutes  = floor($time_seconds/60);
					$time_seconds -= $time_minutes * 60;
					$this->tpl->setVariable("TXT_DATA", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $time);
							break;
						case TYPE_SPSS:
							array_push($csvrow, $time);
							break;
						case TYPE_PRINT:
							array_push($csvrow, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
							break;
					}
				}
				if ($eval_statistical_settings["atimeofwork"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$time = $stat_eval["atimeofwork"];
					$time_seconds = $time;
					$time_hours    = floor($time_seconds/3600);
					$time_seconds -= $time_hours   * 3600;
					$time_minutes  = floor($time_seconds/60);
					$time_seconds -= $time_minutes * 60;
					$this->tpl->setVariable("TXT_DATA", sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $time);
							break;
						case TYPE_SPSS:
							array_push($csvrow, $time);
							break;
						case TYPE_PRINT:
							array_push($csvrow, sprintf("%02d:%02d:%02d", $time_hours, $time_minutes, $time_seconds));
							break;
					}
				}
				if ($eval_statistical_settings["firstvisit"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])));
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, ilUtil::excelTime($stat_eval["firstvisit"]["year"],$stat_eval["firstvisit"]["mon"],$stat_eval["firstvisit"]["mday"],$stat_eval["firstvisit"]["hours"],$stat_eval["firstvisit"]["minutes"],$stat_eval["firstvisit"]["seconds"]), $format_datetime);
							break;
						case TYPE_SPSS:
							array_push($csvrow, date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])));
							break;
						case TYPE_PRINT:
							array_push($csvrow, date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["firstvisit"]["hours"], $stat_eval["firstvisit"]["minutes"], $stat_eval["firstvisit"]["seconds"], $stat_eval["firstvisit"]["mon"], $stat_eval["firstvisit"]["mday"], $stat_eval["firstvisit"]["year"])));
							break;
					}
				}
				if ($eval_statistical_settings["lastvisit"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])));
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, ilUtil::excelTime($stat_eval["lastvisit"]["year"],$stat_eval["lastvisit"]["mon"],$stat_eval["lastvisit"]["mday"],$stat_eval["lastvisit"]["hours"],$stat_eval["lastvisit"]["minutes"],$stat_eval["lastvisit"]["seconds"]), $format_datetime);
							break;
						case TYPE_SPSS:
						case TYPE_PRINT:
							array_push($csvrow, date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], mktime($stat_eval["lastvisit"]["hours"], $stat_eval["lastvisit"]["minutes"], $stat_eval["lastvisit"]["seconds"], $stat_eval["lastvisit"]["mon"], $stat_eval["lastvisit"]["mday"], $stat_eval["lastvisit"]["year"])));
							break;
					}
				}
				if ($eval_statistical_settings["resultspoints"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $stat_eval["resultspoints"] . " " .
						strtolower($this->lng->txt("of")) . " " . $stat_eval["maxpoints"]);
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $stat_eval["resultspoints"]);
							break;
						case TYPE_SPSS:
							array_push($csvrow, $stat_eval["resultspoints"]);
							break;
						case TYPE_PRINT:
							array_push($csvrow, $stat_eval["resultspoints"]." ".strtolower($this->lng->txt("of"))." ". $stat_eval["maxpoints"]);
							break;
					}
				}
				if ($eval_statistical_settings["resultsmarks"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $stat_eval["resultsmarks"]);
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $stat_eval["resultsmarks"]);
							break;
						case TYPE_SPSS:
						case TYPE_PRINT:
							array_push($csvrow, $stat_eval["resultsmarks"]);
							break;
					}

					if ($this->object->ects_output)
					{
						$this->tpl->setCurrentBlock("datacol");
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						if ($stat_eval["resultspoints"] >= $ects_percentiles["A"])
						{
							$this->tpl->setVariable("TXT_DATA", "A");
						}
						else if ($stat_eval["resultspoints"] >= $ects_percentiles["B"])
						{
							$this->tpl->setVariable("TXT_DATA", "B");
						}
						else if ($stat_eval["resultspoints"] >= $ects_percentiles["C"])
						{
							$this->tpl->setVariable("TXT_DATA", "C");
						}
						else if ($stat_eval["resultspoints"] >= $ects_percentiles["D"])
						{
							$this->tpl->setVariable("TXT_DATA", "D");
						}
						else if ($stat_eval["resultspoints"] >= $ects_percentiles["E"])
						{
							$this->tpl->setVariable("TXT_DATA", "E");
						}
						else if (strcmp($this->object->ects_fx, "") != 0)
						{
							if ($stat_eval["maxpoints"] > 0)
							{
								$percentage = ($stat_eval["resultspoints"] / $stat_eval["maxpoints"]) * 100.0;
							}
							else
							{
								$percentage = 0.0;
							}
							if ($percentage >= $this->object->ects_fx)
							{
								$this->tpl->setVariable("TXT_DATA", "FX");
							}
							else
							{
								$this->tpl->setVariable("TXT_DATA", "F");
							}
						}
						else
						{
							$this->tpl->setVariable("TXT_DATA", "F");
						}
						$this->tpl->parseCurrentBlock();
						switch ($_POST["export_type"])
						{
							case TYPE_XLS:
								$worksheet->write($row, $column++, "");
								break;
							case TYPE_SPSS:
							case TYPE_PRINT:
								array_push($csvrow, "");
								break;
						}
					}
				}
				
				if ($eval_statistical_settings["distancemedian"]) {
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					if ($stat_eval["maxpoints"] == 0)
					{
						$pct = 0;
					}
					else
					{
						$pct = ($median / $stat_eval["maxpoints"]) * 100.0;
					}
					$mark = $this->object->mark_schema->get_matching_mark($pct);
					$this->tpl->setVariable("TXT_DATA", $mark->get_short_name());
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$rank_participant = $statistics->rank($stat_eval["resultspoints"]);
					$this->tpl->setVariable("TXT_DATA", $rank_participant);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$rank_median = $statistics->rank_median();
					$this->tpl->setVariable("TXT_DATA", $rank_median);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$total_participants = count($median_array);
					$this->tpl->setVariable("TXT_DATA", $total_participants);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $median);
					$this->tpl->parseCurrentBlock();
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $mark->get_short_name());
							$worksheet->write($row, $column++, $rank_participant);
							$worksheet->write($row, $column++, $rank_median);
							$worksheet->write($row, $column++, $total_participants);
							$worksheet->write($row, $column++, $median);
							break;
						case TYPE_SPSS:
						case TYPE_PRINT:
							array_push($csvrow, $mark->get_short_name());
							array_push($csvrow, $rank_participant);
							array_push($csvrow, $rank_median);
							array_push($csvrow, $total_participants);
							array_push($csvrow, $median);
							break;
					}
				}
				
				$this->tpl->setCurrentBlock("row");
				$this->tpl->setVariable("USER_ID", $key);
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->parseCurrentBlock();

				for ($i = 1; $i <= count($this->object->questions); $i++)
				{
					if ($i == 1)
					{
						$this->tpl->setCurrentBlock("questions_datacol");
						$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
						$this->tpl->setVariable("TXT_DATA", $username);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("questions_datacol");
					$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
					$this->tpl->setVariable("TXT_DATA", $stat_eval[$i-1]["reached"] . " " . strtolower($this->lng->txt("of")) . " " .  $stat_eval[$i-1]["max"]);
					switch ($_POST["export_type"])
					{
						case TYPE_XLS:
							$worksheet->write($row, $column++, $stat_eval[$i-1]["reached"]);
							break;
						case TYPE_SPSS:
							array_push($csvrow, $stat_eval[$i-1]["reached"]);
							break;
						case TYPE_PRINT:
							array_push($csvrow, $stat_eval[$i-1]["reached"] . " " . strtolower($this->lng->txt("of")) . " " .  $stat_eval[$i-1]["max"]);
							break;
					}
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("questions_row");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->parseCurrentBlock();
				$counter++;
				switch ($_POST["export_type"])
				{
					case TYPE_SPSS:
					case TYPE_PRINT:
						array_push($csvfile, $csvrow);
						break;
				}
			}

			$this->tpl->setCurrentBlock("questions_output");
			$this->tpl->setVariable("TXT_QUESTIONS",  $this->lng->txt("ass_questions"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("export_btn");
			$this->tpl->setVariable("EXPORT_DATA", $this->lng->txt("exp_eval_data"));
			$this->tpl->setVariable("TEXT_EXCEL", $this->lng->txt("exp_type_excel"));
			$this->tpl->setVariable("TEXT_CSV", $this->lng->txt("exp_type_spss"));
			$this->tpl->setVariable("BTN_EXPORT", $this->lng->txt("export"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("legend");
			$this->tpl->setVariable("TXT_LEGEND", $this->lng->txt("legend"));
			$this->tpl->setVariable("TXT_LEGEND_LINK", $this->lng->txt("eval_legend_link"));
			$this->tpl->setVariable("TXT_SYMBOL", $this->lng->txt("symbol"));
			$this->tpl->setVariable("TXT_MEANING", $this->lng->txt("meaning"));
			$this->tpl->parseCurrentBlock();

			if ($_POST["export_type"]==TYPE_PRINT)
			{
				$csvfile["nr_of_questions"] = count($this->object->questions);
				$_SESSION["print_eval"] = $csvfile;
				$this->tpl->setCurrentBlock("print_block");
				$this->tpl->setVariable("PRINT_ACTION", $_SERVER['PHP_SELF'] . "?ref_id=" . $_GET["ref_id"] . "&cmd=printEvaluation");
				$this->tpl->setVariable("PRINT_TEXT", $this->lng->txt("print"));
				$this->tpl->setVariable("PRINT_IMAGE", ilUtil::getImagePath("icon_print.gif"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("output");
			$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $add_parameter);
			$this->tpl->parseCurrentBlock();

		}
			else
		{
			if ($this->object->getTestType() != TYPE_SELF_ASSESSMENT)
			{
				$total_persons =& $this->object->evalTotalPersonsArray();
				foreach ($total_persons as $user_id => $user_name)
				{
					$this->tpl->setCurrentBlock("userrow");
					$this->tpl->setVariable("ID_USER", $user_id);
					$this->tpl->setVariable("TXT_USER_NAME", $user_name);
					$this->tpl->parseCurrentBlock();
				}
			}
			if ($this->object->getTestType() != TYPE_SELF_ASSESSMENT)
			{
				$this->tpl->setCurrentBlock("selected_users");
				$this->tpl->setVariable("TXT_STAT_USERS_INTRO_SELECTED", $this->lng->txt("tst_stat_users_intro"));
				$this->tpl->setVariable("TXT_STAT_SELECTED_USERS", $this->lng->txt("tst_stat_selected_users"));
				$this->tpl->setVariable("TXT_STAT_CHOOSE_USERS", $this->lng->txt("tst_stat_choose_users"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("userselect");
			$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $add_parameter);
			$this->tpl->setVariable("TXT_STAT_USERS_INTRO", $this->lng->txt("tst_stat_users_intro"));
			$this->tpl->setVariable("TXT_STAT_ALL_USERS", $this->lng->txt("tst_stat_all_users"));
			$this->tpl->setVariable("TXT_QWORKEDTHROUGH", $this->lng->txt("tst_stat_result_qworkedthrough"));
			$this->tpl->setVariable("TXT_PWORKEDTHROUGH", $this->lng->txt("tst_stat_result_pworkedthrough"));
			$this->tpl->setVariable("TXT_TIMEOFWORK", $this->lng->txt("tst_stat_result_timeofwork"));
			$this->tpl->setVariable("TXT_ATIMEOFWORK", $this->lng->txt("tst_stat_result_atimeofwork"));
			$this->tpl->setVariable("TXT_FIRSTVISIT", $this->lng->txt("tst_stat_result_firstvisit"));
			$this->tpl->setVariable("TXT_LASTVISIT", $this->lng->txt("tst_stat_result_lastvisit"));
			$this->tpl->setVariable("TXT_RESULTSPOINTS", $this->lng->txt("tst_stat_result_resultspoints"));
			$this->tpl->setVariable("TXT_RESULTSMARKS", $this->lng->txt("tst_stat_result_resultsmarks"));
			$this->tpl->setVariable("TXT_DISTANCEMEDIAN", $this->lng->txt("tst_stat_result_distancemedian"));
			$this->tpl->setVariable("TXT_SPECIFICATION", $this->lng->txt("tst_stat_result_specification"));
			$user_settings = $this->object->evalLoadStatisticalSettings($ilUser->id);
			foreach ($user_settings as $key => $value) {
				if ($value == 1) {
					$user_settings[$key] = " checked=\"checked\"";
				} else {
					$user_settings[$key] = "";
				}
			}
			$this->tpl->setVariable("CHECKED_QWORKEDTHROUGH", $user_settings["qworkedthrough"]);
			$this->tpl->setVariable("CHECKED_PWORKEDTHROUGH", $user_settings["pworkedthrough"]);
			$this->tpl->setVariable("CHECKED_TIMEOFWORK", $user_settings["timeofwork"]);
			$this->tpl->setVariable("CHECKED_ATIMEOFWORK", $user_settings["atimeofwork"]);
			$this->tpl->setVariable("CHECKED_FIRSTVISIT", $user_settings["firstvisit"]);
			$this->tpl->setVariable("CHECKED_LASTVISIT", $user_settings["lastvisit"]);
			$this->tpl->setVariable("CHECKED_RESULTSPOINTS", $user_settings["resultspoints"]);
			$this->tpl->setVariable("CHECKED_RESULTSMARKS", $user_settings["resultsmarks"]);
			$this->tpl->setVariable("CHECKED_DISTANCEMEDIAN", $user_settings["distancemedian"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
		$this->tpl->parseCurrentBlock();


		switch ($_POST["export_type"])
		{
			case TYPE_XLS:
				$workbook->close();
				exit;
			case TYPE_SPSS:
				$csv = "";
				foreach ($csvfile as $csvrow)
				{
					$csv .= join($csvrow, ",") . "\n";
				}
				ilUtil::deliverData($csv, "$testname.csv");
				exit();
		}
	}

	function eval_aObject()
	{
		global $ilUser;

		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.il_as_tst_content.html", true);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();
		$this->setLocator();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_eval_anonymous_aggregation.html", true);
		$total_persons = $this->object->evalTotalPersons();
		if ($total_persons) {
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_persons"));
			$this->tpl->setVariable("TXT_VALUE", $total_persons);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished"));
			$total_finished = $this->object->evalTotalFinished();
			$this->tpl->setVariable("TXT_VALUE", $total_finished);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$average_time = $this->object->evalTotalFinishedAverageTime();
			$diff_seconds = $average_time;
			$diff_hours    = floor($diff_seconds/3600);
			$diff_seconds -= $diff_hours   * 3600;
			$diff_minutes  = floor($diff_seconds/60);
			$diff_seconds -= $diff_minutes * 60;
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_finished_average_time"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%02d:%02d:%02d", $diff_hours, $diff_minutes, $diff_seconds));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$passed_tests = $this->object->evalTotalFinishedPassed();
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed"));
			$this->tpl->setVariable("TXT_VALUE", $passed_tests["total_passed"]);
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("tst_eval_total_passed_average_points"));
			$this->tpl->setVariable("TXT_VALUE", sprintf("%2.2f", $passed_tests["average_points"]) . " " . strtolower($this->lng->txt("of")) . " " . sprintf("%2.2f", $passed_tests["maximum_points"]));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$counter++;
			$this->tpl->parseCurrentBlock();
		} else {
			$this->tpl->setCurrentBlock("emptyrow");
			$this->tpl->setVariable("TXT_NO_ANONYMOUS_AGGREGATION", $this->lng->txt("tst_eval_no_anonymous_aggregation"));
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_ANON_EVAL", $this->lng->txt("tst_anon_eval"));
		$this->tpl->setVariable("TXT_RESULT", $this->lng->txt("result"));
		$this->tpl->setVariable("TXT_VALUE", $this->lng->txt("value"));
		$this->tpl->parseCurrentBlock();
	}

/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function confirmDeleteResults() 
	{
		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_delete_results_confirm.html", true);
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_CONFIRM_DELETE_RESULTS", $this->lng->txt("tst_confirm_delete_results"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_OK", $this->lng->txt("tst_delete_results"));
		$this->tpl->setVariable("FORM_ACTION", $_SERVER["PHP_SELF"] . $add_parameter);
		$this->tpl->parseCurrentBlock();
	}

/**
* Output of the learners view of an existing test
*
* Output of the learners view of an existing test
*
* @access public
*/
	function outTestResults() {
		global $ilUser;

		function sort_percent($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["percent"] == $b["percent"]) {
				if ($a["nr"] == $b["nr"]) return 0;
		 	 	return ($a["nr"] < $b["nr"]) ? -1 : 1;
			}
			return ($a["percent"] < $b["percent"]) ? $smaller : $greater;
		}

		function sort_nr($a, $b) {
			if (strcmp($_GET["order"], "ASC")) {
				$smaller = 1;
				$greater = -1;
			} else {
				$smaller = -1;
				$greater = 1;
			}
			if ($a["nr"] == $b["nr"]) return 0;
			return ($a["nr"] < $b["nr"]) ? $smaller : $greater;
		}

		$add_parameter = $this->getAddParameter();
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_finish.html", true);
		$user_id = $ilUser->id;
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		$this->tpl->addBlockFile("TEST_RESULTS", "results", "tpl.il_as_tst_results.html", true);
		$result_array =& $this->object->getTestResult($user_id);
		if (!$result_array["test"]["total_max_points"])
		{
			$percentage = 0;
		}
		else
		{
			$percentage = ($result_array["test"]["total_reached_points"]/$result_array["test"]["total_max_points"])*100;
		}
		$total_max = $result_array["test"]["total_max_points"];
		$total_reached = $result_array["test"]["total_reached_points"];
		$img_title_percent = "";
		$img_title_nr = "";
		switch ($_GET["sortres"]) {
			case "percent":
				usort($result_array, "sort_percent");
				$img_title_percent = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortpercent = "DESC";
				} else {
					$sortpercent = "ASC";
				}
				break;
			case "nr":
				usort($result_array, "sort_nr");
				$img_title_nr = " <img src=\"" . ilUtil::getImagePath(strtolower($_GET["order"]) . "_order.png", true) . "\" alt=\"\" />";
				if (strcmp($_GET["order"], "ASC") == 0) {
					$sortnr = "DESC";
				} else {
					$sortnr = "ASC";
				}
				break;
		}
		if (!$sortpercent) {
			$sortpercent = "ASC";
		}
		if (!$sortnr) {
			$sortnr = "ASC";
		}

		foreach ($result_array as $key => $value) {
			if (preg_match("/\d+/", $key)) {
				$this->tpl->setCurrentBlock("question");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("VALUE_QUESTION_COUNTER", $value["nr"]);
				$this->tpl->setVariable("VALUE_QUESTION_TITLE", $value["title"]);
				$this->tpl->setVariable("VALUE_MAX_POINTS", $value["max"]);
				$this->tpl->setVariable("VALUE_REACHED_POINTS", $value["reached"]);
				if ($value["solution"])
				{
					$this->tpl->setVariable("SOLUTION_HINT", "<a href=\"" . ILIAS_HTTP_PATH . "/content/lm_presentation.php?ref_id=" . $value["solution"] . "\" target=\"content\">" . $this->lng->txt("solution_hint"). "</a>");
				}
				else
				{
					$this->tpl->setVariable("SOLUTION_HINT", "");
				}
				$this->tpl->setVariable("VALUE_PERCENT_SOLVED", $value["percent"]);
				$this->tpl->parseCurrentBlock();
				$counter++;
			}
		}

		$this->tpl->setCurrentBlock("question");
		$this->tpl->setVariable("COLOR_CLASS", "std");
		$this->tpl->setVariable("VALUE_QUESTION_COUNTER", "<strong>" . $this->lng->txt("total") . "</strong>");
		$this->tpl->setVariable("VALUE_QUESTION_TITLE", "");
		$this->tpl->setVariable("SOLUTION_HINT", "");
		$this->tpl->setVariable("VALUE_MAX_POINTS", "<strong>" . sprintf("%d", $total_max) . "</strong>");
		$this->tpl->setVariable("VALUE_REACHED_POINTS", "<strong>" . sprintf("%d", $total_reached) . "</strong>");
		$this->tpl->setVariable("VALUE_PERCENT_SOLVED", "<strong>" . sprintf("%2.2f", $percentage) . " %" . "</strong>");
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("results");
		$this->tpl->setVariable("QUESTION_COUNTER", "<a href=\"" . $_SERVER['PHP_SELF'] . "$add_parameter&sortres=nr&order=$sortnr\">" . $this->lng->txt("tst_question_no") . "</a>$img_title_nr");
		$this->tpl->setVariable("QUESTION_TITLE", $this->lng->txt("tst_question_title"));
		$this->tpl->setVariable("SOLUTION_HINT_HEADER", $this->lng->txt("solution_hint"));
		$this->tpl->setVariable("MAX_POINTS", $this->lng->txt("tst_maximum_points"));
		$this->tpl->setVariable("REACHED_POINTS", $this->lng->txt("tst_reached_points"));
		$this->tpl->setVariable("PERCENT_SOLVED", "<a href=\"" . $_SERVER['PHP_SELF'] . "$add_parameter&sortres=percent&order=$sortpercent\">" . $this->lng->txt("tst_percent_solved") . "</a>$img_title_percent");
		$mark_obj = $this->object->mark_schema->get_matching_mark($percentage);
		if ($mark_obj->get_passed()) {
			$mark = $this->lng->txt("tst_result_congratulations");
		} else {
			$mark = $this->lng->txt("tst_result_sorry");
		}
		$mark .= "<br />" . $this->lng->txt("tst_your_mark_is") . ": &quot;" . $mark_obj->get_official_name() . "&quot;";
		$this->tpl->setVariable("USER_FEEDBACK", $mark);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_RESULTS", $this->lng->txt("tst_results"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Creates the maintenance form for a test
	*
	* Creates the maintenance form for a test
	*
	* @access	public
	*/
	function maintenanceObject()
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id)) {
			if ($_POST["cmd"]["delete_all_user_data"])
			{
				$this->object->removeAllTestEditings();
				sendInfo($this->lng->txt("tst_all_user_data_deleted"));
			}
			$add_parameter = $this->getAddParameter();
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_maintenance.html", true);
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("BTN_DELETE_ALL", $this->lng->txt("tst_delete_all_user_data"));
			$this->tpl->setVariable("FORM_ACTION", $_SERVER['PHP_SELF'] . $add_parameter);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			sendInfo($this->lng->txt("cannot_maintain_test"));
		}
	}	

	/**
	* Creates the status output for a test
	*
	* Creates the status output for a test
	*
	* @access	public
	*/
	function statusObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_status.html", true);
		if (!$this->object->isComplete())
		{
			if (count($this->object->questions) == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_questions"));
				$this->tpl->parseCurrentBlock();
			}
			if (count($this->object->mark_schema->mark_steps) == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_marks"));
				$this->tpl->parseCurrentBlock();
			}
			if (strcmp($this->object->author, "") == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_author"));
				$this->tpl->parseCurrentBlock();
			}
			if (strcmp($this->object->title, "") == 0)
			{
				$this->tpl->setCurrentBlock("list_element");
				$this->tpl->setVariable("TEXT_ELEMENT", $this->lng->txt("tst_missing_author"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("status_list");
			$this->tpl->setVariable("TEXT_MISSING_ELEMENTS", $this->lng->txt("tst_status_missing_elements"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		if ($this->object->isComplete())
		{
			$this->tpl->setVariable("TEXT_STATUS_MESSAGE", $this->lng->txt("tst_status_ok"));
			$this->tpl->setVariable("STATUS_CLASS", "bold");
		}
		else
		{
			$this->tpl->setVariable("TEXT_STATUS_MESSAGE", $this->lng->txt("tst_status_missing"));
			$this->tpl->setVariable("STATUS_CLASS", "warning");
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
				$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/adm_object.php?ref_id=".$row["child"],"target=\"bottom\"");
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
					$ilias_locator->navigate($i++, $row["title"], ILIAS_HTTP_PATH . "/assessment/test.php" . "?ref_id=".$row["child"] . $param,"target=\"bottom\"");
					if ($this->sequence) {
						if (($this->sequence <= $this->object->getQuestionCount()) and (!$_POST["cmd"]["showresults"])) {
							$ilias_locator->navigate($i++, $this->object->getQuestionTitle($this->sequence), ILIAS_HTTP_PATH . "/assessment/test.php" . "?ref_id=".$row["child"] . $param . "&sequence=" . $this->sequence,"target=\"bottom\"");
						} else {
						}
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
			// rolefolder does not exist, so create one
			if (empty($rolf_id))
			{
				// create a local role folder
				$rfoldObj = $this->object->createRoleFolder();

				// set rolf_id again from new rolefolder object
				$rolf_id = $rfoldObj->getRefId();
			}

			// CHECK ACCESS 'write' of role folder
			if (!$rbacsystem->checkAccess("write",$rolf_id))
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

		ilUtil::redirect($this->getReturnLocation("permSave","assessment/test.php?ref_id=".$_GET["ref_id"]."&cmd=perm"));

	}

} // END class.ilObjTestGUI

?>
