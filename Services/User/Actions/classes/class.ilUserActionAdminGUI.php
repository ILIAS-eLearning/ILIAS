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
				if (in_array($cmd, array("show", "save")))
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
		$tab = new ilUserActionAdminTableGUI($this, "show", $this->getActions());
		$this->tpl->setContent($tab->getHTML());
	}

	/**
	 * Save !!!! note in the future this must depend on the context, currently we only have one
	 */
	function save()
	{
		//var_dump($_POST); exit;
		include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");
		foreach ($this->getActions() as $a)
		{
			ilUserActionAdmin::activateAction("awrn", "toplist", $a["action_comp_id"], $a["action_type_id"],
				(int)$_POST["active"][$a["action_comp_id"] . ":" . $a["action_type_id"]]);
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "show");
	}

	/**
	 * Get actions, !!!! note in the future this must depend on the context, currently we only have one
	 *
	 * @param
	 * @return
	 */
	function getActions()
	{
		include_once("./Services/User/Actions/classes/class.ilUserActionProviderFactory.php");
		include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");
		$data = array();
		foreach(ilUserActionProviderFactory::getAllProviders() as $p)
		{
			foreach ($p->getActionTypes() as $id => $name)
			{
				$data[] = array(
					"action_comp_id" => $p->getComponentId(),
					"action_type_id" => $id,
					"action_type_name" => $name,
					"active" => ilUserActionAdmin::lookupActive("awrn", "toplist", $p->getComponentId(), $id)
				);
			}
		}
		//var_dump($data); exit;
		return $data;
	}


}

?>