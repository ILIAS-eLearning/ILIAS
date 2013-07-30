<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("Services/Repository/classes/class.ilObjRepositorySettings.php");

/**
 * TableGUI class for new item groups
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesRepository
 */
class ilNewItemGroupTableGUI extends ilTable2GUI
{		
	function __construct($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
				
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->setId("repnwitgrptbl");
		
		$this->setTitle($lng->txt("rep_new_item_groups"));

		$this->addColumn("", "", 1);
		$this->addColumn($lng->txt("cmps_add_new_rank"), "");
		$this->addColumn($lng->txt("title"), "");	
		$this->addColumn($lng->txt("rep_new_item_group_nr_subitems"), "");	
		$this->addColumn($lng->txt("action"), "");	

		$this->addCommandButton("saveNewItemGroupOrder", $lng->txt("cmps_save_options"));	
		$this->addMultiCommand("confirmDeleteNewItemGroup", $lng->txt("delete"));
		
	
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_new_item_group.html", "Services/Repository");	
		$this->setLimit(10000);		
		
		$this->setExternalSorting(true);		
		$this->getGroups();
	}
	
	/**
	* Get pages for list.
	*/
	function getGroups()
	{		
		global $lng;
		
		$data = array();
				
		$subitems = ilObjRepositorySettings::getNewItemGroupSubItems();
		
		if($subitems[0])
		{
			ilUtil::sendInfo(sprintf($lng->txt("rep_new_item_group_unassigned_subitems"), sizeof($subitems[0])));
			unset($subitems[0]);
		}
		
		foreach(ilObjRepositorySettings::getNewItemGroups() as $item)
		{
			$data[] = array(
				"id" => $item["id"],
				"pos" => $item["pos"],
				"title" => $item["title"],
				"type" => $item["type"],
				"subitems" => sizeof($subitems[$item["id"]])
			);
		}
		
		$data = ilUtil::sortArray($data, "pos", "asc", true);
		
		$this->setData($data);
	}

	protected function fillRow($a_set)
	{											
		global $lng, $ilCtrl;
		
		$this->tpl->setVariable("VAR_MULTI", "grp_id[]");
		$this->tpl->setVariable("VAL_MULTI", $a_set["id"]);
		
		$this->tpl->setVariable("VAR_POS", "grp_order[".$a_set["id"]."]");
		$this->tpl->setVariable("VAL_POS", $a_set["pos"]);
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		
		if($a_set["type"] == ilObjRepositorySettings::NEW_ITEM_GROUP_TYPE_GROUP)
		{			
			$this->tpl->setVariable("VAL_ITEMS", $a_set["subitems"]);

			$ilCtrl->setParameter($this->parent_obj, "grp_id", $a_set["id"]);
			$url = $ilCtrl->getLinkTarget($this->parent_obj, "editNewItemGroup");
			$ilCtrl->setParameter($this->parent_obj, "grp_id", "");

			$this->tpl->setVariable("URL_EDIT", $url);
			$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
		}		
	}
}

?>