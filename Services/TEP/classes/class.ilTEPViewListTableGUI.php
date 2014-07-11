<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for TEP list view
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesTEP
*/
class ilTEPViewListTableGUI extends ilTable2GUI
{
	function __construct($a_parent_obj, $a_parent_cmd, array $a_data = null)
	{
		global $ilCtrl, $lng;
				
		$this->setId("teplist");
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setShowRowsSelector(true);
		$this->setTitle($lng->txt("tep_list_view_title"));
		$this->setDescription($lng->txt("tep_list_view_info"));
		
		$this->addColumn($lng->txt("title"), "title");		
		$this->addColumn($lng->txt("tep_entry_period"), "period");
		$this->addColumn($lng->txt("tep_entry_type"), "type");
		$this->addColumn($lng->txt("tep_entry_location"), "location");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.view_list_row.html", "Services/TEP");

		$this->setDefaultOrderField("period");
		$this->setDefaultOrderDirection("asc");
		
		if($a_data)
		{
			$this->getItems($a_data);		
		}
	}

	function getItems(array $a_data)
	{
		include_once "Services/TEP/classes/class.ilCalEntryType.php";		
		foreach(ilCalEntryType::getListData() as $item)
		{			
			$type_map[$item["id"]] = $item["title"];
		}
			
		include_once "Services/Link/classes/class.ilLink.php";
		
		$data = array();
		foreach($a_data as $event)
		{					
			if($event["fullday"])
			{
				$start = new ilDate($event["start"], IL_CAL_DATE);
				$end = new ilDate($event["end"], IL_CAL_DATE);
			}
			else
			{
				$start = new ilDateTime($event["starta"], IL_CAL_DATE, "UTC");
				$end = new ilDateTime($event["enda"], IL_CAL_DATE, "UTC");
			}
			
			$data[] = array(
				"title" => $event["title"],
				"type" => $type_map[$event["entry_type"]],
				"period" => $event["start"],
				"period_formatted" => ilDatePresentation::formatPeriod($start, $end, true),
				"location" => $event["location"],
				"url" => $event["url"]
			);
		}			
		
		$this->setData($data);
	}

	protected function fillRow($a_set)
	{		
		$this->tpl->setVariable("URL_TITLE", $a_set["url"]);
		$this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
		$this->tpl->setVariable("VAL_PERIOD", $a_set["period_formatted"]);
		$this->tpl->setVariable("VAL_TYPE", $a_set["type"]);
		$this->tpl->setVariable("VAL_LOCATION", $a_set["location"]);		
	}
}

?>