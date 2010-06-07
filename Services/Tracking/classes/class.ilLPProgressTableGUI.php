<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGui.php");

/**
* TableGUI class for learning progress
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilLPProgressTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPProgressTableGUI extends ilLPTableBaseGUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_user = "", $obj_ids = NULL, $details = false, $objectives_mode = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->tracked_user = $a_user;
		$this->obj_ids = $obj_ids;
		$this->objectives_mode = $objectives_mode;
		$this->details = $details;

		$this->setId("lp_table");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setLimit(ilSearchSettings::getInstance()->getMaxHits());

		if(!$this->details)
		{
			$this->addColumn("", "", "1", true);
			$this->addColumn($this->lng->txt("trac_title"), "title", "26%");
			$this->addColumn($this->lng->txt("status"), "status", "7%");
			$this->addColumn($this->lng->txt("trac_percentage"), "percentage", "7%");
			$this->addColumn($this->lng->txt("trac_mark"), "", "5%");
			$this->addColumn($this->lng->txt("comment"), "", "10%");
			$this->addColumn($this->lng->txt("trac_mode"), "", "20%");
			$this->addColumn($this->lng->txt("path"), "", "20%");
			$this->addColumn($this->lng->txt("actions"), "", "5%");

			$this->setTitle($this->lng->txt("learning_progress"));
			$this->initFilter();

			$this->setSelectAllCheckbox("item_id");
			$this->addMultiCommand("hideSelected", $lng->txt("trac_hide_selected"));
		}
		else
		{
			$this->addColumn($this->lng->txt("trac_title"), "title", "31%");
			$this->addColumn($this->lng->txt("status"), "status", "7%");
			$this->addColumn($this->lng->txt("trac_percentage"), "percentage", "7%");
			$this->addColumn($this->lng->txt("trac_mark"), "", "5%");
			$this->addColumn($this->lng->txt("comment"), "", "10%");
			$this->addColumn($this->lng->txt("trac_mode"), "", "20%");
			$this->addColumn($this->lng->txt("path"), "", "20%");
		}
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setRowTemplate("tpl.lp_progress_list_row.html", "Services/Tracking");
		$this->setEnableHeader(true);
		$this->setEnableNumInfo(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderField("title");
		$this->setDefaultOrderDirection("asc");

		// area selector gets in the way
		if($this->tracked_user)
		{
			$this->getItems();
		}
	}

	function getItems()
	{
		$obj_ids = $this->obj_ids;
		if(!$obj_ids && !$this->details)
	    {
			$obj_ids = $this->searchObjects($this->getCurrentFilter(true));
		}
		if($obj_ids)
		{
			include_once("./Services/Tracking/classes/class.ilTrQuery.php");
			if(!$this->objectives_mode)
			{
				$data = ilTrQuery::getObjectsStatusForUser($this->tracked_user->getId(), $obj_ids);
			}
			else
			{
				$data = ilTrQuery::getObjectivesStatusForUser($this->tracked_user->getId(), $obj_ids);
			}
			$this->setData($data);
		}
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilObjDataCache, $ilCtrl;

		if(!$this->details)
		{
			$this->tpl->setCurrentBlock("column_checkbox");
			$this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("ICON_SRC", ilUtil::getTypeIconPath($a_set["type"], $a_set["obj_id"], "small"));
		$this->tpl->setVariable("ICON_ALT", $lng->txt($a_set["type"]));
		$this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);
		$this->tpl->setVariable("PERCENTAGE_VALUE", sprintf("%d%%", $a_set["percentage"]));

		$this->tpl->setVariable("STATUS_ALT", ilLearningProgressBaseGUI::_getStatusText($a_set["status"]));
		$this->tpl->setVariable("STATUS_IMG", ilLearningProgressBaseGUI::_getImagePathForStatus($a_set["status"]));

		$this->tpl->setVariable("MODE_TEXT", ilLPObjSettings::_mode2Text($a_set["u_mode"]));
		$this->tpl->setVariable("MARK_VALUE", $a_set["mark"]);
		$this->tpl->setVariable("COMMENT_TEXT", $a_set["comment"]);

		// path
		$path = $this->buildPath($a_set["ref_ids"]);
		if($path)
		{
			$this->tpl->setCurrentBlock("item_path");
			foreach($path as $path_item)
			{
				$this->tpl->setVariable("PATH_ITEM", $path_item);
				$this->tpl->parseCurrentBlock();
			}
		}

		// tlt warning
		if($a_set["status"] != LP_STATUS_COMPLETED_NUM)
		{
			$ref_id = $a_set["ref_ids"];
			$ref_id = array_shift($ref_id);
			include_once 'Modules/Course/classes/Timings/class.ilTimingCache.php';
			if(ilCourseItems::_hasCollectionTimings($ref_id) && ilTimingCache::_showWarning($ref_id, $this->tracked_user->getId()))
			{
				$this->tpl->setCurrentBlock('warning_img');
				$this->tpl->setVariable('WARNING_IMG', ilUtil::getImagePath('warning.gif'));
				$this->tpl->setVariable('WARNING_ALT', $lng->txt('trac_time_passed'));
				$this->tpl->parseCurrentBlock();
			}
		}

		// hide / unhide?!
		if(!$this->details)
		{
			$this->tpl->setCurrentBlock("item_command");
			$ilCtrl->setParameterByClass(get_class($this),'hide', $a_set["obj_id"]);
			$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass(get_class($this),'hide'));
			$this->tpl->setVariable("TXT_COMMAND", $lng->txt('trac_hide'));
			$this->tpl->parseCurrentBlock();

			if(ilLPObjSettings::_isContainer($a_set["u_mode"]))
			{
				$ref_id = $a_set["ref_ids"];
				$ref_id = array_shift($ref_id);
				$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', $ref_id);
				$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), 'details'));
				$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', '');
				$this->tpl->setVariable("TXT_COMMAND", $lng->txt('trac_subitems'));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("column_action");
			$this->tpl->parseCurrentBlock();
		}
	}
}

?>