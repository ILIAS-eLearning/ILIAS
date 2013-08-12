<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Component/classes/class.ilPlugin.php';

/**
 * Abstract parent class for all event hook plugin classes.
 * @author  Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
abstract class ilTestSignaturePlugin extends ilPlugin
{
	/**
	 * Get Component Type
	 * @return        string        Component Type
	 */
	final public function getComponentType()
	{
		return IL_COMP_MODULE;
	}

	/**
	 * Get Component Name.
	 * @return        string        Component Name
	 */
	final public function getComponentName()
	{
		return "Test";
	}

	/**
	 * Get Slot Name.
	 * @return        string        Slot Name
	 */
	final public function getSlot()
	{
		return "Signature";
	}

	/**
	 * Get Slot ID.
	 * @return        string        Slot Id
	 */
	final public function getSlotId()
	{
		return "tsig";
	}

	/**
	 * Object initialization done by slot.
	 */
	final protected function slotInit()
	{
	}

	/**
	 * Passes the control to the plugin.
	 *
	 * @param string|null $cmd Optional command for the plugin
	 *
	 * @return void
	 */
	abstract function invoke($cmd = null);
	
	
}