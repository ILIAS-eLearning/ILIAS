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
* Class ilObjUserTrackingGUI
*
* @author Arlon Yin <arlon_yin@hotmail.com>
* @author Alex Killing <alex.killing@gmx.de>
* @author Jens Conze <jc@databay.de>
*
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*
* @ilCtrl_Calls ilObjUserTrackingGUI: ilLearningProgressGUI, ilPermissionGUI
*/

include_once "classes/class.ilObjectGUI.php";

class ilObjUserTrackingGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	var $conditions;

	var $tpl = null;
	var $ilErr = null;
	var $lng = null;
	var $ctrl = null;

	function ilObjUserTrackingGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $tpl,$ilErr,$lng,$ilCtrl;

		$this->type = "trac";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);

		$this->tpl =& $tpl;
		$this->ilErr =& $ilErr;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('trac');

		$this->ctrl =& $ilCtrl;
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		$this->ctrl->setReturn($this, "show");
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'illearningprogressgui':
				$this->tabs_gui->setTabActive('learning_progress');
				include_once("./Services/Tracking/classes/class.ilLearningProgressGUI.php");
				$lp_gui =& new ilLearningProgressGUI(LP_MODE_ADMINISTRATION);
				$ret =& $this->ctrl->forwardCommand($lp_gui);
				break;
				
			default:
				$cmd = $this->ctrl->getCmd();
				if ($cmd == "view" || $cmd == "")
				{
					$cmd = "trackingDataQueryForm";
				}
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("tracking_data",
								 $this->ctrl->getLinkTarget($this,
															"trackingDataQueryForm"),
								 "trackingDataQueryForm",
								 get_class($this));
			$tabs_gui->addTarget("settings",
								 $this->ctrl->getLinkTarget($this,
															"settings"),
								 "settings",
								 get_class($this));
			$tabs_gui->addTarget("manage_tracking_data",
								 $this->ctrl->getLinkTarget($this,
															"manageData"),
								 "manageData",
								 get_class($this));

			if (ilObjUserTracking::_enabledLearningProgress())
			{
				$tabs_gui->addTarget("learning_progress",
									 $this->ctrl->getLinkTargetByClass("illearningprogressgui",
																	   "show"),
									 "",
									 "illearningprogressgui");
			}
			$tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), 
								 array("perm","info","owner"),
								 'ilpermissiongui');
		}
	}


	/**
	* display tracking settings form
	*/
	function settingsObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read_track"),$ilErr->WARNING);
		}

		$this->tabs_gui->setTabActive('settings');
	
		// Tracking settings
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.trac_settings.html","Services/Tracking");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormaction($this));
		
		// some language variables
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('tracking_settings'));
		$this->tpl->setVariable("TXT_TRACKING_SETTINGS", $this->lng->txt("tracking_settings"));
		$this->tpl->setVariable("TXT_ACTIVATE_TRACKING", $this->lng->txt("activate_tracking"));
		$this->tpl->setVariable("TXT_USER_RELATED_DATA", $this->lng->txt("trac_anonymized"));
		$this->tpl->setVariable("INFO_USER_RELATED_DATA",$this->lng->txt("trac_anonymized_info"));
		$this->tpl->setVariable("TXT_VALID_REQUEST",$this->lng->txt('trac_valid_request'));
		$this->tpl->setVariable("INFO_VALID_REQUEST",$this->lng->txt('info_valid_request'));
		$this->tpl->setVariable("SECONDS",$this->lng->txt('seconds'));

		#$this->tpl->setVariable("TXT_NUMBER_RECORDS", $this->lng->txt("number_of_records"));
		#$this->tpl->setVariable("NUMBER_RECORDS", $this->object->getRecordsTotal());
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		// BEGIN ChangeEvent
		$this->tpl->setVariable('TXT_USER_TRACKING', $this->lng->txt('trac_user_activities'));
		$this->tpl->setVariable('TXT_LEARNING_PROGRESS_TRACKING', $this->lng->txt('trac_learning_progress'));
		$this->tpl->setVariable('TXT_CHANGE_EVENT_TRACKING', $this->lng->txt('trac_repository_changes'));
		if($this->object->getActivationStatus() == UT_ACTIVE_BOTH ||
			$this->object->getActivationStatus() == UT_ACTIVE_UT)
		{
			$this->tpl->setVariable('USER_TRACKING_CHECKED', ' checked="1" ');
		}
		if($this->object->getActivationStatus() == UT_ACTIVE_BOTH ||
			$this->object->getActivationStatus() == UT_ACTIVE_LP)
		{
			$this->tpl->setVariable('LEARNING_PROGRESS_TRACKING_CHECKED', ' checked="1" ');
		}
		if($this->object->isChangeEventTrackingEnabled())
		{
			$this->tpl->setVariable('CHANGE_EVENT_TRACKING_CHECKED', ' checked="1" ');
		}
		// END ChangeEvent
		
		// Anonymized
		if(!$this->object->_enabledUserRelatedData())
		{
			$this->tpl->setVariable("USER_RELATED_CHECKED", " checked=\"1\" ");
		}
		// Max time gap
		$this->tpl->setVariable("VALID_REQUEST",$this->object->getValidTimeSpan());

	}

	/**
	* save user tracking settings
	*/
	function saveSettingsObject()
	{
		// BEGIN ChangeEvent
		if ($_POST['user_tracking'] == '1')
		{
			$activation_status = ($_POST['learning_progress_tracking'] == '1') ? UT_ACTIVE_BOTH : UT_ACTIVE_UT;
		}
		else
		{
			$activation_status = ($_POST['learning_progress_tracking'] == '1') ? UT_ACTIVE_LP : UT_INACTIVE_BOTH;
		}
		$this->object->setActivationStatus($activation_status);
		$this->object->setChangeEventTrackingEnabled($_POST['change_event_tracking'] == '1');
		// END ChangeEvent
		
		$this->object->enableUserRelatedData((int) !$_POST['user_related']);
		$this->object->setValidTimeSpan($_POST['valid_request']);

		if(!$this->object->validateSettings())
		{
			ilUtil::sendInfo($this->lng->txt('tracking_time_span_not_valid'));
			$this->settingsObject();

			return false;
		}
		
		$this->object->updateSettings();
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"));
		$this->settingsObject();
		
		return true;
	}

	/**
	* display tracking settings form
	*/
	function manageDataObject()
	{
		global $tpl,$lng,$ilias;

		// tracking settings
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tracking_manage_data.html");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormaction($this,'gateway'));
		$tpl->setVariable("TXT_TRACKING_DATA", $this->lng->txt("tracking_data"));
		$tpl->setVariable("TXT_MONTH", $lng->txt("month"));
		$tpl->setVariable("TXT_NUMBER_OF_ACC", $lng->txt("number_of_accesses"));
		$tpl->setVariable("TXT_DELETE_OLDER", $lng->txt("delete"));
		$overw = $this->object->getMonthTotalOverview();
		foreach($overw as $month)
		{
			$tpl->setCurrentBlock("load_row");
			$rcol = ($rcol != "tblrow1") ? "tblrow1" : "tblrow2";
			$tpl->setVariable("ROWCOL", $rcol);
			$tpl->setVariable("VAL_MONTH", $month["month"]);
			$tpl->setVariable("VAL_NUMBER_OF_ACC", $month["cnt"]);
			$tpl->parseCurrentBlock();
		}
		$tpl->parseCurrentBlock();
	}

	/**
	* confirm delete tracking data
	*/
	function confirmDeletionDataObject()
	{
		global $tpl, $lng, $rbacsystem;

		if (!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete_track"),$this->ilias->error_obj->WARNING);
		}

		if (!isset($_POST["month"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		$nr = $this->object->getTotalOlderThanMonth($_POST["month"]);
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tracking_confirm_data_deletion.html");
		#$tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"].
		#	"&cmd=gateway&month=".$_POST["month"]);
		$this->ctrl->setParameter($this,'month',$_POST['month']);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormaction($this,'gateway'));

		$tpl->setVariable("TXT_CONFIRMATION", $this->lng->txt("tracking_data_del_confirm"));
		$tpl->setVariable("TXT_MONTH", $lng->txt("month"));
		$tpl->setVariable("VAL_MONTH", $_POST["month"]);
		$tpl->setVariable("TXT_NUMBER_OF_RECORDS", $lng->txt("number_of_records"));
		$tpl->setVariable("VAL_NUMBER_OF_RECORDS", $nr);
		$tpl->setVariable("TXT_NUMBER_OF_ACC", $lng->txt("number_of_accesses"));
		$tpl->setVariable("TXT_DELETE_DATA", $lng->txt("delete_tr_data"));
		$tpl->setVariable("TXT_CANCEL", $lng->txt("cancel"));
	}

	/**
	* cancel deletion of tracking data
	*/
	function cancelDeleteDataObject()
	{
		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		#ilUtil::redirect("adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=manageData");

		$this->ctrl->redirect($this,'manageData');
	}

	/**
	* delete tracking data
	*/
	function deleteDataObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete_track"),$this->ilias->error_obj->WARNING);
		}

		$this->object->deleteTrackingDataBeforeMonth($_GET["month"]);

		ilUtil::sendInfo($this->lng->txt("tracking_data_deleted"),true);
		$this->ctrl->redirect($this,'manageData');

		#ilUtil::redirect("adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=manageData");
	}

	/**
	* display tracking query form
	*/
	function trackingDataQueryFormObject()
	{
		global $tpl;
		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_tracking.html");
		$tpl->setVariable("FORM", $this->showForm());
	}
	
	function showForm()
	{
		global $lng,$ilias;
		for ($i = 2004; $i <= date("Y"); $i++) $year[] = $i;
		$month = array(1,2,3,4,5,6,7,8,9,10,11,12);
		$day = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
		//subject module
		$tpl = new ilTemplate("tpl.tracking_form.html", true, true);
		
		// Tabs gui
		$this->tabs_gui->setTabActive('tracking_data');

		if (ilObjUserTracking::_enabledUserRelatedData())
		{
			$tpl->setCurrentBlock("user_stat");
			$tpl->setVariable("TXT_VIEW_MODE_U", $lng->txt("vm_access_of_users"));
			if ($_SESSION["il_track_stat"] == "u")
			{
				$tpl->setVariable("U_SEL", "selected");
			}
			$tpl->parseCurrentBlock();
		}

		//$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		#$tpl->setVariable("SEARCH_ACTION", "adm_object.php?ref_id=".$_GET["ref_id"].
		#	"&cmd=gateway");
		
		$tpl->setVariable("SEARCH_ACTION",$this->ctrl->getFormaction($this,'gateway'));
		$tpl->setVariable("TXT_TRACKING_DATA", $lng->txt("tracking_data"));
		$tpl->setVariable("TXT_SEARCH_TERMS", $lng->txt("search_terms"));
		$tpl->setVariable("VAL_SEARCH_TERMS", ilUtil::prepareFormOutput($_SESSION["il_track_search_terms"], true));
		$tpl->setVariable("TXT_TIME_SEGMENT", $lng->txt("time_segment"));
		$tpl->setVariable("TXT_VIEW_MODE", $lng->txt("view_mode"));
		$tpl->setVariable("TXT_VIEW_MODE_H", $lng->txt("vm_times_of_day"));
		$tpl->setVariable("TXT_VIEW_MODE_D", $lng->txt("vm_days_of_period"));
		$tpl->setVariable("TXT_USER_LANGUAGE",$lng->txt("user_language"));
		$tpl->setVariable("TXT_LM",$lng->txt("lm"));
		$tpl->setVariable("TXT_HTLM",$lng->txt("htlm"));
#		$tpl->setVariable("TXT_TST",$lng->txt("test"));
		$tpl->setVariable("TXT_SHOW_TR_DATA",$lng->txt("query_data"));
		$tpl->setVariable("TXT_TRACKED_OBJECTS",$lng->txt("tracked_objects"));
		$tpl->setVariable("TXT_FILTER_AREA",$lng->txt("trac_filter_area"));
		$tpl->setVariable("TXT_CHANGE",$lng->txt("change"));

		$languages = $lng->getInstalledLanguages();

		// get all learning modules
		// $lms = ilObject::_getObjectsDataForType("lm", true);
/*		$authors = ilObjUserTracking::allAuthor("usr","lm");
		if(count($authors)>0)
		{
			$tpl->setCurrentBlock("javascript");
			$tpl->setVariable("ALL_LMS", $this->lng->txt("all_lms"));
			foreach ($authors as $author)
			{
				$lms = ilObjUserTracking::authorLms($author["obj_id"],"lm");
				//echo count($lms);
				foreach ($lms as $lm)
				{
					$tpl->setCurrentBlock("select_value");
					$tpl->setVariable("VALUE", $author["title"]);
					$tpl->setVariable("LMVALUE", $lm["title"]);
					$tpl->parseCurrentBlock();
				}
			
			}
			$tpl->parseCurrentBlock();
		}
		$authors1 = ilObjUserTracking::allAuthor("usr","tst");
		if(count($authors1)>0)
		{
			$tpl->setCurrentBlock("javascript1");
			$tpl->setVariable("ALL_TSTS", $this->lng->txt("all_tsts"));
			foreach ($authors1 as $author1)
			{
				$tsts = ilObjUserTracking::authorLms($author1["obj_id"],"tst");
				foreach ($tsts as $tst)
				{
					$tpl->setCurrentBlock("select_value1");
					$tpl->setVariable("VALUE1", $author1["title"]);
					$tpl->setVariable("TSTVALUE", $tst["title"]);
					$tpl->parseCurrentBlock();
				}
			}
			$tpl->parseCurrentBlock();
		}*/

		if ($_SESSION["il_track_yearf"] == "") $_SESSION["il_track_yearf"] = date("Y");

		if ($_SESSION["il_track_yeart"] == "") $_SESSION["il_track_yeart"] = date("Y");
		if ($_SESSION["il_track_montht"] == "") $_SESSION["il_track_montht"] = date("m");
		if ($_SESSION["il_track_dayt"] == "") $_SESSION["il_track_dayt"] = date("d");

		foreach($year as $key)
		{
			$tpl->setCurrentBlock("fromyear_selection");
			$tpl->setVariable("YEARFR", $key);
			$tpl->setVariable("YEARF", $key);
			if ($_SESSION["il_track_yearf"] == $key)
			{
				$tpl->setVariable("YEARF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($month as $key)
		{
			$tpl->setCurrentBlock("frommonth_selection");
			$tpl->setVariable("MONTHFR", $key);
			$tpl->setVariable("MONTHF", $key);
			if ($_SESSION["il_track_monthf"] == $key)
			{
				$tpl->setVariable("MONTHF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($day as $key)
		{
			$tpl->setCurrentBlock("fromday_selection");
			$tpl->setVariable("DAYFR", $key);
			$tpl->setVariable("DAYF", $key);
			if ($_SESSION["il_track_dayf"] == $key)
			{
				$tpl->setVariable("DAYF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($day as $key)
		{
			$tpl->setCurrentBlock("today_selection");
			$tpl->setVariable("DAYTO", $key);
			$tpl->setVariable("DAYT", $key);
			if ($_SESSION["il_track_dayt"] == $key)
			{
				$tpl->setVariable("DAYT_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($month as $key)
		{
			$tpl->setCurrentBlock("tomonth_selection");
			$tpl->setVariable("MONTHTO", $key);
			$tpl->setVariable("MONTHT", $key);
			if ($_SESSION["il_track_montht"] == $key)
			{
				$tpl->setVariable("MONTHT_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach($year as $key)
		{
			$tpl->setCurrentBlock("toyear_selection");
			$tpl->setVariable("YEARTO", $key);
			$tpl->setVariable("YEART", $key);
			if ($_SESSION["il_track_yeart"] == $key)
			{
				$tpl->setVariable("YEART_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		// language selection
		$tpl->setCurrentBlock("language_selection");
		$tpl->setVariable("LANG", $lng->txt("any_language"));
		$tpl->setVariable("LANGSHORT", "0");
		$tpl->parseCurrentBlock();
		foreach ($languages as $lang_key)
		{
			$tpl->setCurrentBlock("language_selection");
			$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
			$tpl->setVariable("LANGSHORT", $lang_key);
			if ($_SESSION["il_track_language"] == $lang_key)
			{
				$tpl->setVariable("LANG_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}

		// statistic type
		if (!in_array($_SESSION["il_track_stat"], array("d", "h", "o", "u"))) $_SESSION["il_track_stat"] = "d";

		if ($_SESSION["il_track_stat"] == "d")
		{
			$tpl->setVariable("D_SEL", "selected");
		}
		elseif ($_SESSION["il_track_stat"] == "h")
		{
			$tpl->setVariable("H_SEL", "selected");
		}
		
		// tracked object type
		$tpl->setVariable(strtoupper($_SESSION["il_object_type"])."_SEL", "selected");

		// author selection
/*		$tpl->setCurrentBlock("author_selection");
		$tpl->setVariable("AUTHOR", 0);
		$tpl->setVariable("AUTHOR_SELECT", $this->lng->txt("all_authors"));
		$tpl->parseCurrentBlock();
		foreach ($authors as $author)
		{
			$tpl->setCurrentBlock("author_selection");
			$tpl->setVariable("AUTHOR", $author["title"]);
			$tpl->setVariable("AUTHOR_SELECT", $author["title"]);
			if ($_SESSION["il_track_author"] == $author["title"])
			{
				$tpl->setVariable("AUTHOR_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		$tpl->setCurrentBlock("author_selection_tst");
		$tpl->setVariable("AUTHOR1", 0);
		$tpl->setVariable("AUTHOR1_SELECT", $this->lng->txt("all_authors"));
		$tpl->parseCurrentBlock();
		foreach ($authors1 as $author1)
		{
			$tpl->setCurrentBlock("author_selection_tst");
			$tpl->setVariable("AUTHOR1", $author1["title"]);
			$tpl->setVariable("AUTHOR1_SELECT", $author1["title"]);
			if ($_SESSION["il_track_author1"] == $author1["title"])
			{
				$tpl->setVariable("AUTHOR1_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		//test module
		
		$result_test = ilObjUserTracking::getTestId($_SESSION["AccountId"]);

		//$test = $tracking->TestTitle($_SESSION["AccountId"]);

		$tsts = ilObject::_getObjectsDataForType($type, true);
		$tpl->setCurrentBlock("test_selection");
		$tpl->setVariable("TEST", 0);
		$tpl->setVariable("TEST_SELECT", $this->lng->txt("all_tsts"));
		$tpl->parseCurrentBlock();
		foreach($tsts as $tst)
		{
			$tpl->setCurrentBlock("test_selection");
			$tpl->setVariable("TEST", $tst["id"]);
			$tpl->setVariable("TEST_SELECT", $tst["title"]." [".$tst["id"]."]");
			$tpl->parseCurrentBlock();
		}*/
		
		return $tpl->get();

	}

	/**
	* output tracking data
	*/
	function outputTrackingDataObject()
	{
		global $tpl,$lng,$ilias,$ilSetting;

		$TYPES = array(
			'lm' => $lng->txt("lm"),
			'htlm' => $lng->txt("htlm"),
			'tst' => $lng->txt("test")
		);

		include_once "./Services/Table/classes/class.ilTableGUI.php";

		if(!in_array($_POST["stat"], array("d", "h", "o", "u")))
		{
			$_POST["stat"] = "d";
		}
 		if ($_POST["author"] == "") $_POST["author"] = "0";
 		if ($_POST["author1"] == "") $_POST["author1"] = "0";
 
		// save selected values in session
		$_SESSION["il_track_search_terms"] = ilUtil::stripSlashes($_POST["search_terms"]);
		$_SESSION["il_track_yearf"] = $_POST["yearf"];
		$_SESSION["il_track_yeart"] = $_POST["yeart"];
		$_SESSION["il_track_monthf"] = $_POST["monthf"];
		$_SESSION["il_track_montht"] = $_POST["montht"];
		$_SESSION["il_track_dayf"] = $_POST["dayf"];
		$_SESSION["il_track_dayt"] = $_POST["dayt"];
		$_SESSION["il_track_stat"] = $_POST["stat"];
		$_SESSION["il_track_language"] = $_POST["language"];
		$_SESSION["il_track_author"] = $_POST["author"];
		$_SESSION["il_track_author1"] = $_POST["author1"];
		$_SESSION["il_track_lm"] = $_POST["lm"];
		$_SESSION["il_track_htlm"] = $_POST["htlm"];
		$_SESSION["il_track_tst"] = $_POST["tst"];
		$_SESSION["il_object_type"] = $_POST["object_type"];

		$yearf = $_POST["yearf"];
		$monthf = $_POST["monthf"];
		$dayf = $_POST["dayf"];
		$yeart = $_POST["yeart"];
		$montht= $_POST["montht"];
		$dayt = $_POST["dayt"];
		$from = date("Y-m-d", mktime(12, 0, 0, $monthf, $dayf, $yearf));
		$to = date("Y-m-d", mktime(12, 0, 0, $montht, $dayt, $yeart));

		if(($yearf > $yeart)or($yearf==$yeart and $monthf>$montht)or($yearf==$yeart and $monthf==$montht and $dayf>$dayt))
		{
			$this->ilias->raiseError($lng->txt("msg_err_search_time"),
				$this->ilias->error_obj->MESSAGE);
		}

		$condition = $this->getCondition()." and acc_time >= '".$from." 00:00:00' and acc_time <= '".$to." 23:59:59'";

		/*
		if($_POST["stat"]!='h' and $_POST["stat"]!='d')
		{
			$this->ilias->raiseError($lng->txt("msg_no_search_time"),
				$this->ilias->error_obj->MESSAGE);
		}*/

		$tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.tracking_result.html");
		$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		$tpl->setVariable("FORM", $this->showForm());

		$objectCondition = "";

		if (($max_acc_objects = $this->object->countResults($condition)) == 0)
		{
			$this->ilias->raiseError($lng->txt("msg_no_search_result"),
				$this->ilias->error_obj->MESSAGE);
		}

		$max_hits = $ilias->getSetting('search_max_hits', 50);

		if ($_POST["search_terms"] != "")
		{
			$tplTable =& new ilTemplate("tpl.table.html", true, true);
			$tplTable->addBlockFile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");
	
			$tbl = new ilTableGUI(0, false);
			$tbl->setTemplate($tplTable);
			
			$searchTermsCondition = $this->getSearchTermsCondition();
			$acc_object = $this->object->getAccessTotalPerObj($condition,$searchTermsCondition);
			
			$max_acc_objects = count($acc_object);

			if ($max_acc_objects < 1)
			{
				$this->ilias->raiseError($lng->txt("msg_no_search_result"),
					$this->ilias->error_obj->MESSAGE);
			}
			else
			{
				$info = sprintf($lng->txt("info_found_objects"), $TYPES[$_POST["object_type"]]);

				if ($max_hits < $max_acc_objects)
				{
					$info .= " ".sprintf($lng->txt("found_too_much_objects"), $max_hits);
					unset($tmp);
					for ($i = 0; $i < count($acc_object) && $i < $max_hits; $i++)
					{
						$tmp[$i] = $acc_object[$i];
					}
					$acc_object = $tmp;
					$max_acc_objects = $max_hits;
				}

				$tpl->setVariable("INFO", $info);
			}

			$tbl->setTitle($lng->txt("found_objects"),0,0);
#			if(($_POST["object_type"]=="lm" and $_POST["author"] == "0") or ($_POST["object_type"]=="tst" and $_POST["author1"] == "0"))
#			{
				$title_new = array("author", "subject", "total_dwell_time", "count","");	
				$tbl->setColumnWidth(array("20%", "30%", "20%", "10%", "*"));
#			}
#			else
#			{
#				$title_new = array("subject", "count","");
#				$tbl->setColumnWidth(array("30%", "10%", "*"));
#			}
			foreach ($title_new as $val)
			{
				$header_names[] = $lng->txt($val);
			}
			$tbl->disable("sort");
			$tbl->setHeaderNames($header_names);
			$tbl->setMaxCount($max_acc_objects);
	#			$tbl->setStyle("table", "std");
	
			$max = 0;
			unset($ids);
			for ($i = 0; $i < count($acc_object); $i++)
			{
				$max = ($max > $acc_object[$i]["cnt"]) ? $max : $acc_object[$i]["cnt"];
				$ids[$i] = $acc_object[$i]["id"];
			}
			if (is_array($ids))
			{
				$objectCondition = " AND acc_obj_id IN (".implode(",", $ids).") ";
			}
	
			for ($i = 0; $i < count($acc_object); $i++)
			{
				unset($data);
#				if(($_POST["object_type"]=="lm" and $_POST["author"]=="0") or ($_POST["object_type"]=="tst" and $_POST["author1"]=="0"))
#				{
					$data[0] = $acc_object[$i]["author"];
					$data[1] = $acc_object[$i]["title"];
					$data[2] = ilFormat::_secondsToString($acc_object[$i]["duration"]);
					$data[3] = $acc_object[$i]["cnt"];
					$width = ($max > 0)
						? round($data[3] / $max * 100)
						: 0;
					$data[4] = "<img src=\"".ilUtil::getImagePath("ray.gif")."\" border=\"0\" ".
						"width=\"".$width."\" height=\"10\"/>";
/*				}
				else
				{
					$data[0] = $obj["title"];
					$data[1] = $obj["cnt"];
					$width = ($max > 0)
						? round($data[1] / $max * 100)
						: 0;
					$data[2] = "<img src=\"".ilUtil::getImagePath("ray.gif")."\" border=\"0\" ".
						"width=\"".$width."\" height=\"10\"/>";
				}*/
				$css_row = $i%2==0?"tblrow1":"tblrow2";
				foreach ($data as $key => $val)
				{
					if($val=="")
					{
						$val=0;
					}
					$tplTable->setCurrentBlock("text");
					$tplTable->setVariable("TEXT_CONTENT", $val);
					$tplTable->parseCurrentBlock();
					$tplTable->setCurrentBlock("table_cell");
					$tplTable->parseCurrentBlock();
				} //foreach
				$tplTable->setCurrentBlock("tbl_content");
				$tplTable->setVariable("CSS_ROW", $css_row);
				$tplTable->parseCurrentBlock();
			} //for
	
			$tbl->render();
			$tpl->setVariable("OBJECTS_TABLE", $tplTable->get());
			$tpl->setVariable("TXT_INFO_DWELL_TIME", $lng->txt("info_dwell_time"));
			unset($tplTable);
			unset($tbl);
		}
		else
		{
			$tpl->setVariable("INFO", sprintf($lng->txt("info_all_objects"), $TYPES[$_POST["object_type"]]));
		}
		
		if ($max_acc_objects > 0)
		{

			$tplTable =& new ilTemplate("tpl.table.html", true, true);
			$tplTable->addBlockFile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");
	
			$tbl = new ilTableGUI(0, false);
			$tbl->setTemplate($tplTable);
	
			// user access statistic
			if($_POST["stat"] == "u")	// user access
			{
				if($_POST["mode"] == "user")
				{
					$tpl->setCurrentBlock("user_mode");
					#$tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"].
					#"&cmd=gateway");
					$tpl->setVariable("FORMACTION",$this->ctrl->getFormaction($this,'gateway'));
					if($_POST["object_type"]=="lm")
					{
						$tpl->setVariable("AUTHOR", "author");
						$tpl->setVariable("AUTHORS", $_POST["author"]);
						$tpl->setVariable("OBJECT", "lm");
						$tpl->setVariable("OBJECTS", $_POST["lm"]);
					}
					else if($_POST["object_type"]=="htlm")
					{
						$tpl->setVariable("AUTHOR", "author");
						$tpl->setVariable("AUTHORS", $_POST["author"]);
						$tpl->setVariable("OBJECT", "htlm");
						$tpl->setVariable("OBJECTS", $_POST["htlm"]);
					}
					else
					{
						$tpl->setVariable("AUTHOR", "author1");
						$tpl->setVariable("AUTHORS", $_POST["author1"]);
						$tpl->setVariable("OBJECT", "tst");
						$tpl->setVariable("OBJECTS", $_POST["tst"]);
					}
					$tpl->setVariable("YEARF",$_POST["yearf"]);
					$tpl->setVariable("MONTHF",$_POST["monthf"]);
					$tpl->setVariable("DAYF",$_POST["dayf"]);
					$tpl->setVariable("YEART",$_POST["yeart"]);
					$tpl->setVariable("MONTHT",$_POST["montht"]);
					$tpl->setVariable("DAYT",$_POST["dayt"]);
					$tpl->setVariable("LAN", $_POST["language"]);
					$tpl->setVariable("TYPE", $_POST["object_type"]);
					$tpl->setVariable("SEARCH_TERMS", ilUtil::prepareFormOutput($_POST["search_terms"]));
					$tpl->setVariable("FROM", $from);
					$tpl->setVariable("TO", $to);
					$tpl->setVariable("TXT_SHOW_USER_DATA", $lng->txt("user_statistics"));
					$tpl->parseCurrentBlock();
	
					$title_new = array("user","client_ip","language","object","time");
					$condition = $this->getConditions()." and acc_time >= '".$from." 00:00:00' and acc_time <= '".$to." 23:59:59'";
					$searchTermsCondition = $this->getSearchTermsCondition();
					$user_acc = $this->object->getAccessPerUserDetail($condition, $searchTermsCondition, $objectCondition);
					$this->maxcount = count($user_acc);
					if ($this->maxcount < 1)
					{
						$this->ilias->raiseError($lng->txt("msg_no_search_result"),
							$this->ilias->error_obj->MESSAGE);
					}
	
#					$tbl->setTitle($lng->txt("search_result"),0,0);
					$tbl->setTitle($lng->txt("obj_trac").": ".$lng->txt("vm_access_of_users")." [".$lng->txt("details")."]",0,0);
					unset($header_names);
					foreach ($title_new as $val)
					{
						$header_names[] = $lng->txt($val);
					}
					$tbl->disable("sort");
	
					$tbl->setHeaderNames($header_names);
					$tbl->setColumnWidth(array("20%", "15%", "15%", "30%", "*"));
					$tbl->setMaxCount($this->maxcount);
	#				$tbl->setStyle("table", "std");
	
					$max = 0;
	
					$i = 0;
					foreach ($user_acc as $user)
					{
						unset($data);
						$data[0] = $user["name"];
						$data[1] = $user["client_ip"];
						$data[2] = $user["language"];
						$data[3] = $user["acc_obj_id"];
						$data[4] = $user["acc_time"];
						$css_row = $i%2==0?"tblrow1":"tblrow2";
						foreach ($data as $key => $val)
						{
							if($val=="")
							{
								$val=0;
							}
							$tplTable->setCurrentBlock("text");
							$tplTable->setVariable("TEXT_CONTENT", $val);
							$tplTable->parseCurrentBlock();
							$tplTable->setCurrentBlock("table_cell");
							$tplTable->parseCurrentBlock();
						} //foreach
						$tplTable->setCurrentBlock("tbl_content");
						$tplTable->setVariable("CSS_ROW", $css_row);
						$tplTable->parseCurrentBlock();
						$i++;
					} //for
				}
				else
				{
					$tpl->setCurrentBlock("user_mode");
					#$tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"].
					#"&cmd=gateway");
					$tpl->setVariable("FORMACTION",$this->ctrl->getFormaction($this,'gateway'));
					if($_POST["object_type"]=="lm")
					{
						$tpl->setVariable("AUTHOR", "author");
						$tpl->setVariable("AUTHORS", $_POST["author"]);
						$tpl->setVariable("OBJECT", "lm");
						$tpl->setVariable("OBJECTS", $_POST["lm"]);
					}
					else if($_POST["object_type"]=="htlm")
					{
						$tpl->setVariable("AUTHOR", "author");
						$tpl->setVariable("AUTHORS", $_POST["author"]);
						$tpl->setVariable("OBJECT", "htlm");
						$tpl->setVariable("OBJECTS", $_POST["htlm"]);
					}
					else
					{
						$tpl->setVariable("AUTHOR", "author1");
						$tpl->setVariable("AUTHORS", $_POST["author1"]);
						$tpl->setVariable("OBJECT", "tst");
						$tpl->setVariable("OBJECTS", $_POST["tst"]);
					}
					$tpl->setVariable("YEARF",$_POST["yearf"]);
					$tpl->setVariable("MONTHF",$_POST["monthf"]);
					$tpl->setVariable("DAYF",$_POST["dayf"]);
					$tpl->setVariable("YEART",$_POST["yeart"]);
					$tpl->setVariable("MONTHT",$_POST["montht"]);
					$tpl->setVariable("DAYT",$_POST["dayt"]);
					$tpl->setVariable("USER", "user");
					$tpl->setVariable("LAN", $_POST["language"]);
					$tpl->setVariable("TYPE", $_POST["object_type"]);
					$tpl->setVariable("SEARCH_TERMS", ilUtil::prepareFormOutput($_POST["search_terms"]));
					$tpl->setVariable("FROM", $from);
					$tpl->setVariable("TO", $to);
					$tpl->setVariable("TXT_SHOW_USER_DATA", $lng->txt("user_detail"));
					$tpl->parseCurrentBlock();
					$title_new = array("user", "count", "");
	
					$searchTermsCondition = $this->getSearchTermsCondition();
					$user_acc = $this->object->getAccessTotalPerUser($condition, $searchTermsCondition, $objectCondition);
	
					$this->maxcount = count($user_acc);
	
					// check if result is given
					if ($this->maxcount < 1)
					{
						$this->ilias->raiseError($lng->txt("msg_no_search_result"),
							$this->ilias->error_obj->MESSAGE);
					}
	
#					$tbl->setTitle($lng->txt("search_result"),0,0);
					$tbl->setTitle($lng->txt("obj_trac").": ".$lng->txt("vm_access_of_users"),0,0);
					unset($header_names);
					foreach ($title_new as $val)
					{
						$header_names[] = $lng->txt($val);
					}
					$tbl->disable("sort");
					$tbl->setHeaderNames($header_names);
					$tbl->setColumnWidth(array("20%", "10%", "*"));
					$tbl->setMaxCount($this->maxcount);
	#				$tbl->setStyle("table", "std");
	
					$max = 0;
					foreach ($user_acc as $user)
					{
						$max = ($max > $user["cnt"]) ? $max : $user["cnt"];
					}
	
					$i = 0;
					foreach ($user_acc as $user)
					{
						unset($data);
						$data[0] = $user["name"];
						$data[1] = $user["cnt"];
						$width = ($max > 0)
							? round($data[1] / $max * 100)
							: 0;
						$data[2] = "<img src=\"".ilUtil::getImagePath("ray.gif")."\" border=\"0\" ".
							"width=\"".$width."\" height=\"10\"/>";
	
						$css_row = $i%2==0?"tblrow1":"tblrow2";
						foreach ($data as $key => $val)
						{
							if($val=="")
							{
								$val=0;
							}
							$tplTable->setCurrentBlock("text");
							$tplTable->setVariable("TEXT_CONTENT", $val);
							$tplTable->parseCurrentBlock();
							$tplTable->setCurrentBlock("table_cell");
							$tplTable->parseCurrentBlock();
						} //foreach
						$tplTable->setCurrentBlock("tbl_content");
						$tplTable->setVariable("CSS_ROW", $css_row);
						$tplTable->parseCurrentBlock();
						$i++;
					} //for
				}
	
			}
			else //user not selected
			{
				$title_new = array("time", "count", "");
	
#				$tbl->setTitle($lng->txt("obj_trac"),0,0);
				unset($header_names);
				foreach ($title_new as $val)
				{
					$header_names[] = $lng->txt($val);
				}
				$tbl->disable("sort");
				$tbl->setHeaderNames($header_names);
				if($_POST["stat"]=='h')		//hours of day
				{
					$tbl->setTitle($lng->txt("obj_trac").": ".$lng->txt("vm_times_of_day"),0,0);
					$tbl->setColumnWidth(array("30%", "10%", "*"));
				}
				else
				{
					$tbl->setTitle($lng->txt("obj_trac").": ".$lng->txt("vm_days_of_period"),0,0);
					$tbl->setColumnWidth(array("15%", "10%", "*"));
				}
	
				if($_POST["stat"]=='h')
				{
					$num = 24;
					$tbl->setMaxCount($num);
				}
				else
				{
					$num = $this->numDay($from,$to);
					$from1 = $this->addDay($from);
					$tbl->setMaxCount($num);
				}
	#			$tbl->setStyle("table", "std");
	
				// contition
				$condition = $this->getCondition();
	
				if($_POST["stat"]=='h')		//hours of day
				{
					$searchTermsCondition = $this->getSearchTermsCondition();
					$time = $this->selectTime($from,$to,$condition,$searchTermsCondition,$objectCondition);
					$max = 0;
					for($i=0;$i<24;$i++)
					{
						$k = $i+1;
	
						// count number of accesses in hour $i
						$cou = 0;
						for($j=0;$j<count($time);$j++)
						{
							$time1 = strtotime($time[$j][0]);
							$day = date("d",$time1);
							$month = date("m",$time1);
							$year = date("Y",$time1);
							$hour = date("H",$time1);
							$min = date("i",$time1);
							$sec = date("s",$time1);
							$numb = date("H",mktime($hour,$min,$sec,$month,$day,$year));
							$numb = intval($numb);
							if($numb >=$i and $numb <$k)
							{
								$cou=$cou+1;
							}
						}
						$count[$i] = $cou;
						$max = ($cou > $max) ? $cou : $max;
					}
	
					for($i=0;$i<24;$i++)
					{
						$k = $i+1;
						unset($data);
						$data[0] = ($i < 10 ? "0".$i : $i).":00:00  ~  ".($k < 10 ? "0".$k : $k).":00:00";
						$data[1] = $count[$i];
						$width = ($max > 0)
							? round($count[$i] / $max * 100)
							: 0;
						$data[2] = "<img src=\"".ilUtil::getImagePath("ray.gif")."\" border=\"0\" ".
							"width=\"".$width."\" height=\"10\"/>";
	
						$css_row = $i%2==0?"tblrow1":"tblrow2";
						foreach ($data as $key => $val)
						{
							$tplTable->setCurrentBlock("text");
							$tplTable->setVariable("TEXT_CONTENT", $val);
							$tplTable->parseCurrentBlock();
							$tplTable->setCurrentBlock("table_cell");
							$tplTable->parseCurrentBlock();
						}
						$tplTable->setCurrentBlock("tbl_content");
						$tplTable->setVariable("CSS_ROW", $css_row);
						$tplTable->parseCurrentBlock();
					} //for
				}
				else //day selected
				{
					$max = 0;
					$searchTermsCondition = $this->getSearchTermsCondition();
					for($i=0;$i<$num;$i++)
					{
						$fro[$i] = $from;
						$cou[$i] = $this->countNum($from,$from1,$condition,$searchTermsCondition,$objectCondition);
						$from = $from1;
						$from1 = $this->addDay($from);
						$max = ($max > $cou[$i]) ? $max : $cou[$i];
					}
					for($i=0;$i<$num;$i++)
					{
						unset($data);
						$data[0] = $fro[$i];
						$data[1] = $cou[$i];
						$width = ($max > 0)
							? round($cou[$i] / $max * 100)
							: 0;
						$data[2] = "<img src=\"".ilUtil::getImagePath("ray.gif")."\" border=\"0\" ".
							"width=\"".$width."\" height=\"10\"/>";
	
						$css_row = $i%2==0?"tblrow1":"tblrow2";
						foreach ($data as $key => $val)
						{
							$tplTable->setCurrentBlock("text");
							$tplTable->setVariable("TEXT_CONTENT", $val);
							$tplTable->parseCurrentBlock();
							$tplTable->setCurrentBlock("table_cell");
							$tplTable->parseCurrentBlock();
						}
						$tplTable->setCurrentBlock("tbl_content");
						$tplTable->setVariable("CSS_ROW", $css_row);
						$tplTable->parseCurrentBlock();
					} //for
				}
			}//else
	
			$tbl->render();
			$tpl->setVariable("TRACK_TABLE", $tplTable->get());
			unset($tplTable);
			unset($tbl);

		}

		// output statistic settings
/*		$tpl->setCurrentBlock("adm_content");
		$tpl->setVariable("TXT_TIME_PERIOD", $lng->txt("time_segment"));
		switch ($_POST["stat"])
		{
			case "h":
				$tpl->setVariable("TXT_STATISTIC", $lng->txt("hours_of_day"));
				break;

			case "u":
				$tpl->setVariable("TXT_STATISTIC", $lng->txt("user_access"));
				break;

			case "d":
				$tpl->setVariable("TXT_STATISTIC", $lng->txt("days_of_period"));
				break;

			case "o":
				$tpl->setVariable("TXT_STATISTIC", $lng->txt("per_object"));
				break;
		}
		$tpl->setVariable("VAL_DATEF", date("Y-m-d", mktime(0,0,0,$monthf,$dayf,$yearf)));
		$tpl->setVariable("TXT_SEARCH_TERMS", $lng->txt("search_terms"));
		$tpl->setVariable("VAL_SEARCH_TERMS", ilUtil::stripSlashes($_POST["search_terms"]));
		$tpl->setVariable("TXT_TO", $lng->txt("to"));
		$tpl->setVariable("VAL_DATET", date("Y-m-d", mktime(0,0,0,$montht,$dayt,$yeart)));
		$tpl->setVariable("TXT_USER_LANGUAGE", $lng->txt("user_language"));
		if ($_POST["language"] == "0")
		{
			$tpl->setVariable("VAL_LANGUAGE", $lng->txt("any_language"));
		}
		else
		{
			$tpl->setVariable("VAL_LANGUAGE", $lng->txt("lang_".$_POST["language"]));
		}
		$tpl->setVariable("TXT_TRACKED_OBJECTS", $lng->txt("tracked_objects"));
		if ($_POST[$_POST["object_type"]] != 0)
		{
			$tpl->setVariable("VAL_TRACKED_OBJECTS",
				ilObject::_lookupTitle($_POST[$_POST["object_type"]]));
		}
		else
		{
			$tpl->setVariable("VAL_TRACKED_OBJECTS",
				$lng->txt("all_".$_POST["object_type"]."s"));
		}
		$tpl->parseCurrentBlock();*/
	}

	/**
	* get complete condition string
	*/
	function getCondition()
	{
		$lang_cond = $this->getLanguageCondition();
		//echo ":$lang_cond:";
		if ($lang_cond == "")
		{
			$this->setConditions($this->getObjectCondition());
			return $this->getObjectCondition();
		}
		else
		{
			$this->setConditions($lang_cond." AND ".$this->getObjectCondition());
			return $lang_cond." AND ".$this->getObjectCondition();
		}
	}


	/**
	* get object condition string
	*/
	function getObjectCondition()
	{
		global $ilDB;

		$type = $_POST["object_type"];
		$condition = "";
		if($_POST["object_type"]=="lm")
		{
			if($_POST["author"]=="0")
			{
				return " acc_obj_type = 'lm'";
			}
			elseif($_POST["lm"]=="0" or $_POST["lm"]=="")
			{
				if (is_array($authors = ilObjUserTracking::allAuthor("usr","lm")))
				{
					foreach ($authors as $author)
					{
						if($author["title"]==$_POST["author"])
						{
							if (is_array($lms = ilObjUserTracking::authorLms($author["obj_id"],"lm")))
							{
								foreach ($lms as $lm)
								{
									$condition = $condition." or acc_obj_id = ".$lm["obj_id"];
								}
							}
						}
					}
				}
				return " ( 0 ".$condition." ) ";
			}
			else
			{
				$condition.= " acc_obj_id = ".ilObjUserTracking::getObjId($_POST["lm"],$type);
				return $condition;
			}

		}
		else if($_POST["object_type"]=="htlm")
		{
			if($_POST["author"]=="0")
			{
				return " acc_obj_type = 'htlm'";
			}
			elseif($_POST["htlm"]=="0" or $_POST["htlm"]=="")
			{
				if (is_array($authors = ilObjUserTracking::allAuthor("usr","htlm")))
				{
					foreach ($authors as $author)
					{
						if($author["title"]==$_POST["author"])
						{
							if (is_array($htlms = ilObjUserTracking::authorLms($author["obj_id"],"htlm")))
							{
								foreach ($htlms as $htlm)
								{
									$condition = $condition." or acc_obj_id = ".$htlm["obj_id"];
								}
							}
						}
					}
				}
				return " ( 0 ".$condition." ) ";
			}
			else
			{
				$condition.= " acc_obj_id = ".ilObjUserTracking::getObjId($_POST["htlm"],$type);
				return $condition;
			}

		}
		else
		{
			if($_POST["author1"]=="0")
			{
				return " acc_obj_type = 'tst'";
			}
			elseif($_POST["tst"]=="0" or $_POST["tst"]=="")
			{
				if (is_array($authors = ilObjUserTracking::allAuthor("usr","tst")))
				{
					foreach ($authors as $author)
					{
						if($author["title"]==$_POST["author1"])
						{
							if (is_array($lms = ilObjUserTracking::authorLms($author["obj_id"],"tst")))
							{
								foreach ($lms as $lm)
								{
									$condition = $condition." or acc_obj_id = ".$lm["obj_id"];
								}
							}
						}
					}
				}
				return " ( 0 ".$condition." ) ";
			}
			else
			{
				$condition.= " acc_obj_id = ".ilObjUserTracking::getObjId($_POST["tst"],$type);
				return $condition;
			}
		}
	}

	/**
	* get language condition string
	*/
	function getLanguageCondition()
	{
		global $ilDB;

		if ($_POST["language"] != "0")
		{
			return "ut_access.language =".$ilDB->quote($_POST["language"]);
		}

		return "";
	}	

	/**
	* get language condition string
	*/
	function getSearchTermsCondition()
	{
		global $ilDB;

		if (trim($_POST["search_terms"]) != "")
		{
			$sub_ret = "";
			$terms = explode(" ", $_POST["search_terms"]);
			for ($i = 0; $i < count($terms); $i++)
			{
				if (trim($terms[$i]) != "") $sub_ret .= "oa.title LIKE '%".ilUtil::addSlashes(trim($terms[$i]))."%' OR ";
			}
			if ($sub_ret != "")
			{
				return " INNER JOIN object_data AS oa ON oa.obj_id = acc_obj_id WHERE (".substr($sub_ret, 0, strlen($sub_ret)-4) . ") AND ";
			}
		}

		return "";
	}	

	function setConditions($con)
	{
		$this->conditions = $con;
	}
	function getConditions()
	{
		return $this->conditions;
	}
	
	/**
	* Return the nums of days between 'from' and 'to'
	*/
	function numDay($from,$to)
	{

		$from = strtotime($from);
		$to = strtotime($to);

		$dayf = date ("d",$from);
		$dayt = date ("d",$to);
		$yearf = date ("Y",$from); 
		$yeart = date ("Y",$to); 
		$montht = date ("m",$to); 
		$monthf = date ("m",$from); 

#		$ret = ( mktime(0,0,0,$montht,$dayt,$yeart) - mktime(0,0,0,$monthf,$dayf,$yearf))/(3600*24); 
#		return $ret; 

		$from = mktime(12,0,0,$monthf,$dayf,$yearf);
		$to = mktime(12,0,0,$montht,$dayt,$yeart);

		$ret = (round(($to - $from) / 86400) + 1);
		return $ret;

#		$x0 = gregoriantojd($monthf,$dayf,$yearf);
#		$x1 = gregoriantojd($montht,$dayt,$yeart); 
#		return (($x1 - $x0)+1);
	}
	
	/**
	* Return the nums of hours between 'from' and 'to'
	*/
	function numHour($from,$to)
	{
		$from = strtotime($from);
		$to = strtotime($to);
		$dayf = date ("d",$from); 
		$dayt = date ("d",$to);
		$yearf = date ("Y",$from); 
		$yeart = date ("Y",$to); 
		$montht = date ("m",$to); 
		$monthf = date ("m",$from); 
		$hourt = date ("h",$to);
		$hourf = date ("h",$from);
		$ret = (mktime($hourt,0,0,$montht,$dayt,$yeart)-mktime($hourf,0,0,$monthf,$dayf,$yearf))/3600; 
		$ret = strftime($ret);
		return $ret; 
	}
	
	/**
	* Add one hour to the 'time' and return it
	*/
	function addHour($time)
	{
		$time = strtotime($time);
		$day = date("d",$time);
		$month = date("m",$time);
		$year = date("Y",$time);
		$hour = date("H",$time);
		$min = date("i",$time);
		$sec = date("s",$time);
		$hour = $hour+1;
		$ret = date("H:i:s", mktime($hour,$min,$sec,$month,$day,$year));
		return $ret;
	}
	
	/**
	* Add one day to the 'time' and return it
	*/
	function addDay($time)
	{
		$time = strtotime($time);
		$day = date("d",$time);
		$month = date("m",$time);
		$year = date("y",$time);
		$min = date("i",$time);
		$hour = date("h",$time);
		$sec = date("s",$time);
		$day = $day + 1;
		$ret = date ("Y-m-d", mktime($hour,$min,$sec,$month,$day,$year));
		return $ret;
	}
	
	/**
	* Get the access time between 'from' to 'to' and under the 'condition'
	*/
	function selectTime($from,$to,$condition,$searchTermsCondition="",$objectCondition="")
	{
		$q = "SELECT acc_time from ut_access "
			.($searchTermsCondition != "" ? $searchTermsCondition : " WHERE ")
			." (acc_time >= '".$from." 00:00:00'"
			." AND acc_time <= '".$to." 23:59:59')"
			." AND ".$condition
			.$objectCondition
			." GROUP BY acc_time";
		$res = $this->ilias->db->query($q);
		for($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	/**
	* Get the access num between 'from' to 'from1' and under the 'condition'
	*/
	function countNum($from,$from1,$condition,$searchTermsCondition="",$objectCondition="")
	{
		$q = "SELECT id FROM ut_access"
			.($searchTermsCondition != "" ? $searchTermsCondition : " WHERE ")
			." (acc_time >= '".$from." 00:00:00'"
			." AND acc_time < '".$from1." 00:00:00')"
			." AND ".$condition
			.$objectCondition
			." GROUP BY id";
		$res = $this->ilias->db->query($q);
		return $res->numRows();
	}
} 
?>
