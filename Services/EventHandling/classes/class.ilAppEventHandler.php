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

/**
* Global event handler
*
* The event handler delegates application events (not gui events)
* between components that trigger events and components that listen to events.
* A component is a module or a service.
*
* The component that triggers an event calls the raise function of the event
* handler through the global instance ilAppEventHandler:
*
* E.g. in ilObjUser->delete():
* $ilAppEventHandler->raise("Services/User", "deleteUser", array("id" => ..., ...))
*
* A listener has to subscribe to the events of another component. This currently
* is done here in the constructor, e.g. if the News service listens to the User
* service, add a
* $this->listener["Services/User"] = array("Services/News");
* This information will go to xml files in the future.
* 
* A component has to implement a listener class that implements
* Services/EventHandling/interfaces/interface.ilAppEventListener.php
*
* The location must be <component>/classes/class.il<comp_name>AppEventListener.php,
* e.g. ./Services/News/classes/class.ilNewsAppEventListener.php
*
* The class name must be il<comp_name>AppEventListener.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup EventHandling
*/
class ilAppEventHandler
{
	/**
	* Constructor
	*/
	function __construct()
	{
		// this information should be determined by service/module
		// xml files in the future
		$this->listener["Services/News"] = array("Modules/Forum");
	}
	
	
	/**
	* Raise an event. The event is passed to all interested listeners.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	function raise($a_component, $a_event, $a_parameter = "")
	{
		if (is_array($this->listener[$a_component]))
		{
			foreach ($this->listener[$a_component] as $listener)
			{
				// detemine class and file
				$comp = explode("/", $listener);
				$class = "il".$comp[1]."AppEventListener";
				$file = "./".$listener."/classes/class.".$class.".php";

				// if file exists, call listener
				if (is_file($file))
				{
					include_once($file);
					call_user_func(array($class, 'handleEvent'), $a_component, $a_event, $a_parameter);
				}
			}
		}
	}
}
?>
