<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
 * User profile info settings UI class
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilUserProfileInfoSettingsGUI:
 *
 * @ingroup ServicesUser
 */
class ilUserProfileInfoSettingsGUI
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
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC["tpl"];
		$this->lng = $DIC->language();
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
	 * Show settings
	 */
	function show()
	{
		$tpl = $this->tpl;

		$form = $this->initForm();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init  form.
	 * @return ilPropertyFormGUI
	 */
	public function initForm()
	{
		$lng = $this->lng;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		// info text
		include_once("Services/Form/classes/class.ilTextAreaMultiLangInputGUI.php");
		$ti = new ilTextAreaMultiLangInputGUI($this->lng->txt("user_profile_info_text"), "user_profile_info_text");
		$ti->setInfo($this->lng->txt("user_profile_info_text_info"));
		$form->addItem($ti);

		$form->addCommandButton("save", $lng->txt("save"));

		$form->setTitle($lng->txt("user_profile_info_settings"));
		$form->setFormAction($this->ctrl->getFormAction($this));

		return $form;
	}

}