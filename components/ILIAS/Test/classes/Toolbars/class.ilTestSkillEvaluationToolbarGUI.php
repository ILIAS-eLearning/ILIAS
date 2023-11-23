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
class ilTestSkillEvaluationToolbarGUI extends ilToolbarGUI
{
    private array $availableSkillProfiles;
    private bool $noSkillProfileOptionEnabled;
    private int $selectedEvaluationMode;

    public function __construct(
        private ilCtrl $ctrl
    ) {
        parent::__construct();
    }

    public function setAvailableSkillProfiles(array $availableSkillProfiles): void
    {
        $this->availableSkillProfiles = $availableSkillProfiles;
    }

    public function getAvailableSkillProfiles(): array
    {
        return $this->availableSkillProfiles;
    }

    public function setNoSkillProfileOptionEnabled(bool $noSkillProfileOptionEnabled): void
    {
        $this->noSkillProfileOptionEnabled = $noSkillProfileOptionEnabled;
    }

    public function isNoSkillProfileOptionEnabled(): bool
    {
        return $this->noSkillProfileOptionEnabled;
    }

    public function setSelectedEvaluationMode(int $selectedEvaluationMode): void
    {
        $this->selectedEvaluationMode = $selectedEvaluationMode;
    }

    public function getSelectedEvaluationMode(): int
    {
        return $this->selectedEvaluationMode;
    }

    public function build(): void
    {
        $this->setFormAction($this->ctrl->getFormActionByClass(ilTestSkillEvaluationGUI::class));

        $select = new ilSelectInputGUI($this->lng->txt('tst_analysis'), ilTestSkillEvaluationGUI::SKILL_PROFILE_PARAM);
        $select->setOptions($this->buildEvaluationModeOptionsArray());
        $select->setValue($this->getSelectedEvaluationMode());
        $this->addInputItem($select, true);

        $this->addFormButton($this->lng->txt('select'), ilTestSkillEvaluationGUI::CMD_SHOW);
    }

    private function buildEvaluationModeOptionsArray(): array
    {
        $options = array();

        if ($this->isNoSkillProfileOptionEnabled()) {
            $options[0] = $this->lng->txt('tst_all_test_competences');
            ;
        }

        foreach ($this->getAvailableSkillProfiles() as $skillProfileId => $skillProfileTitle) {
            $options[$skillProfileId] = "{$this->lng->txt('tst_gap_analysis')}: {$skillProfileTitle}";
        }

        return $options;
    }
}
