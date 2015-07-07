<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Object/classes/class.ilObjectGUI.php';
include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
include_once './Services/SystemCheck/classes/class.ilSystemCheckTrash.php';


/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version           $Id$
 * @ilCtrl_Calls      ilObjSystemCheckGUI: ilPermissionGUI, ilObjectOwnershipManagmentGUI, ilObjSystemFolderGUI
 * @ilCtrl_isCalledBy ilObjSystemCheckGUI: ilAdministrationGUI
 */
class ilObjSystemCheckGUI extends ilObjectGUI
{
	const SECTION_MAIN = 'main';
	
	/**
	 * @var ilLanguage
	 */
	public $lng;

	/**
	 * @var ilCtrl
	 */
	public $ctrl;

	/**
	 * @param      $a_data
	 * @param      $a_id
	 * @param      $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = true)
	{
		$this->type = 'sysc';
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->lng->loadLanguageModule('sysc');
	}

	/**
	 * Get language obj
	 * @return ilLanguage
	 */
	public function getLang()
	{
		return $this->lng;
	}
	
	/**
	 * ilCtrl execute command
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case "ilobjectownershipmanagementgui":
				$this->setSubTabs(self::SECTION_MAIN,'no_owner');
				include_once 'Services/Object/classes/class.ilObjectOwnershipManagementGUI.php';
				$gui = new ilObjectOwnershipManagementGUI(0);
				$this->ctrl->forwardCommand($gui);
				break;
			
			case 'ilobjsystemfoldergui':
				include_once './Modules/SystemFolder/classes/class.ilObjSystemFolderGUI.php';
				$sys_folder = new ilObjSystemFolderGUI('',SYSTEM_FOLDER_ID,TRUE);
				$this->ctrl->forwardCommand($sys_folder);
				
				$GLOBALS['ilTabs']->clearTargets();
				
				$this->setSubTabs(self::SECTION_MAIN,'sc');
				break;
			
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == '' || $cmd == 'view')
				{
					$cmd = 'overview';
				}
				$this->$cmd();
				break;
		}
	}

	/**
	 * Get administration tabs
	 * @param ilTabsGUI $tabs_gui
	 */
	public function getAdminTabs(ilTabsGUI $tabs_gui)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		if($rbacsystem->checkAccess('read', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('overview', $this->ctrl->getLinkTarget($this, 'overview'));
		}
		if($rbacsystem->checkAccess('edit_permission', $this->object->getRefId()))
		{
			$tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'), array('perm', 'info', 'owner'), 'ilpermissiongui');
		}
	}

	/**
	 * Show overview table
	 */
	protected function overview()
	{
		$this->setSubTabs(self::SECTION_MAIN, 'sc');
		
		
		include_once 'Services/SystemCheck/classes/class.ilSCGroupTableGUI.php';
		
		$table = new ilSCGroupTableGUI($this,'overview');
		$table->init();
		$table->parse();
		
		$GLOBALS['tpl']->setContent($table->getHTML());
		return true;
	}

	/**
	 * Show trash form
	 * @param ilPropertyFormGUI $form
	 */
	protected function trash(ilPropertyFormGUI $form = null)
	{
		$this->setSubTabs(self::SECTION_MAIN,'trash');
		if(!$form instanceof ilPropertyFormGUI)
		{
			$form = $this->initFormTrash();
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}
	
	/**
	 * Show trash restore form
	 * @return ilPropertyFormGUI
	 */
	protected function initFormTrash()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$form->setTitle($this->lng->txt('sysc_administrate_deleted'));
		
		$action = new ilRadioGroupInputGUI($this->lng->txt('sysc_trash_action'), 'type');
		
		// Restore
		$restore = new ilRadioOption($this->lng->txt('sysc_trash_restore'),  ilSystemCheckTrash::MODE_TRASH_RESTORE);
		$restore->setInfo($this->lng->txt('sysc_trash_restore_info'));
		$action->addOption($restore);
		
		// Remove
		$remove = new ilRadioOption($this->lng->txt('sysc_trash_remove'), ilSystemCheckTrash::MODE_TRASH_REMOVE);
		$remove->setInfo($this->lng->txt('sysc_trash_remove_info'));
		$action->addOption($remove);
		
		// limit number 
		$num = new ilNumberInputGUI($this->lng->txt('sysc_trash_limit_num'), 'number');
		$num->setInfo($this->lng->txt('purge_count_limit_desc'));
		$num->setSize(10);
		$num->setMinValue(1);
		$remove->addSubItem($num);
		
		$age = new ilDateTimeInputGUI($this->lng->txt('sysc_trash_limit_age'), 'age');
		$age->setInfo($this->lng->txt('purge_age_limit_desc'));
		$age->setMinuteStepSize(15);
		$age->setMode(ilDateTimeInputGUI::MODE_INPUT);
		#$earlier = new ilDateTime(time(),IL_CAL_UNIX);
		#$earlier->increment(IL_CAL_MONTH,-6);
		#$age->setDate($earlier);
		$remove->addSubItem($age);
		
		// limit types
		$types = new ilSelectInputGUI($this->lng->txt('sysc_trash_limit_type'), 'types');
		/*
		 * @var ilObjDefinition
		 */
		$sub_objects = $GLOBALS['objDefinition']->getAllRepositoryTypes();
		$options = array();
		$options[0] = '';
		foreach($sub_objects as $obj_type)
		{
			if(!$GLOBALS['objDefinition']->isRBACObject($obj_type) or !$GLOBALS['objDefinition']->isAllowedInRepository($obj_type))
			{
				continue;
			}
			$options[$obj_type] = $this->lng->txt('obj_'.$obj_type);
		}
		
		sort($options);
		
		$types->setOptions($options);
		$remove->addSubItem($types);
		
		$form->addItem($action);
		
		
		$form->addCommandButton('handleTrashAction', $this->lng->txt('start_scan'));
		$form->addCommandButton('',$this->lng->txt('cancel'));
		
		return $form;
	}
	
	/**
	 * Handle Trash action
	 */
	protected function handleTrashAction()
	{
		$form = $this->initFormTrash();
		if($form->checkInput())
		{
			$trash = new ilSystemCheckTrash();
			
			$dt_arr = $form->getInput('age');
			if($dt_arr['date'])
			{
				$trash->setAgeLimit(new ilDate($dt_arr['date'],IL_CAL_DATETIME));
			}
			$trash->setNumberLimit($form->getInput('number'));
			$trash->setTypesLimit((array) $form->getInput('types'));
			$trash->setMode($form->getInput('type'));
			$trash->start();
			
			$this->ctrl->redirect($this,'trash');
		}
		
	}

		
	/**
	 * Set subtabs
	 * @param type $a_section
	 */
	protected function setSubTabs($a_section, $a_active)
	{
		switch($a_section)
		{
			case self::SECTION_MAIN:
				$GLOBALS['ilTabs']->addSubTab(
						'sc',
						$this->getLang()->txt('obj_sysc'),
						$this->ctrl->getLinkTargetByClass('ilobjsystemfoldergui','check')
				);
//				$GLOBALS['ilTabs']->addSubTab(
//						'no_owner',
//						$this->getLang()->txt('system_check_no_owner'),
//						$this->ctrl->getLinkTargetByClass('ilObjectOwnershipManagementGUI')
//				);
				$GLOBALS['ilTabs']->addSubTab(
						'trash',
						$this->getLang()->txt('sysc_tab_trash'),
						$this->ctrl->getLinkTarget($this,'trash')
				);
				break;
		}
		$GLOBALS['ilTabs']->activateSubTab($a_active);
	}

}
?>