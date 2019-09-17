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
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	protected $has_write; // [bool]
	
	function __construct($a_parent_obj, $a_parent_cmd = "", $a_has_write = false)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->access = $DIC->access();
		$ilCtrl = $DIC->ctrl();
		$lng = $DIC->language();
		
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
		$lng = $this->lng;
		
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
				$options[$id] = ilBadge::getExtendedTypeCaption($type);
			}
		}
		asort($options);
		
		$type = $this->addFilterItemByMetaType("type", self::FILTER_SELECT, false, $lng->txt("type"));
		$type->setOptions($options);
		$this->filter["type"] = $type->getValue();						
	}	
	
	function getItems()
	{		
		$lng = $this->lng;
		$ilAccess = $this->access;
		
		$data = $filter_types = array();
		
		$types = ilBadgeHandler::getInstance()->getAvailableTypes();
		
		include_once "Services/Badge/classes/class.ilBadgeRenderer.php";
		include_once "Services/Link/classes/class.ilLink.php";
		
		foreach(ilBadge::getObjectInstances($this->filter) as $badge_item)
		{
			// :TODO: container presentation
			$container_url = null;
			$container = '<img class="ilIcon" src="'.
					ilObject::_getIcon($badge_item["parent_id"], "big", $badge_item["parent_type"]).
					'" alt="'.$lng->txt("obj_".$badge_item["parent_type"]).
					'" title="'.$lng->txt("obj_".$badge_item["parent_type"]).'" /> '.
					$badge_item["parent_title"];
			
			if((bool)$badge_item["deleted"])
			{
				$container .=  ' <span class="il_ItemAlertProperty">'.$lng->txt("deleted").'</span>';
			}
			else
			{
				$ref_id = array_shift(ilObject::_getAllReferences($badge_item["parent_id"]));
				if($ilAccess->checkAccess("read", "", $ref_id))
				{
					$container_url = ilLink::_getLink($ref_id);
				}
			}
			
			$type_caption = ilBadge::getExtendedTypeCaption($types[$badge_item["type_id"]]);
									
			$data[] = array(
				"id" => $badge_item["id"],
				"active"=> $badge_item["active"],
				"type" => $type_caption,
				"title" => $badge_item["title"],
				"container_meta" => $container,
				"container_url" => $container_url,
				"container_id" => $badge_item["parent_id"],
				"renderer" => new ilBadgeRenderer(null, new ilBadge($badge_item["id"]))
			);			
			
			$filter_types[$badge_item["type_id"]] = $type_caption;
		}
		
		$this->setData($data);
	}
	
	protected function fillRow($a_set)
	{					
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;
		
		if($a_set["container_url"])
		{
			$this->tpl->setCurrentBlock("container_link_bl");
			$this->tpl->setVariable("TXT_CONTAINER", $a_set["container_meta"]);	
			$this->tpl->setVariable("URL_CONTAINER", $a_set["container_url"]);	
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("container_nolink_bl");
			$this->tpl->setVariable("TXT_CONTAINER_STATIC", $a_set["container_meta"]);				
			$this->tpl->parseCurrentBlock();
		}
		
		if($this->has_write)
		{	
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		}
		
		$this->tpl->setVariable("PREVIEW", $a_set["renderer"]->getHTML());	
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);	
		$this->tpl->setVariable("TXT_TYPE", $a_set["type"]);	
		$this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
			? $lng->txt("yes")
			: $lng->txt("no"));		
		
		if($this->has_write)
		{	
			$ilCtrl->setParameter($this->getParentObject(), "pid", $a_set["container_id"]);
			$ilCtrl->setParameter($this->getParentObject(), "bid", $a_set["id"]);
			$url = $ilCtrl->getLinkTarget($this->getParentObject(), "listObjectBadgeUsers");
			$ilCtrl->setParameter($this->getParentObject(), "bid", "");
			$ilCtrl->setParameter($this->getParentObject(), "pid", "");
			
			$this->tpl->setVariable("TXT_LIST", $lng->txt("users"));	
			$this->tpl->setVariable("URL_LIST", $url);	
		}
	}
}
