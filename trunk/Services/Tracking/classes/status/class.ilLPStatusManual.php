<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Tracking/classes/class.ilLPStatus.php';

/**
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup	ServicesTracking
*
*/
class ilLPStatusManual extends ilLPStatus
{

	function ilLPStatusManual($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getInProgress($a_obj_id)
	{
		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
		
		// Exclude all users with status completed.
		$users = array_diff((array) $users,ilLPStatusWrapper::_getCompleted($a_obj_id));

		return $users;						
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;
		
		$usr_ids = array();

		$query = "SELECT DISTINCT(usr_id) user_id FROM ut_lp_marks ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND completed = '1' ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->user_id;
		}

		return $usr_ids;
	}

	/**
	 * Determine status
	 *
	 * @param	integer		object id
	 * @param	integer		user id
	 * @param	object		object (optional depends on object type)
	 * @return	integer		status
	 */
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		global $ilObjDataCache, $ilDB;

		$status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
		switch ($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'dbk':
			case 'lm':
			case 'htlm':
				include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
				if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id))
				{
					$status = self::LP_STATUS_IN_PROGRESS_NUM;
					
					// completed?
					$set = $ilDB->query($q = "SELECT usr_id FROM ut_lp_marks ".
						"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
						"AND usr_id = ".$ilDB->quote($a_user_id ,'integer')." ".
						"AND completed = '1' ");
					if ($rec = $ilDB->fetchAssoc($set))
					{
						$status = self::LP_STATUS_COMPLETED_NUM;
					}
				}
				break;			
		}
		return $status;		
	}
	
	/**
	 * Get failed users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupFailedForObject($a_obj_id, $a_user_ids = null)
	{
		return array();
	}	
}	
?>