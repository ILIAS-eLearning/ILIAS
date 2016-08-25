<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User action administration GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionAdminGUI
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
	 * @var ilLanguage
	 */
	protected $lng;


	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];

		$this->lng->loadLanguageModule("usr");

	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("show");

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
	 *
	 * @param
	 * @return
	 */
	function show()
	{
		ilUtil::sendInfo($this->lng->txt("user_actions_activation_info"));

		include_once("./Services/User/Actions/classes/class.ilUserActionAdminTableGUI.php");
		$tab = new ilUserActionAdminTableGUI($this, "show", "awrn", "toplist");
		$this->tpl->setContent($tab->getHTML());
	}


}

?>