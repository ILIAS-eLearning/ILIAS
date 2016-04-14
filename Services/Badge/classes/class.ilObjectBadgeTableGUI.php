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
		$this->addColumn($lng->txt("object"), "container");		
		$this->addColumn($lng->txt("active"), "active");
		$this->addColumn($lng->txt("action"), "");		
			
		if($this->has_write)
		{												
			$this->addMultiCommand("activateObjectBadges", $lng->txt("activate"));		
			$this->addMultiCommand("deactivateObjectBadges", $lng->txt("deactivate"));	
			$this->addMultiCommand("confirmDeleteObjectBadges", $lng->txt("delete"));	
			$this->setSelectAllCheckbox("id");
		}
			
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.object_badge_row.html", "Services/Badge");	
		$this->setDefaultOrderField("title");
		
		$this->setFilterCommand("applyObjectFilter");
		$this->setResetCommand("resetObjectFilter");
						
		$this->initFilter();
			
		$this->getItems();				
	}
	
	public function initFilter()
	{
		global $lng;
		
		$title = $this->addFilterItemByMetaType("title", self::FILTER_TEXT, false, $lng->txt("title"));		
		$this->filter["title"] = $title->getValue();
		
		$object = $this->addFilterItemByMetaType("object", self::FILTER_TEXT, false, $lng->txt("object"));		
		$this->filter["object"] = $object->getValue();
		
		$lng->loadLanguageModule("search");
						
		$options = array(
			"" => $lng->txt("search_any"),
		);
		foreach(ilBadgeHandler::getInstance()->getAvailableTypes() as $id => $type)
		{
			// no activity badges
			if(!in_array("bdga", $type->getValidObjectTypes()))
			{
				$options[$id] = $type->getCaption();
			}
		}
		asort($options);
		
		$type = $this->addFilterItemByMetaType("type", self::FILTER_SELECT, false, $lng->txt("type"));
		$type->setOptions($options);
		$this->filter["type"] = $type->getValue();						
	}	
	
	function getItems()
	{		
		global $lng;
		
		$data = $filter_types = array();
		
		$types = ilBadgeHandler::getInstance()->getAvailableTypes();
		
		foreach(ilBadge::getObjectInstances($this->filter) as $badge_item)
		{
			// :TODO: container presentation
			$container = '<img src="'.
					ilObject::_getIcon($badge_item["parent_id"], "big", $badge_item["parent_type"]).
					'" alt="'.$lng->txt("obj_".$badge_item["parent_type"]).
					'" title="'.$lng->txt("obj_".$badge_item["parent_type"]).'" /> '.
					$badge_item["parent_title"];
			
			if((bool)$badge_item["deleted"])
			{
				$container .=  ' <span class="il_ItemAlertProperty">'.$lng->txt("deleted").'</span>';
			}
			
			$type_caption = $types[$badge_item["type_id"]]->getCaption();
									
			$data[] = array(
				"id" => $badge_item["id"],
				"active"=> $badge_item["active"],
				"type" => $type_caption,
				"title" => $badge_item["title"],
				"container_meta" => $container,
				"container_id" => $badge_item["parent_id"]
			);			
			
			$filter_types[$badge_item["type_id"]] = $type_caption;
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
		$this->tpl->setVariable("TXT_TYPE", $a_set["type"]);	
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
