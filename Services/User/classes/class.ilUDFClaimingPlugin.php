<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
 * Abstract parent class for all udf claiming plugin classes.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
abstract class ilUDFClaimingPlugin extends ilPlugin
{	
	//
	// plugin slot
	// 
	
	final function getComponentType()
	{		
		return IL_COMP_SERVICE;
	}

	final function getComponentName()
	{
		return "User";
	}

	final function getSlot()
	{
		return "UDFClaiming";		
	}

	final function getSlotId()
	{
		return "udfc";
	}
	
	protected final function slotInit()
	{
		require_once "Services/User/classes/class.ilUDFPermissionHelper.php";
	}	
	
	
	//
	// permission
	// 
	
	/**
	 * Check permission
	 * 
	 * @param int $a_user_id
	 * @param int $a_context_type
	 * @param int $a_context_id
	 * @param int $a_action_id
	 * @param int $a_action_sub_id
	 * @return bool	 
	 */
	abstract public function checkPermission($a_user_id, $a_context_type, $a_context_id, $a_action_id, $a_action_sub_id);
	
	
	//
	// db update helper
	// 
	
	
	// :TODO:
}

?>