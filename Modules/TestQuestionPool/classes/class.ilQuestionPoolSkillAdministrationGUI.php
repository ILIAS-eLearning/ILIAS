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
 * @package     Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilQuestionPoolSkillAdministrationGUI: ilAssQuestionSkillAssignmentsGUI
 * @ilCtrl_Calls ilQuestionPoolSkillAdministrationGUI: ilAssQuestionSkillUsagesTableGUI
 */
class ilQuestionPoolSkillAdministrationGUI
{
    /**
     * @var ILIAS
     */
    private $ilias;

    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * @var ilAccessHandler
     */
    private $access;

    /**
     * @var ilTabsGUI
     */
    private $tabs;

    /**
     * @var ilGlobalTemplateInterface
     */
    private $tpl;

    /**
     * @var ilLanguage
     */
    private $lng;

    /**
     * @var ilDBInterface
     */
    private $db;

    private ilComponentRepository $component_repository;

    /**
     * @var ilObjQuestionPool
     */
    private $poolOBJ;

    /** @var string|int|null  */
    private $refId;

    public function __construct(
        ILIAS $ilias,
        ilCtrl $ctrl,
        ilAccessHandler $access,
        ilTabsGUI $tabs,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilDBInterface $db,
        ilComponentRepository $component_repository,
        ilObjQuestionPool $poolOBJ,
        $refId
    ) {
        $this->ilias = $ilias;
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
        $this->component_repository = $component_repository;
        $this->poolOBJ = $poolOBJ;
        $this->refId = $refId;
    }

    private function isAccessDenied(): bool
    {
        if (!$this->poolOBJ->isSkillServiceEnabled()) {
            return true;
        }

        if (!ilObjQuestionPool::isSkillManagementGloballyActivated()) {
            return true;
        }

        if (!$this->access->checkAccess('write', '', $this->refId)) {
            return true;
        }

        return false;
    }

    public function manageTabs($activeSubTabId): void
    {
        $link = $this->ctrl->getLinkTargetByClass(
            'ilAssQuestionSkillAssignmentsGUI',
            ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
        );
        $this->tabs->addSubTab(
            'ilassquestionskillassignmentsgui',
            $this->lng->txt('qpl_skl_sub_tab_quest_assign'),
            $link
        );

        $link = $this->ctrl->getLinkTargetByClass(
            'ilAssQuestionSkillUsagesTableGUI',
            ilAssQuestionSkillUsagesTableGUI::CMD_SHOW
        );
        $this->tabs->addSubTab(
            'ilassquestionskillusagestablegui',
            $this->lng->txt('qpl_skl_sub_tab_usages'),
            $link
        );

        $this->tabs->activateTab('qpl_tab_competences');
        $this->tabs->activateSubTab($activeSubTabId);
    }

    public function executeCommand(): void
    {
        if ($this->isAccessDenied()) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $nextClass = $this->ctrl->getNextClass();

        $this->manageTabs($nextClass);

        switch ($nextClass) {
            case 'ilassquestionskillassignmentsgui':
                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);
                $questionList->setParentObjId($this->poolOBJ->getId());
                $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);
                $questionList->load();

                $gui = new ilAssQuestionSkillAssignmentsGUI($this->ctrl, $this->access, $this->tpl, $this->lng, $this->db);
                $gui->setAssignmentEditingEnabled(true);
                $gui->setQuestionContainerId($this->poolOBJ->getId());
                $gui->setQuestionList($questionList);

                $this->ctrl->forwardCommand($gui);

                break;

            case 'ilassquestionskillusagestablegui':

                $gui = new ilAssQuestionSkillUsagesTableGUI(
                    $this->ctrl,
                    $this->tpl,
                    $this->lng,
                    $this->db,
                    $this->poolOBJ->getId()
                );

                $this->ctrl->forwardCommand($gui);

                break;
        }
    }
}
