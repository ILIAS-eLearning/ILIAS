<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
 * Class ilObjSystemFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * $Id$
 *
 * @ilCtrl_Calls ilObjSystemFolderGUI: ilPermissionGUI, ilImprintGUI
 * @ilCtrl_Calls ilObjSystemFolderGUI: ilObjectOwnershipManagementGUI, ilCronManagerGUI
 *
 * @extends ilObjectGUI
 */
class ilObjSystemFolderGUI extends ilObjectGUI
{
	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* Constructor
	* @access public
	*/
	function ilObjSystemFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "adm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);

		$this->lng->loadLanguageModule("administration");
		$this->lng->loadLanguageModule("adm");
	}

	function &executeCommand()
	{
		global $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		$this->prepareOutput();
		
		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			case 'ilimprintgui':
				// page editor will set its own tabs
				$ilTabs->clearTargets();
				$ilTabs->setBackTarget($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, ""));
			
				include_once("./Services/Imprint/classes/class.ilImprintGUI.php");
				$igui = new ilImprintGUI();
								
				// needed for editor			
				$igui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0, "impr"));
				
				if(!$this->checkPermissionBool("write"))
				{
					$igui->setEnableEditing(false);
				}
				
				$ret = $this->ctrl->forwardCommand($igui);				
				if ($ret != "")
				{																								
					$this->tpl->setContent($ret);					
				}
				break;
				
			case "ilobjectownershipmanagementgui":
				$this->setSystemCheckSubTabs("no_owner");
				include_once("Services/Object/classes/class.ilObjectOwnershipManagementGUI.php");
				$gui = new ilObjectOwnershipManagementGUI(0);
				$this->ctrl->forwardCommand($gui);
				break;		
			
			case "ilcronmanagergui":
				$ilTabs->activateTab("cron_jobs");
				include_once("Services/Cron/classes/class.ilCronManagerGUI.php");
				$gui = new ilCronManagerGUI();
				$this->ctrl->forwardCommand($gui);
				break;		
			
			default:
