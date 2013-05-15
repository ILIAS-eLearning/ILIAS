<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilSurveyResultsCumulatedTableGUI.php 42216 2013-05-15 12:15:44Z jluetzen $
*
* @ingroup ServicesPersonalDesktop
*/
class ilPDSelectedItemsTableGUI extends ilTable2GUI
{	
	public function __construct($a_parent_obj, $a_parent_cmd, array &$a_data, $a_view, $a_by_location = false)
	{
		global $ilCtrl;
		
		$this->setId("pdmng");
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn('','');
		$this->addColumn($this->lng->txt("type"), 'type_caption');
		$this->addColumn($this->lng->txt("title"), 'title');
		
		$this->setDefaultOrderField("title");
		
		$this->setRowTemplate("tpl.pd_manage_row.html", "Services/PersonalDesktop");

		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		
		if($a_view == ilPDSelectedItemsBlockGUI::VIEW_MY_OFFERS)
		{
			$this->setTitle($this->lng->txt("pd_my_offers"));
			$this->addMultiCommand('confirmRemove', $this->lng->txt('unsubscribe'));			
		}
		else
		{			
			$this->setTitle($this->lng->txt("pd_my_memberships"));
			$this->addMultiCommand('confirmRemove', $this->lng->txt('crs_unsubscribe'));			
		}
		
		$this->addCommandButton("getHTML", $this->lng->txt("cancel"));
		
		foreach($a_data as $idx => $item)
		{
			$a_data[$idx]["type_caption"] = $this->lng->txt("obj_".$item["type"]);
		}
		
		$this->setData($a_data);				
	}
	
	public function fillRow($a_set)
	{
		$this->tpl->setVariable("VAL_REF_ID", $a_set["ref_id"]);
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_DESCRIPTION", $a_set["desc"]);
		$this->tpl->setVariable("URL_ICON", ilUtil::getTypeIconPath($a_set["type"], $a_set["obj_id"]));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("icon")." ".$a_set["type_caption"]);		
	}	
}

?>