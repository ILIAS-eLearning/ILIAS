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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestSkillLevelThresholdImportFails
{
    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var integer
     */
    protected $parentObjId;

    /**
     * ilTestSkillLevelThresholdImportFails constructor.
     * @param $parentObjId
     */
    public function __construct($parentObjId)
    {
        $this->parentObjId = $parentObjId;
    }

    /**
     * @return ilSetting
     */
    protected function getSettings(): ilSetting
    {
        if ($this->settings === null) {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionAssignedSkillList.php';

            $this->settings = new ilSetting('assimportfails');
        }

        return $this->settings;
    }

    /**
     * @return int
     */
    protected function getParentObjId(): int
    {
        return $this->parentObjId;
    }

    /**
     * @return string
     */
    protected function buildSettingsKey(): string
    {
        return 'failed_imp_slt_parentobj_' . $this->getParentObjId();
    }

    /**
     * @return ilAssQuestionAssignedSkillList|null
     */
    public function getFailedImports(): ?ilAssQuestionAssignedSkillList
    {
        $value = $this->getSettings()->get($this->buildSettingsKey(), null);

        if ($value !== null) {
            return unserialize($value);
        }

        return null;
    }

    /**
     * @param ilAssQuestionAssignedSkillList $skillList
     */
    public function registerFailedImports(ilAssQuestionAssignedSkillList $skillList)
    {
        $this->getSettings()->set($this->buildSettingsKey(), serialize($skillList));
    }

    /**
     */
    public function deleteRegisteredImportFails()
    {
        $this->getSettings()->delete($this->buildSettingsKey());
    }

    /**
     * @return bool
     */
    public function failedImportsRegistered(): bool
    {
        return $this->getFailedImports() !== null;
    }

    /**
     * @param ilLanguage $lng
     * @return string
     */
    public function getFailedImportsMessage(ilLanguage $lng): string
    {
        $msg = $lng->txt('tst_failed_imp_skl_thresholds');

        $msg .= '<ul>';
        foreach ($this->getFailedImports() as $skillKey) {
            list($skillBaseId, $skillTrefId) = explode(':', $skillKey);
            $skillTitle = ilBasicSkill::_lookupTitle($skillBaseId, $skillTrefId);

            $msg .= '<li>' . $skillTitle . '</li>';
        }
        $msg .= '</ul>';

        return $msg;
    }
}
