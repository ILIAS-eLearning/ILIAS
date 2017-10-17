<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
require_once 'Services/Form/classes/class.ilSelectInputGUI.php';

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillEvaluationToolbarGUI extends ilToolbarGUI
{
	const SKILL_PROFILE_PARAM = 'skill_profile';

	/**
	 * @var ilCtrl
	 */
	private $ctrl;

	private $parentGUI;
	private $parentCMD;

	private $availableSkillProfiles;

	private $noSkillProfileOptionEnabled;

	private $selectedEvaluationMode;
	
	private $testResultButtonEnabled = false;

	public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI, $parentCMD)
	{
		$this->ctrl = $ctrl;

		$this->parentGUI = $parentGUI;
		$this->parentCMD = $parentCMD;

		parent::__construct();
	}

	public function setAvailableSkillProfiles($availableSkillProfiles)
	{
		$this->availableSkillProfiles = $availableSkillProfiles;
	}

	public function getAvailableSkillProfiles()
	{
		return $this->availableSkillProfiles;
	}

	public function setNoSkillProfileOptionEnabled($noSkillProfileOptionEnabled)
	{
		$this->noSkillProfileOptionEnabled = $noSkillProfileOptionEnabled;
	}

	public function isNoSkillProfileOptionEnabled()
	{
		return $this->noSkillProfileOptionEnabled;
	}

	public function setSelectedEvaluationMode($selectedEvaluationMode)
	{
		$this->selectedEvaluationMode = $selectedEvaluationMode;
	}

	public function getSelectedEvaluationMode()
	{
		return $this->selectedEvaluationMode;
	}

	public function isTestResultButtonEnabled()
	{
		return $this->testResultButtonEnabled;
	}

	public function setTestResultButtonEnabled($testResultButtonEnabled)
	{
		$this->testResultButtonEnabled = $testResultButtonEnabled;
	}

	public function build()
	{
		if( $this->isTestResultButtonEnabled() )
		{
			$link = ilLinkButton::getInstance(); // always returns a new instance
			$link->setUrl($this->ctrl->getLinkTargetByClass('ilTestEvaluationGUI', 'outUserResultsOverview'));
			$link->setCaption($this->lng->txt("tst_show_results"), false);
			$this->addButtonInstance($link);

			$this->addSeparator();
		}
		
		$this->setFormAction($this->ctrl->getFormAction($this->parentGUI));

		$select = new ilSelectInputGUI($this->lng->txt("tst_analysis"), self::SKILL_PROFILE_PARAM);
		$select->setOptions($this->buildEvaluationModeOptionsArray());
		$select->setValue($this->getSelectedEvaluationMode());
		$this->addInputItem($select, true);

		$this->addFormButton($this->lng->txt("select"), $this->parentCMD);
	}

	private function buildEvaluationModeOptionsArray()
	{
		$options = array();

		if( $this->isNoSkillProfileOptionEnabled() )
		{
			$options[0] =  $this->lng->txt('tst_all_test_competences');;
		}

		foreach($this->getAvailableSkillProfiles() as $skillProfileId => $skillProfileTitle)
		{
			$options[$skillProfileId] = "{$this->lng->txt('tst_gap_analysis')}: {$skillProfileTitle}";
		}

		return $options;
	}

	public static function fetchSkillProfileParam($postData)
	{
		if( isset($postData[self::SKILL_PROFILE_PARAM]) )
		{
			return (int)$postData[self::SKILL_PROFILE_PARAM];
		}

		return 0;
	}
}