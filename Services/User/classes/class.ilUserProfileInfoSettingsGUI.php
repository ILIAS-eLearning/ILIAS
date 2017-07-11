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
				if (in_array($cmd, array("show", "save")))
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
		$ctrl = $this->ctrl;

		$lng->loadLanguageModule("meta");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		$setting = new ilSetting("user");

		$first = true;
		foreach ($lng->getInstalledLanguages() as $l)
		{
			// info text
			include_once("Services/Form/classes/class.ilTextAreaInputGUI.php");
			$ti = new ilTextAreaInputGUI($lng->txt("meta_l_".$l), "user_profile_info_text_".$l);
			$ti->setRows(7);
			if ($first)
			{
				$ti->setInfo($lng->txt("user_profile_info_text_info"));
			}
			$first = false;
			$ti->setValue($setting->get("user_profile_info_".$l));
			$form->addItem($ti);
		}

		$form->addCommandButton("save", $lng->txt("save"));

		$form->setTitle($lng->txt("user_profile_info"));
		$form->setFormAction($ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save
	 */
	public function save()
	{
		$lng = $this->lng;
		$ctrl = $this->ctrl;
		$tpl = $this->tpl;

		$form = $this->initForm();
		if ($form->checkInput())
		{
			$setting = new ilSetting("user");
			foreach ($lng->getInstalledLanguages() as $l)
			{
				$setting->set("user_profile_info_".$l, $form->getInput("user_profile_info_text_".$l));
			}

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ctrl->redirect($this, "show");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}

}