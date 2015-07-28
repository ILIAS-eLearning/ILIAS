<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilObjectGUI.php';

/** 
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjLoggingSettingsGUI: ilPermissionGUI
*/
class ilObjLoggingSettingsGUI extends ilObjectGUI
{
	const SECTION_SETTINGS = 'settings';
	const SUB_SECTION_MAIN = 'log_general_settings';
	const SUB_SECTION_COMPONENTS = 'log_components';
	
	
	public $tpl;
	public $lng;
	public $ctrl;
	protected $tabs_gui;
	protected $form;
	protected $settings;
	
	

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		global $lng,$tpl,$ilCtrl,$ilTabs;
		
		$this->type = 'logs';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng = $lng;

		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tabs_gui = $ilTabs;

		$this->initSettings();
		$this->lng->loadLanguageModule('logging');
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == "" || $cmd == "view")
				{
					$cmd = "settings";
				}
				$this->$cmd();

				break;
		}
		return true;
	}
	

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		global $rbacsystem, $ilAccess;
		
 		if ($ilAccess->checkAccess("read",'',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget(
				static::SECTION_SETTINGS,
				$this->ctrl->getLinkTargetByClass('ilobjloggingsettingsgui', "settings")
			);
		}
		if ($ilAccess->checkAccess('edit_permission','',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget(
				"perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm")
			);
		}
	}
	
	public function setSubTabs($a_section)
	{
		$this->tabs_gui->addSubTab(
				static::SUB_SECTION_MAIN,
				$this->lng->txt(static::SUB_SECTION_MAIN),
				$this->ctrl->getLinkTarget($this,'settings')
		);
		$this->tabs_gui->addSubTab(
				static::SUB_SECTION_COMPONENTS,
				$this->lng->txt(static::SUB_SECTION_COMPONENTS),
				$this->ctrl->getLinkTarget($this,'components')
		);
		
		$this->tabs_gui->activateSubTab($a_section);
	}

	protected function initSettings()
	{
		include_once("Services/Logging/classes/class.ilLoggingSettings.php");
		$this->settings = ilLoggingSettings::getInstance();
	}

	/**
	 * Show settings
	 * @access	public
	 */
	public function settings()
	{
		global $ilAccess,$ilErr;

		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		
		$this->tabs_gui->setTabActive(static::SECTION_SETTINGS);
		$this->setSubTabs(static::SUB_SECTION_MAIN);
		
		$this->initFormSettings();
		$this->tpl->setContent($this->form->getHTML());
		return true;
	}

	/**
	 * Save settings
	 * @access	public
	 */
	public function updateSettingsObject()
	{
		include_once 'Services/WebServices/RPC/classes/class.ilRPCServerSettings.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$this->settings->set((int) $_POST['']);

		$this->settings->update();

		ilUtil::sendInfo($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'settings');

		return true;
	}

	/**
	 * Init settings form
	 * 
	 */
	protected function initFormSettings()
	{
		global $lng,$ilDB;

		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		include_once './Services/Search/classes/class.ilSearchSettings.php';

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'updateSettings'));
		$this->form->addCommandButton('updateSettings',$this->lng->txt('save'));
		$this->form->setTitle($this->lng->txt('logs_settings'));
		
	}
	
	
	/**
	 * Show components
	 */
	protected function components()
	{
		$this->tabs_gui->activateTab(static::SECTION_SETTINGS);
		$this->setSubTabs(static::SUB_SECTION_COMPONENTS);
		
		include_once './Services/Logging/classes/class.ilLogComponentTableGUI.php';
		$table = new ilLogComponentTableGUI($this, 'components');
		$table->init();
		$table->parse();
		
		$GLOBALS['tpl']->setContent($table->getHTML());
		
	}

}
?>