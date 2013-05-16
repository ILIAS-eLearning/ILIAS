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
		global $ilCtrl, $tree, $ilUser;
		
		$this->setId("pdmng");
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn('','', '5%');
		$this->addColumn($this->lng->txt("type"), 'type_caption', '1%');
		$this->addColumn($this->lng->txt("title"), 'title', '44%');
		$this->addColumn($this->lng->txt("container"), 'container', '50%');
		
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
				
		// root node caption
		$root = $tree->getNodeData(ROOT_FOLDER_ID);
		$root = $root["title"];
		if ($root == "ILIAS")
		{
			$root = $this->lng->txt("repository");
		}
		
		foreach($a_data as $idx => $item)
		{
			if(!$item["parent_ref"])
			{
			   unset($a_data[$idx]);
			   continue;
			}
			
			if($a_view == ilPDSelectedItemsBlockGUI::VIEW_MY_MEMBERSHIPS)
			{
				$a_data[$idx]["last_admin"] = false;
				
				switch($item["type"])
				{
					case "crs":
						// see ilObjCourseGUI:performUnsubscribeObject()		
						include_once "Modules/Course/classes/class.ilCourseParticipants.php";
						$members = new ilCourseParticipants($item["obj_id"]);						
						break;
					
					case "grp":
						include_once "Modules/Group/classes/class.ilGroupParticipants.php";
						$members = new ilGroupParticipants($item["obj_id"]);
						break;
					
					default:
						// do nothing?
						continue;
				}
						
				$a_data[$idx]["last_admin"] = $members->isLastAdmin($ilUser->getId());
			}
			
			$a_data[$idx]["type_caption"] = $this->lng->txt("obj_".$item["type"]);
			
			// parent
			if ($tree->getRootId() != $item["parent_ref"])
			{
				$a_data[$idx]["container"] = ilObject::_lookupTitle(ilObject::_lookupObjId($item["parent_ref"]));
			}
			else
			{
				$a_data[$idx]["container"] = $root;
			}
			
		}
		
		$this->setData($a_data);				
	}
	
	public function fillRow($a_set)
	{		
		if($a_set["last_admin"])
		{
			$this->tpl->setCurrentBlock("warning_bl");
			$this->tpl->setVariable("TXT_WARNING", $this->lng->txt('pd_min_one_admin'));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setVariable("CHK_DISABLED", ' disabled="disabled"');		
		}
		
		$this->tpl->setVariable("VAL_REF_ID", $a_set["ref_id"]);
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_DESCRIPTION", $a_set["desc"]);
		$this->tpl->setVariable("URL_ICON", ilUtil::getTypeIconPath($a_set["type"], $a_set["obj_id"]));
		$this->tpl->setVariable("TXT_TYPE", $this->lng->txt("icon")." ".$a_set["type_caption"]);		
		$this->tpl->setVariable("TXT_CONTAINER", $a_set["container"]);			
	}	
}

?>