<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	 * Get Javascript files
	 */
	function getJavascripFiles()
	{
		return array();
	}
	
	/**
	 * Get css files
	 */
	function getCssFiles()
	{
		return array();
	}
	
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
	 * Get UI plugin class
	 */
	function getUIClassInstance()
	{
		$class = "il".$this->getPluginName()."PluginGUI";
		$this->includeClass("class.".$class.".php");
		$obj = new $class();
		$obj->setPlugin($this);
		return $obj;
	}

}
?>
