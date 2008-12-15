<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once('./Services/EventHandling/interfaces/interface.ilAppEventListener.php');

/** 
* Handles delete events from courses and categories.
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup 
*/
class ilContainerReferenceAppEventListener  implements ilAppEventListener
{
	/**
	 * Handle events like create, update, delete
	 *
	 * @access public
	 * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	 * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	 * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)	 * 
	 * @static
	 */
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{
		global $ilLog;
		
		switch($a_component)
		{
			case 'Modules/Course':
			case 'Modules/Category':
			
				switch($a_event)
				{
					case 'delete':
						$ilLog->write(__METHOD__.': Handling delete event.');
						self::deleteReferences($a_parameter['obj_id']);
						break;
				}
				break;
		}
	}
	
	/**
	 * Delete references 
	 *
	 * @static
	 */
	 public static function deleteReferences($a_target_id)
	 {
	 	global $ilLog;
	 	
	 	include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
	 	if(!$source_id = ilContainerReference::_lookupSourceId($a_target_id))
	 	{
	 		return true;
	 	}
	 	foreach(ilObject::_getAllReferences($source_id) as $ref_id)
	 	{
	 		if(!$instance = ilObjectFactory::getInstanceByRefId($ref_id,false))
	 		{
	 			continue;
	 		}
	 		switch($instance->getType())
	 		{
	 			case 'crsr':
	 			case 'catr':
	 				$instance->delete();
	 				$ilLog->write(__METHOD__.': Deleted reference object of type '.$instance->getType().' with Id '.$instance->getId());
	 				break;
	 				
	 			default:
	 				$ilLog->write(__METHOD__.': Unexpected object type '.$instance->getType().' with Id '.$instance->getId());
	 				break;
	 		}
	 		
	 	}
	 	return true;
	 }
}
?>
