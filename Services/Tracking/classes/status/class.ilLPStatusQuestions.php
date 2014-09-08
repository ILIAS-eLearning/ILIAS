<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @version $Id: class.ilLPStatusCollectionManual.php 40252 2013-03-01 12:21:49Z jluetzen $
 *
 * @package ilias-tracking 
 */
class ilLPStatusQuestions extends ilLPStatus
{
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
		$usr_ids = array();
		
		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
		
		include_once "Modules/LearningModule/classes/class.ilLMTracker.php";
		foreach($users as $user_id)
		{
			// :TODO: this ought to be optimized
			$tracker = ilLMTracker::getInstanceByObjId($a_obj_id, $user_id);
			if($tracker->getAllQuestionsCorrect())
			{		
				$usr_ids[] = $user_id;
			}
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
		$status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
		
		include_once "Services/Tracking/classes/class.ilChangeEvent.php";
		if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id))
		{
			$status = self::LP_STATUS_IN_PROGRESS_NUM;
			
			include_once "Modules/LearningModule/classes/class.ilLMTracker.php";
			$tracker = ilLMTracker::getInstanceByObjId($a_obj_id, $a_user_id);
			if($tracker->getAllQuestionsCorrect())
			{
				$status = self::LP_STATUS_COMPLETED_NUM;
			}
		}
	
		return $status;		
	}
}	
