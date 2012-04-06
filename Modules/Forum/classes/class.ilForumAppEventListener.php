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
			}
	}
}
?>
