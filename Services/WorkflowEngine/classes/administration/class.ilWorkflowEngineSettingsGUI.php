<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilWorkflowEngineSettingsGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineSettingsGUI
{
	/** @var  ilObjWorkflowEngineGUI */
	protected $parent_gui;

	/**
	 * ilWorkflowEngineSettingsGUI constructor.
	 *
	 * @param \ilObjWorkflowEngineGUI $parent_gui
	 */
	public function __construct(ilObjWorkflowEngineGUI $parent_gui)
	{
		$this->parent_gui = $parent_gui;
	}

	/**
	 * @param string $command
	 *
	 * @return string
	 */
	public function handle($command)
	{
		return "";
	}
}