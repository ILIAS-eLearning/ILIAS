<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Services/Membership/classes/class.ilWaitingList.php');

/**
 * Session waiting list
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 * @version $Id$
 * 
 * @extends ilWaitingList
 */
class ilSessionWaitingList extends ilWaitingList
{
	
	/**
	 * Add to waiting list and raise event
	 * @param int $a_usr_id
	 */
	public function addToList($a_usr_id)
	{
		global $ilAppEventHandler, $ilLog;
		
		if(!parent::addToList($a_usr_id))
		{
			return FALSE;
		}
		
		$ilLog->write(__METHOD__.': Raise new event: Modules/Session addToWaitingList');
		$ilAppEventHandler->raise(
				"Modules/Session", 
				'addToWaitingList', 
				array(
					'obj_id' => $this->obj_id,
					'usr_id' => $a_usr_id
				)
			);
		return TRUE;
	}
}
?>