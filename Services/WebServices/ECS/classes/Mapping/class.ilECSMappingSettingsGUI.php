<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';

/* 
 * Class for ECS node and directory mapping settings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $ID$
 *
 * @ingroup ServicesWebServicesECS
 * @ilCtrl_isCalledBy ilECSMappingSettingsGUI: ilECSSettingsGUI
 */
class ilECSMappingSettingsGUI
{
	const TAB_DIRECTORY = 1;
	const TAB_COURSE = 2;

	private $container = null;
	private $server = null;

	protected $lng = null;
	protected  $ctrl = null;

	/**
	 * Constructor
	 * @param ilObjectGUI $settingsContainer
	 */
	public function __construct($settingsContainer, $server_id)
	{
		global $lng,$ilCtrl;
		
		$this->container = $settingsContainer;
		$this->server = ilECSSetting::getInstanceByServerId($server_id);
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
	}

	/**
	 * Get container object
	 * @return ilObjectGUI
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 *
	 * @return ilECSSetting
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * ilCtrl executeCommand
	 */
	public function executeCommand()
	{
		global $ilCtrl;

		$GLOBALS['tpl']->setTitle($this->lng->txt('ecs_campus_connect_title'));

		$this->ctrl->saveParameter($this,'server_id');

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->setTabs();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "cStart";
				}
				$this->$cmd();
				break;
		}

		$GLOBALS['tpl']->setTitle($this->getServer()->getTitle());
		$GLOBALS['tpl']->setDescription('');

		return true;
	}

	/**
	 * return to parent container
	 */
	public function cancel()
	{
		$GLOBALS['ilCtrl']->returnToParent($this);
	}

	/**
	 * Goto default page
	 * @return <type>
	 */
	protected function cStart()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		if(!ilECSNodeMappingSettings::getInstance()->isEnabled())
		{
			return $this->cSettings();
		}
	}

	/**
	 * Goto default page
	 * @return <type>
	 */
	protected function dStart()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		if(!ilECSNodeMappingSettings::getInstance()->isEnabled())
		{
			return $this->dSettings();
		}
		return $this->dSettings();
	}

	/**
	 * Show course allocation
	 * @global ilTabsGUI $ilTabs
	 * @return bool
	 */
	protected function cSettings()
	{
		global $ilTabs;
		
		$ilTabs->activateTab('ecs_crs_allocation');

		$form = $this->initFormCSettings();

		$GLOBALS['tpl']->setContent($form->getHTML());

		return true;
	}

	/**
	 * Init settings form
	 */
	protected function initFormCSettings()
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('settings'));

		// add default container
		$imp = new ilCustomInputGUI($this->lng->txt('ecs_import_id'),'import_id');
		$imp->setRequired(true);

		$tpl = new ilTemplate('tpl.ecs_import_id_form.html',true,true,'Services/WebServices/ECS');
		$tpl->setVariable('SIZE',5);
		$tpl->setVariable('MAXLENGTH',11);
		$tpl->setVariable('POST_VAR','import_id');
