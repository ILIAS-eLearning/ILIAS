<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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

    public function getHTML(): string
    {
        $gui = new ilPersonalSkillsGUI();

        $gui->setGapAnalysisActualStatusModePerObject($this->getTestId(), $this->lng->txt('tst_test_result'));

        $gui->setTriggerObjectsFilter(array($this->getTestId()));

        // this is not required, we have no self evals in the test context,
        // getReachedSkillLevel is a "test evaluation"
        //$gui->setGapAnalysisSelfEvalLevels($this->getReachedSkillLevels());

        $gui->setProfileId($this->getSelectedSkillProfile());

        $html = $gui->getGapAnalysisHTML((int) $this->getUsrId(), $this->getAvailableSkills());

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
    public function getTestId(): int
    {
        return $this->testId;
    }
}
