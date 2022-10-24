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
 * @package     Modules/TestQuestionPool
 */
class ilAssQuestionSkillAssignmentImportFails
{
    /**
     * @var ilAssQuestionSkillAssignmentRegistry
     */
    protected $settings;

    /**
     * @var integer
     */
    protected $parentObjId;

    /**
     * ilAssQuestionSkillAssignmentImportFails constructor.
     * @param $parentObjId
     */
    public function __construct($parentObjId)
    {
        $this->parentObjId = $parentObjId;
    }

    /**
     * @return ilAssQuestionSkillAssignmentRegistry
     */
    protected function getSettings(): ilAssQuestionSkillAssignmentRegistry
    {
        if ($this->settings === null) {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentImportList.php';
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssQuestionSkillAssignmentRegistry.php';
            $this->settings = new ilAssQuestionSkillAssignmentRegistry(new ilSetting('assimportfails'));
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
        return 'failed_imp_qsa_parentobj_' . $this->getParentObjId();
    }

    /**
     * @return ilAssQuestionSkillAssignmentImportList|null
     */
    public function getFailedImports(): ?ilAssQuestionSkillAssignmentImportList
    {
        $value = $this->getSettings()->getStringifiedImports($this->buildSettingsKey(), null);

        if ($value !== null) {
            return unserialize($value);
        }

        return null;
    }

    /**
     * @param ilAssQuestionSkillAssignmentImportList $assignmentList
     */
    public function registerFailedImports(ilAssQuestionSkillAssignmentImportList $assignmentList): void
    {
        $this->getSettings()->setStringifiedImports($this->buildSettingsKey(), serialize($assignmentList));
    }

    /**
     */
    public function deleteRegisteredImportFails(): void
    {
        $this->getSettings()->deleteStringifiedImports($this->buildSettingsKey());
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
        $handledSkills = array();
        $msg = $lng->txt('tst_failed_imp_qst_skl_assign');

        $msg .= '<ul>';
        foreach ($this->getFailedImports() as $assignmentImport) {
            $sklBaseId = $assignmentImport->getImportSkillBaseId();
            $sklTrefId = $assignmentImport->getImportSkillTrefId();

            if (isset($handledSkills["$sklBaseId:$sklTrefId"])) {
                continue;
            }

            $handledSkills["$sklBaseId:$sklTrefId"] = true;

            $msg .= '<li>' . $assignmentImport->getImportSkillTitle() . '</li>';
        }
        $msg .= '</ul>';

        return $msg;
    }
}
