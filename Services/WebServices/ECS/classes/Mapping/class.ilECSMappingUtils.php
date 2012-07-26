<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Mapping utils
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSMappingUtils
{
	const MAPPED_WHOLE_TREE = 1;
	const MAPPED_MANUAL = 2;
	const MAPPED_UNMAPPED = 3;


	/**
	 * Lookup mapping status
	 * @param int $a_server_id
	 * @param int $a_tree_id
	 * @return int
	 */
	public static function lookupMappingStatus($a_server_id, $a_mid, $a_tree_id)
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingAssignments.php';

		if(ilECSNodeMappingAssignments::hasAssignments($a_server_id, $a_mid, $a_tree_id))
		{
			if(ilECSNodeMappingAssignments::isWholeTreeMapped($a_server_id, $a_mid, $a_tree_id))
			{
				return self::MAPPED_WHOLE_TREE;
			}
			return self::MAPPED_MANUAL;
		}
		return self::MAPPED_UNMAPPED;
	}
	
	/** 
	 * Get mapping status as string
	 * @param int $a_status 
	 */
	public static function mappingStatusToString($a_status)
	{
		global $lng;
		
		return $lng->txt('ecs_node_mapping_status_'.$a_status);
	}


}
?>
