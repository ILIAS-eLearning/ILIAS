<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjAssessmentFolderGUI
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjAssessmentFolderGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/
class ilObjAssessmentFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	var $conditions;

	function ilObjAssessmentFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $rbacsystem;

		$this->type = "assf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);

		if (!$rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"),$this->ilias->error_obj->WARNING);
		}
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == "" || $cmd == "view")
				{
					$cmd = "settings";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
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
		ilUtil::sendInfo($this->lng->txt("object_added"),true);

		$this->ctrl->redirect($this);
	}


	/**
	* display assessment folder settings form
	*/
	function settingsObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.assessment_settings.html");
	
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$questiontypes =& ilObjQuestionPool::_getQuestionTypes(TRUE);
		$manscoring = $this->object->_getManualScoring();
		foreach ($questiontypes as $type_name => $qtype)
		{
			$type_id = $qtype["question_type_id"];
			$this->tpl->setCurrentBlock("manual_scoring");
			$this->tpl->setVariable("VALUE_MANUAL_SCORING", $type_id);
			$this->tpl->setVariable("TXT_MANUAL_SCORING", $type_name);
			if (in_array($type_id, $manscoring))
			{
				$this->tpl->setVariable("CHECKED_MANUAL_SCORING", " checked=\"checked\"");
			}
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("allowed_questiontypes");
			$this->tpl->setVariable("VALUE_ALLOWED_QUESTIONTYPES", $type_id);
			$this->tpl->setVariable("TEXT_ALLOWED_QUESTIONTYPES", $type_name);
			$forbidden_types = $this->object->_getForbiddenQuestionTypes();
			if (!in_array($type_id, $forbidden_types))
			{
				$this->tpl->setVariable("CHECKED_ALLOWED_QUESTIONTYPES", " checked=\"checked\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_LOGGING", $this->lng->txt("assessment_log_logging"));
		$this->tpl->setVariable("TXT_ACTIVATE_ASSESSMENT_LOGGING", $this->lng->txt("activate_assessment_logging"));
		$this->tpl->setVariable("TXT_ASSESSMENT_SETTINGS", $this->lng->txt("assessment_settings"));
		$this->tpl->setVariable("TXT_REPORTING_LANGUAGE", $this->lng->txt("assessment_settings_reporting_language"));
		$languages = $this->lng->getInstalledLanguages();
		$default_language = $this->object->_getLogLanguage();
		if (!in_array($default_language, $languages))
		{
			$default_language = "en";
		}
		foreach ($languages as $key)
		{
			$this->tpl->setCurrentBlock("reporting_lang_row");
			$this->tpl->setVariable("LANG_VALUE", $key);
			$this->tpl->setVariable("LANG_NAME", $this->lng->txt("lang_" . $key));
			if (strcmp($default_language, $key) == 0)
			{
				$this->tpl->setVariable("LANG_SELECTED", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("TXT_QUESTIONTYPES_HEADER", $this->lng->txt("assf_questiontypes"));
		$this->tpl->setVariable("TXT_ALLOWED_QUESTIONTYPES", $this->lng->txt("assf_allowed_questiontypes"));
		$this->tpl->setVariable("TXT_ALLOWED_QUESTIONTYPES_DESCRIPTION", $this->lng->txt("assf_allowed_questiontypes_desc"));
		$this->tpl->setVariable("TXT_MANUAL_SCORING_DESCRIPTION", $this->lng->txt("assessment_log_manual_scoring_desc"));
		$this->tpl->setVariable("TXT_MANUAL_SCORING_ACTIVATE", $this->lng->txt("assessment_log_manual_scoring_activate"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		if($this->object->_enabledAssessmentLogging())
		{
			$this->tpl->setVariable("ASSESSMENT_LOGGING_CHECKED", " checked=\"checked\"");
		}

		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Save Assessment settings
	*/
	function saveSettingsObject()
	{
		if ($_POST["chb_assessment_logging"] == 1)
		{
			$this->object->_enableAssessmentLogging(1);
		}
		else
		{
			$this->object->_enableAssessmentLogging(0);
		}
		$this->object->_setLogLanguage($_POST["reporting_language"]);
		$this->object->_setManualScoring($_POST["chb_manual_scoring"]);
		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		$questiontypes =& ilObjQuestionPool::_getQuestionTypes(TRUE);
		$forbidden_types = array();
		foreach ($questiontypes as $name => $row)
		{
			if (!in_array($row["question_type_id"], $_POST["chb_allowed_questiontypes"]))
			{
				array_push($forbidden_types, $row["question_type_id"]);
			}
		}
		$this->object->_setForbiddenQuestionTypes($forbidden_types);
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->redirect($this,'settings');
	}
	
	/**
	* Called when the a log should be shown
	*/
	function showLogObject()
	{
		$this->logsObject();
	}
	
	/**
	* Called when the a log should be exported
	*/
	function exportLogObject()
	{
		$this->logsObject();
	}

	/**
	* display assessment folder logs form
	*/
	function logsObject()
	{
		$this->lng->loadLanguageModule("jscalendar");
		$this->tpl->addBlockFile("CALENDAR_LANG_JAVASCRIPT", "calendar_javascript", "tpl.calendar.html");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.assessment_logs.html");
		$this->tpl->setCurrentBlock("calendar_javascript");
		$this->tpl->setVariable("FULL_SUNDAY", $this->lng->txt("l_su"));
		$this->tpl->setVariable("FULL_MONDAY", $this->lng->txt("l_mo"));
		$this->tpl->setVariable("FULL_TUESDAY", $this->lng->txt("l_tu"));
		$this->tpl->setVariable("FULL_WEDNESDAY", $this->lng->txt("l_we"));
		$this->tpl->setVariable("FULL_THURSDAY", $this->lng->txt("l_th"));
		$this->tpl->setVariable("FULL_FRIDAY", $this->lng->txt("l_fr"));
		$this->tpl->setVariable("FULL_SATURDAY", $this->lng->txt("l_sa"));
		$this->tpl->setVariable("SHORT_SUNDAY", $this->lng->txt("s_su"));
		$this->tpl->setVariable("SHORT_MONDAY", $this->lng->txt("s_mo"));
		$this->tpl->setVariable("SHORT_TUESDAY", $this->lng->txt("s_tu"));
		$this->tpl->setVariable("SHORT_WEDNESDAY", $this->lng->txt("s_we"));
		$this->tpl->setVariable("SHORT_THURSDAY", $this->lng->txt("s_th"));
		$this->tpl->setVariable("SHORT_FRIDAY", $this->lng->txt("s_fr"));
		$this->tpl->setVariable("SHORT_SATURDAY", $this->lng->txt("s_sa"));
		$this->tpl->setVariable("FULL_JANUARY", $this->lng->txt("l_01"));
		$this->tpl->setVariable("FULL_FEBRUARY", $this->lng->txt("l_02"));
		$this->tpl->setVariable("FULL_MARCH", $this->lng->txt("l_03"));
		$this->tpl->setVariable("FULL_APRIL", $this->lng->txt("l_04"));
		$this->tpl->setVariable("FULL_MAY", $this->lng->txt("l_05"));
		$this->tpl->setVariable("FULL_JUNE", $this->lng->txt("l_06"));
		$this->tpl->setVariable("FULL_JULY", $this->lng->txt("l_07"));
		$this->tpl->setVariable("FULL_AUGUST", $this->lng->txt("l_08"));
		$this->tpl->setVariable("FULL_SEPTEMBER", $this->lng->txt("l_09"));
		$this->tpl->setVariable("FULL_OCTOBER", $this->lng->txt("l_10"));
		$this->tpl->setVariable("FULL_NOVEMBER", $this->lng->txt("l_11"));
		$this->tpl->setVariable("FULL_DECEMBER", $this->lng->txt("l_12"));
		$this->tpl->setVariable("SHORT_JANUARY", $this->lng->txt("s_01"));
		$this->tpl->setVariable("SHORT_FEBRUARY", $this->lng->txt("s_02"));
		$this->tpl->setVariable("SHORT_MARCH", $this->lng->txt("s_03"));
		$this->tpl->setVariable("SHORT_APRIL", $this->lng->txt("s_04"));
		$this->tpl->setVariable("SHORT_MAY", $this->lng->txt("s_05"));
		$this->tpl->setVariable("SHORT_JUNE", $this->lng->txt("s_06"));
		$this->tpl->setVariable("SHORT_JULY", $this->lng->txt("s_07"));
		$this->tpl->setVariable("SHORT_AUGUST", $this->lng->txt("s_08"));
		$this->tpl->setVariable("SHORT_SEPTEMBER", $this->lng->txt("s_09"));
		$this->tpl->setVariable("SHORT_OCTOBER", $this->lng->txt("s_10"));
		$this->tpl->setVariable("SHORT_NOVEMBER", $this->lng->txt("s_11"));
		$this->tpl->setVariable("SHORT_DECEMBER", $this->lng->txt("s_12"));
		$this->tpl->setVariable("ABOUT_CALENDAR", $this->lng->txt("about_calendar"));
		$this->tpl->setVariable("ABOUT_CALENDAR_LONG", $this->lng->txt("about_calendar_long"));
		$this->tpl->setVariable("ABOUT_TIME_LONG", $this->lng->txt("about_time"));
		$this->tpl->setVariable("PREV_YEAR", $this->lng->txt("prev_year"));
		$this->tpl->setVariable("PREV_MONTH", $this->lng->txt("prev_month"));
		$this->tpl->setVariable("GO_TODAY", $this->lng->txt("go_today"));
		$this->tpl->setVariable("NEXT_MONTH", $this->lng->txt("next_month"));
		$this->tpl->setVariable("NEXT_YEAR", $this->lng->txt("next_year"));
		$this->tpl->setVariable("SEL_DATE", $this->lng->txt("select_date"));
		$this->tpl->setVariable("DRAG_TO_MOVE", $this->lng->txt("drag_to_move"));
		$this->tpl->setVariable("PART_TODAY", $this->lng->txt("part_today"));
		$this->tpl->setVariable("DAY_FIRST", $this->lng->txt("day_first"));
		$this->tpl->setVariable("CLOSE", $this->lng->txt("close"));
		$this->tpl->setVariable("TODAY", $this->lng->txt("today"));
		$this->tpl->setVariable("TIME_PART", $this->lng->txt("time_part"));
		$this->tpl->setVariable("DEF_DATE_FORMAT", $this->lng->txt("def_date_format"));
		$this->tpl->setVariable("TT_DATE_FORMAT", $this->lng->txt("tt_date_format"));
		$this->tpl->setVariable("WK", $this->lng->txt("wk"));
		$this->tpl->setVariable("TIME", $this->lng->txt("time"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("CalendarJS");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR", "./Modules/Test/js/calendar/calendar.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_SETUP", "./Modules/Test/js/calendar/calendar-setup.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_STYLESHEET", "./Modules/Test/js/calendar/calendar.css");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("javascript_call_calendar");
		$this->tpl->setVariable("INPUT_FIELDS_STARTING_DATE", "starting_date");
		$this->tpl->setVariable("INPUT_FIELDS_ENDING_DATE", "ending_date");
		$this->tpl->setVariable("INPUT_FIELDS_REPORTING_DATE", "reporting_date");
		$this->tpl->parseCurrentBlock();
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$available_tests =& ilObjTest::_getAvailableTests(1);
		foreach ($available_tests as $key => $value)
		{
			$this->tpl->setCurrentBlock("sel_test_row");
			$this->tpl->setVariable("TXT_OPTION", ilUtil::prepareFormOutput($value) . " [" . $this->object->getNrOfLogEntries($key) . " " . $this->lng->txt("assessment_log_log_entries") . "]");
			$this->tpl->setVariable("VALUE_OPTION", $key);
			if (($_POST["sel_test"] > -1) && ($_POST["sel_test"] == $key))
			{
				$this->tpl->setVariable("SELECTED_OPTION", " selected=\"selected\"");
			}
			$this->tpl->parseCurrentBlock();
		}
		
		if ((strcmp($this->ctrl->getCmd(), "showLog") == 0) ||
			(strcmp($this->ctrl->getCmd(), "exportLog") == 0))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$separator = ";";
			$csv = array();
			if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
			{
				$row = array(
						$this->lng->txt("assessment_log_datetime"),
						$this->lng->txt("user"),
						$this->lng->txt("assessment_log_text"),
						$this->lng->txt("question")
				);
				array_push($csv, ilUtil::processCSVRow($row, TRUE, $separator));
			}
			$ts_from = mktime($_POST["log_from_time"]["h"], $_POST["log_from_time"]["m"], 0, $_POST["log_from_date"]["m"], $_POST["log_from_date"]["d"], $_POST["log_from_date"]["y"]);
			$ts_to = mktime($_POST["log_to_time"]["h"], $_POST["log_to_time"]["m"], 0, $_POST["log_to_date"]["m"], $_POST["log_to_date"]["d"], $_POST["log_to_date"]["y"]);
			$log_output =& $this->object->getLog($ts_from, $ts_to, $_POST["sel_test"]);
			$users = array();
			foreach ($log_output as $key => $log)
			{
				if (array_key_exists("value1", $log))
				{
					$tblrow = array("tblrow1light", "tblrow2light");
				}
				else
				{
					$tblrow = array("tblrow1", "tblrow2");
				}
				$title = "";
				if (!array_key_exists($log["user_fi"], $users))
				{
					$users[$log["user_fi"]] = ilObjUser::_lookupName($log["user_fi"]);
				}
				$this->tpl->setCurrentBlock("output_row");
				$this->tpl->setVariable("ROW_CLASS", $tblrow[$key % 2]);
				$date = new ilDateTime($log["timestamp14"],IL_CAL_TIMESTAMP);
				$this->tpl->setVariable("TXT_DATETIME",$date->get(IL_CAL_FKT_DATE,'Y-m-d H:i'));
				$csvrow = array();
				if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
				{
					$date = new ilDate($log['timestamp14'],IL_CAL_TIMESTAMP);
					array_push($csvrow, $date->get(IL_CAL_FKT_DATE,'Y-m-d H:i'));
					
				}
				if ($log["question_fi"] || $log["original_fi"])
				{
					$title = assQuestion::_getQuestionTitle($log["question_fi"]);
					if (strlen($title) == 0)
					{
						$title = assQuestion::_getQuestionTitle($log["original_fi"]);
					}
					$title = $this->lng->txt("assessment_log_question") . ": " . $title;
				}
				$this->tpl->setVariable("TXT_USER", trim($users[$log["user_fi"]]["title"] . " " . $users[$log["user_fi"]]["firstname"] . " " . $users[$log["user_fi"]]["lastname"]));
				if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
				{
					array_push($csvrow, trim($users[$log["user_fi"]]["title"] . " " . $users[$log["user_fi"]]["firstname"] . " " . $users[$log["user_fi"]]["lastname"]));
				}
				if (array_key_exists("value1", $log))
				{
					if (strlen($title))
					{
						$this->tpl->setVariable("TXT_LOGTEXT", ilUtil::prepareFormOutput($this->lng->txt("assessment_log_user_answer") . " (" . $title . ")"));
					}
					else
					{
						$this->tpl->setVariable("TXT_LOGTEXT", ilUtil::prepareFormOutput($this->lng->txt("assessment_log_user_answer")));
					}
					if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
					{
						array_push($csvrow, $this->lng->txt("assessment_log_user_answer"));
						array_push($csvrow, $title);
					}
				}
				else
				{
					if (strlen($title))
					{
						$this->tpl->setVariable("TXT_LOGTEXT", trim(ilUtil::prepareFormOutput($log["logtext"]) . " (" . $title . ")"));
					}
					else
					{
						$this->tpl->setVariable("TXT_LOGTEXT", trim(ilUtil::prepareFormOutput($log["logtext"])));
					}
					if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
					{
						array_push($csvrow, trim($log["logtext"]));
						array_push($csvrow, $title);
					}
				}
				$this->tpl->parseCurrentBlock();
				if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
				{
					array_push($csv, ilUtil::processCSVRow($csvrow, TRUE, $separator));
				}
			}
			if (count($log_output) == 0)
			{
				$this->tpl->setCurrentBlock("empty_row");
				$this->tpl->setVariable("TXT_NOLOG", $this->lng->txt("assessment_log_no_log"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
				{
					$csvoutput = "";
					foreach ($csv as $row)
					{
						$csvoutput .= join($row, $separator) . "\n";
					}
					ilUtil::deliverData($csvoutput, str_replace(" ", "_", "log_" . $ts_from . "_" . $ts_to . "_" . $available_tests[$_POST["sel_test"]]).".csv");
					return;
				}
			}
			$this->tpl->setCurrentBlock("log_output");
			$this->tpl->setVariable("HEADER_DATETIME", $this->lng->txt("assessment_log_datetime"));
			$this->tpl->setVariable("HEADER_USER", $this->lng->txt("user"));
			$this->tpl->setVariable("HEADER_LOGTEXT", $this->lng->txt("assessment_log_text"));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_ASSESSMENT_LOG", $this->lng->txt("assessment_log"));
		$this->tpl->setVariable("TXT_LOG_FROM", $this->lng->txt("from"));
		if (!is_array($_POST["log_from_date"]))
		{
			$date_input = ilUtil::makeDateSelect("log_from_date", "", "1", "1", 2004);
			$time_input = ilUtil::makeTimeSelect("log_from_time", TRUE, 0, 0);
		}
		else
		{
			$date_input = ilUtil::makeDateSelect("log_from_date", $_POST["log_from_date"]["y"], $_POST["log_from_date"]["m"], $_POST["log_from_date"]["d"], 2004);
		  $time_input = ilUtil::makeTimeSelect("log_from_time", TRUE, $_POST["log_from_time"]["h"], $_POST["log_from_time"]["m"]);
		}
		$this->tpl->setVariable("INPUT_LOG_FROM", $date_input." / ".$time_input);
		$this->tpl->setVariable("IMG_CALENDAR", ilUtil::getImagePath("calendar.png"));
		$this->tpl->setVariable("TXT_LOG_FROM_CALENDAR", $this->lng->txt("assessment_log_open_calendar"));
		$this->tpl->setVariable("INPUT_FIELDS_LOG_FROM", "log_from_date");
		$this->tpl->setVariable("TXT_LOG_TO", $this->lng->txt("to"));
		if (!is_array($_POST["log_to_date"]))
		{
			$date_input = ilUtil::makeDateSelect("log_to_date", "", "", "", 2004);
			$time_input = ilUtil::makeTimeSelect("log_to_time");
		}
		else
		{
			$date_input = ilUtil::makeDateSelect("log_to_date", $_POST["log_to_date"]["y"], $_POST["log_to_date"]["m"], $_POST["log_to_date"]["d"], 2004);
		  $time_input = ilUtil::makeTimeSelect("log_to_time", TRUE, $_POST["log_to_time"]["h"], $_POST["log_to_time"]["m"]);
		}
		$this->tpl->setVariable("INPUT_LOG_TO", $date_input." / ".$time_input);
		$this->tpl->setVariable("TXT_LOG_TO_CALENDAR", $this->lng->txt("assessment_log_open_calendar"));
		$this->tpl->setVariable("INPUT_FIELDS_LOG_TO", "log_to_date");
		$this->tpl->setVariable("TXT_CREATE", $this->lng->txt("show"));
		$this->tpl->setVariable("TXT_EXPORT", $this->lng->txt("export"));
		$this->tpl->setVariable("TXT_TEST", $this->lng->txt("assessment_log_for_test"));
		$this->tpl->setVariable("TXT_SELECT_TEST", $this->lng->txt("assessment_log_select_test"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Deletes the log entries for one or more tests
	*
	* @access public
	*/
	function deleteLogObject()
	{
		if (is_array($_POST["chb_test"]) && (count($_POST["chb_test"])))
		{
			$this->object->deleteLogEntries($_POST["chb_test"]);
			ilUtil::sendInfo($this->lng->txt("ass_log_deleted"));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("ass_log_delete_no_selection"));
		}
		$this->logAdminObject();
	}
	
	/**
	* Administration output for assessment log files
	*
	* @access public
	*/
	function logAdminObject()
	{
		global $ilUser;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.assessment_log_admin.html");
		
		// get test titles with ref_id
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$available_tests =& ilObjTest::_getAvailableTests(true);
		$count = count($available_tests);
		if ($count)
		{
			$data = array();
			$i=0;
			foreach ($available_tests as $obj_id => $title)
			{
				$nr = $this->object->getNrOfLogEntries($obj_id);
				array_push($data, array("<input type=\"checkbox\" name=\"chb_test[]\" value=\"$obj_id\" />", $title, $nr));
			}

			$offset = ($_GET["offset"]) ? $_GET["offset"] : 0;
			$orderdirection = ($_GET["sort_order"]) ? $_GET["sort_order"] : "asc";
			$ordercolumn = ($_GET["sort_by"]) ? $_GET["sort_by"] : "title";
			
			$maxentries = $ilUser->getPref("hits_per_page");
			if ($maxentries < 1)
			{
				$maxentries = 9999;
			}
			
			include_once("./Services/Table/classes/class.ilTableGUI.php");
			$table = new ilTableGUI(0, FALSE);
			$table->setTitle($this->lng->txt("ass_log_available_tests"));

			$header_names = array(
				"",
				$this->lng->txt("title"),
				$this->lng->txt("ass_log_count_datasets"),
			);
			$table->setHeaderNames($header_names);
	
			$table->enable("auto_sort");
			$table->enable("sort");
			$table->enable("select_all");
			$table->enable("action");
			$table->setLimit($maxentries);
	
			$table->addActionButton("deleteLog", $this->lng->txt("ass_log_delete_entries"));
			
			$table->setFormName("formLogAdmin");
			$table->setSelectAllCheckbox("chb_test");
			$header_params = $this->ctrl->getParameterArray($this, "logAdmin");
			$header_vars = array("", "title", "count");
			$table->setHeaderVars($header_vars, $header_params);
			$table->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
	
			$table->setOffset($offset);
			$table->setMaxCount(count($available_tests));
			$table->setOrderColumn($ordercolumn);
			$table->setOrderDirection($orderdirection);
			$table->setData($data);

			// footer
			$table->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));

			// render table
			$tableoutput = $table->render();
			$this->tpl->setVariable("TABLE_DATA", $tableoutput);
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "deleteLog"));
		}
	}

	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	function getLogdataSubtabs()
	{
		global $ilTabs;
		
		// log output
		$ilTabs->addSubTabTarget("ass_log_output",
			 $this->ctrl->getLinkTarget($this, "logs"),
			 array("logs", "showLog", "exportLog")
			 , "");
	
		// log administration
		$ilTabs->addSubTabTarget("ass_log_admin",
			$this->ctrl->getLinkTarget($this, "logAdmin"),
			array("logAdmin", "deleteLog"),
			"", "");

	}

	/**
	* Default settings tab for Test & Assessment
	*
	* @access	public
	*/
	function defaultsObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl, $tpl;
		
		$assessmentSetting = new ilSetting("assessment");
		$use_javascript = array_key_exists("use_javascript", $_GET) ? $_GET["use_javascript"] : $assessmentSetting->get("use_javascript");
		$imap_line_color = array_key_exists("imap_line_color", $_GET) ? $_GET["imap_line_color"] : $assessmentSetting->get("imap_line_color");
		if (strlen($imap_line_color) == 0) $imap_line_color = "FF0000";
		
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("assessment_defaults"));
		
		// Enable javascript
		$enable = new ilCheckboxInputGUI($lng->txt("assessment_use_javascript"), "use_javascript");
		$enable->setChecked($use_javascript);
		$enable->setInfo($lng->txt("assessment_use_javascript_desc"));
		$form->addItem($enable);
		
		$linepicker = new ilColorPickerInputGUI($lng->txt("assessment_imap_line_color"), "imap_line_color");
		$linepicker->setValue($imap_line_color);
		$form->addItem($linepicker);
				
		$form->addCommandButton("saveDefaults", $lng->txt("save"));
		$form->addCommandButton("defaults", $lng->txt("cancel"));
		
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}
	
	/**
	* Save default settings for test & assessment
	*
	* @access	public
	*/
	function saveDefaultsObject()
	{
		global $ilCtrl;

		$assessmentSetting = new ilSetting("assessment");
		if ($_POST["use_javascript"])
		{
			$assessmentSetting->set("use_javascript", "1");
		}
		else
		{
			$assessmentSetting->set("use_javascript", "0");
		}
		if (strlen($_POST["imap_line_color"]) == 6)
		{
			$assessmentSetting->set("imap_line_color", $_POST["imap_line_color"]);
		}
		$ilCtrl->redirect($this, "defaults");
	}


	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		switch ($this->ctrl->getCmd())
		{
			case "logs":
			case "showLog":
			case "exportLog":
			case "logAdmin":
			case "deleteLog":
				$this->getLogdataSubtabs();
				break;
		}
		
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"), array("settings","","view"), "", "");

			$tabs_gui->addTarget("logs",
				$this->ctrl->getLinkTarget($this, "logs"), 
					array("logs","showLog", "exportLog", "logAdmin", "deleteLog"), 
					"", "");

				$tabs_gui->addTarget("defaults",
					$this->ctrl->getLinkTarget($this, "defaults"), array("defaults","saveDefaults"), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
} // END class.ilObjAssessmentFolderGUI
?>
