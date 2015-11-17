<?php

/** 
* @author Adam MacDonald <adam.macdonald@cpkn.ca>
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesTracking 
*/

include_once 'Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusRubric extends ilLPStatus
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int object id
	 * 
	 */
	function __construct($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db = $ilDB;
	}
	
	/**
	 * get not attempted
	 *
	 * @access public
	 * @param int object id
	 * @return array int Array of user ids
	 * 
	 */
	public function _getNotAttempted($a_obj_id)
	{		
		$users = array();
	
		$members = self::getMembers($a_obj_id);
		if($members)
		{
			// diff in progress and completed (use stored result in LPStatusWrapper)
			$users = array_diff($members, ilLPStatusWrapper::_getInProgress($a_obj_id));
			$users = array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));			
		}
		
		return $users;
	}
	
	/**
	 * get in progress
	 *
	 * @access public
	 * @param int object id
	 * @return array int Array of user ids
	 */
	public function _getInProgress($a_obj_id)
	{		
		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
		
		// Exclude all users with status completed.
		$users = array_diff((array) $users,ilLPStatusWrapper::_getCompleted($a_obj_id));

		if($users)
		{
			// Exclude all non members
			$users = array_intersect(self::getMembers($a_obj_id), (array)$users);
		}
		
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
		
		if($usr_ids)
		{
			// Exclude all non members
			$usr_ids = array_intersect(self::getMembers($a_obj_id), (array)$usr_ids);
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
        case "crs":
        case "grp":
        case "exc":
            // completed?
            $set = $ilDB->query($q = "SELECT usr_id,mark FROM ut_lp_marks ".
            "WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
            "AND usr_id = ".$ilDB->quote($a_user_id ,'integer')." ".
            "AND completed = '1' ");
            
            $grade = $ilDB->fetchAssoc($ilDB->query("SELECT passing_grade FROM rubric WHERE obj_id = ".$ilDB->quote($a_obj_id, 'integer')));            
            
            if ($rec = $ilDB->fetchAssoc($set))
            {
                if($rec['mark']<$grade['passing_grade'])
                {
                $status = self::LP_STATUS_FAILED_NUM;
                }
                else
                {
                $status = self::LP_STATUS_COMPLETED_NUM;
                }
            }
            else
            {
                include_once './Services/Tracking/classes/class.ilChangeEvent.php';
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id))
                {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;
                }
            }   
        break;
        
        }
        return $status;
    }
	
	/**
	 * Get members for object
	 * @param int $a_obj_id
	 * @return array
	 */
	protected static function getMembers($a_obj_id)
	{
		global $ilObjDataCache;
        
		switch($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'crs':
				include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
				$member_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
				return $member_obj->getMembers();
			case 'exc':
                include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
                return ilExerciseMembers::_getMembers($a_obj_id);
			case 'grp':
				include_once './Modules/Group/classes/class.ilObjGroup.php';
				return ilObjGroup::_getMembers($a_obj_id);			
		}
		
		return array();
	}
	
	/**
	 * Get completed users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupCompletedForObject($a_obj_id, $a_user_ids = null)
	{
		if(!$a_user_ids)
		{
			$a_user_ids = self::getMembers($a_obj_id);
			if(!$a_user_ids)
			{
				return array();
			}
		}
		return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_COMPLETED_NUM, $a_user_ids);
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
	
	/**
	 * Get in progress users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupInProgressForObject($a_obj_id, $a_user_ids = null)
	{
		if(!$a_user_ids)
		{
			$a_user_ids = self::getMembers($a_obj_id);
			if(!$a_user_ids)
			{
				return array();
			}
		}
		return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_IN_PROGRESS_NUM, $a_user_ids);
	}	
}
?>