#		$tpl->setVariable('PROPERTY_VALUE',$this->rule->getContainerId());

		#if($this->settings->getImportId())
		{
		#	$tpl->setVariable('COMPLETE_PATH',$this->buildPath($this->rule->getContainerId()));
		}

		$imp->setHTML($tpl->get());
		$imp->setInfo($this->lng->txt('ecs_import_id_info'));
		$form->addItem($imp);

		// individual course allocation
		$check = new ilCheckboxInputGUI($this->lng->txt('ecs_individual_alloc'), 'individual');
		$check->setInfo($this->lng->txt('ecs_individual_alloc'));
		$form->addItem($check);

		$form->addCommandButton('cUpdateSettings',$this->lng->txt('save'));
		$form->addCommandButton('cSettings', $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * Show directory allocation
	 * @global ilTabsGUI $ilTabs
	 */
	protected function dSettings()
	{
		global $ilTabs;

		$this->setSubTabs(self::TAB_DIRECTORY);
		$ilTabs->activateTab('ecs_dir_allocation');
		$ilTabs->activateSubTab('dSettings');

		$form = $this->initFormDSettings();

		$GLOBALS['tpl']->setContent($form->getHTML());
		return true;
	}

	/**
	 * Update node mapping settings
	 */
	protected function dUpdateSettings()
	{
		global $ilCtrl;

		$form = $this->initFormDSettings();
		if($form->checkInput())
		{
			include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
			ilECSNodeMappingSettings::getInstance()->enable((bool) $form->getInput('active'));
			ilECSNodeMappingSettings::getInstance()->enableEmptyContainerCreation(!$form->getInput('empty'));
			ilECSNodeMappingSettings::getInstance()->update();
			ilUtil::sendSuccess($this->lng->txt('saved_settings'),true);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('err_check_input'),true);
			$form->setValuesByPost();
		}
		$ilCtrl->redirect($this,'dSettings');
	}

	/**
	 *
	 */
	protected function initFormDSettings()
	{
		include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('general_settings'));

		$active = new ilCheckboxInputGUI($this->lng->txt('ecs_node_mapping_activate'), 'active');
		$active->setChecked(ilECSNodeMappingSettings::getInstance()->isEnabled());
		$form->addItem($active);

		$create_empty = new ilCheckboxInputGUI($this->lng->txt('ecs_node_mapping_create_empty'), 'empty');
		$create_empty->setChecked(!ilECSNodeMappingSettings::getInstance()->isEmptyContainerCreationEnabled());
		$create_empty->setInfo($this->lng->txt('ecs_node_mapping_create_empty_info'));
		$form->addItem($create_empty);

		$form->addCommandButton('dUpdateSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * Show directory trees
	 */
	protected function dTrees()
	{
		$this->setSubTabs(self::TAB_DIRECTORY);
		$GLOBALS['ilTabs']->activateSubTab('dTrees');
		$GLOBALS['ilTabs']->activateTab('ecs_dir_allocation');
	}

	/**
	 * Show directory trees
	 */
	protected function dMappingOverview()
	{
		$this->setSubTabs(self::TAB_DIRECTORY);
		$GLOBALS['ilTabs']->activateSubTab('dMappingOverview');
		$GLOBALS['ilTabs']->activateTab('ecs_dir_allocation');
	}

	/**
	 * Set tabs
	 * @global ilTabsGUI $ilTabs
	 */
	protected function setTabs()
	{
		global $ilTabs;

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget(
			$this->lng->txt('ecs_back_settings'),
			$this->ctrl->getParentReturn($this)
		);
		$ilTabs->addTab(
			'ecs_dir_allocation',
			$this->lng->txt('ecs_dir_alloc'),
			$this->ctrl->getLinkTarget($this,'dSettings')
		);
		$ilTabs->addTab(
			'ecs_crs_allocation',
			$this->lng->txt('ecs_crs_alloc'),
			$this->ctrl->getLinkTarget($this,'cSettings')
		);
	}

	/**
	 * Set Sub tabs
	 * @global ilTabsGUI $ilTabs
	 * @param string $a_tab 
	 */
	protected function setSubTabs($a_tab)
	{
		global $ilTabs;

		if($a_tab == self::TAB_DIRECTORY)
		{
			$ilTabs->addSubTab(
				'dMappingOverview',
				$this->lng->txt('ecs_cc_mapping_overview'),
				$this->ctrl->getLinkTarget($this,'dMappingOverview')
			);
			$ilTabs->addSubTab(
				'dTrees',
				$this->lng->txt('ecs_cms_dir_tree'),
				$this->ctrl->getLinkTarget($this,'dTrees')
			);
			$ilTabs->addSubTab(
				'dSettings',
				$this->lng->txt('settings'),
				$this->ctrl->getLinkTarget($this,'dSettings')
			);
		}
	}
}
?>
