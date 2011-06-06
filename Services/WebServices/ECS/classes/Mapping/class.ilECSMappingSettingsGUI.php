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

		$this->ctrl->saveParameter($this,'server_id');

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->setTabs();
		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "dSettings";
				}
				$this->$cmd();
				break;
		}

		$GLOBALS['tpl']->setTitle($this->getServer()->getTitle());
		$GLOBALS['tpl']->setDescription('');

		return true;
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

		$ilTabs->activateTab('ecs_dir_allocation');

		return true;
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
}
?>
