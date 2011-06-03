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
 * @ilCtrl_isCalledBy: ilECSSettingsGUI
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
	public function __construct(ilObjectGUI $settingsContainer, $server_id)
	{
		global $lng;
		
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
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		//$this->setSubTabs();

		$this->setTabs();

		switch($next_class)
		{
			default:
				if(!$cmd)
				{
					$cmd = "cSettings";
				}
				$this->$cmd();
				break;
		}
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
		#$ilTabs->setBackTarget($a_title, $a_target)
	}
}
?>
