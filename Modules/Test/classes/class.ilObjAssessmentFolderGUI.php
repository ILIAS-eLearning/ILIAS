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

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjAssessmentFolderGUI
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
* 
* @ilCtrl_Calls ilObjAssessmentFolderGUI: ilPermissionGUI, ilSettingsTemplateGUI
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

	public function ilObjAssessmentFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $rbacsystem;

		$this->type = "assf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);

		if (!$rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"),$this->ilias->error_obj->WARNING);
		}
	}
	
	public function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

                global $ilTabs;

		switch($next_class)
		{
			case 'ilpermissiongui':
                                $ilTabs->activateTab('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilsettingstemplategui':
				$ilTabs->setTabActive("templates");
				include_once("./Services/Administration/classes/class.ilSettingsTemplateGUI.php");
				$set_tpl_gui = new ilSettingsTemplateGUI($this->getSettingsTemplateConfig());
				$this->ctrl->forwardCommand($set_tpl_gui);
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
	public function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// put here object specific stuff

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		$this->ctrl->redirect($this);
	}


	/**
	* display assessment folder settings form
	*/
	public function settingsObject()
	{
		global $ilAccess;

                global $ilTabs;
                $ilTabs->setTabActive('settings');

		include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("settings");

		// general properties
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("assessment_log_logging"));
		$form->addItem($header);
		
		// assessment logging
		$logging = new ilCheckboxInputGUI('', "chb_assessment_logging");
		$logging->setValue(1);
		$logging->setChecked($this->object->_enabledAssessmentLogging());
		$logging->setOptionTitle($this->lng->txt("activate_assessment_logging"));
		$form->addItem($logging);

		// reporting language
		$reporting = new ilSelectInputGUI($this->lng->txt('assessment_settings_reporting_language'), "reporting_language");
		$languages = $this->lng->getInstalledLanguages();
		$options = array();
		foreach ($languages as $lang)
		{
			$options[$lang] = $this->lng->txt("lang_" . $lang);
		}
		$reporting->setOptions($options);
		$reporting->setValue($this->object->_getLogLanguage());
		$form->addItem($reporting);

		// question settings
		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("assf_questiontypes"));
		$form->addItem($header);

		// available question types
		$allowed = new ilCheckboxGroupInputGUI($this->lng->txt('assf_allowed_questiontypes'), "chb_allowed_questiontypes");
		$questiontypes =& ilObjQuestionPool::_getQuestionTypes(TRUE);
		$forbidden_types = $this->object->_getForbiddenQuestionTypes();
		$allowedtypes = array();
		foreach ($questiontypes as $qt)
		{
			if (!in_array($qt['question_type_id'], $forbidden_types)) array_push($allowedtypes, $qt['question_type_id']);
		}
		$allowed->setValue($allowedtypes);
		foreach ($questiontypes as $type_name => $qtype)
		{
			$allowed->addOption(new ilCheckboxOption($type_name, $qtype["question_type_id"]));
		}
		$allowed->setInfo($this->lng->txt('assf_allowed_questiontypes_desc'));
		$form->addItem($allowed);

		// manual scoring
		$manual = new ilCheckboxGroupInputGUI($this->lng->txt('assessment_log_manual_scoring_activate'), "chb_manual_scoring");
		$manscoring = $this->object->_getManualScoring();
		$manual->setValue($manscoring);
		foreach ($questiontypes as $type_name => $qtype)
		{
			$manual->addOption(new ilCheckboxOption($type_name, $qtype["question_type_id"]));
		}
		$manual->setInfo($this->lng->txt('assessment_log_manual_scoring_desc'));
		$form->addItem($manual);

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$form->addCommandButton("saveSettings", $this->lng->txt("save"));
		}
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}
	
	/**
	* Save Assessment settings
	*/
	public function saveSettingsObject()
	{
		global $ilAccess;
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) $this->ctrl->redirect($this,'settings');
		
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
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->redirect($this,'settings');
	}
	
	/**
	* Called when the a log should be shown
	*/
	public function showLogObject()
	{
		$from = mktime($_POST['log_from']['time']['h'], $_POST['log_from']['time']['m'], 0, $_POST['log_from']['date']['m'], $_POST['log_from']['date']['d'], $_POST['log_from']['date']['y']);
		$until = mktime($_POST['log_until']['time']['h'], $_POST['log_until']['time']['m'], 0, $_POST['log_until']['date']['m'], $_POST['log_until']['date']['d'], $_POST['log_until']['date']['y']);
		$test = $_POST['sel_test'];
		$this->logsObject($from, $until, $test);
	}
	
	/**
	* Called when the a log should be exported
	*/
	public function exportLogObject()
	{
		$from = mktime($_POST['log_from']['time']['h'], $_POST['log_from']['time']['m'], 0, $_POST['log_from']['date']['m'], $_POST['log_from']['date']['d'], $_POST['log_from']['date']['y']);
		$until = mktime($_POST['log_until']['time']['h'], $_POST['log_until']['time']['m'], 0, $_POST['log_until']['date']['m'], $_POST['log_until']['date']['d'], $_POST['log_until']['date']['y']);
		$test = $_POST['sel_test'];

		$csv = array();
		$separator = ";";
		$row = array(
				$this->lng->txt("assessment_log_datetime"),
				$this->lng->txt("user"),
				$this->lng->txt("assessment_log_text"),
				$this->lng->txt("question")
		);
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$available_tests =& ilObjTest::_getAvailableTests(1);
		array_push($csv, ilUtil::processCSVRow($row, TRUE, $separator));
		$log_output =& $this->object->getLog($from, $until, $test);
		$users = array();
		foreach ($log_output as $key => $log)
		{
			if (!array_key_exists($log["user_fi"], $users))
			{
				$users[$log["user_fi"]] = ilObjUser::_lookupName($log["user_fi"]);
			}
			$title = "";
			if ($log["question_fi"] || $log["original_fi"])
			{
				$title = assQuestion::_getQuestionTitle($log["question_fi"]);
				if (strlen($title) == 0)
				{
					$title = assQuestion::_getQuestionTitle($log["original_fi"]);
				}
				$title = $this->lng->txt("assessment_log_question") . ": " . $title;
			}
			$csvrow = array();
			$date = new ilDateTime($log['tstamp'],IL_CAL_UNIX);
			array_push($csvrow, $date->get(IL_CAL_FKT_DATE,'Y-m-d H:i'));
			array_push($csvrow, trim($users[$log["user_fi"]]["title"] . " " . $users[$log["user_fi"]]["firstname"] . " " . $users[$log["user_fi"]]["lastname"]));
			array_push($csvrow, trim($log["logtext"]));
			array_push($csvrow, $title);
			array_push($csv, ilUtil::processCSVRow($csvrow, TRUE, $separator));
		}
		$csvoutput = "";
		foreach ($csv as $row)
		{
			$csvoutput .= join($row, $separator) . "\n";
		}
		ilUtil::deliverData($csvoutput, str_replace(" ", "_", "log_" . $from . "_" . $until . "_" . $available_tests[$test]).".csv");
	}

	/**
	* display assessment folder logs form
	*/
	public function logsObject($p_from = null, $p_until = null, $p_test = null)
	{
                global $ilTabs;
                $ilTabs->activateTab('logs');

		$template = new ilTemplate("tpl.assessment_logs.html", TRUE, TRUE, "Modules/Test");

		include_once "./Modules/Test/classes/class.ilObjTest.php";
		include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
		$available_tests =& ilObjTest::_getAvailableTests(1);
		if (count($available_tests) == 0)
		{
			ilUtil::sendInfo($this->lng->txt('assessment_log_no_data'));
			return;
		}

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTableWidth("100%");
		$form->setId("logs");

		$header = new ilFormSectionHeaderGUI();
		$header->setTitle($this->lng->txt("assessment_log"));
		$form->addItem($header);
		
		// from
		$from = new ilDateTimeInputGUI($this->lng->txt('cal_from'), "log_from");
		$from->setShowDate(true);
		$from->setShowTime(true);
		$now = getdate();
		$fromdate = ($p_from) ? $p_from : (($_GET['log_from']) ? $_GET['log_from'] : mktime(0, 0, 0, 1, 1, $now['year']));
		$from->setDate(new ilDateTime($fromdate, IL_CAL_UNIX));
		$form->addItem($from);

		// until
		$until = new ilDateTimeInputGUI($this->lng->txt('cal_until'), "log_until");
		$until->setShowDate(true);
		$until->setShowTime(true);
		$untildate = ($p_until) ? $p_until : (($_GET['log_until']) ? $_GET['log_until'] : time());
		$until->setDate(new ilDateTime($untildate, IL_CAL_UNIX));
		$form->addItem($until);

		// tests
		$fortest = new ilSelectInputGUI($this->lng->txt('assessment_log_for_test'), "sel_test");
		$options = array();
		foreach ($available_tests as $key => $value)
		{
			$options[$key] = ilUtil::prepareFormOutput($value) . " [" . $this->object->getNrOfLogEntries($key) . " " . $this->lng->txt("assessment_log_log_entries") . "]";
		}
		$fortest->setOptions($options);
		$p_test = ($p_test) ? $p_test : $_GET['sel_test'];
		if ($p_test) $fortest->setValue($p_test);
		$form->addItem($fortest);
		$this->ctrl->setParameter($this, 'sel_test', $p_test);
		$this->ctrl->setParameter($this, 'log_until', $untildate);
		$this->ctrl->setParameter($this, 'log_from', $fromdate);
		$form->addCommandButton("showLog", $this->lng->txt("show"));
		$form->addCommandButton("exportLog", $this->lng->txt("export"));
		$template->setVariable("FORM", $form->getHTML());

		if ($p_test)
		{
			include_once "./Modules/Test/classes/tables/class.ilAssessmentFolderLogTableGUI.php";
			$table_gui = new ilAssessmentFolderLogTableGUI($this, 'logs');
			$log_output =& $this->object->getLog($fromdate, $untildate, $p_test);
			$table_gui->setData($log_output);
			$template->setVariable('LOG', $table_gui->getHTML());	
		}
		$this->tpl->setVariable("ADM_CONTENT", $template->get());
	}

	/**
	* Deletes the log entries for one or more tests
	*/
	public function deleteLogObject()
	{
		if (is_array($_POST["chb_test"]) && (count($_POST["chb_test"])))
		{
			$this->object->deleteLogEntries($_POST["chb_test"]);
			ilUtil::sendSuccess($this->lng->txt("ass_log_deleted"));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("ass_log_delete_no_selection"));
		}
		$this->logAdminObject();
	}
	
	/**
	* Administration output for assessment log files
	*/
	public function logAdminObject()
	{
		global $ilAccess, $ilTabs;

                $ilTabs->activateTab('logs');

		$a_write_access = ($ilAccess->checkAccess("write", "", $this->object->getRefId())) ? true : false;
		
		include_once "./Modules/Test/classes/tables/class.ilAssessmentFolderLogAdministrationTableGUI.php";
		$table_gui = new ilAssessmentFolderLogAdministrationTableGUI($this, 'logAdmin', $a_write_access);
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$available_tests =& ilObjTest::_getAvailableTests(true);
		$data = array();
		foreach ($available_tests as $obj_id => $title)
		{
			$nr = $this->object->getNrOfLogEntries($obj_id);
			array_push($data, array("title" => $title, "nr" => $nr, "id" => $obj_id));
		}
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}

	public function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	public function getLogdataSubtabs()
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
	*/
	public function defaultsObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl, $tpl, $ilTabs;

                $ilTabs->activateTab('defaults');

		$assessmentSetting = new ilSetting("assessment");
		$use_javascript = array_key_exists("use_javascript", $_GET) ? $_GET["use_javascript"] : $assessmentSetting->get("use_javascript");
		$imap_line_color = array_key_exists("imap_line_color", $_GET) ? $_GET["imap_line_color"] : $assessmentSetting->get("imap_line_color");
		if (strlen($imap_line_color) == 0) $imap_line_color = "FF0000";
		
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

		if (@file_exists("./Modules/Test/classes/class.ilTestResultsToXML.php"))
		{
			global $ilDB;
			$user_criteria = array_key_exists("user_criteria", $_GET) ? $_GET["user_criteria"] : $assessmentSetting->get("user_criteria");
			$userCriteria = new ilSelectInputGUI($this->lng->txt("user_criteria"), "user_criteria");
			$userCriteria->setInfo($this->lng->txt("user_criteria_desc"));
			$userCriteria->setRequired(true);
			$userCriteria->setValue(($isSingleline) ? 0 : 1);

			$manager = $ilDB->db->loadModule('Manager');
			$fields = $manager->listTableFields('usr_data');
			$usr_fields = array();
			foreach ($fields as $field)
			{
				$usr_fields[$field] = $field;
			}
			$userCriteria->setOptions($usr_fields);
			$userCriteria->setValue($user_criteria);
			$form->addItem($userCriteria);
		}

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$form->addCommandButton("saveDefaults", $lng->txt("save"));
		}
		
		
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}
	
	/**
	* Save default settings for test & assessment
	*/
	public function saveDefaultsObject()
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
		if (@file_exists("./Modules/Test/classes/class.ilTestResultsToXML.php"))
		{
			$assessmentSetting->set("user_criteria", $_POST["user_criteria"]);
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "defaults");
	}


	/**
	* get tabs
	*
	* @param	object	tabs gui object
	*/
	public function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $lng;

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

			$tabs_gui->addTab("templates",
				$lng->txt("adm_settings_templates"),
				$this->ctrl->getLinkTargetByClass("ilsettingstemplategui", ""));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}


	/**
	 * Get settings template configuration object
	 *
	 * @return object settings template configuration object
	 */
	public static function getSettingsTemplateConfig()
	{
		global $lng;

		$lng->loadLanguageModule("tst");
                $lng->loadLanguageModule("assessment");

		include_once("./Services/Administration/classes/class.ilSettingsTemplateConfig.php");
		$config = new ilSettingsTemplateConfig("tst");

                $config->addHidableTab("questions", $lng->txt('edit_test_questions'));
                $config->addHidableTab("mark_schema", $lng->txt('settings') . ' - ' . $lng->txt("mark_schema"));
                $config->addHidableTab("certificate", $lng->txt('settings') . ' - ' . $lng->txt("certificate"));
                $config->addHidableTab("defaults", $lng->txt('settings') . ' - ' . $lng->txt("defaults"));

		$config->addHidableTab("learning_progress", $lng->txt("learning_progress"));
		$config->addHidableTab("manscoring", $lng->txt("manscoring"));
                $config->addHidableTab("history", $lng->txt("history"));
		$config->addHidableTab("meta_data", $lng->txt("meta_data"));
		$config->addHidableTab("export", $lng->txt("export"));
                $config->addHidableTab("permissions", $lng->txt("permission"));

		/////////////////////////////////////
		// Settings
		/////////////////////////////////////

		//general properties
		$config->addSetting(
			"anonymity",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_anonymity"),
			false,
			0,
			array(
                            '0' => $lng->txt("tst_anonymity_no_anonymization"),
                            '1' => $lng->txt("tst_anonymity_anonymous_test"),
                        )
			);

		$config->addSetting(
			"title_output",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_title_output"),
                        true,
                        0,
			array(
                            '0' => $lng->txt("test_enable_view_table"),
                            '1' => $lng->txt("test_enable_view_express"),
                            '2' => $lng->txt("test_enable_view_both"),
                        )
			);
		$config->addSetting(
			"random_test",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_random_selection"),
			true
			);

		$config->addSetting(
			"use_pool",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("test_question_pool_usage"),
			true
			);

		// Information at beginning and end of test
		$config->addSetting(
			"showinfo",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("showinfo"),
			true
			);

		$config->addSetting(
			"showfinalstatement",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("final_statement_show"),
			false
			);

		// Session Settings
		$config->addSetting(
			"nr_of_tries",
			ilSettingsTemplateConfig::TEXT,
			$lng->txt("tst_nr_of_tries"),
			false,
                        3
			);


		$config->addSetting(
			"chb_processing_time",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_processing_time"),
			false
			);

                $config->addSetting(
			"chb_starting_time",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_starting_time"),
			false
			);

                $config->addSetting(
			"chb_ending_time",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_ending_time"),
			false
			);

		$config->addSetting(
			"password",
			ilSettingsTemplateConfig::TEXT,
			$lng->txt("tst_password"),
			true,
                        20
			);

		// Presentation Properties

		$config->addSetting(
			"chb_use_previous_answers",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_use_previous_answers"),
			false
			);
		$config->addSetting(
			"forcejs",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("forcejs_short"),
			true
			);

		$config->addSetting(
			"title_output",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_title_output"),
                        true,
                        0,
			array(
                            '0' => $lng->txt("test_enable_view_table"),
                            '1' => $lng->txt("test_enable_view_express"),
                            '2' => $lng->txt("test_enable_view_both"),
                        )
			);

		// Sequence Properties

		$config->addSetting(
			"chb_postpone",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_postpone"),
			true
			);
		$config->addSetting(
			"chb_shuffle_questions",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_shuffle_questions"),
			false
			);
		$config->addSetting(
			"list_of_questions",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_show_summary"),
			false
			);

		$config->addSetting(
			"chb_show_marker",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("question_marking"),
			true
			);
		$config->addSetting(
			"chb_show_cancel",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_show_cancel"),
			true
			);

		// Notifications

		$config->addSetting(
			"mailnotification",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_finish_notification"),
                        true,
                        0,
			array(
                            '0' => $lng->txt("tst_finish_notification_no"),
                            '1' => $lng->txt("tst_finish_notification_simple"),
                            '2' => $lng->txt("tst_finish_notification_advanced"),
                        )
			);

		$config->addSetting(
			"mailnottype",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("mailnottype"),
			true
			);

		// Kiosk Mode

		$config->addSetting(
			"kiosk",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("kiosk"),
			true
			);


		// Participants Restriction

		$config->addSetting(
			"fixedparticipants",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("participants_invitation"),
			true
			);

                $config->addSetting(
			"allowedUsers",
			ilSettingsTemplateConfig::TEXT,
			$lng->txt("tst_allowed_users"),
			true,
                        3
			);

                $config->addSetting(
			"allowedUsersTimeGap",
			ilSettingsTemplateConfig::TEXT,
			$lng->txt("tst_allowed_users_time_gap"),
			true,
                        4
			);

		/////////////////////////////////////
		// Scoring and Results
		/////////////////////////////////////

		$config->addSetting(
			"count_system",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_text_count_system"),
                        true,
                        0,
			array(
                            '0' => $lng->txt("tst_count_partial_solutions"),
                            '1' => $lng->txt("tst_count_correct_solutions"),
                        )
			);

 		$config->addSetting(
			"mc_scoring",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_score_mcmr_questions"),
                        true,
                        0,
			array(
                            '0' => $lng->txt("tst_score_mcmr_zero_points_when_unanswered"),
                            '1' => $lng->txt("tst_score_mcmr_use_scoring_system"),
                        )
			);

  		$config->addSetting(
			"score_cutting",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_score_cutting"),
                        true,
                        0,
			array(
                            '0' => $lng->txt("tst_score_cut_question"),
                            '1' => $lng->txt("tst_score_cut_test"),
                        )
			);

  		$config->addSetting(
			"pass_scoring",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_pass_scoring"),
                        false,
                        0,
			array(
                            '0' => $lng->txt("tst_pass_last_pass"),
                            '1' => $lng->txt("tst_pass_best_pass"),
                        )
			);

                $config->addSetting(
			"instant_feedback",
			ilSettingsTemplateConfig::CHECKBOX,
			$lng->txt("tst_instant_feedback"),
                        false,
                        0,
			array(
                            'instant_feedback_answer' => $lng->txt("tst_instant_feedback_answer_specific"),
                            'instant_feedback_points' => $lng->txt("tst_instant_feedback_results"),
                            'instant_feedback_solution' => $lng->txt("tst_instant_feedback_solution"),
                        )
			);

                $config->addSetting(
			"results_access",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("tst_results_access"),
                        false,
                        0,
			array(
                            '1' => $lng->txt("tst_results_access_finished"),
                            '2' => $lng->txt("tst_results_access_always"),
                            '3' => $lng->txt("tst_results_access_never"),
                            '4' => $lng->txt("tst_results_access_date"),
                        )
			);

			$config->addSetting(
				"print_bs_with_res",
				ilSettingsTemplateConfig::BOOL,
				$lng->txt("tst_results_print_best_solution"),
				true
			);

                $config->addSetting(
			"results_presentation",
			ilSettingsTemplateConfig::CHECKBOX,
			$lng->txt("tst_results_presentation"),
                        false,
                        0,
			array(
                            'pass_details' => $lng->txt("tst_show_pass_details"),
                            'solution_details' => $lng->txt("tst_show_solution_details"),
                            'solution_printview' => $lng->txt("tst_show_solution_printview"),
                            'solution_feedback' => $lng->txt("tst_show_solution_feedback"),
                            'solution_answers_only' => $lng->txt("tst_show_solution_answers_only"),
                            'solution_signature' => $lng->txt("tst_show_solution_signature"),
                            'solution_suggested' => $lng->txt("tst_show_solution_suggested"),
                        )
			);

                $config->addSetting(
			"export_settings",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("tst_export_settings"),
                        true
			);
		return $config;
	}

} // END class.ilObjAssessmentFolderGUI
?>
