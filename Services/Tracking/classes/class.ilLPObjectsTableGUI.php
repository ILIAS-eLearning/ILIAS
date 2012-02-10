<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
* TableGUI class for learning progress (object overview)
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilLPObjectsTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPObjectsTableGUI extends ilLPTableBaseGUI
{
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_type = "")
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId("lpobjtbl");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($this->lng->txt("learning_progress"));
		$this->setDescription($this->lng->txt("trac_learning_progress_objects_description"));
		$this->setLimit(9999);
		
		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt("trac_title"), "title", "30%");

		include_once("Services/Tracking/classes/class.ilLPStatus.php");
		$all_status = array(LP_STATUS_NOT_ATTEMPTED_NUM => "status_not_attempted",
			LP_STATUS_IN_PROGRESS_NUM => "status_in_progress",
			LP_STATUS_COMPLETED_NUM => "status_completed",
			LP_STATUS_FAILED_NUM => "status_failed");
		foreach($all_status as $status => $column)
		{
			$caption = ilLearningProgressBaseGUI::_getStatusText($status);
		    $caption = "<img src=\"".ilLearningProgressBaseGUI::_getImagePathForStatus($status)."\" alt=\"".$caption."\" title=\"".$caption."\" /> ".$caption;

			$this->addColumn($caption, $column, "10%");
		}

		$this->addColumn($this->lng->txt("path"), "", "20%");
		$this->addColumn($this->lng->txt("actions"), "", "10%");
		
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setRowTemplate("tpl.lp_object_list_row.html", "Services/Tracking");
		#$this->disable("footer");
		$this->setEnableHeader(true);
		$this->setEnableNumInfo(true);
		$this->setEnableTitle(true);
		$this->setShowTemplates(true);
		
		$this->initFilter();

		$this->setSelectAllCheckbox("item_id");

		$this->addMultiCommand("hideSelected", $lng->txt("trac_hide_selected"));

		$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
		
		$this->getItems();
	}

	function getItems()
	{
		$obj_ids = $this->obj_ids;
		if(!$obj_ids)
	    {
			$obj_ids = $this->searchObjects($this->getCurrentFilter(true), "edit_learning_progress");
		}
		if($obj_ids)
		{
			$this->is_anonymized = !ilObjUserTracking::_enabledUserRelatedData();
			
			include_once("./Services/Tracking/classes/class.ilTrQuery.php");
			$data = ilTrQuery::getObjectsStatus($obj_ids);
			foreach($data as $idx => $item)
			{
				$data[$idx]["offline"] = ilLearningProgressBaseGUI::isObjectOffline($item["obj_id"], $item["type"]);
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

		$this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
		$this->tpl->setVariable("ICON_SRC", ilUtil::getTypeIconPath($a_set["type"], $a_set["obj_id"], "tiny"));
		$this->tpl->setVariable("ICON_ALT", $lng->txt($a_set["type"]));
		$this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);

		if($a_set["offline"])
		{
			$this->tpl->setCurrentBlock("offline");
			$this->tpl->setVariable("TEXT_STATUS", $this->lng->txt("status"));
			$this->tpl->setVariable("TEXT_OFFLINE", $this->lng->txt("offline"));
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("STATUS_NOT_ATTEMPTED_VALUE", $a_set["status_not_attempted"]);
		$this->tpl->setVariable("STATUS_IN_PROGRESS_VALUE", $a_set["status_in_progress"]);
		$this->tpl->setVariable("STATUS_COMPLETED_VALUE", $a_set["status_completed"]);
		$this->tpl->setVariable("STATUS_FAILED_VALUE", $a_set["status_failed"]);

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

		// hide / unhide?!
		$this->tpl->setCurrentBlock("item_command");
		$ilCtrl->setParameterByClass(get_class($this),'hide', $a_set["obj_id"]);
		$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass(get_class($this),'hide'));
		$this->tpl->setVariable("TXT_COMMAND", $lng->txt('trac_hide'));
		$this->tpl->parseCurrentBlock();

		if(!$this->is_anonymized)
		{
			$ref_id = $a_set["ref_ids"];
			$ref_id = array_shift($ref_id);
			$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', $ref_id);
			$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), 'details'));
			$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', '');
			$this->tpl->setVariable("TXT_COMMAND", $lng->txt('trac_participants'));
			$this->tpl->parseCurrentBlock();
		}
	}

	protected function fillHeaderExcel($worksheet, &$a_row)
	{
		$worksheet->write($a_row, 0, $this->lng->txt("type"));
		$worksheet->write($a_row, 1, $this->lng->txt("trac_title"));
		$worksheet->write($a_row, 2, ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_NOT_ATTEMPTED_NUM));
		$worksheet->write($a_row, 3, ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_IN_PROGRESS_NUM));
		$worksheet->write($a_row, 4, ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_COMPLETED_NUM));
		$worksheet->write($a_row, 5, ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_FAILED_NUM));
		// $worksheet->write($a_row, 6, $this->lng->txt("path"));
	}

	protected function fillRowExcel($worksheet, &$a_row, $a_set)
	{
		$worksheet->write($a_row, 0, $this->lng->txt($a_set["type"]));
		$worksheet->write($a_row, 1, $a_set["title"]);
		$worksheet->write($a_row, 2, $a_set["status_not_attempted"]);
		$worksheet->write($a_row, 3, $a_set["status_in_progress"]);
		$worksheet->write($a_row, 4, $a_set["status_completed"]);
		$worksheet->write($a_row, 5, $a_set["status_failed"]);
		
		/*
		// path
		$path = $this->buildPath($a_set["ref_ids"]);
		if($path)
		{
			$col = 6;
			foreach($path as $path_item)
			{
				$worksheet->write($a_row, $col, strip_tags($path_item));
				$col++;
			}
		}
		*/

	}

	protected function fillHeaderCSV($a_csv)
	{
		$a_csv->addColumn($this->lng->txt("type"));
		$a_csv->addColumn($this->lng->txt("trac_title"));
		$a_csv->addColumn(ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_NOT_ATTEMPTED_NUM));
		$a_csv->addColumn(ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_IN_PROGRESS_NUM));
		$a_csv->addColumn(ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_COMPLETED_NUM));
		$a_csv->addColumn(ilLearningProgressBaseGUI::_getStatusText(LP_STATUS_FAILED_NUM));
		// $a_csv->addColumn($this->lng->txt("path"));
		$a_csv->addRow();
	}

	protected function fillRowCSV($a_csv, $a_set)
	{
		$a_csv->addColumn($this->lng->txt($a_set["type"]));
		$a_csv->addColumn($a_set["title"]);
		$a_csv->addColumn($a_set["status_not_attempted"]);
		$a_csv->addColumn($a_set["status_in_progress"]);
		$a_csv->addColumn($a_set["status_completed"]);
		$a_csv->addColumn($a_set["status_failed"]);

		/*
		// path
		$path = $this->buildPath($a_set["ref_ids"]);
		if($path)
		{
			$col = 6;
			foreach($path as $path_item)
			{
				$a_csv->addColumn(strip_tags($path_item));
				$col++;
			}
		}
		*/

		$a_csv->addRow();
	}
}

?>