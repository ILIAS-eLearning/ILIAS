<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Forum listener. Listens to events of other components.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesForum
*/
class ilForumAppEventListener
{
	protected static $ref_ids = array();
	
	/**
	* Handle an event in a listener.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	static function handleEvent($a_component, $a_event, $a_parameter)
	{
		switch($a_component)
		{
			case "Services/News":
				switch ($a_event)
				{
					case "readNews":
						// here we could set postings to read, if news is
						// read (has to be implemented)
						break;
				}
				break;

			case "Services/Tree":
				switch ($a_event)
				{
					case "moveTree":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';
						ilForumNotification::_clearForcedForumNotifications($a_parameter);
						break;
				}
				break;
			
			case "Modules/Course":
				switch($a_event)
				{
					case "addParticipant":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';
						
						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
							break;
						}
						
						break;
					case 'deleteParticipant':
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
							break;
						}
						break;
				}
				break;
			case "Modules/Group":
				switch($a_event)
				{
					case "addParticipant":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
							break;
						}

						break;
					case 'deleteParticipant':
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
							break;
						}
						break;
				}
				break;
		}
	}

	/**
	 * @param int $obj_id
	 */
	private function getCachedReferences($obj_id)
	{
		if(!array_key_exists($obj_id, self::$ref_ids))
		{
			self::$ref_ids[$obj_id] = ilObject::_getAllReferences($obj_id);	
		}
		return self::$ref_ids[$obj_id];
	}
}
?>
