<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skills for container (course/group) (top gui class)
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesContainer
 * @ilCtrl_Calls ilContainerSkillGUI: ilContSkillPresentationGUI, ilContSkillAdminGUI
 */
class ilContainerSkillGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilContainerGUI
	 */
	protected $container_gui;


	/**
	 * @var ilContainer
	 */
	protected $container;

	/**
	 * Constructor
	 *
	 * @param
	 */
	function __construct($a_container_gui)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tabs = $DIC->tabs();
		$this->lng = $DIC->language();

		$this->container_gui = $a_container_gui;
		$this->container = $a_container_gui->object;
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("show");

		$this->addTabs();

		switch ($next_class)
		{
			case "ilcontskillpresentationgui":
				include_once("./Services/Container/Skills/classes/class.ilContSkillPresentationGUI.php");
				$gui = new ilContSkillPresentationGUI($this->container_gui);
				$ctrl->forwardCommand($gui);
				break;

			case "ilcontskilladmingui":
				include_once("./Services/Container/Skills/classes/class.ilContSkillAdminGUI.php");
				$gui = new ilContSkillAdminGUI($this->container_gui);
				$ctrl->forwardCommand($gui);
				break;

			default:
				if (in_array($cmd, array("show")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Add tabs
	 *
	 * @param
	 */
	function addTabs()
	{
		$tabs = $this->tabs;
		$lng = $this->lng;
		$ctrl = $this->ctrl;

		$tabs->addSubTab("list",
			$lng->txt("cont_skill_show"),
			$ctrl->getLinkTargetByClass("ilContSkillPresentationGUI", ""));

		$tabs->addSubTab("members",
			$lng->txt("cont_skill_members"),
			$ctrl->getLinkTargetByClass("ilContSkillAdminGUI", "listMembers"));

		$tabs->addSubTab("competences",
			$lng->txt("cont_skill_assigned_comp"),
			$ctrl->getLinkTargetByClass("ilContSkillAdminGUI", "listCompetences"));

		$tabs->addSubTab("settings",
			$lng->txt("settings"),
			$ctrl->getLinkTargetByClass("ilContSkillAdminGUI", "settings"));
	}

}

?>