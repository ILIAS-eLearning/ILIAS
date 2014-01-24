<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Component/classes/class.ilPlugin.php';

/**
 * Authentication plugin
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilAuthPlugin extends ilPlugin
{


	/**
	 * Get component name
	 */
	public function getComponentName()
	{
		return 'Authentication';
	}

	/**
	 * Get service
	 */
	public function getComponentType()
	{
		return IL_COMP_SERVICE;
	}


	/**
	 * Get slot
	 */
	public function getSlot()
	{
		return 'AuthenticationHook';
	}

	/**
	 * Get slot id
	 */
	public function getSlotId()
	{
		return 'authhk';
	}
}
?>
