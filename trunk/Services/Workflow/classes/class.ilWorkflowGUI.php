<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Workflow user interface class. Generates workflow presentation
* as a sequence of steps.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesWorkflow
*/
class ilWorkflowGUI
{
	/**
	* Constructor
	*/
	function __construct($a_workflow)
	{
		$this->workflow = $a_workflow;
	}

	/**
	* Set workflow
	*
	* @param	object	workflow
	*/
	function setWorkflow($a_val)
	{
		$this->workflow = $a_val;
	}
	
	/**
	* Get workflow
	*
	* @return	object	workflow
	*/
	function getWorkflow()
	{
		return $this->workflow;
	}

	/**
	* Get HTML for workflow
	*/
	function getHTML()
	{
		$wf_tpl = new ilTemplate("tpl.workflow.html", true, true, "Services/Workflow");
		$nr = 1;
		foreach ($this->getWorkflow()->getSteps() as $ws_step)
		{
			$wf_tpl->setCurrentBlock("ws_step");
			$wf_tpl->setVariable("NR", $nr.". ");
			$wf_tpl->setVariable("STEPTITLE", $ws_step->getTitle());
			$wf_tpl->parseCurrentBlock();
			$nr++;
		}
		return $wf_tpl->getHTML();
	}
}

?>