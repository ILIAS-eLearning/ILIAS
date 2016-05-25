<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeTypeGUI.php";

/**
 * Course LP badge gui 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ModulesCourse
 */
class ilCourseLPBadgeGUI implements ilBadgeTypeGUI
{	
	protected $parent_ref_id; // [int]
	
	public function initConfigForm(ilPropertyFormGUI $a_form, $a_parent_ref_id)
	{
		global $lng;
		
		$this->parent_ref_id = (int)$a_parent_ref_id;
		
		$subitems = new ilCheckboxGroupInputGUI($lng->txt("objects"), "subitems");	
		$subitems->setRequired(true);
		$a_form->addItem($subitems);
		
		foreach(ilCourseLPBadge::getValidSubItems($this->parent_ref_id) as $item)
		{
			$option = new ilCheckboxOption($item["title"], $item["obj_id"]);
			$subitems->addOption($option);								
		}
	}
	
	public function importConfigToForm(ilPropertyFormGUI $a_form, array $a_config)
	{
		if(is_array($a_config["subitems"]))
		{	
			$items = $a_form->getItemByPostVar("subitems");		
			$items->setValue($a_config["subitems"]);			
		}
	}
	
	public function getConfigFromForm(ilPropertyFormGUI $a_form)
	{		
		return array("subitems" => $a_form->getInput("subitems"));
	}
}