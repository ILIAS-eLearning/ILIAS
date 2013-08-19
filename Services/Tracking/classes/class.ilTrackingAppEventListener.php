<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/EventHandling/interfaces/interface.ilAppEventListener.php';

/** 
* Update lp data from Services/Object events 
* 
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesTracking
*/
class ilTrackingAppEventListener implements ilAppEventListener
{
	/**
	* Handle an event in a listener.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	public static function handleEvent($a_component, $a_event, $a_params)
	{				
		$obj_id = $a_params['obj_id'];
		
		// #11514
		
		switch($a_component)
		{					
			case 'Services/Object':				
				switch($a_event)
				{					
					case 'toTrash':
						self::handleToTrash($obj_id);
						break;
						
					case 'delete':
						self::handleDelete($obj_id);
						break;						
				}
				break;			
		}
		
		return true;
	}
	
	protected static function handleToTrash($a_obj_id)
	{			
		self::updateParentCollections($a_obj_id);		
	}
	
	protected static function handleDelete($a_obj_id)
	{					
		include_once "Services/Tracking/classes/class.ilLPMarks.php";
		ilLPMarks::deleteObject($a_obj_id);

		include_once "Services/Tracking/classes/class.ilChangeEvent.php";
		ilChangeEvent::_delete($a_obj_id);		
		
		include_once "Services/Tracking/classes/class.ilLPCollections.php";		
		ilLPCollections::_deleteAll($a_obj_id);		
		
		self::updateParentCollections($a_obj_id);	
	}
	
	protected static function updateParentCollections($a_obj_id)
	{
		global $ilDB;
		
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		
		// update parent collections?		
		$set = $ilDB->query("SELECT ut_lp_collections.obj_id obj_id FROM ".
				"object_reference JOIN ut_lp_collections ON ".
				"(object_reference.obj_id = ".$ilDB->quote($a_obj_id, "integer").
				" AND object_reference.ref_id = ut_lp_collections.item_id)");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if (in_array(ilObject::_lookupType($rec["obj_id"]), array("crs", "grp", "fold")))
			{				
				// remove from parent collection
				$query = "DELETE FROM ut_lp_collections".
					" WHERE obj_id = ".$ilDB->quote($rec["obj_id"], "integer").
					" AND item_id = ".$ilDB->quote($a_obj_id, "integer");
				$ilDB->manipulate($query);
					
				ilLPStatusWrapper::_refreshStatus($rec["obj_id"]);			
			}
		}
	}
}

?>