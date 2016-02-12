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
class ilBadgeTableGUI extends ilTable2GUI
{		
	protected $has_write; // [bool]
	
	function __construct($a_parent_obj, $a_parent_cmd = "", $a_parent_obj_id, $a_has_write = false)
	{
		global $ilCtrl, $lng;
		
		$this->setId("bdgbdg");
		$this->has_write = (bool)$a_has_write;
				
		parent::__construct($a_parent_obj, $a_parent_cmd);
			
		$this->setLimit(9999);		
		
		$this->setTitle($lng->txt("obj_bdga"));
						
		if($this->has_write)
		{	
			$this->addColumn("", "", 1);
		}
		
		$this->addColumn($lng->txt("title"), "title");
		$this->addColumn($lng->txt("active"), "active");
				
		if($this->has_write)
		{			
			$this->addColumn($lng->txt("action"), "");		
			$this->addMultiCommand("deleteBadges", $lng->txt("delete"));		
		}
		
		$this->setSelectAllCheckbox("id");
			
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.badge_row.html", "Services/Badge");	
		$this->setDefaultOrderField("title");
								
		$this->getItems($a_parent_obj_id);				
	}
	
	function getItems($a_parent_obj_id)
	{		
		$data = array();
		
		foreach(ilBadge::getInstancesByParentId($a_parent_obj_id) as $badge)
		{				
			$data[] = array(
				"id" => $badge->getId(),
				"title" => $badge->getTitle(),	
				"active" => $badge->isActive()
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
		$this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
			? $lng->txt("yes")
			: "&nbsp;");		
		
		if($this->has_write)
		{	
			$ilCtrl->setParameter($this->getParentObject(), "bid", $a_set["id"]);
			$url = $ilCtrl->getLinkTarget($this->getParentObject(), "editBadge");
			$ilCtrl->setParameter($this->getParentObject(), "bid", "");
			
			$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));	
			$this->tpl->setVariable("URL_EDIT", $url);	
		}
	}
}
