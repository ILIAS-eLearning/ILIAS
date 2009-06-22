<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Workflow. A Workflow manages a series of steps that are needed to
* fulfill a certain task. You may overwrite this class, e.g. to change
* the default behaviour that determines the next step.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup 
*/
class ilWorkflow
{
	var $steps = array();
	
	/**
	* Constructor
	*/
	function __construct()
	{
	}
	
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
	* Add a workflow step
	* 
	* @param	object		workflow step
	*/
	function addStep($a_step)
	{
		$this->steps[] = $a_step;
	}
	
	/**
	* Get workflow steps
	*/
	function getSteps()
	{
		return $this->steps;
	}
	
	/**
	* Determine next step. The standard behaviour looks for the first step
	* that either has not run yet or is not fulfilled.
	*/
	function determineNextStep()
	{
		foreach ($this->steps as $step)
		{
			if (!$step->hasRun())
			{
				return $step;
			}
			
			if (!$step->isFulfilled())
			{
				return $step;
			}
		}
		return null;
	}
}

?>