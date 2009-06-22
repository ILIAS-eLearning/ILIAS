<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Workflow step. One step in a series of steps of a workflow.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesWorkflow
*/
abstract class ilWorkflowStep
{
	/**
	* Set title
	*
	* @param	string	title
	*/
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	* Get title
	*
	* @return	string	title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Has run. Determine whether the step has already run
	*/
	abstract function hasRun();
	
	/**
	* Is fulfilled. Check whether all conditions are met/data is available needed
	* be the step. Please note that this can already be the case before the step
	* has run. In this case it is presented (by the standard workflow behaviour)
	* to the user, to confirm.
	*/
	abstract function isFulfilled();
}

?>
