<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
 * LP handler class for plugins 
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @package ServicesTracking
 */
class ilLPStatusPlugin extends ilLPStatus
{
	static protected $plugins; // [array]
	
	/**
	 * Get ilObjectPlugin for object id
	 * 
	 * @param int $a_obj_id
	 * @return ilObjectPlugin
	 */
	protected static function initPluginObj($a_obj_id)
	{		
		if(!isset(self::$plugins[$a_obj_id]))
		{			
			self::$plugins[$a_obj_id] = false;
			
			include_once 'Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php';	
			if(ilRepositoryObjectPluginSlot::isTypePluginWithLP(ilObject::_lookupType($a_obj_id)))
			{
				$obj = ilObjectFactory::getInstanceByObjId($a_obj_id);
				if($obj && $obj instanceof ilLPStatusPluginInterface)
				{
					self::$plugins[$a_obj_id] = $obj;
				}
			}			
		}	
		
		return self::$plugins[$a_obj_id];
	}

	function _getNotAttempted($a_obj_id)
	{			
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			return (array)$plugin->getLPNotAttempted();
		}
		return array();
	}

	function _getInProgress($a_obj_id)
	{
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			return (array)$plugin->getLPInProgress();
		}
		return array();
	}

	function _getCompleted($a_obj_id)
	{
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			return (array)$plugin->getLPCompleted();
		}
		return array();
	}
	
	function _getFailed($a_obj_id)
	{			
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			return (array)$plugin->getLPFailed();
		}
		return array();
	}
	
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			// :TODO: create read_event here to make sure?
			
			return $plugin->getLPStatusForUser($a_user_id);
		}		
	}
}	

?>