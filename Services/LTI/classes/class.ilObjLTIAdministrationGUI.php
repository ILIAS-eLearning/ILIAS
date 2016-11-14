<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once 'Services/LTI/classes/ActiveRecord/class.ilLTIExternalConsumer.php';

/**
 * Class ilObjLTIAdministrationGUI
 * @author Jesús López <lopez@leifos.com>
 *
 * @ilCtrl_Calls      ilObjLTIAdministrationGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjLTIAdministrationGUI: ilAdministrationGUI
 *
 * @ingroup ServicesLTI
 */
class ilObjLTIAdministrationGUI extends ilObjectGUI
{

	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "ltis";
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

	}

	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if (!$cmd || $cmd == 'view')
				{
					$cmd = "initSettingsForm";
				}
				elseif ($cmd == 'createconsumer')
				{
					$cmd = "initConsumerForm";
				}
				$this->$cmd();
				break;
		}
	}

	public function getType()
	{
		return "ltis";
	}

	public function getAdminTabs()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "initSettingsForm"));

			$this->tabs_gui->addTab("consumers",
				$this->lng->txt("consumers"),
				$this->ctrl->getLinkTarget($this, "listConsumers"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"));
		}

	}

	public function initSettingsForm(ilPropertyFormGUI $form = null)
	{
		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getSettingsForm();
		}
		$this->tabs_gui->activateTab("settings");
		$this->tpl->setContent($form->getHTML());
	}


	protected function getSettingsForm()
	{
		require_once ("Services/Form/classes/class.ilPropertyFormGui.php");

		$form = new ilPropertyFormGUI();
		/*
		$form->setFormAction($this->ctrl->getFormAction($this,'saveSettingsForm'));
		$form->setTitle($this->lng->txt("lti_settings"));

		// object types
		$cb_obj_types = new ilCheckboxGroupInputGUI($this->lng->txt("act_lti_for_obj_type"), 'types');

		$valid_obj_types = $this->object->getLTIObjectTypes();
		foreach($valid_obj_types as $obj_type_id => $obj_name)
		{
			$cb_obj_types->addOption(new ilCheckboxOption($obj_name, $obj_type_id));
		}
		$objs_active = $this->object->getActiveObjectTypes();
		$cb_obj_types->setValue($objs_active);
		$form->addItem($cb_obj_types);

		// test roles
		$roles = $this->object->getLTIRoles();
		foreach($roles as $role_id => $role_name)
		{
			$options[$role_id] = $role_name;
		}
		$si_roles = new ilSelectInputGUI($this->lng->txt("gbl_roles_to_users"), 'roles');
		$si_roles->setOptions($options);
		$si_roles->setValue($this->object->getCurrentRole());
		$form->addItem($si_roles);

		$form->addCommandButton("saveSettingsForm", $this->lng->txt("save"));
		*/
		return $form;

	}

	/*
	protected function saveSettingsForm()
	{
		global $ilCtrl;

		$this->checkPermission("write");

		$form = $this->getSettingsForm();
		if($form->checkInput())
		{
			$obj_types = $form->getInput('types');

			$role = $form->getInput('role');

			$this->object->saveData($obj_types, $role);

			ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
		}

		$form->setValuesByPost();
		$this->initSettingsForm($form);
	}
	*/

	// consumers

	public function initConsumerForm(ilPropertyFormGUI $form = null)
	{
		if(!($form instanceof ilPropertyFormGUI))
		{
			$form = $this->getConsumerForm();
		}
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @param string $consumer_id
	 * @return ilPropertyFormGUI
	 */
	public function getConsumerForm($a_mode = '')
	{
		$this->tabs_gui->activateTab("consumers");

		require_once ("Services/Form/classes/class.ilPropertyFormGui.php");

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'createLTIConsumer'));
		$form->setTitle($this->lng->txt("lti_create_consumer"));

		$ti_title = new ilTextInputGUI($this->lng->txt("title"), 'title');
		$ti_description = new ilTextInputGUI($this->lng->txt("description"), 'description');
		$ti_prefix = new ilTextInputGUI($this->lng->txt("prefix"), 'prefix');
		$ti_key = new ilTextInputGUI($this->lng->txt("lti_consumer_key"), 'key');
		$ti_secret = new ilTextInputGUI($this->lng->txt("lti_consumer_secret"), 'secret');

		$languages = $this->lng->getInstalledLanguages();
		$array_lang = array();
		foreach($languages as $lang_key)
		{
			$array_lang[$lang_key] = ilLanguage::_lookupEntry($lang_key,"meta", "meta_l_".$lang_key);
		}

		$si_language = new ilSelectInputGUI($this->lng->txt("language"), "language");
		$si_language->setOptions($array_lang);
		
		$cb_active = new ilCheckboxInputGUI($this->lng->txt('active'), 'active');

		$form->addItem($ti_title);
		$form->addItem($ti_description);
		$form->addItem($ti_prefix);
		$form->addItem($ti_key);
		$form->addItem($ti_secret);
		$form->addItem($si_language);
		$form->addItem($cb_active);

		// object types
		$cb_obj_types = new ilCheckboxGroupInputGUI($this->lng->txt("act_lti_for_obj_type"), 'types');

		$valid_obj_types = $this->object->getLTIObjectTypes();
		foreach($valid_obj_types as $obj_type_id => $obj_name)
		{
			$cb_obj_types->addOption(new ilCheckboxOption($obj_name, $obj_type_id));
		}
		$form->addItem($cb_obj_types);

		// test roles
		$roles = $this->object->getLTIRoles();
		foreach($roles as $role_id => $role_name)
		{
			$options[$role_id] = $role_name;
		}
		$si_roles = new ilSelectInputGUI($this->lng->txt("gbl_roles_to_users"), 'role');
		$si_roles->setOptions($options);
		$form->addItem($si_roles);

		if($a_mode == 'edit')
		{
			$form->addCommandButton("updateLTIConsumer", $this->lng->txt("edit"));
		}
		else
		{
			$form->addCommandButton("createLTIConsumer", $this->lng->txt("save"));
		}

		return $form;
	}

	public function editConsumer(ilPropertyFormGUI $a_form = null)
	{
		global $ilCtrl, $tpl;
		$consumer_id = $_REQUEST["cid"];
		$ilCtrl->setParameter($this, "cid", $consumer_id);

		if(!$consumer_id)
		{
			$ilCtrl->redirect($this, "listConsumers");
		}
		$consumer = new ilLTIExternalConsumer($consumer_id);

		if(!$a_form)
		{
			$a_form = $this->getConsumerForm('edit');
			$a_form->getItemByPostVar("title")->setValue($consumer->getTitle());
			$a_form->getItemByPostVar("description")->setValue($consumer->getDescription());
			$a_form->getItemByPostVar("prefix")->setValue($consumer->getPrefix());
			$a_form->getItemByPostVar("key")->setValue($consumer->getKey());
			$a_form->getItemByPostVar("secret")->setValue($consumer->getSecret());
			$a_form->getItemByPostVar("language")->setValue($consumer->getLanguage());
			$a_form->getItemByPostVar("active")->setChecked($consumer->getActive());
			$a_form->getItemByPostVar("role")->setValue($consumer->getRole());
			$a_form->getItemByPostVar("types")->setValue($this->object->getActiveObjectTypes($consumer_id));
		}

		$tpl->setContent($a_form->getHTML());

	}

	public function createLTIConsumer()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		$this->checkPermission("write");

		$form = $this->getConsumerForm();
		if($form->checkInput())
		{
			$consumer = new ilLTIExternalConsumer();
			$consumer->setId($ilDB->nextId('lti_ext_consumer'));
			$consumer->setTitle($form->getInput('title'));
			$consumer->setDescription($form->getInput('description'));
			$consumer->setPrefix($form->getInput('prefix'));
			$consumer->setKey($form->getInput('key'));
			$consumer->setSecret($form->getInput('secret'));
			$consumer->setLanguage($form->getInput('language'));
			$consumer->setActive($form->getInput('active'));
			$consumer->setRole($form->getInput('role'));
			$consumer->create();

			$this->object->saveConsumerObjectTypes($consumer->getId(), $form->getInput('types'));

			ilUtil::sendSuccess($this->lng->txt("lti_consumer_created"),true);
		}

		$form->setValuesByPost();
		$this->listConsumers();

	}

	protected function updateLTIConsumer()
	{
		global $ilCtrl;

		$consumer_id = $_REQUEST["cid"];
		if (!$consumer_id)
		{
			$ilCtrl->redirect($this, "listConsumers");
		}

		$ilCtrl->setParameter($this, "cid", $consumer_id);

		$consumer = new ilLTIExternalConsumer($consumer_id);

		$form = $this->getConsumerForm($consumer_id);

		if($form->checkInput())
		{
			$consumer->setTitle($form->getInput('title'));
			$consumer->setDescription($form->getInput('description'));
			$consumer->setPrefix($form->getInput('prefix'));
			$consumer->setKey($form->getInput('key'));
			$consumer->setSecret($form->getInput('secret'));
			$consumer->setLanguage($form->getInput('language'));
			$consumer->setActive($form->getInput('active'));
			$consumer->setRole($form->getInput('role'));

			$consumer->update();

			$this->object->saveConsumerObjectTypes($consumer_id, $form->getInput('types'));

			ilUtil::sendSuccess($this->lng->txt("lti_consumer_updated"),true);
		}
		$this->listConsumers();

	}

	protected function deleteLTIConsumer()
	{
		global $ilCtrl;

		$consumer_id = $_REQUEST['cid'];

		if (!$consumer_id)
		{
			$ilCtrl->redirect($this, "listConsumers");
		}
		$consumer = new ilLTIExternalConsumer($consumer_id);

		$consumer->delete();

		ilUtil::sendSuccess($this->lng->txt("lti_consumer_deleted"),true);

		$this->listConsumers();
	}


	protected function listConsumers()
	{
		global $ilAccess, $ilToolbar;

		//$this->ctrl->setParameter($this,'new_consumer','consumer');
		$ilToolbar->addButton(
			$this->lng->txt('lti_create_consumer'),
			$this->ctrl->getLinkTarget($this,'createconsumer')
		);

		$this->tabs_gui->activateTab("consumers");

		include_once "Services/LTI/classes/Consumer/class.ilLTIConsumerTableGUI.php";
		$tbl = new ilObjectConsumerTableGUI($this, "listConsumers",
			$ilAccess->checkAccess("write", "", $this->object->getRefId()));
		$this->tpl->setContent($tbl->getHTML());

	}

}