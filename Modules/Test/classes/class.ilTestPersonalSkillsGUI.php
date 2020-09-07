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

    private $availableSkills;

    private $selectedSkillProfile;

    private $reachedSkillLevels;

    private $usrId;

    /**
     * @var int
     */
    private $testId;

    /**
     * @param ilLanguage $lng
     * @param int        $testId
     */
    public function __construct(ilLanguage $lng, $testId)
    {
        $this->lng = $lng;
        $this->testId = $testId;
    }

    public function getHTML()
    {
        $gui = new ilPersonalSkillsGUI();

        $gui->setGapAnalysisActualStatusModePerObject($this->getTestId(), $this->lng->txt('tst_test_result'));

        $gui->setTriggerObjectsFilter(array($this->getTestId()));
        $gui->setHistoryView(true); // NOT IMPLEMENTED YET

        // this is not required, we have no self evals in the test context,
        // getReachedSkillLevel is a "test evaluation"
        //$gui->setGapAnalysisSelfEvalLevels($this->getReachedSkillLevels());

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

    /**
     * @return int
     */
    public function getTestId()
    {
        return $this->testId;
    }
}
