<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Component/classes/class.ilPlugin.php");
 
/**
 * Abstract parent class for all cron hook plugin classes.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesCron
 */
abstract class ilCronHookPlugin extends ilPlugin
{
	final function getComponentType()
	{		
		return IL_COMP_SERVICE;
	}

	final function getComponentName()
	{
		return "Cron";
	}

	final function getSlot()
	{
		return "CronHook";		
	}

	final function getSlotId()
	{
		return "crnhk";
	}
	
	protected final function slotInit()
	{
		// nothing to do here
	}	
	
	abstract public function getCronJobInstances();		
	
	abstract public function getCronJobInstance($a_job_id);
}

?>