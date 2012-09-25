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
	
	const INACTIVE_PLUGIN = -1;
	
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
			
			// active plugin?
			include_once 'Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php';	
			if(ilRepositoryObjectPluginSlot::isTypePluginWithLP(ilObject::_lookupType($a_obj_id)))
			{
				$obj = ilObjectFactory::getInstanceByObjId($a_obj_id);
				if($obj && $obj instanceof ilLPStatusPluginInterface)
				{
					self::$plugins[$a_obj_id] = $obj;
				}
			}			
			// inactive plugin?
			else if(ilRepositoryObjectPluginSlot::isTypePluginWithLP(ilObject::_lookupType($a_obj_id), false))
			{
				self::$plugins[$a_obj_id] = self::INACTIVE_PLUGIN;
			}
		}	
		
		return self::$plugins[$a_obj_id];
	}

	function _getNotAttempted($a_obj_id)
	{			
		$plugin = self::initPluginObj($a_obj_id);		
		if($plugin)
		{
			if($plugin !== self::INACTIVE_PLUGIN)
			{
				return (array)$plugin->getLPNotAttempted();
			}
			else
			{
				// re-use existing data for inactive plugin
				return self::getLPStatusData($a_obj_id, LP_STATUS_NOT_ATTEMPTED_NUM);
			}
		}
		return array();
	}

	function _getInProgress($a_obj_id)
	{
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			if($plugin !== self::INACTIVE_PLUGIN)
			{
				return (array)$plugin->getLPInProgress();
			}
			else
			{
				// re-use existing data for inactive plugin
				return self::getLPStatusData($a_obj_id, LP_STATUS_IN_PROGRESS_NUM);
			}
		}
		return array();
	}

	function _getCompleted($a_obj_id)
	{
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			if($plugin !== self::INACTIVE_PLUGIN)
			{
				return (array)$plugin->getLPCompleted();
			}
			else
			{
				// re-use existing data for inactive plugin
				return self::getLPStatusData($a_obj_id, LP_STATUS_COMPLETED_NUM);
			}
		}
		return array();
	}
	
	function _getFailed($a_obj_id)
	{			
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{
			if($plugin !== self::INACTIVE_PLUGIN)
			{
				return (array)$plugin->getLPFailed();
			}
			else
			{
				// re-use existing data for inactive plugin
				return self::getLPStatusData($a_obj_id, LP_STATUS_FAILED_NUM);
			}
		}
		return array();
	}
	
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		$plugin = self::initPluginObj($a_obj_id);
		if($plugin)
		{					
			if($plugin !== self::INACTIVE_PLUGIN)
			{
				// :TODO: create read_event here to make sure?
				return $plugin->getLPStatusForUser($a_user_id);
			}
			else
			{
				// re-use existing data for inactive plugin
				return self::getLPDataForUser($a_obj_id, $a_user_id);
			}
		}		
	}
	
	/**
	 * Read existing LP status data
	 * 
	 * @param int $a_obj_id
	 * @param int $a_status
	 * @return array user ids
	 */
	protected static function getLPStatusData($a_obj_id, $a_status)
	{
		global $ilDB;
		
		$all = array();
		
		$set = $ilDB->query("SELECT usr_id".
			" FROM ut_lp_marks".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND status = ".$ilDB->quote($a_status, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$all[] = $row["usr_id"];
		}
		return $all;
	}
	
	/**
	 * Read existing LP status data for user 
	 * 
	 * @param int $a_obj_id
	 * @param int $a_user_id
	 * @return int
	 */
	protected static function getLPDataForUser($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT status".
			" FROM ut_lp_marks".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		$status = $row["status"];
		if(!$status)
		{
			$status = LP_STATUS_NOT_ATTEMPTED_NUM;
		}
		return $status;
	}
}	

?>