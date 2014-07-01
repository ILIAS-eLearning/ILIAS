<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course Pool listener. Listens to events of other components.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesParticipationStatus
 */
class ilParticipationStatusAppEventListener
{		
	/**
	* Handle an event in a listener.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	static function handleEvent($a_component, $a_event, $a_parameter)
	{		
		if($a_component == "Services/Tracking" && $a_event == "updateStatus")
		{
			$obj_id = $a_parameter["obj_id"];
			$user_id = $a_parameter["usr_id"];
			$status = $a_parameter["status"];
			
			if($obj_id && $user_id)
			{				
				if (ilObject::_lookupType($obj_id) != "crs")
				{
					return;
				}		
				
				$ref_id = array_pop(ilObject::_getAllReferences($obj_id));
				
				require_once "Services/ParticipationStatus/classes/class.ilParticipationStatus.php";
				$pstatus_obj = ilParticipationStatus::getInstanceByRefId($ref_id);
				
				if($pstatus_obj->getMode() == ilParticipationStatus::MODE_CONTINUOUS)
				{
					switch($status)
					{
						case ilLPStatus::LP_STATUS_COMPLETED_NUM:
							$pstatus = ilParticipationStatus::STATUS_SUCCESSFUL;
							break;
						
						// #35
						case ilLPStatus::LP_STATUS_FAILED_NUM:						
						case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:							
						case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
							$pstatus = ilParticipationStatus::STATUS_NOT_SET;
							break;						
					}
					
					$pstatus_obj->setStatus($user_id, $pstatus);
				}				
			}
		}
	}
}
