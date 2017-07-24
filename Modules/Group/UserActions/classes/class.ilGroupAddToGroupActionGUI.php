<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Add user to group from awareness tool
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ModulesGroup
 */
class ilGroupAddToGroupActionGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * Constructor
	 *
	 * @param
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->ui = $DIC->ui();
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;

		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("show");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("show")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Show
	 */
	function show()
	{
		$this->tpl->setContent("Hello World");
		$content = $this->ui->factory()->legacy("Content");
		$modal = $this->ui->factory()->modal()->roundtrip(
			"Hello World", $content)->withOnLoadCode(function($id) {
			return "alert('Component has id: $id');";
		});
		echo $this->ui->renderer()->renderAsync($modal);
		exit;

	}


}

?>