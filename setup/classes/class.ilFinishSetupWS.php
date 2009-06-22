<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Database selection workflow step
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesSetup
*/
class ilFinishSetupWS extends ilWorkflowStep
{
	/**
	* Constructor
	*/
	function __construct($a_setup)
	{
		$this->setup = $a_setup;
		$this->setTitle($lng->txt("db_selection"));
	}
	
	/**
	* Has run. Determine whether the step has already run
	*/
	function hasRun()
	{
		return false;
	}
	
	/**
	* Is fulfilled.
	*/
	function isFulfilled()
	{
		return false;
	}
}

?>
