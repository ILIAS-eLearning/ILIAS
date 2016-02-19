<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Badge/classes/class.ilBadge.php");

/**
 * TableGUI class for badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilObjectBadgeTableGUI extends ilTable2GUI
{		
	protected $has_write; // [bool]
	
	function __construct($a_parent_obj, $a_parent_cmd = "", $a_has_write = false)
	{
		global $ilCtrl, $lng;
		
		$this->setId("bdgobdg");
		$this->has_write = (bool)$a_has_write;
				
		parent::__construct($a_parent_obj, $a_parent_cmd);
			
		$this->setLimit(9999);		
		
		$this->setTitle($lng->txt("badge_object_badges"));
						
		if($this->has_write)
		{	
			$this->addColumn("", "", 1);
		}
				
		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("type"), "type");
		$this->addColumn($lng->txt("container"), "container");		
		$this->addColumn($lng->txt("active"), "active");
		$this->addColumn($lng->txt("action"), "");		
			
		if($this->has_write)
		{												
			$this->addMultiCommand("activateBadges", $lng->txt("activate"));		
			$this->addMultiCommand("deactivateBadges", $lng->txt("deactivate"));	
			$this->addMultiCommand("confirmDeleteBadges", $lng->txt("delete"));	
			$this->setSelectAllCheckbox("id");
		}
			
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.object_badge_row.html", "Services/Badge");	
		$this->setDefaultOrderField("title");
		
		// :TODO: filter
								
		$this->getItems();				
	}
	
	function getItems()
	{		
		$data = array();
		
		$types = ilBadgeHandler::getInstance()->getAvailableTypes();
		
		foreach(ilBadge::getObjectInstances() as $badge_item)
		{
			// :TODO: container meta
			$container = "(".$badge_item["parent_type"]."/".
					$badge_item["parent_id"].") ".
					$badge_item["parent_title"];						
			if((bool)$badge_item["deleted"])
			{
				$container = '<s>'.$container.'</s>';
			}
									
			$data[] = array(
				"id" => $badge_item["id"],
				"active"=> $badge_item["active"],
				"type" => $types[$badge_item["type_id"]],
				"title" => $badge_item["title"],
				"container_meta" => $container,
				"container_id" => $badge_item["parent_id"]
			);			
		}
	
		$this->setData($data);
	}
	
	protected function fillRow($a_set)
	{					
		global $lng, $ilCtrl;
		
		if($this->has_write)
		{	
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		}
		
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);	
		$this->tpl->setVariable("TXT_TYPE", $a_set["type"]->getCaption());	
		$this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
			? $lng->txt("yes")
			: "&nbsp;");		
		
		$this->tpl->setVariable("TXT_CONTAINER", $a_set["container_meta"]);	
		
		if($this->has_write)
		{	
			$ilCtrl->setParameter($this->getParentObject(), "pid", $a_set["container_id"]);
			$url = $ilCtrl->getLinkTarget($this->getParentObject(), "listObjectBadgeUsers");
			$ilCtrl->setParameter($this->getParentObject(), "pid", "");
			
			$this->tpl->setVariable("TXT_LIST", $lng->txt("details"));	
			$this->tpl->setVariable("URL_LIST", $url);	
		}
	}
}
