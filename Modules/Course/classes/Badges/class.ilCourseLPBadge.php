<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeType.php";
require_once "./Services/Badge/interfaces/interface.ilBadgeAuto.php";

/**
 * Class ilCourseLPBadge
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ModulesCourse
 */
class ilCourseLPBadge implements ilBadgeType, ilBadgeAuto
{
	public function getId()
	{
		return "course_lp";
	}
	
	public function getCaption()
	{
		global $lng;
		return $lng->txt("badge_course_lp");
	}
	
	public function isSingleton()
	{
		return false;
	}
	
	public function getValidObjectTypes()
	{
		return array("crs");
	}
	
	public function getConfigGUIInstance()
	{
		include_once "Modules/Course/classes/Badges/class.ilCourseLPBadgeGUI.php";
		return new ilCourseLPBadgeGUI();
	}
	
	public function evaluate($a_user_id, array $a_params, array $a_config)
	{
		$trigger_subitem_id = $a_params["obj_id"];
				
		// relevant for current badge instance?
		if(in_array($trigger_subitem_id, $a_config["subitems"]))
		{
			$completed = true;
			
			// check if all subitems are completed now
			foreach($a_config["subitems"] as $subitem_id)
			{
				if(ilLPStatus::_lookupStatus($subitem_id, $a_user_id) != ilLPStatus::LP_STATUS_COMPLETED_NUM)
				{
					$completed = false;
					break;
				}
			}
			
			return $completed;
		}
		
		return false;		
	}
	
	public static function getValidSubItems($a_ref_id)
	{
		global $tree;
		
		$res = array();
		
		$root = $tree->getNodeData($a_ref_id);
		$sub_items = $tree->getSubTree($root);
		array_shift($sub_items); // remove root
		
		include_once "Services/Object/classes/class.ilObjectLP.php";
		foreach($sub_items as $node)
		{
			if(ilObjectLP::isSupportedObjectType($node["type"]))
			{
				$res[] = array(
					"type" => $node["type"],
					"obj_id" => $node["obj_id"],
					"ref_id" => $node["ref_id"],
					"title" => $node["title"],
					"parent_ref_id" => $node["parent"]
				);
			}			
		}		
		
		return $res;
	}
}