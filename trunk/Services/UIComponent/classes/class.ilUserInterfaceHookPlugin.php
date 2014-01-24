<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");

/**
 * User interface hook plugin
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
abstract class ilUserInterfaceHookPlugin extends ilPlugin
{
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
		return "UIComponent";
	}

	/**
	 * Get Slot Name.
	 *
	 * @return        string        Slot Name
	 */
	final function getSlot()
	{
		return "UserInterfaceHook";
	}

	/**
	 * Get Slot ID.
	 *
	 * @return        string        Slot Id
	 */
	final function getSlotId()
	{
		return "uihk";
	}

	/**
	 * Object initialization done by slot.
	 */
	protected final function slotInit()
	{
		// nothing to do here
	}

	/**
	 * Get UI plugin class
	 */
	function getUIClassInstance()
	{
		$class = "il".$this->getPluginName()."UIHookGUI";
		$this->includeClass("class.".$class.".php");
		$obj = new $class();
		$obj->setPluginObject($this);
		return $obj;
	}
}
?>