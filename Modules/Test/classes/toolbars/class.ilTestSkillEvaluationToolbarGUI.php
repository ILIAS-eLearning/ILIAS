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
class ilTestSkillEvaluationToolbarGUI extends ilToolbarGUI
{
    public const SKILL_PROFILE_PARAM = 'skill_profile';

    private ilCtrl $ctrl;

    private $parentGUI;
    private $parentCMD;
    private $availableSkillProfiles;
    private $noSkillProfileOptionEnabled;
    private $selectedEvaluationMode;

    public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI, $parentCMD)
    {
        $this->ctrl = $ctrl;

        $this->parentGUI = $parentGUI;
        $this->parentCMD = $parentCMD;

        parent::__construct();
    }

    public function setAvailableSkillProfiles($availableSkillProfiles): void
    {
        $this->availableSkillProfiles = $availableSkillProfiles;
    }

    public function getAvailableSkillProfiles()
    {
        return $this->availableSkillProfiles;
    }

    public function setNoSkillProfileOptionEnabled($noSkillProfileOptionEnabled): void
    {
        $this->noSkillProfileOptionEnabled = $noSkillProfileOptionEnabled;
    }

    public function isNoSkillProfileOptionEnabled()
    {
        return $this->noSkillProfileOptionEnabled;
    }

    public function setSelectedEvaluationMode($selectedEvaluationMode): void
    {
        $this->selectedEvaluationMode = $selectedEvaluationMode;
    }

    public function getSelectedEvaluationMode()
    {
        return $this->selectedEvaluationMode;
    }

    public function build(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->parentGUI));

        $select = new ilSelectInputGUI($this->lng->txt("tst_analysis"), self::SKILL_PROFILE_PARAM);
        $select->setOptions($this->buildEvaluationModeOptionsArray());
        $select->setValue($this->getSelectedEvaluationMode());
        $this->addInputItem($select, true);

        $this->addFormButton($this->lng->txt("select"), $this->parentCMD);
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

    public static function fetchSkillProfileParam($postData): int
    {
        if (isset($postData[self::SKILL_PROFILE_PARAM])) {
            return (int) $postData[self::SKILL_PROFILE_PARAM];
        }

        return 0;
    }
}
