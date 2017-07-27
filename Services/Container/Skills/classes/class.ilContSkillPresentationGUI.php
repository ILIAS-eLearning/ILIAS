<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill presentatio for container (course/group)
 *
 * @author Alex Killing <killing@leifos.de>
 * @ingroup ServicesContainer
 */
class ilContSkillPresentationGUI
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
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilContainerGUI
	 */
	protected $container_gui;

	/**
	 * @var ilContainer
	 */
	protected $container;

	/**
	 * @var ilObjUser
	 */
	protected $user;

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
		$this->tpl = $DIC["tpl"];
		$this->user = $DIC->user();

		$this->container_gui = $a_container_gui;
		$this->container = $a_container_gui->object;

		include_once("./Services/Container/Skills/classes/class.ilContainerSkills.php");
		$this->container_skills = new ilContainerSkills($this->container->getId());

	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;
		$tabs = $this->tabs;

		$tabs->activateSubTab("list");

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
	 * Show
	 */
	function show()
	{
		$user = $this->user;
		$tpl = $this->tpl;
		$lng = $this->lng;

		include_once("./Services/Skill/classes/class.ilPersonalSkillsGUI.php");
		$gui = new ilPersonalSkillsGUI();

		$gui->setGapAnalysisActualStatusModePerObject($this->container->getId(), $lng->txt('cont_skills'));

		$gui->setHistoryView(true); // NOT IMPLEMENTED YET

		// this is not required, we have no self evals in the test context,
		// getReachedSkillLevel is a "test evaluation"
		//$gui->setGapAnalysisSelfEvalLevels($this->getReachedSkillLevels());

		//$gui->setProfileId($this->getSelectedSkillProfile());


		$skills = array_map(function ($v) {
			return array(
				"base_skill_id" => $v["skill_id"],
				"tref_id" => $v["tref_id"]
			);
		}, $this->container_skills->getSkills());

		$html = $gui->getGapAnalysisHTML($user->getId(), $skills);


		$tpl->setContent($html);
	}


}

?>