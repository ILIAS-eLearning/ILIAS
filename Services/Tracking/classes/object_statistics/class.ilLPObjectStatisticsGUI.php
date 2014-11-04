<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Tracking/classes/class.ilLearningProgressBaseGUI.php";

/**
* Class ilObjectStatisticsGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPListOfObjectsGUI.php 27489 2011-01-19 16:58:09Z jluetzen $
*
* @ilCtrl_Calls ilLPObjectStatisticsGUI: ilLPObjectStatisticsTableGUI
*
* @package ilias-tracking
*
*/
class ilLPObjectStatisticsGUI extends ilLearningProgressBaseGUI
{
	function ilLPObjectStatisticsGUI($a_mode,$a_ref_id = 0)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);
	
		if(!$this->ref_id)
		{
			$this->ref_id = (int)$_REQUEST["ref_id"];
		}		
	}

	protected function setTabs()
	{
		global $ilAccess;
		
		$this->tabs_gui->addSubTab('trac_object_stat_access', 
				$this->lng->txt('trac_object_stat_access'),
				$this->ctrl->getLinkTarget($this, 'accessFilter'));		
		$this->tabs_gui->addSubTab('trac_object_stat_daily', 
				$this->lng->txt('trac_object_stat_daily'),
				$this->ctrl->getLinkTarget($this, 'dailyFilter'));		
		$this->tabs_gui->addSubTab('trac_object_stat_lp', 
				$this->lng->txt('trac_object_stat_lp'),
				$this->ctrl->getLinkTarget($this, 'learningProgressFilter'));
		$this->tabs_gui->addSubTab('trac_object_stat_types', 
				$this->lng->txt('trac_object_stat_types'),
				$this->ctrl->getLinkTarget($this, 'typesFilter'));
		
		if($ilAccess->checkAccess("write", "", $this->ref_id))
		{		
			$this->tabs_gui->addSubTab('trac_object_stat_admin', 
					$this->lng->txt('trac_object_stat_admin'),
					$this->ctrl->getLinkTarget($this, 'admin'));
		}
	}
	
	/**
	* execute command
	*/
	function executeCommand()
	{
		$this->ctrl->setReturn($this, "");
		
		$this->setTabs();

		switch($this->ctrl->getNextClass())
		{
			default:
			    $cmd = $this->__getDefaultCommand();
				$this->$cmd();
		}

		return true;
	}

	function applyAccessFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access", null, false);
		$lp_table->resetOffset();
		$lp_table->writeFilterToSession();
		$this->access();
	}

	function resetAccessFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access", null, false);
		$lp_table->resetOffset();
		$lp_table->resetFilter();
		$this->access();
	}

	function accessFilter()
	{
		$this->access(false);
	}

	function access($a_load_data = true)
	{
		global $tpl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_access');
		
		$this->showAggregationInfo();

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access", null, $a_load_data);
		
		if(!$a_load_data)
		{
			$lp_table->disable("content");
			$lp_table->disable("header");
		}
		
		$tpl->setContent($lp_table->getHTML());
	}

	function showAccessGraph()
	{
		global $lng, $tpl;
		
		if(!$_POST["item_id"])
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->access();
		}
		
		$this->tabs_gui->activateSubTab('trac_object_stat_access');

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access", $_POST["item_id"]);

		$tpl->setContent($lp_table->getGraph($_POST["item_id"]).$lp_table->getHTML());
	}

	function applyTypesFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", null, false);
		$lp_table->resetOffset();
		$lp_table->writeFilterToSession();
		$this->types();
	}

	function resetTypesFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", null, false);
		$lp_table->resetOffset();
		$lp_table->resetFilter();
		$this->types();
	}

	function typesFilter()
	{
		$this->types(false);
	}

	function types($a_load_data = true)
	{
		global $tpl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_types');

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", null, $a_load_data);

		if(!$a_load_data)
		{
			$lp_table->disable("content");
			$lp_table->disable("header");
		}
		
		$tpl->setContent($lp_table->getHTML());
	}

	function showTypesGraph()
	{
		global $lng, $tpl;

		if(!$_POST["item_id"])
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->types();
		}
		
		$this->tabs_gui->activateSubTab('trac_object_stat_types');

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", $_POST["item_id"]);

		$tpl->setContent($lp_table->getGraph($_POST["item_id"]).$lp_table->getHTML());
	}

	function applyDailyFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", null, false);
		$lp_table->resetOffset();
		$lp_table->writeFilterToSession();
		$this->daily();
	}

	function resetDailyFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", null, false);
		$lp_table->resetOffset();
		$lp_table->resetFilter();
		$this->daily();
	}

	function dailyFilter()
	{
		$this->daily(false);
	}

	function daily($a_load_data = true)
	{
		global $tpl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_daily');
		
		$this->showAggregationInfo();

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", null, $a_load_data);

		if(!$a_load_data)
		{
			$lp_table->disable("content");
			$lp_table->disable("header");
		}
		
		$tpl->setContent($lp_table->getHTML());
	}

	function showDailyGraph()
	{
		global $lng, $tpl;

		if(!$_POST["item_id"])
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->daily();
		}
		
		$this->tabs_gui->activateSubTab('trac_object_stat_daily');

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", $_POST["item_id"]);

		$tpl->setContent($lp_table->getGraph($_POST["item_id"]).$lp_table->getHTML());
	}

	function admin()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl, $ilAccess;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_admin');
		
		$this->showAggregationInfo(false);

		$ilToolbar->addButton($lng->txt("trac_sync_obj_stats"),
			$ilCtrl->getLinkTarget($this, "adminSync"));

		if($ilAccess->checkAccess("delete", "", $this->ref_id))
		{
			include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsAdminTableGUI.php");
			$lp_table = new ilLPObjectStatisticsAdminTableGUI($this, "admin");

			$tpl->setContent($lp_table->getHTML());
		}
	}

	function adminSync()
	{
		global $ilCtrl, $lng;
		
		include_once "Services/Tracking/classes/class.ilChangeEvent.php";
		ilChangeEvent::_syncObjectStats(time(), 1);

		ilUtil::sendSuccess($lng->txt("trac_sync_obj_stats_success"), true);
		$ilCtrl->redirect($this, "admin");
	}

	function confirmDeleteData()
	{
		global $lng, $tpl, $ilTabs, $ilCtrl;

		if(!$_POST["item_id"])
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->admin();
		}

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "admin"));

		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($ilCtrl->getFormAction($this));
		$cgui->setHeaderText($lng->txt("trac_sure_delete_data"));
		$cgui->setCancel($lng->txt("cancel"), "admin");
		$cgui->setConfirm($lng->txt("delete"), "deleteData");

		// list objects that should be deleted
		foreach ($_POST["item_id"] as $i)
		{
			$caption = $lng->txt("month_".str_pad(substr($i, 5), 2, "0", STR_PAD_LEFT)."_long").
			" ".substr($i, 0, 4);
			
			$cgui->addItem("item_id[]", $i, $caption);
		}

		$tpl->setContent($cgui->getHTML());
	}

	function deleteData()
	{
		global $lng;
		
		if(!$_POST["item_id"])
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->admin();
		}

	   include_once "Services/Tracking/classes/class.ilTrQuery.php";
	   ilTrQuery::deleteObjectStatistics($_POST["item_id"]);
	   ilUtil::sendSuccess($lng->txt("trac_data_deleted"));
	   $this->admin();
	}
	
	function applyLearningProgressFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
		$lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", null, false);
		$lp_table->resetOffset();
		$lp_table->writeFilterToSession();
		$this->learningProgress();
	}

	function resetLearningProgressFilter()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
		$lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", null, false);
		$lp_table->resetOffset();
		$lp_table->resetFilter();
		$this->learningProgress();
	}
	
	function learningProgressFilter()
	{
		$this->learningProgress(false);
	}

	function learningProgress($a_load_data = true)
	{
		global $tpl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_lp');

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
		$lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", null, $a_load_data);
		
		if(!$a_load_data)
		{
			$lp_table->disable("content");
			$lp_table->disable("header");
		}
		
		$tpl->setContent($lp_table->getHTML());
	}

	function showLearningProgressGraph()
	{
		global $lng, $tpl;
		
		if(!$_POST["item_id"])
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"));
			return $this->learningProgress();
		}
		
		$this->tabs_gui->activateSubTab('trac_object_stat_lp');

		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
		$lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", $_POST["item_id"], true, true);
				
		$tpl->setContent($lp_table->getGraph($_POST["item_id"]).$lp_table->getHTML());
	}

	function showLearningProgressDetails()
	{
		include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
		$lp_table = new ilLPObjectStatisticsLPTableGUI($this, "showLearningProgressDetails", array($_GET["item_id"]), true, false, true);
		
		$a_tpl = new ilTemplate("tpl.lp_object_statistics_lp_details.html", true, true, "Services/Tracking");
		$a_tpl->setVariable("CONTENT", $lp_table->getHTML());
		$a_tpl->setVariable('CLOSE_IMG_TXT', $this->lng->txt('close'));
		echo $a_tpl->get();
		exit();	
	}
	
	protected function showAggregationInfo($a_show_link = true)
	{		
		global $ilAccess, $lng, $ilCtrl;
		
		include_once "Services/Tracking/classes/class.ilTrQuery.php";		
		$info = ilTrQuery::getObjectStatisticsLogInfo();
		$info_date = ilDatePresentation::formatDate(new ilDateTime($info["tstamp"], IL_CAL_UNIX));
					
		$link = "";
		if($a_show_link && $ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$link = " <a href=\"".$ilCtrl->getLinkTarget($this, "admin")."\">&raquo;".
				$lng->txt("trac_log_info_link")."</a>";
		}
		
		ilUtil::sendInfo(sprintf($lng->txt("trac_log_info"), $info_date, $info["counter"]).$link);

	}
}

?>