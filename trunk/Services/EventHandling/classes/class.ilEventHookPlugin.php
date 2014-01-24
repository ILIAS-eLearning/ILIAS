<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
* Abstract parent class for all event hook plugin classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesEventHandling
*/
abstract class ilEventHookPlugin extends ilPlugin
{
	/**
	* Get Component Type
	*
	* @return	string	Component Type
	*/
	final function getComponentType()
	{
		return IL_COMP_SERVICE;
	}
	
	/**
	* Get Component Name.
	*
	* @return	string	Component Name
	*/
	final function getComponentName()
	{
		return "EventHandling";
	}

	/**
	* Get Slot Name.
	*
	* @return	string	Slot Name
	*/
	final function getSlot()
	{
		return "EventHook";
	}

	/**
	* Get Slot ID.
	*
	* @return	string	Slot Id
	*/
	final function getSlotId()
	{
		return "evhk";
	}

	/**
	* Object initialization done by slot.
	*/
	protected final function slotInit()
	{
		// nothing to do here
	}

	/**
	 * Handle the event
	 *
	 * @param	string		component, e.g. "Services/User"
	 * @param	event		event, e.g. "afterUpdate"
	 * @param	array		array of event specific parameters
	 */
	abstract function handleEvent($a_component, $a_event, $a_parameter);

}
?>