//var_dump($_POST);
				$cmd = $this->ctrl->getCmd("view");

				$cmd .= "Object";
				$this->$cmd();

				break;
		}

		return true;
	}

	/**
	* show admin subpanels and basic settings form
	*
	* @access	public
	*/
	function viewObject()
	{
		global $ilAccess;

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			return $this->showBasicSettingsObject();
		}
		return $this->showServerInfoObject();
	}

	function viewScanLogObject()
	{
		return $this->viewScanLog();
	}
	
	/**
	* Set sub tabs for general settings
	*/
	function setSystemCheckSubTabs($a_activate)
	{
		global $ilTabs, $ilCtrl;
		
		$ilTabs->addSubTab("system_check_sub", $this->lng->txt("system_check"), 
			$ilCtrl->getLinkTarget($this, "check"));
		$ilTabs->addSubTab("no_owner",  $this->lng->txt("system_check_no_owner"), 
			$ilCtrl->getLinkTargetByClass("ilObjectOwnershipManagementGUI"));
		
		$ilTabs->setSubTabActive($a_activate);
		$ilTabs->setTabActive("system_check");
	}

	/**
	* displays system check menu
	*
	* @access	public
	*/
	function checkObject()
	{
		global $rbacsystem, $ilias, $objDefinition, $ilSetting;
		
		$this->setSystemCheckSubTabs("system_check_sub");

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
//echo "1";

		if ($_POST['count_limit'] !== null || $_POST['age_limit'] !== null || $_POST['type_limit'] !== null)
		{
			$ilias->account->writePref('systemcheck_count_limit',
				(is_numeric($_POST['count_limit']) && $_POST['count_limit'] > 0) ? $_POST['count_limit'] : ''
			);
			$ilias->account->writePref('systemcheck_age_limit',
				(is_numeric($_POST['age_limit']) && $_POST['age_limit'] > 0) ? $_POST['age_limit'] : '');
			$ilias->account->writePref('systemcheck_type_limit', trim($_POST['type_limit']));
		}

		if ($_POST["mode"])
		{
//echo "3";
			$this->writeCheckParams();
			$this->startValidator($_POST["mode"],$_POST["log_scan"]);
		}
		else
		{
//echo "4";
			include_once "./Services/Repository/classes/class.ilValidator.php";
			$validator = new ilValidator();
			$hasScanLog = $validator->hasScanLog();

			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_check.html",
				"Modules/SystemFolder");
			
			if ($hasScanLog)
			{
				$this->tpl->setVariable("TXT_VIEW_LOG", $this->lng->txt("view_last_log"));
			}

			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("systemcheck"));
			$this->tpl->setVariable("COLSPAN", 3);
			$this->tpl->setVariable("TXT_ANALYZE_TITLE", $this->lng->txt("analyze_data"));
			$this->tpl->setVariable("TXT_ANALYSIS_OPTIONS", $this->lng->txt("analysis_options"));
			$this->tpl->setVariable("TXT_REPAIR_OPTIONS", $this->lng->txt("repair_options"));
			$this->tpl->setVariable("TXT_OUTPUT_OPTIONS", $this->lng->txt("output_options"));
			$this->tpl->setVariable("TXT_SCAN", $this->lng->txt("scan"));
			$this->tpl->setVariable("TXT_SCAN_DESC", $this->lng->txt("scan_desc"));
			$this->tpl->setVariable("TXT_DUMP_TREE", $this->lng->txt("dump_tree"));
			$this->tpl->setVariable("TXT_DUMP_TREE_DESC", $this->lng->txt("dump_tree_desc"));
			$this->tpl->setVariable("TXT_CLEAN", $this->lng->txt("clean"));
			$this->tpl->setVariable("TXT_CLEAN_DESC", $this->lng->txt("clean_desc"));
			$this->tpl->setVariable("TXT_RESTORE", $this->lng->txt("restore_missing"));
			$this->tpl->setVariable("TXT_RESTORE_DESC", $this->lng->txt("restore_missing_desc"));
			$this->tpl->setVariable("TXT_PURGE", $this->lng->txt("purge_missing"));
			$this->tpl->setVariable("TXT_PURGE_DESC", $this->lng->txt("purge_missing_desc"));
			$this->tpl->setVariable("TXT_RESTORE_TRASH", $this->lng->txt("restore_trash"));
			$this->tpl->setVariable("TXT_RESTORE_TRASH_DESC", $this->lng->txt("restore_trash_desc"));
			$this->tpl->setVariable("TXT_PURGE_TRASH", $this->lng->txt("purge_trash"));
			$this->tpl->setVariable("TXT_PURGE_TRASH_DESC", $this->lng->txt("purge_trash_desc"));
			$this->tpl->setVariable("TXT_COUNT_LIMIT", $this->lng->txt("purge_count_limit"));
			$this->tpl->setVariable("TXT_COUNT_LIMIT_DESC", $this->lng->txt("purge_count_limit_desc"));
			$this->tpl->setVariable("COUNT_LIMIT_VALUE", $ilias->account->getPref("systemcheck_count_limit"));
			$this->tpl->setVariable("TXT_AGE_LIMIT", $this->lng->txt("purge_age_limit"));
			$this->tpl->setVariable("TXT_AGE_LIMIT_DESC", $this->lng->txt("purge_age_limit_desc"));
			$this->tpl->setVariable("AGE_LIMIT_VALUE", $ilias->account->getPref("systemcheck_age_limit"));
			$this->tpl->setVariable("TXT_TYPE_LIMIT", $this->lng->txt("purge_type_limit"));
			$this->tpl->setVariable("TXT_TYPE_LIMIT_DESC", $this->lng->txt("purge_type_limit_desc"));

			if($ilias->account->getPref('systemcheck_mode_scan'))
				$this->tpl->touchBlock('mode_scan_checked');
			if($ilias->account->getPref('systemcheck_mode_dump_tree'))
				$this->tpl->touchBlock('mode_dump_tree_checked');
			if($ilias->account->getPref('systemcheck_mode_clean'))
				$this->tpl->touchBlock('mode_clean_checked');
			if($ilias->account->getPref('systemcheck_mode_restore'))
			{
				$this->tpl->touchBlock('mode_restore_checked');
				$this->tpl->touchBlock('mode_purge_disabled');
			}
			elseif($ilias->account->getPref('systemcheck_mode_purge'))
			{
				$this->tpl->touchBlock('mode_purge_checked');
				$this->tpl->touchBlock('mode_restore_disabled');
			}
			if($ilias->account->getPref('systemcheck_mode_restore_trash'))
			{
				$this->tpl->touchBlock('mode_restore_trash_checked');
				$this->tpl->touchBlock('mode_purge_trash_disabled');
			}
			elseif($ilias->account->getPref('systemcheck_mode_purge_trash'))
			{
				$this->tpl->touchBlock('mode_purge_trash_checked');
				$this->tpl->touchBlock('mode_restore_trash_disabled');
			}
			if($ilias->account->getPref('systemcheck_log_scan'))
				$this->tpl->touchBlock('log_scan_checked');
			
			
			// #9520 - restrict to types which can be found in tree
			
			$obj_types_in_tree = array();
			
			global $ilDB;
			$set = $ilDB->query('SELECT type FROM object_data od'.
				' JOIN object_reference ref ON (od.obj_id = ref.obj_id)'.
				' JOIN tree ON (tree.child = ref.ref_id)'.
				' WHERE tree.tree < 1'.
				' GROUP BY type');
			while($row = $ilDB->fetchAssoc($set))
			{
				$obj_types_in_tree[] = $row['type'];
			}
			
			$types = $objDefinition->getAllObjects();
			$ts = array("" => "");
			foreach ($types as $t)
			{
				if ($t != "" && !$objDefinition->isSystemObject($t) && $t != "root" &&
					in_array($t, $obj_types_in_tree))
				{
					if ($objDefinition->isPlugin($t))
					{
						$ts[$t] = ilPlugin::lookupTxt("rep_robj", $t, "obj_".$t);
					}
					else
					{
						$ts[$t] = $this->lng->txt("obj_".$t);
					}
				}
			}
			asort($ts);
			$this->tpl->setVariable("TYPE_LIMIT_CHOICE",
				ilUtil::formSelect(
					$ilias->account->getPref("systemcheck_type_limit"),
					'type_limit',
					$ts, false, true
					)
			);
			$this->tpl->setVariable("TXT_LOG_SCAN", $this->lng->txt("log_scan"));
			$this->tpl->setVariable("TXT_LOG_SCAN_DESC", $this->lng->txt("log_scan_desc"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("start_scan"));

			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_params_for_cron"));
			
			include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
			
			$cron_form = new ilPropertyFormGUI();
			$cron_form->setFormAction($this->ctrl->getFormAction($this));
			$cron_form->setTitle($this->lng->txt('systemcheck_cronform'));
			
				$radio_group = new ilRadioGroupInputGUI($this->lng->txt('systemcheck_cron'), 'cronjob' );
				$radio_group->setValue( $ilSetting->get('systemcheck_cron') );
	
					$radio_opt = new ilRadioOption($this->lng->txt('disabled'),0);
				$radio_group->addOption($radio_opt);
	
					$radio_opt = new ilRadioOption($this->lng->txt('enabled'),1);
				$radio_group->addOption($radio_opt);
				
			$cron_form->addItem($radio_group);
			
			$cron_form->addCommandButton('saveCheckCron',$this->lng->txt('save'));
			
			$this->tpl->setVariable('CRON_FORM',$cron_form->getHTML());
		}
	}
	
	private function saveCheckParamsObject()
	{
		$this->writeCheckParams();
		unset($_POST['mode']);
		return $this->checkObject();
	}
	
	private function writeCheckParams()
	{
		include_once "./Services/Repository/classes/class.ilValidator.php";
		$validator = new ilValidator();
		$modes = $validator->getPossibleModes();
		
		$prefs = array();
		foreach($modes as $mode)
		{
			if( isset($_POST['mode'][$mode]) ) $value = (int)$_POST['mode'][$mode];
			else $value = 0;
			$prefs[ 'systemcheck_mode_'.$mode ] = $value;
		}
		
		if( isset($_POST['log_scan']) ) $value = (int)$_POST['log_scan'];
		else $value = 0;
		$prefs['systemcheck_log_scan'] = $value;
		
		global $ilUser;
		foreach($prefs as $key => $val)
		{
			$ilUser->writePref($key,$val);
		}
	}
	
	private function saveCheckCronObject()
	{
		global $ilSetting;
		
		$systemcheck_cron = ($_POST['cronjob'] ? 1 : 0);
		$ilSetting->set('systemcheck_cron',$systemcheck_cron);
		
		unset($_POST['mode']);
		return $this->checkObject();
	}

	/**
	* edit header title form
	*
	* @access	private
	*/
	function changeHeaderTitleObject()
	{
		global $rbacsystem, $styleDefinition;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.header_title_edit.html",
			"Modules/SystemFolder");

		$array_push = true;

		if ($_SESSION["error_post_vars"])
		{
			$_SESSION["translation_post"] = $_SESSION["error_post_vars"];
			$_GET["mode"] = "session";
			$array_push = false;
		}

		// load from db if edit category is called the first time
		if (($_GET["mode"] != "session"))
		{
			$data = $this->object->getHeaderTitleTranslations();
			$_SESSION["translation_post"] = $data;
			$array_push = false;
		}	// remove a translation from session
		elseif ($_GET["entry"] != 0)
		{
			array_splice($_SESSION["translation_post"]["Fobject"],$_GET["entry"],1,array());

			if ($_GET["entry"] == $_SESSION["translation_post"]["default_language"])
			{
				$_SESSION["translation_post"]["default_language"] = "";
			}
		}

		$data = $_SESSION["translation_post"];

		// add additional translation form
		if (!$_GET["entry"] and $array_push)
		{
			$count = array_push($data["Fobject"],array("title" => "","desc" => ""));
		}
		else
		{
			$count = count($data["Fobject"]);
		}

		// stripslashes in form?
		$strip = isset($_SESSION["translation_post"]) ? true : false;

		foreach ($data["Fobject"] as $key => $val)
		{
			// add translation button
			if ($key == $count -1)
			{
				$this->tpl->setCurrentBlock("addTranslation");
				$this->tpl->setVariable("TXT_ADD_TRANSLATION",$this->lng->txt("add_translation")." >>");
				$this->tpl->parseCurrentBlock();
			}

			// remove translation button
			if ($key != 0)
			{
				$this->tpl->setCurrentBlock("removeTranslation");
				$this->tpl->setVariable("TXT_REMOVE_TRANSLATION",$this->lng->txt("remove_translation"));
				$this->ctrl->setParameter($this, "entry", $key);
				$this->ctrl->setParameter($this, "mode", "edit");
				$this->tpl->setVariable("LINK_REMOVE_TRANSLATION",
					$this->ctrl->getLinkTarget($this, "removeTranslation"));
				$this->tpl->parseCurrentBlock();
			}

			// lang selection
			$this->tpl->addBlockFile("SEL_LANGUAGE", "sel_language", "tpl.lang_selection.html",
				"Services/MetaData");
			$this->tpl->setVariable("SEL_NAME", "Fobject[".$key."][lang]");

			include_once('Services/MetaData/classes/class.ilMDLanguageItem.php');

			$languages = ilMDLanguageItem::_getLanguages();

			foreach ($languages as $code => $language)
			{
				$this->tpl->setCurrentBlock("lg_option");
				$this->tpl->setVariable("VAL_LG", $code);
				$this->tpl->setVariable("TXT_LG", $language);

				if ($code == $val["lang"])
				{
					$this->tpl->setVariable("SELECTED", "selected=\"selected\"");
				}

				$this->tpl->parseCurrentBlock();
			}

			// object data
			$this->tpl->setCurrentBlock("obj_form");

			if ($key == 0)
			{
				$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("change_header_title"));
			}
			else
			{
				$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("translation")." ".$key);
			}

			if ($key == $data["default_language"])
			{
				$this->tpl->setVariable("CHECKED", "checked=\"checked\"");
			}

			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
			$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
			$this->tpl->setVariable("TXT_DEFAULT", $this->lng->txt("default"));
			$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
			$this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($val["title"],$strip));
			$this->tpl->setVariable("DESC", ilUtil::stripSlashes($val["desc"]));
			$this->tpl->setVariable("NUM", $key);
			$this->tpl->parseCurrentBlock();
		}

		// global
		$this->tpl->setCurrentBlock("adm_content");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "saveHeaderTitle");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* save header title
	*/
	function saveHeaderTitleObject()
	{
		$data = $_POST;

		// default language set?
		if (!isset($data["default_language"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_default_language"),$this->ilias->error_obj->MESSAGE);
		}

		// prepare array fro further checks
		foreach ($data["Fobject"] as $key => $val)
		{
			$langs[$key] = $val["lang"];
		}

		$langs = array_count_values($langs);

		// all languages set?
		if (array_key_exists("",$langs))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_language_selected"),$this->ilias->error_obj->MESSAGE);
		}

		// no single language is selected more than once?
		if (array_sum($langs) > count($langs))
		{
			$this->ilias->raiseError($this->lng->txt("msg_multi_language_selected"),$this->ilias->error_obj->MESSAGE);
		}

		// copy default translation to variable for object data entry
		$_POST["Fobject"]["title"] = $_POST["Fobject"][$_POST["default_language"]]["title"];
		$_POST["Fobject"]["desc"] = $_POST["Fobject"][$_POST["default_language"]]["desc"];

		// first delete all translation entries...
		$this->object->removeHeaderTitleTranslations();

		// ...and write new translations to object_translation
		foreach ($data["Fobject"] as $key => $val)
		{
			if ($key == $data["default_language"])
			{
				$default = 1;
			}
			else
			{
				$default = 0;
			}

			$this->object->addHeaderTitleTranslation(ilUtil::stripSlashes($val["title"]),ilUtil::stripSlashes($val["desc"]),$val["lang"],$default);
		}

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->redirect($this);
	}

	function cancelObject()
	{
		$this->ctrl->redirect($this, "view");
	}

	/**
	* adds a translation form & save post vars to session
	*
	* @access	public
	*/
	function addHeaderTitleTranslationObject()
	{
		$_SESSION["translation_post"] = $_POST;

		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->setParameter($this, "entry", "0");
		$this->ctrl->redirect($this, "changeHeaderTitle");
	}

	/**
	* removes a translation form & save post vars to session
	*
	* @access	public
	*/
	function removeTranslationObject()
	{
		$this->ctrl->setParameter($this, "entry", $_GET["entry"]);
		$this->ctrl->setParameter($this, "mode", "session");
		$this->ctrl->redirect($this, "changeHeaderTitle");
	}


	function startValidator($a_mode,$a_log)
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$logging = ($a_log) ? true : false;
		include_once "./Services/Repository/classes/class.ilValidator.php";
		$validator = new ilValidator($logging);
		$validator->setMode("all",false);

		$modes = array();
		foreach ($a_mode as $mode => $value)
		{
			$validator->setMode($mode,(bool) $value);
			$modes[] = $mode.'='.$value;
		}

		$scan_log = $validator->validate();

		$mode = $this->lng->txt("scan_modes").": ".implode(', ',$modes);

		// output
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_scan.html",
			"Modules/SystemFolder");

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("scanning_system"));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_SCAN_LOG", $scan_log);
		$this->tpl->setVariable("TXT_MODE", $mode);

		if ($logging === true)
		{
			$this->tpl->setVariable("TXT_VIEW_LOG", $this->lng->txt("view_log"));
		}

		$this->tpl->setVariable("TXT_DONE", $this->lng->txt("done"));

		$validator->writeScanLogLine($mode);
	}

	function viewScanLog()
	{
		include_once "./Services/Repository/classes/class.ilValidator.php";
		$validator = new IlValidator();
		$scan_log =& $validator->readScanLog();

		if (is_array($scan_log))
		{
			$scan_log = '<pre>'.implode("",$scan_log).'</pre>';
			$this->tpl->setVariable("ADM_CONTENT", $scan_log);
		}
		else
		{
			$scan_log = "no scanlog found.";
		}

		// output
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_scan.html",
			"Modules/SystemFolder");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("scan_details"));
		$this->tpl->setVariable("COLSPAN", 3);
		$this->tpl->setVariable("TXT_SCAN_LOG", $scan_log);
		$this->tpl->setVariable("TXT_DONE", $this->lng->txt("done"));
	}


	/**
	 * Benchmark settings
	 */
	function benchmarkObject()
	{
		global $ilBench, $rbacsystem, $lng, $ilCtrl, $ilSetting, $tpl;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->benchmarkSubTabs("settings");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// Activate DB Benchmark
		$cb = new ilCheckboxInputGUI($lng->txt("adm_activate_db_benchmark"), "enable_db_bench");
		$cb->setChecked($ilSetting->get("enable_db_bench"));
		$cb->setInfo($lng->txt("adm_activate_db_benchmark_desc"));
		$this->form->addItem($cb);

		// DB Benchmark User
		$ti = new ilTextInputGUI($lng->txt("adm_db_benchmark_user"), "db_bench_user");
		$ti->setValue($ilSetting->get("db_bench_user"));
		$ti->setInfo($lng->txt("adm_db_benchmark_user_desc"));
		$this->form->addItem($ti);

		$this->form->addCommandButton("saveBenchSettings", $lng->txt("save"));

		$this->form->setTitle($lng->txt("adm_db_benchmark"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Show db benchmark results
	 */
	function showDbBenchChronologicalObject()
	{
		$this->benchmarkSubTabs("chronological");
		$this->showDbBenchResults("chronological");
	}

	/**
	 * Show db benchmark results
	 */
	function showDbBenchSlowestFirstObject()
	{
		$this->benchmarkSubTabs("slowest_first");
		$this->showDbBenchResults("slowest_first");
	}

	/**
	 * Show db benchmark results
	 */
	function showDbBenchSortedBySqlObject()
	{
		$this->benchmarkSubTabs("sorted_by_sql");
		$this->showDbBenchResults("sorted_by_sql");
	}

	/**
	 * Show db benchmark results
	 */
	function showDbBenchByFirstTableObject()
	{
		$this->benchmarkSubTabs("by_first_table");
		$this->showDbBenchResults("by_first_table");
	}

	/**
	 * Show Db Benchmark Results
	 *
	 * @param	string		mode
	 */
	function showDbBenchResults($a_mode)
	{
		global $ilBench, $lng, $tpl;

		$rec = $ilBench->getDbBenchRecords();

		include_once("./Modules/SystemFolder/classes/class.ilBenchmarkTableGUI.php");
		$table = new ilBenchmarkTableGUI($this, "benchmark", $rec, $a_mode);
		$tpl->setContent($table->getHTML());
	}

	/**
	 * Benchmark sub tabs
	 *
	 * @param
	 * @return
	 */
	function benchmarkSubTabs($a_current)
	{
		global $ilTabs, $lng, $ilCtrl, $ilBench;
		
		$ilTabs->activateTab("benchmarks"); // #18083

		$ilTabs->addSubtab("settings",
			$lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, "benchmark"));

		$rec = $ilBench->getDbBenchRecords();
		if (count($rec) > 0)
		{
			$ilTabs->addSubtab("chronological",
				$lng->txt("adm_db_bench_chronological"),
				$ilCtrl->getLinkTarget($this, "showDbBenchChronological"));
			$ilTabs->addSubtab("slowest_first",
				$lng->txt("adm_db_bench_slowest_first"),
				$ilCtrl->getLinkTarget($this, "showDbBenchSlowestFirst"));
			$ilTabs->addSubtab("sorted_by_sql",
				$lng->txt("adm_db_bench_sorted_by_sql"),
				$ilCtrl->getLinkTarget($this, "showDbBenchSortedBySql"));
			$ilTabs->addSubtab("by_first_table",
				$lng->txt("adm_db_bench_by_first_table"),
				$ilCtrl->getLinkTarget($this, "showDbBenchByFirstTable"));
		}

		$ilTabs->activateSubTab($a_current);
	}


	/**
	 * Save benchmark settings
	 */
	function saveBenchSettingsObject()
	{
		global $ilBench;

		if ($_POST["enable_db_bench"])
		{
			$ilBench->enableDbBench(true, ilUtil::stripSlashes($_POST["db_bench_user"]));
		}
		else
		{
			$ilBench->enableDbBench(false);
		}

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

		$this->ctrl->redirect($this, "benchmark");
	}


	/**
	* save benchmark settings
	*/
	function switchBenchModuleObject()
	{
		global $ilBench;

		$this->ctrl->setParameter($this,'cur_mod',$_POST['module']);
		$this->ctrl->redirect($this, "benchmark");
	}


	/**
	* delete all benchmark records
	*/
	function clearBenchObject()
	{
		global $ilBench;

		$ilBench->clearData();
		$this->saveBenchSettingsObject();

	}

	// get tabs
	function getAdminTabs(&$tabs_gui)
	{
		global $rbacsystem, $ilHelp;
		
//		$ilHelp->setScreenIdComponent($this->object->getType());

		$this->ctrl->setParameter($this,"ref_id",$this->object->getRefId());

		// general settings
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("general_settings",
				$this->ctrl->getLinkTarget($this, "showBasicSettings"),
				array("showBasicSettings", "saveBasicSettings"), get_class($this));
		}

		// server info
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("server",
				$this->ctrl->getLinkTarget($this, "showServerInfo"),
				array("showServerInfo", "view"), get_class($this));
		}

		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("cron_jobs",
				$this->ctrl->getLinkTargetByClass("ilCronManagerGUI", ""), "", get_class($this));

//			$tabs_gui->addTarget("system_check",
//				$this->ctrl->getLinkTarget($this, "check"), array("check","viewScanLog","saveCheckParams","saveCheckCron"), get_class($this));

			$tabs_gui->addTarget("benchmarks",
				$this->ctrl->getLinkTarget($this, "benchmark"), "benchmark", get_class($this));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	/**
	* Show PHP Information
	*/
	function showPHPInfoObject()
	{
		phpinfo();
		exit;
	}

	//
	//
	// Server Info
	//
	//
	
	/**
	* Set sub tabs for server info
	*/
	function setServerInfoSubTabs($a_activate)
	{
		global $ilTabs, $ilCtrl, $rbacsystem;
				
		$ilTabs->addSubTabTarget("server_data", $ilCtrl->getLinkTarget($this, "showServerInfo"));
		
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$ilTabs->addSubTabTarget("adm_https", $ilCtrl->getLinkTarget($this, "showHTTPS"));	
			$ilTabs->addSubTabTarget("proxy", $ilCtrl->getLinkTarget($this, "showProxy"));		
			$ilTabs->addSubTabTarget("java_server", $ilCtrl->getLinkTarget($this, "showJavaServer"));	
			$ilTabs->addSubTabTarget("webservices", $ilCtrl->getLinkTarget($this, "showWebServices"));							
		}
		
		$ilTabs->setSubTabActive($a_activate);
		$ilTabs->setTabActive("server");
	}

	/**
	* Show server info
	*/
	function showServerInfoObject()
	{
		/**
		 * @var $ilToolbar ilToolbarGUI
		 * @var $lng       ilLanguage
		 * @var $ilCtrl    ilCtrl
		 * @var $tpl       ilTemplate
		 */
		global $tpl, $ilCtrl, $ilToolbar, $lng;

		require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
		$button = ilLinkButton::getInstance();
		$button->setCaption('vc_information');
		$button->setUrl($this->ctrl->getLinkTarget($this, 'showVcsInformation'));
		$ilToolbar->addButtonInstance($button);

		$this->initServerInfoForm();
		$this->setServerInfoSubTabs("server_data");
		
		$btpl = new ilTemplate("tpl.server_data.html", true, true, "Modules/SystemFolder");
		$btpl->setVariable("FORM", $this->form->getHTML());
		$btpl->setVariable("PHP_INFO_TARGET", $ilCtrl->getLinkTarget($this, "showPHPInfo"));
		$tpl->setContent($btpl->get());
	}
	
	/**
	* Init server info form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initServerInfoForm()
	{
		global $lng, $ilClientIniFile, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// installation name
		$ne = new ilNonEditableValueGUI($lng->txt("inst_name"), "");
		$ne->setValue($ilClientIniFile->readVariable("client","name"));
		$ne->setInfo($ilClientIniFile->readVariable("client","description"));
		$this->form->addItem($ne);

		// client id
		$ne = new ilNonEditableValueGUI($lng->txt("client_id"), "");
		$ne->setValue(CLIENT_ID);
		$this->form->addItem($ne);
		
		// installation id
		$ne = new ilNonEditableValueGUI($lng->txt("inst_id"), "");
		$ne->setValue($ilSetting->get("inst_id"));
		$this->form->addItem($ne);
		
		// database version
		$ne = new ilNonEditableValueGUI($lng->txt("db_version"), "");
		$ne->setValue($ilSetting->get("db_version"));
		
		include_once ("./Services/Database/classes/class.ilDBUpdate.php");
		$this->form->addItem($ne);
		
		// ilias version
		$ne = new ilNonEditableValueGUI($lng->txt("ilias_version"), "");
		$ne->setValue($ilSetting->get("ilias_version"));
		$this->form->addItem($ne);

		// host
		$ne = new ilNonEditableValueGUI($lng->txt("host"), "");
		$ne->setValue($_SERVER["SERVER_NAME"]);
		$this->form->addItem($ne);
		
		// ip & port
		$ne = new ilNonEditableValueGUI($lng->txt("ip_address")." & ".$this->lng->txt("port"), "");
		$ne->setValue($_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]);
		$this->form->addItem($ne);
		
		// server
		$ne = new ilNonEditableValueGUI($lng->txt("server_software"), "");
		$ne->setValue($_SERVER["SERVER_SOFTWARE"]);
		$this->form->addItem($ne);
		
		// http path
		$ne = new ilNonEditableValueGUI($lng->txt("http_path"), "");
		$ne->setValue(ILIAS_HTTP_PATH);
		$this->form->addItem($ne);
		
		// absolute path
		$ne = new ilNonEditableValueGUI($lng->txt("absolute_path"), "");
		$ne->setValue(ILIAS_ABSOLUTE_PATH);
		$this->form->addItem($ne);
		
		$not_set = $lng->txt("path_not_set");
		
		// convert
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_convert"), "");
		$ne->setValue((PATH_TO_CONVERT) ? PATH_TO_CONVERT : $not_set);
		$this->form->addItem($ne);
		
		// zip
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_zip"), "");
		$ne->setValue((PATH_TO_ZIP) ? PATH_TO_ZIP : $not_set);
		$this->form->addItem($ne);

		// unzip
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_unzip"), "");
		$ne->setValue((PATH_TO_UNZIP) ? PATH_TO_UNZIP : $not_set);
		$this->form->addItem($ne);

		// java
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_java"), "");
		$ne->setValue((PATH_TO_JAVA) ? PATH_TO_JAVA : $not_set);
		$this->form->addItem($ne);
		
		// htmldoc
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_htmldoc"), "");
		$ne->setValue((PATH_TO_HTMLDOC) ? PATH_TO_HTMLDOC : $not_set);
		$this->form->addItem($ne);

		// mkisofs
		$ne = new ilNonEditableValueGUI($lng->txt("path_to_mkisofs"), "");
		$ne->setValue((PATH_TO_MKISOFS) ? PATH_TO_MKISOFS : $not_set);
		$this->form->addItem($ne);

		// latex
		$ne = new ilNonEditableValueGUI($lng->txt("url_to_latex"), "");
		$ne->setValue((URL_TO_LATEX) ? URL_TO_LATEX : $not_set);
		$this->form->addItem($ne);


		$this->form->setTitle($lng->txt("server_data"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	//
	//
	// General Settings
	//
	//
	
	/**
	* Set sub tabs for general settings
	*/
	function setGeneralSettingsSubTabs($a_activate)
	{
		global $ilTabs, $ilCtrl;
		
		$ilTabs->addSubTabTarget("basic_settings", $ilCtrl->getLinkTarget($this, "showBasicSettings"));
		$ilTabs->addSubTabTarget("header_title", $ilCtrl->getLinkTarget($this, "showHeaderTitle"));			
		$ilTabs->addSubTabTarget("contact_data", $ilCtrl->getLinkTarget($this, "showContactInformation"));
		$ilTabs->addSubTabTarget("adm_imprint", $ilCtrl->getLinkTargetByClass("ilimprintgui", "preview"));
	
		$ilTabs->setSubTabActive($a_activate);
		$ilTabs->setTabActive("general_settings");
	}

	//
	//
	// Basic Settings
	//
	//
	
	/**
	* Show basic settings
	*/
	function showBasicSettingsObject()
	{
		global $tpl;

		$this->initBasicSettingsForm();
		$this->setGeneralSettingsSubTabs("basic_settings");
		
		$tpl->setContent($this->form->getHTML());
	}

	
	/**
	* Init basic settings form.
	*/
	public function initBasicSettingsForm()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilSetting ilSetting
		 */
		global $lng, $ilSetting;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$lng->loadLanguageModule("pd");
		
		// installation short title
		$ti = new ilTextInputGUI($this->lng->txt("short_inst_name"), "short_inst_name");
		$ti->setMaxLength(200);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("short_inst_name"));
		$ti->setInfo($this->lng->txt("short_inst_name_info"));
		$this->form->addItem($ti);

		// public section
		$cb = new ilCheckboxInputGUI($this->lng->txt("pub_section"), "pub_section");
		$cb->setInfo($lng->txt("pub_section_info"));
		if ($ilSetting->get("pub_section"))
		{
			$cb->setChecked(true);
		}				
		$this->form->addItem($cb);
				
			// Enable Global Profiles
			$cb_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_user_publish'), 'enable_global_profiles');
			$cb_prop->setInfo($lng->txt('pd_enable_user_publish_info'));
			$cb_prop->setChecked($ilSetting->get('enable_global_profiles'));
			$cb->addSubItem($cb_prop);

		// search engine
		include_once('Services/PrivacySecurity/classes/class.ilRobotSettings.php');
		$robot_settings = ilRobotSettings::_getInstance();
		$cb2 = new ilCheckboxInputGUI($this->lng->txt("search_engine"), "open_google");
		$cb2->setInfo($this->lng->txt("enable_search_engine"));
		$this->form->addItem($cb2);

		if(!$robot_settings->checkRewrite())
		{
			$cb2->setAlert($lng->txt("allow_override_alert"));
			$cb2->setChecked(false);
			$cb2->setDisabled(true);
		}
		else
		{
			if ($ilSetting->get("open_google"))
			{
				$cb2->setChecked(true);
			}
		}			
		
		// locale
		$ti = new ilTextInputGUI($this->lng->txt("adm_locale"), "locale");
		$ti->setMaxLength(80);
		$ti->setSize(40);
		$ti->setInfo($this->lng->txt("adm_locale_info"));
		$ti->setValue($ilSetting->get("locale"));
		$this->form->addItem($ti);				
	
		// starting point
		include_once "Services/User/classes/class.ilUserUtil.php";
		$si = new ilRadioGroupInputGUI($this->lng->txt("adm_user_starting_point"), "usr_start");
		$si->setRequired(true);
		$si->setInfo($this->lng->txt("adm_user_starting_point_info"));
		$valid = array_keys(ilUserUtil::getPossibleStartingPoints());
		foreach(ilUserUtil::getPossibleStartingPoints(true) as $value => $caption)
		{
			$opt = new ilRadioOption($caption, $value);
			$si->addOption($opt);
			
			if(!in_array($value, $valid))
			{
				$opt->setInfo($this->lng->txt("adm_user_starting_point_invalid_info"));
			}			
		}
		$si->setValue(ilUserUtil::getStartingPoint());		
		$this->form->addItem($si);
		
		// starting point: repository object
		$repobj = new ilRadioOption($lng->txt("adm_user_starting_point_object"), ilUserUtil::START_REPOSITORY_OBJ);
		$repobj_id = new ilTextInputGUI($lng->txt("adm_user_starting_point_ref_id"), "usr_start_ref_id");
		$repobj_id->setRequired(true);
		$repobj_id->setSize(5);
		if($si->getValue() == ilUserUtil::START_REPOSITORY_OBJ)
		{
			$start_ref_id = ilUserUtil::getStartingObject();
			$repobj_id->setValue($start_ref_id);
			if($start_ref_id)
			{
				$start_obj_id = ilObject::_lookupObjId($start_ref_id);
				if($start_obj_id)
				{
					$repobj_id->setInfo($lng->txt("obj_".ilObject::_lookupType($start_obj_id)).
						": ".ilObject::_lookupTitle($start_obj_id));
				}
			}
		}		
		$repobj->addSubItem($repobj_id);
		$si->addOption($repobj);
		
		// starting point: personal		
		$startp = new ilCheckboxInputGUI($lng->txt("adm_user_starting_point_personal"), "usr_start_pers");
		$startp->setInfo($lng->txt("adm_user_starting_point_personal_info"));
		$startp->setChecked(ilUserUtil::hasPersonalStartingPoint());
		$si->addSubItem($startp);
				
		
		// save and cancel commands
		$this->form->addCommandButton("saveBasicSettings", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("basic_settings"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save basic settings form
	*
	*/
	public function saveBasicSettingsObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
	
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initBasicSettingsForm();
		if ($this->form->checkInput())
		{
			$ilSetting->set("short_inst_name", $_POST["short_inst_name"]);
			$ilSetting->set("pub_section", $_POST["pub_section"]);
			
				$global_profiles = ($_POST["pub_section"])
					? (int)$_POST['enable_global_profiles']
					: 0;				
				$ilSetting->set('enable_global_profiles', $global_profiles);
								
			$ilSetting->set("open_google", $_POST["open_google"]);			
			$ilSetting->set("locale", $_POST["locale"]);
						
			include_once "Services/User/classes/class.ilUserUtil.php";
			ilUserUtil::setStartingPoint($this->form->getInput('usr_start'), $this->form->getInput('usr_start_ref_id'));
			ilUserUtil::togglePersonalStartingPoint($this->form->getInput('usr_start_pers'));

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showBasicSettings");
		}
		$this->setGeneralSettingsSubTabs("basic_settings");
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	//
	//
	// Header title
	//
	//

	/**
	* Show header title
	*/
	function showHeaderTitleObject($a_get_post_values = false)
	{
		global $tpl;
		
		$this->setGeneralSettingsSubTabs("header_title");
		include_once("./Services/Object/classes/class.ilObjectTranslationTableGUI.php");
		$table = new ilObjectTranslationTableGUI($this, "showHeaderTitle", false);
		if ($a_get_post_values)
		{
			$vals = array();
			foreach($_POST["title"] as $k => $v)
			{
				$vals[] = array("title" => $v,
					"desc" => $_POST["desc"][$k],
					"lang" => $_POST["lang"][$k],
					"default" => ($_POST["default"] == $k));
			}
			$table->setData($vals);
		}
		else
		{
			$data = $this->object->getHeaderTitleTranslations();
			if (is_array($data["Fobject"]))
			{
				foreach($data["Fobject"] as $k => $v)
				{
					if ($k == $data["default_language"])
					{
						$data["Fobject"][$k]["default"] = true;
					}
					else
					{
						$data["Fobject"][$k]["default"] = false;
					}
				}
			}
			else
			{
				$data["Fobject"] = array();
			}
			$table->setData($data["Fobject"]);
		}
		$tpl->setContent($table->getHTML());
	}

	/**
	* Save header titles
	*/
	function saveHeaderTitlesObject()
	{
		global $ilCtrl, $lng, $rbacsystem;

		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
//		var_dump($_POST);
		
		// default language set?
		if (!isset($_POST["default"]) && count($_POST["lang"]) > 0)
		{
			ilUtil::sendFailure($lng->txt("msg_no_default_language"));
			return $this->showHeaderTitleObject(true);
		}

		// all languages set?
		if (array_key_exists("",$_POST["lang"]))
		{
			ilUtil::sendFailure($lng->txt("msg_no_language_selected"));
			return $this->showHeaderTitleObject(true);
		}

		// no single language is selected more than once?
		if (count(array_unique($_POST["lang"])) < count($_POST["lang"]))
		{
			ilUtil::sendFailure($lng->txt("msg_multi_language_selected"));
			return $this->showHeaderTitleObject(true);
		}

		// save the stuff
		$this->object->removeHeaderTitleTranslations();
		foreach($_POST["title"] as $k => $v)
		{
			$this->object->addHeaderTitleTranslation(
				ilUtil::stripSlashes($v),
				ilUtil::stripSlashes($_POST["desc"][$k]),
				ilUtil::stripSlashes($_POST["lang"][$k]),
				($_POST["default"] == $k));
		}
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "showHeaderTitle");
	}
	
	/**
	* Add a header title
	*/
	function addHeaderTitleObject()
	{
		global $ilCtrl, $lng;
		
		if (is_array($_POST["title"]))
		{
			foreach($_POST["title"] as $k => $v) {}
		}
		$k++;
		$_POST["title"][$k] = "";
		$this->showHeaderTitleObject(true);
	}
	
	/**
	* Remove header titles
	*/
	function deleteHeaderTitlesObject()
	{
		global $ilCtrl, $lng;
//var_dump($_POST);
		foreach($_POST["title"] as $k => $v)
		{
			if ($_POST["check"][$k])
			{
				unset($_POST["title"][$k]);
				unset($_POST["desc"][$k]);
				unset($_POST["lang"][$k]);
				if ($k == $_POST["default"])
				{
					unset($_POST["default"]);
				}
			}
		}
		$this->saveHeaderTitlesObject();
	}
	
	
	//
	//
	// Cron Jobs
	//
	//	
								
	/*
	 * OLD GLOBAL CRON JOB SWITCHES (ilSetting)
	 * 
	 * cron_user_check => obsolete
	 * cron_inactive_user_delete => obsolete
	 * cron_inactivated_user_delete => obsolete
	 * cron_link_check => obsolete	 
	 * cron_web_resource_check => migrated
	 * cron_lucene_index => obsolete	 
	 * forum_notification => migrated
	 * mail_notification => migrated
	 * disk_quota/enabled => migrated
	 * payment_notification => migrated
	 * crsgrp_ntf => migrated 
	 * cron_upd_adrbook => migrated		
	 */		
	
	function jumpToCronJobsObject()
	{
		// #13010 - this is used for external settings 
		$this->ctrl->redirectByClass("ilCronManagerGUI", "render");
	}
	
	
	//
	//
	// Contact Information
	//
	//
	
	/**
	* Show contact information
	*/
	function showContactInformationObject()
	{
		global $tpl;
		
		$this->initContactInformationForm();
		$this->setGeneralSettingsSubTabs("contact_data");
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init contact information form.
	*/
	public function initContactInformationForm()
	{
		global $lng, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// first name
		$ti = new ilTextInputGUI($this->lng->txt("firstname"), "admin_firstname");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_firstname"));
		$this->form->addItem($ti);
		
		// last name
		$ti = new ilTextInputGUI($this->lng->txt("lastname"), "admin_lastname");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_lastname"));
		$this->form->addItem($ti);
		
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "admin_title");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("admin_title"));
		$this->form->addItem($ti);
		
		// position
		$ti = new ilTextInputGUI($this->lng->txt("position"), "admin_position");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("admin_position"));
		$this->form->addItem($ti);
		
		// institution
		$ti = new ilTextInputGUI($this->lng->txt("institution"), "admin_institution");
		$ti->setMaxLength(200);
		$ti->setSize(40);
		$ti->setValue($ilSetting->get("admin_institution"));
		$this->form->addItem($ti);
		
		// street
		$ti = new ilTextInputGUI($this->lng->txt("street"), "admin_street");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_street"));
		$this->form->addItem($ti);
		
		// zip code
		$ti = new ilTextInputGUI($this->lng->txt("zipcode"), "admin_zipcode");
		$ti->setMaxLength(10);
		$ti->setSize(5);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_zipcode"));
		$this->form->addItem($ti);
		
		// city
		$ti = new ilTextInputGUI($this->lng->txt("city"), "admin_city");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_city"));
		$this->form->addItem($ti);
		
		// country
		$ti = new ilTextInputGUI($this->lng->txt("country"), "admin_country");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_country"));
		$this->form->addItem($ti);
		
		// phone
		$ti = new ilTextInputGUI($this->lng->txt("phone"), "admin_phone");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		//$ti->setRequired(true);
		$ti->setValue($ilSetting->get("admin_phone"));
		$this->form->addItem($ti);
		
		// email
		$ti = new ilEmailInputGUI($this->lng->txt("email"), "admin_email");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->allowRFC822(true);
		$ti->setValue($ilSetting->get("admin_email"));
		$this->form->addItem($ti);
		
		// feedback recipient
		/* currently used in:
		- footer
		- terms of service: no document found message
		*/
		/*$ti = new ilEmailInputGUI($this->lng->txt("feedback_recipient"), "feedback_recipient");
		$ti->setInfo(sprintf($this->lng->txt("feedback_recipient_info"), $this->lng->txt("contact_sysadmin")));
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->setRequired(true);
		$ti->allowRFC822(true);
		$ti->setValue($ilSetting->get("feedback_recipient"));		
		$this->form->addItem($ti);*/

		// System support contacts
		include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");
		$ti = new ilTextInputGUI($this->lng->txt("adm_support_contacts"), "adm_support_contacts");
		$ti->setMaxLength(500);
		$ti->setValue(ilSystemSupportContacts::getList());
		//$ti->setSize();
		$ti->setInfo($this->lng->txt("adm_support_contacts_info"));
		$this->form->addItem($ti);

		
		// error recipient
		/*$ti = new ilEmailInputGUI($this->lng->txt("error_recipient"), "error_recipient");
		$ti->setMaxLength(64);
		$ti->setSize(40);
		$ti->allowRFC822(true);
		$ti->setValue($ilSetting->get("error_recipient"));
		$this->form->addItem($ti);*/
		
		$this->form->addCommandButton("saveContactInformation", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("contact_data"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save contact information form
	*
	*/
	public function saveContactInformationObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
	
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initContactInformationForm();
		if ($this->form->checkInput())
		{
			$fs = array("admin_firstname", "admin_lastname", "admin_title", "admin_position", 
				"admin_institution", "admin_street", "admin_zipcode", "admin_city", 
				"admin_country", "admin_phone", "admin_email");
			foreach ($fs as $f)
			{
				$ilSetting->set($f, $_POST[$f]);
			}

			include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");
			ilSystemSupportContacts::setList($_POST["adm_support_contacts"]);

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showContactInformation");
		}
		else
		{
			$this->setGeneralSettingsSubTabs("contact_data");
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}

	//
	//
	// Web Services
	//
	//

	/**
	* Show Web Services
	*/
	function showWebServicesObject()
	{
		global $tpl;
		
		$this->initWebServicesForm();
		$this->setServerInfoSubTabs("webservices");
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init web services form.
	*/
	public function initWebServicesForm()
	{
		global $lng, $ilSetting;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// soap administration
		$cb = new ilCheckboxInputGUI($this->lng->txt("soap_user_administration"), "soap_user_administration");
		$cb->setInfo($this->lng->txt("soap_user_administration_desc"));
		if ($ilSetting->get("soap_user_administration"))
		{
			$cb->setChecked(true);
		}
		$this->form->addItem($cb);
		
		// wsdl path
		$wsdl = new ilTextInputGUI($this->lng->txt('soap_wsdl_path'), 'soap_wsdl_path');
		$wsdl->setInfo(sprintf($this->lng->txt('soap_wsdl_path_info'), "<br />'".ILIAS_HTTP_PATH."/webservice/soap/server.php?wsdl'"));
		$wsdl->setValue((string)$ilSetting->get('soap_wsdl_path'));
		$wsdl->setSize(60);
		$wsdl->setMaxLength(255);
		$this->form->addItem($wsdl);

		// response timeout
		$ctime = new ilNumberInputGUI($this->lng->txt('soap_connect_timeout'), 'ctimeout');
		$ctime->setMinValue(1);
		$ctime->setSize(2);
		$ctime->setMaxLength(3);
		include_once './Services/WebServices/SOAP/classes/class.ilSoapClient.php';
		$ctime->setValue((int) $ilSetting->get('soap_connect_timeout',  ilSoapClient::DEFAULT_CONNECT_TIMEOUT));
		$ctime->setInfo($this->lng->txt('soap_connect_timeout_info'));
		$this->form->addItem($ctime);

		$this->form->addCommandButton("saveWebServices", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("webservices"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save web services form
	*
	*/
	public function saveWebServicesObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
	
		$this->initWebServicesForm();
		if ($this->form->checkInput())
		{
			$ilSetting->set('soap_user_administration', $this->form->getInput('soap_user_administration'));
			$ilSetting->set('soap_wsdl_path', trim($this->form->getInput('soap_wsdl_path')));
			$ilSetting->set('soap_connect_timeout',$this->form->getInput('ctimeout'));
			
			ilUtil::sendSuccess($lng->txt('msg_obj_modified'), true);
			$ilCtrl->redirect($this, 'showWebServices');
		}
		else
		{
			$this->setGeneralSettingsSubTabs("webservices");
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	//
	//
	// Java Server
	//
	//

	/**
	* Show Java Server Settings
	*/
	function showJavaServerObject()
	{
		global $tpl;
		
		$tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.java_settings.html','Modules/SystemFolder');
		
		$GLOBALS['lng']->loadLanguageModule('search');

		include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->lng->txt('lucene_create_ini'),
			$this->ctrl->getLinkTarget($this,'createJavaServerIni'));
		$tpl->setVariable('ACTION_BUTTONS',$toolbar->getHTML());
		
		$this->initJavaServerForm();
		$this->setServerInfoSubTabs("java_server");
		$tpl->setVariable('SETTINGS_TABLE',$this->form->getHTML());
	}
	
	/**
	 * Create a server ini file
	 * @return 
	 */
	public function createJavaServerIniObject()
	{	
		$this->setGeneralSettingsSubTabs('java_server');
		$this->initJavaServerIniForm();
		$this->tpl->setContent($this->form->getHTML());
	}
	
	protected function initJavaServerIniForm()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		
		$this->form = new ilPropertyFormGUI();
		
		$GLOBALS['lng']->loadLanguageModule('search');
		
		$this->form->setTitle($this->lng->txt('lucene_tbl_create_ini'));
		$this->form->setFormAction($this->ctrl->getFormAction($this,'createJavaServerIni'));
		$this->form->addCommandButton('downloadJavaServerIni',$this->lng->txt('lucene_download_ini'));
		$this->form->addCommandButton('showJavaServer', $this->lng->txt('cancel'));
		
		// Host
		$ip = new ilTextInputGUI($this->lng->txt('lucene_host'),'ho');
		$ip->setInfo($this->lng->txt('lucene_host_info'));
		$ip->setMaxLength(128);
		$ip->setSize(32);
		$ip->setRequired(true);
		$this->form->addItem($ip);
		
		// Port
		$port = new ilNumberInputGUI($this->lng->txt('lucene_port'),'po');
		$port->setSize(5);
		$port->setMinValue(1);
		$port->setMaxValue(65535);
		$port->setRequired(true);
		$this->form->addItem($port);
		
		// Index Path
		$path = new ilTextInputGUI($this->lng->txt('lucene_index_path'),'in');
		$path->setSize(80);
		$path->setMaxLength(1024);
		$path->setInfo($this->lng->txt('lucene_index_path_info'));
		$path->setRequired(true);
		$this->form->addItem($path);
		
		// Logging
		$log = new ilTextInputGUI($this->lng->txt('lucene_log'),'lo');
		$log->setSize(80);
		$log->setMaxLength(1024);
		$log->setInfo($this->lng->txt('lucene_log_info'));
		$log->setRequired(true);
		$this->form->addItem($log);
		
		// Level
		$lev = new ilSelectInputGUI($this->lng->txt('lucene_level'),'le');
		$lev->setOptions(array(
			'DEBUG'		=> 'DEBUG',
			'INFO'		=> 'INFO',
			'WARN'		=> 'WARN',
			'ERROR'		=> 'ERROR',
			'FATAL'		=> 'FATAL'));
		$lev->setValue('INFO');
		$lev->setRequired(true);
		$this->form->addItem($lev);
		
		// CPU
		$cpu = new ilNumberInputGUI($this->lng->txt('lucene_cpu'),'cp');
		$cpu->setValue(1);
		$cpu->setSize(1);
		$cpu->setMaxLength(2);
		$cpu->setMinValue(1);
		$cpu->setRequired(true);
		$this->form->addItem($cpu);

		// Max file size
		$fs = new ilNumberInputGUI($this->lng->txt('lucene_max_fs'), 'fs');
		$fs->setInfo($this->lng->txt('lucene_max_fs_info'));
		$fs->setValue(500);
		$fs->setSize(4);
		$fs->setMaxLength(4);
		$fs->setMinValue(1);
		$fs->setRequired(true);
		$this->form->addItem($fs);
		
		return true;
	}
	
	/**
	 * Create and offer server ini file for download
	 * @return 
	 */
	protected function downloadJavaServerIniObject()
	{
		$this->initJavaServerIniForm();
		if($this->form->checkInput())
		{
			include_once './Services/WebServices/RPC/classes/class.ilRpcIniFileWriter.php';
			$ini = new ilRpcIniFileWriter();
			$ini->setHost($this->form->getInput('ho'));
			$ini->setPort($this->form->getInput('po'));
			$ini->setIndexPath($this->form->getInput('in'));
			$ini->setLogPath($this->form->getInput('lo'));
			$ini->setLogLevel($this->form->getInput('le'));
			$ini->setNumThreads($this->form->getInput('cp'));
			$ini->setMaxFileSize($this->form->getInput('fs'));
			
			$ini->write();
			ilUtil::deliverData($ini->getIniString(),'ilServer.ini','text/plain','utf-8');
			return true;
		}
		
		$this->form->setValuesByPost();
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->setGeneralSettingsSubTabs('java_server');
		$this->tpl->setContent($this->form->getHTML());
		return true;
	}

	/**
	* Init java server form.
	*/
	public function initJavaServerForm()
	{
		global $lng, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// host
		$ti = new ilTextInputGUI($this->lng->txt("java_server_host"), "rpc_server_host");
		$ti->setMaxLength(64);
		$ti->setSize(32);
		$ti->setValue($ilSetting->get("rpc_server_host"));
		$this->form->addItem($ti);
		
		// port
		$ti = new ilNumberInputGUI($this->lng->txt("java_server_port"), "rpc_server_port");
		$ti->setMaxLength(5);
		$ti->setSize(5);
		$ti->setValue($ilSetting->get("rpc_server_port"));
		$this->form->addItem($ti);
		
		// pdf fonts
		$pdf = new ilFormSectionHeaderGUI();
		$pdf->setTitle($this->lng->txt('rpc_pdf_generation'));
		$this->form->addItem($pdf);
		
		$pdf_font = new ilTextInputGUI($this->lng->txt('rpc_pdf_font'), 'rpc_pdf_font');
		$pdf_font->setInfo($this->lng->txt('rpc_pdf_font_info'));
		$pdf_font->setSize(64);
		$pdf_font->setMaxLength(1024);
		$pdf_font->setRequired(true);
		$pdf_font->setValue(
				$ilSetting->get('rpc_pdf_font','Helvetica, unifont'));
		$this->form->addItem($pdf_font);
		
	
		// save and cancel commands
		$this->form->addCommandButton("saveJavaServer", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("java_server"));
		$this->form->setDescription($lng->txt("java_server_info").
			'<br /><a href="Services/WebServices/RPC/lib/README.txt" target="_blank">'.
			$lng->txt("java_server_readme").'</a>');
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Save java server form
	*
	*/
	public function saveJavaServerObject()
	{
		global $tpl, $lng, $ilCtrl, $ilSetting, $rbacsystem;
	
		if (!$rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initJavaServerForm();
		if ($this->form->checkInput())
		{
			$ilSetting->set("rpc_server_host", trim($_POST["rpc_server_host"]));
			$ilSetting->set("rpc_server_port", trim($_POST["rpc_server_port"]));
			$ilSetting->set('rpc_pdf_font',ilUtil::stripSlashes($_POST['rpc_pdf_font']));
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showJavaServer");
			
			// TODO check settings, ping server 
		}
		else
		{
			$this->setGeneralSettingsSubTabs("java_server");
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
	
	/**
	 * 
	 * Show proxy settings
	 * 
	 * @access	public
	 * 
	 */
	public function showProxyObject()
	{
		global $tpl, $ilAccess, $ilSetting;
		
		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		require_once './Services/Http/classes/class.ilProxySettings.php';
		
		$this->initProxyForm();
		$this->form->setValuesByArray(array(
			'proxy_status' => ilProxySettings::_getInstance()->isActive(),
			'proxy_host' => ilProxySettings::_getInstance()->getHost(),
			'proxy_port' => ilProxySettings::_getInstance()->getPort()
		));
		if(ilProxySettings::_getInstance()->isActive())
		{
			$this->printProxyStatus();
		}
		
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * 
	 * Print proxy settings
	 * 
	 * @access	private
	 * 
	 */
	private function printProxyStatus()
	{
		try
		{
			ilProxySettings::_getInstance()->checkConnection();
			$this->form->getItemByPostVar('proxy_availability')->setHTML(
				'<img src="'.ilUtil::getImagePath('icon_ok.svg').'" /> '.
				$this->lng->txt('proxy_connectable')
			);	
		}
		catch(ilProxyException $e)
		{
			$this->form->getItemByPostVar('proxy_availability')->setHTML(
				'<img src="'.ilUtil::getImagePath('icon_not_ok.svg').'" /> '.
				$this->lng->txt('proxy_not_connectable')
			);
			ilUtil::sendFailure($this->lng->txt('proxy_pear_net_socket_error').': '.$e->getMessage());
		}
	}
	
	/**
	 * 
	 * Save proxy settings
	 * 
	 * @access	public
	 * 
	 */
	public function saveProxyObject()
	{
		global $tpl, $ilAccess, $ilSetting, $lng;
		
		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		require_once './Services/Http/classes/class.ilProxySettings.php';
		
		$this->initProxyForm();	
		$isFormValid = $this->form->checkInput();
		ilProxySettings::_getInstance()->isActive((int)$this->form->getInput('proxy_status'))
							   		   ->setHost(trim($this->form->getInput('proxy_host')))
							   			->setPort(trim($this->form->getInput('proxy_port')));
		if($isFormValid)
		{
			if(ilProxySettings::_getInstance()->isActive())
			{
				if(!strlen(ilProxySettings::_getInstance()->getHost()))
				{
					$isFormValid = false;
					$this->form->getItemByPostVar('proxy_host')->setAlert($lng->txt('msg_input_is_required'));
				}
				if(!strlen(ilProxySettings::_getInstance()->getPort()))
				{
					$isFormValid = false;
					$this->form->getItemByPostVar('proxy_port')->setAlert($lng->txt('msg_input_is_required'));
				}
				if(!preg_match('/[0-9]{1,}/', ilProxySettings::_getInstance()->getPort()) ||
				   ilProxySettings::_getInstance()->getPort() < 0 || 
				   ilProxySettings::_getInstance()->getPort() > 65535)
				{
					$isFormValid = false;
					$this->form->getItemByPostVar('proxy_port')->setAlert($lng->txt('proxy_port_numeric'));
				}
			}
			
			if($isFormValid)
			{
				ilProxySettings::_getInstance()->save();
				ilUtil::sendSuccess($lng->txt('saved_successfully'));
				if(ilProxySettings::_getInstance()->isActive())
				{		
					$this->printProxyStatus();
				}
			}
			else
			{
				ilUtil::sendFailure($lng->txt('form_input_not_valid'));
			}		
		}
		
		$this->form->setValuesByPost();		
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * 
	 * Initialize proxy settings form
	 * 
	 * @access	public
	 * 
	 */
	private function initProxyForm()
	{
		global $lng, $ilCtrl;
		
		$this->setServerInfoSubTabs('proxy');
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($ilCtrl->getFormAction($this, 'saveProxy'));
		
		// Proxy status
		$proxs = new ilCheckboxInputGUI($lng->txt('proxy_status'), 'proxy_status');
		$proxs->setInfo($lng->txt('proxy_status_info'));
		$proxs->setValue(1);
		$this->form->addItem($proxs);
		
		// Proxy availability
		$proxa = new ilCustomInputGUI('', 'proxy_availability');
		$proxs->addSubItem($proxa);
	
		// Proxy
		$prox = new ilTextInputGUI($lng->txt('proxy_host'), 'proxy_host');
		$prox->setInfo($lng->txt('proxy_host_info'));
		$proxs->addSubItem($prox);

		// Proxy Port
		$proxp = new ilTextInputGUI($lng->txt('proxy_port'), 'proxy_port');
		$proxp->setInfo($lng->txt('proxy_port_info'));
		$proxp->setSize(10);
		$proxp->setMaxLength(10);
		$proxs->addSubItem($proxp);
	
		// save and cancel commands
		$this->form->addCommandButton('saveProxy', $lng->txt('save'));
	}
	
	public function showHTTPSObject()
	{
		global $tpl, $ilAccess;
		
		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$form = $this->initHTTPSForm();		
		$tpl->setContent($form->getHTML());
	}
	
	public function saveHTTPSObject()
	{
		global $tpl, $lng, $ilCtrl;
		
		$form = $this->initHTTPSForm();
		if($form->checkInput())
		{
			$security = ilSecuritySettings::_getInstance();
			
			// ilias https handling settings
			$security->setHTTPSEnabled($_POST["https_enabled"]);
			
			if($security->validate($form))
			{
				$security->save();
				
				ilUtil::sendSuccess($lng->txt('saved_successfully'), true);
				$ilCtrl->redirect($this, "showHTTPS");
			}			
		}
		
		$form->setValuesByPost();		
		$tpl->setContent($form->getHTML());
	}
	
	private function initHTTPSForm()
	{
		global $ilCtrl, $lng;
		
		$this->setServerInfoSubTabs('adm_https');
		
		$lng->loadLanguageModule('ps');
		
		include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
		$security = ilSecuritySettings::_getInstance();
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setTitle($lng->txt("adm_https"));
		$form->setFormAction($ilCtrl->getFormAction($this, 'saveHTTPS'));
		
		$check2 = new ilCheckboxInputGUI($lng->txt('activate_https'),'https_enabled');
		$check2->setChecked($security->isHTTPSEnabled() ? 1 : 0);
		$check2->setValue(1);
		$form->addItem($check2);
		
		// save and cancel commands
		$form->addCommandButton('saveHTTPS', $lng->txt('save'));
		
		return $form;
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{
		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_SECURITY:
				
				include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
				$security = ilSecuritySettings::_getInstance();
								
				$subitems = null;
				
				$fields['activate_https'] = 
					array($security->isHTTPSEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL);
				
				return array("general_settings" => array("showHTTPS", $fields));
		}
	}
	
	/**
	 * goto target group
	 */
	public static function _goto()
	{
		global $ilAccess, $ilErr, $lng;

		$a_target = SYSTEM_FOLDER_ID;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			ilUtil::redirect("ilias.php?baseClass=ilAdministrationGUI");
			exit;
		}
		else
		{
			if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
			{
				ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
					ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
				ilObjectGUI::_gotoRepositoryRoot();
			}
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

	/**
	 *
	 */
	protected function showVcsInformationObject()
	{
		$vc_info = array();

		require_once 'Services/Administration/classes/class.ilSubversionInformation.php';
		require_once 'Services/Administration/classes/class.ilGitInformation.php';

		foreach(array(new ilSubversionInformation(), new ilGitInformation()) as $vc)
		{
			$html = $vc->getInformationAsHtml();
			if($html)
			{
				$vc_info[] = $html;
			}
		}

		if($vc_info)
		{
			ilUtil::sendInfo(implode("<br />", $vc_info));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('vc_information_not_determined'));
		}

		$this->showServerInfoObject();
	}
}