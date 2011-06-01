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
	}

	protected function setTabs()
	{
		$this->tabs_gui->addSubTab('trac_object_stat_access', 
				$this->lng->txt('trac_object_stat_access'),
				$this->ctrl->getLinkTarget($this, 'access'));		
		$this->tabs_gui->addSubTab('trac_object_stat_daily', 
				$this->lng->txt('trac_object_stat_daily'),
				$this->ctrl->getLinkTarget($this, 'daily'));
		$this->tabs_gui->addSubTab('trac_object_stat_types', 
				$this->lng->txt('trac_object_stat_types'),
				$this->ctrl->getLinkTarget($this, 'types'));
		$this->tabs_gui->addSubTab('trac_object_stat_admin', 
				$this->lng->txt('trac_object_stat_admin'),
				$this->ctrl->getLinkTarget($this, 'admin'));
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
		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access");
		$lp_table->resetOffset();
		$lp_table->writeFilterToSession();
		$this->access();
	}

	function resetAccessFilter()
	{
		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access");
		$lp_table->resetOffset();
		$lp_table->resetFilter();
		$this->access();
	}

	function access()
	{
		global $tpl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_access');

		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access");
		
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

		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTableGUI($this, "access", $_POST["item_id"]);

		$tpl->setContent($lp_table->getGraph($_POST["item_id"]).$lp_table->getHTML());
	}

	function applyTypesFilter()
	{
		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types");
		$lp_table->resetOffset();
		$lp_table->writeFilterToSession();
		$this->types();
	}

	function resetTypesFilter()
	{
		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types");
		$lp_table->resetOffset();
		$lp_table->resetFilter();
		$this->types();
	}

	function types()
	{
		global $tpl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_types');

		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types");

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

		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsTypesTableGUI.php");
		$lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", $_POST["item_id"]);

		$tpl->setContent($lp_table->getGraph($_POST["item_id"]).$lp_table->getHTML());
	}

	function applyDailyFilter()
	{
		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily");
		$lp_table->resetOffset();
		$lp_table->writeFilterToSession();
		$this->daily();
	}

	function resetDailyFilter()
	{
		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily");
		$lp_table->resetOffset();
		$lp_table->resetFilter();
		$this->daily();
	}

	function daily()
	{
		global $tpl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_daily');

		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily");

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

		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsDailyTableGUI.php");
		$lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", $_POST["item_id"]);

		$tpl->setContent($lp_table->getGraph($_POST["item_id"]).$lp_table->getHTML());
	}

	function admin()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		$this->tabs_gui->activateSubTab('trac_object_stat_admin');

		$ilToolbar->addButton($lng->txt("trac_sync_obj_stats"),
			$ilCtrl->getLinkTarget($this, "adminSync"));

		include_once("./Services/Tracking/classes/class.ilLPObjectStatisticsAdminTableGUI.php");
		$lp_table = new ilLPObjectStatisticsAdminTableGUI($this, "admin");

		$tpl->setContent($lp_table->getHTML());
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
}

?>