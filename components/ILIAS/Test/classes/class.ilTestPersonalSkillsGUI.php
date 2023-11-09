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

declare(strict_types=1);

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestPersonalSkillsGUI
{
    private array $availableSkills;
    private int $selectedSkillProfile;
    private array$reachedSkillLevels;
    private int $usr_id;

    public function __construct(
        private ilLanguage $lng,
        private int $test_id
    ) {
    }

    public function getHTML(): string
    {
        $gui = new ilPersonalSkillsGUI();

        $gui->setGapAnalysisActualStatusModePerObject($this->getTestId(), $this->lng->txt('tst_test_result'));
        $gui->setTriggerObjectsFilter(array($this->getTestId()));
        $gui->setHistoryView(true); // NOT IMPLEMENTED YET
        $gui->setProfileId($this->getSelectedSkillProfile());

        $html = $gui->getGapAnalysisHTML($this->getUsrId(), $this->getAvailableSkills());

        return $html;
    }

    public function setAvailableSkills(array $availableSkills): void
    {
        $this->availableSkills = $availableSkills;
    }

    public function getAvailableSkills(): array
    {
        return $this->availableSkills;
    }

    public function setSelectedSkillProfile(int $selectedSkillProfile): void
    {
        $this->selectedSkillProfile = $selectedSkillProfile;
    }

    public function getSelectedSkillProfile(): int
    {
        return $this->selectedSkillProfile;
    }

    public function setReachedSkillLevels(array $reachedSkillLevels): void
    {
        $this->reachedSkillLevels = $reachedSkillLevels;
    }

    public function getReachedSkillLevels()
    {
        return $this->reachedSkillLevels;
    }

    public function setUsrId(int $usr_id): void
    {
        $this->usr_id = $usr_id;
    }

    public function getUsrId()
    {
        return $this->usr_id;
    }

    /**
     * @return int
     */
    public function getTestId(): int
    {
        return $this->test_id;
    }
}
