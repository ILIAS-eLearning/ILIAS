<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");
include_once("./Services/Badge/classes/class.ilBadgeHandler.php");

/**
 * TableGUI class for badge type listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesBadge
 */
class ilBadgeTypesTableGUI extends ilTable2GUI
{		
	function __construct($a_parent_obj, $a_parent_cmd = "", $a_has_write)
	{
		global $ilCtrl, $lng;
		
		$this->setId("bdgtps");
				
		parent::__construct($a_parent_obj, $a_parent_cmd);
			
		$this->setLimit(9999);		
		
		$this->setTitle($lng->txt("badge_types"));
		
		$lng->loadLanguageModule("cmps");

		$this->addColumn($lng->txt("active"), "inactive", 1);
		$this->addColumn($lng->txt("cmps_component"), "comp");
		$this->addColumn($lng->txt("name"), "caption");			
		$this->addColumn($lng->txt("badge_manual"), "manual");			
	
		if((bool)$a_has_write)
		{			
			$this->addCommandButton("saveTypes", $lng->txt("save"));		
			$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		}
					
		$this->setRowTemplate("tpl.type_row.html", "Services/Badge");	
				
		$this->getItems();				
	}
	
	function getItems()
	{		
		$data = array();
		
		$handler = ilBadgeHandler::getInstance();
		$inactive = $handler->getInactiveTypes();
		foreach($handler->getComponents() as $component)
		{			
			$provider = $handler->getProviderInstance($component);
			if($provider)
			{				
				foreach($provider->getBadgeTypes() as $badge_obj)
				{
					$id = $handler->getUniqueTypeId($component, $badge_obj);
					
					$data[] = array(
						"id" => $id,
						"comp" => $handler->getComponentCaption($component),
						"caption" => $badge_obj->getCaption(),
						"manual" => ($badge_obj instanceof ilBadgeManual),
						"inactive" => in_array($id, $inactive)
					);					
				}
			}
		}
		
		$this->setData($data);
	}
	
	protected function fillRow($a_set)
	{					
		global $lng;
		
		$this->tpl->setVariable("VAL_ID", $a_set["id"]);
		$this->tpl->setVariable("TXT_COMP", $a_set["comp"]);
		$this->tpl->setVariable("TXT_NAME", $a_set["caption"]);
		$this->tpl->setVariable("TXT_MANUAL", $a_set["manual"]
			? $lng->txt("yes")
			: "&nbsp;");
		
		if(!$a_set["inactive"])
		{
			$this->tpl->setVariable("VAL_ACTIVE", ' checked="checked"');
		}
	}
}
