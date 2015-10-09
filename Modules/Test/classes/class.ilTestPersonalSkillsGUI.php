<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Skill/classes/class.ilPersonalSkillsGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestPersonalSkillsGUI
{
	/**
	 * @var ilLanguage
	 */
	private $lng;

	/**
	 * @var ilObjTest
	 */
	private $testOBJ;

	private $availableSkills;

	private $selectedSkillProfile;

	private $reachedSkillLevels;

	private $usrId;

	public function __construct(ilLanguage $lng, ilObjTest $testOBJ)
	{
		$this->lng = $lng;
		$this->testOBJ = $testOBJ;
	}

	public function getHTML()
	{
		$gui = new ilPersonalSkillsGUI();

		$gui->setGapAnalysisActualStatusModePerObject($this->testOBJ->getId(), $this->lng->txt('tst_test_result'));

		$gui->setProfileId($this->getSelectedSkillProfile());

		$html = $gui->getGapAnalysisHTML($this->getUsrId(), $this->getAvailableSkills());

		return $html;
	}

	public function setAvailableSkills($availableSkills)
	{
		$this->availableSkills = $availableSkills;
	}

	public function getAvailableSkills()
	{
		return $this->availableSkills;
	}

	public function setSelectedSkillProfile($selectedSkillProfile)
	{
		$this->selectedSkillProfile = $selectedSkillProfile;
	}

	public function getSelectedSkillProfile()
	{
		return $this->selectedSkillProfile;
	}

	public function setReachedSkillLevels($reachedSkillLevels)
	{
		$this->reachedSkillLevels = $reachedSkillLevels;
	}

	public function getReachedSkillLevels()
	{
		return $this->reachedSkillLevels;
	}

	public function setUsrId($usrId)
	{
		$this->usrId = $usrId;
	}

	public function getUsrId()
	{
		return $this->usrId;
	}

} 