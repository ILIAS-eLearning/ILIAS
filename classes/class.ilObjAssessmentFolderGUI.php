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
* Class ilObjAssessmentFolderGUI
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjAssessmentFolderGUI: ilPermissionGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/

include_once "class.ilObjectGUI.php";

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
		sendInfo($this->lng->txt("object_added"),true);

		$this->ctrl->redirect($this);
		//header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		//exit();
	}


	/**
	* display assessment folder settings form
	*/
	function settingsObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.assessment_settings.html");
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
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
		sendInfo($this->lng->txt("msg_obj_modified"),true);

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
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR", "./assessment/js/calendar/calendar.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_SETUP", "./assessment/js/calendar/calendar-setup.js");
		$this->tpl->setVariable("LOCATION_JAVASCRIPT_CALENDAR_STYLESHEET", "./assessment/js/calendar/calendar.css");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("javascript_call_calendar");
		$this->tpl->setVariable("INPUT_FIELDS_STARTING_DATE", "starting_date");
		$this->tpl->setVariable("INPUT_FIELDS_ENDING_DATE", "ending_date");
		$this->tpl->setVariable("INPUT_FIELDS_REPORTING_DATE", "reporting_date");
		$this->tpl->parseCurrentBlock();
		include_once "./assessment/classes/class.ilObjTest.php";
		$available_tests =& ilObjTest::_getAvailableTests(1);
		foreach ($available_tests as $key => $value)
		{
			$this->tpl->setCurrentBlock("sel_test_row");
			$this->tpl->setVariable("TXT_OPTION", ilUtil::prepareFormOutput($value) . " (" . $this->object->getNrOfLogEntries($key) . " " . $this->lng->txt("assessment_log_log_entries") . ")");
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
			include_once "./classes/class.ilUtil.php";
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
			include_once "./assessment/classes/class.assQuestion.php";
			$ts_from = sprintf("%04d%02d%02d%02d%02d%02d", $_POST["log_from_date"]["y"], $_POST["log_from_date"]["m"], $_POST["log_from_date"]["d"], $_POST["log_from_time"]["h"], $_POST["log_from_time"]["m"], 0);
			$ts_to = sprintf("%04d%02d%02d%02d%02d%02d", $_POST["log_to_date"]["y"], $_POST["log_to_date"]["m"], $_POST["log_to_date"]["d"], $_POST["log_to_time"]["h"], $_POST["log_to_time"]["m"], 0);
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
				$this->tpl->setVariable("TXT_DATETIME", ilFormat::formatDate(ilFormat::ftimestamp2datetimeDB($log["TIMESTAMP14"]), "datetime"));
				$csvrow = array();
				if (strcmp($this->ctrl->getCmd(), "exportLog") == 0)
				{
					array_push($csvrow, ilFormat::formatDate(ilFormat::ftimestamp2datetimeDB($log["TIMESTAMP14"]), "datetime"));
				}
				if ($log["question_fi"] || $log["original_fi"])
				{
					$title = ASS_Question::_getQuestionTitle($log["question_fi"]);
					if (strlen($title) == 0)
					{
						$title = ASS_Question::_getQuestionTitle($log["original_fi"]);
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
			$date_input = ilUtil::makeDateSelect("log_from_date", "", "", "", 2004);
			$time_input = ilUtil::makeTimeSelect("log_from_time");
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

	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"), array("settings","","view"), "", "");

			$tabs_gui->addTarget("logs",
				$this->ctrl->getLinkTarget($this, "logs"), array("logs","showLog", "exportLog"), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
} // END class.ilObjAssessmentFolderGUI
?>
