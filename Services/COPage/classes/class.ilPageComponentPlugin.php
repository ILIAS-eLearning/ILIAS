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
* Abstract parent class for all page component plugin classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
abstract class ilPageComponentPlugin extends ilPlugin
{
	const TXT_CMD_INSERT = "cmd_insert";
	const CMD_INSERT = "insert";
	const CMD_EDIT = "edit";
	
	/**
	* Get Component Type
	*
	* @return        string        Component Type
	*/
	final function getComponentType()
	{
		return IL_COMP_SERVICE;
	}
	
	/**
	* Get Component Name.
	*
	* @return        string        Component Name
	*/
	final function getComponentName()
	{
		return "COPage";
	}
	
	/**
	* Get Slot Name.
	*
	* @return        string        Slot Name
	*/
	final function getSlot()
	{
		return "PageComponent";
	}
	
	/**
	* Get Slot ID.
	*
	* @return        string        Slot Id
	*/
	final function getSlotId()
	{
		return "pgcp";
	}
	
	/**
	* Object initialization done by slot.
	*/
	protected final function slotInit()
	{
		// nothing to do here
	}
	
	/**
	* Determines the resources that allow to include the
	* new content component.
	*
	* @param	string		$a_type		Parent type (e.g. "cat", "lm", "glo", "wiki", ...)
	*
	* @return	boolean		true/false if the resource type allows
	*/
	abstract function isValidParentType($a_type);
	
	/**
	* Determines the resources that allow to include the
	* new content component.
	*
	* @param	string		$a_text_id		values: TXT_CMD_INSERT
	*
	* @return	string		User Interface Text String
	*/
	abstract function getUIText($a_text_id);

	/**
	* Set Mode.
	*
	* @param	string	$a_mode	Mode
	*/
	final function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* Get Mode.
	*
	* @return	string	Mode
	*/
	final function getMode()
	{
		return $this->mode;
	}

	/**
	* Set Properties.
	*
	* @param	array	$a_properties	Properties
	*/
	final function setProperties($a_properties)
	{
		$this->properties = $a_properties;
	}

	/**
	* Get Properties.
	*
	* @return	array	Properties
	*/
	final function getProperties()
	{
		return $this->properties;
	}

	/**
	* Add save cancel button to insert/edit form
	*/
	final function addSaveCancelButtons($a_form)
	{
		global $lng;
		
		if ($this->getMode() == ilPageComponentPlugin::CMD_INSERT)
		{
			$a_form->addCommandButton("create_plug", $lng->txt("save"));
			$a_form->addCommandButton("cancelCreate", $lng->txt("cancel"));
		}
		else
		{
			$a_form->addCommandButton("update_plug", $lng->txt("save"));
			$a_form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
	}
}
?>
