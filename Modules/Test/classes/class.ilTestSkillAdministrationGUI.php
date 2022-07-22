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
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestSkillAdministrationGUI: ilAssQuestionSkillAssignmentsGUI
 * @ilCtrl_Calls ilTestSkillAdministrationGUI: ilTestSkillLevelThresholdsGUI
 */
class ilTestSkillAdministrationGUI
{
    private ILIAS $ilias;
    private ilCtrlInterface $ctrl;
    private ilAccessHandler $access;
    private ilTabsGUI $tabs;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilDBInterface $db;
    private ilTree $tree;
    private ilComponentRepository $component_repository;
    private ilObjTest $testOBJ;
    private $refId;

    public function __construct(
        ILIAS $ilias,
        ilCtrl $ctrl,
        ilAccessHandler $access,
        ilTabsGUI $tabs,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilDBInterface $db,
        ilTree $tree,
        ilComponentRepository $component_repository,
        ilObjTest $testOBJ,
        $refId
    ) {
        $this->ilias = $ilias;
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
        $this->tree = $tree;
        $this->component_repository = $component_repository;
        $this->testOBJ = $testOBJ;
        $this->refId = $refId;
    }

    public function executeCommand()
    {
        if ($this->isAccessDenied()) {
            $this->ilias->raiseError($this->lng->txt("permission_denied"), $this->ilias->error_obj->MESSAGE);
        }

        $nextClass = $this->ctrl->getNextClass();

        $this->manageTabs($nextClass);

        switch ($nextClass) {
            case 'ilassquestionskillassignmentsgui':

                $questionContainerId = $this->getQuestionContainerId();
                
                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);
                $questionList->setParentObjId($questionContainerId);
                $questionList->setQuestionInstanceTypeFilter($this->getRequiredQuestionInstanceTypeFilter());
                $questionList->load();

                $gui = new ilAssQuestionSkillAssignmentsGUI($this->ctrl, $this->access, $this->tpl, $this->lng, $this->db);
                $gui->setAssignmentEditingEnabled($this->isAssignmentEditingRequired());
                $gui->setQuestionContainerId($questionContainerId);
                $gui->setQuestionList($questionList);
                
                if ($this->testOBJ->isFixedTest()) {
                    $gui->setQuestionOrderSequence($this->testOBJ->getQuestions());
                } else {
                    $gui->setAssignmentConfigurationHintMessage($this->buildAssignmentConfigurationInPoolHintMessage());
                }

                $this->ctrl->forwardCommand($gui);
                
                break;

            case 'iltestskilllevelthresholdsgui':

                $gui = new ilTestSkillLevelThresholdsGUI($this->ctrl, $this->tpl, $this->lng, $this->db, $this->testOBJ->getTestId());
                $gui->setQuestionAssignmentColumnsEnabled(!$this->testOBJ->isRandomTest());
                $gui->setQuestionContainerId($this->getQuestionContainerId());
                $this->ctrl->forwardCommand($gui);
                break;
        }
    }
    
    private function isAssignmentEditingRequired() : bool
    {
        if (!$this->testOBJ->isFixedTest()) {
            return false;
        }
        
        if ($this->testOBJ->participantDataExist()) {
            return false;
        }
        
        return true;
    }

    public function manageTabs($activeSubTabId)
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
            'ilTestSkillLevelThresholdsGUI',
            ilTestSkillLevelThresholdsGUI::CMD_SHOW_SKILL_THRESHOLDS
        );
        $this->tabs->addSubTab(
            'iltestskilllevelthresholdsgui',
            $this->lng->txt('tst_skl_sub_tab_thresholds'),
            $link
        );

        $this->tabs->activateTab('tst_tab_competences');
        $this->tabs->activateSubTab($activeSubTabId);
    }

    private function isAccessDenied() : bool
    {
        if (!$this->testOBJ->isSkillServiceEnabled()) {
            return true;
        }

        if (!ilObjTest::isSkillManagementGloballyActivated()) {
            return true;
        }

        if (!$this->access->checkAccess('write', '', $this->refId)) {
            return true;
        }

        return false;
    }
    
    private function getQuestionContainerId() : ?int
    {
        if ($this->testOBJ->isDynamicTest()) {
            $questionSetConfigFactory = new ilTestQuestionSetConfigFactory(
                $this->tree,
                $this->db,
                $this->component_repository,
                $this->testOBJ
            );

            $questionSetConfig = $questionSetConfigFactory->getQuestionSetConfig();

            return $questionSetConfig->getSourceQuestionPoolId();
        }

        return $this->testOBJ->getId();
    }
    
    private function getRequiredQuestionInstanceTypeFilter() : ?string
    {
        if ($this->testOBJ->isDynamicTest()) {
            return ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS;
        }
        
        if ($this->testOBJ->isRandomTest()) {
            return ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES;
        }
        
        return null;
    }
    
    private function buildAssignmentConfigurationInPoolHintMessage() : string
    {
        $questionSetConfigFactory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->component_repository,
            $this->testOBJ
        );
        
        $questionSetConfig = $questionSetConfigFactory->getQuestionSetConfig();
        
        if ($this->testOBJ->isRandomTest()) {
            $testMode = $this->lng->txt('tst_question_set_type_random');
            $poolLinks = $questionSetConfig->getCommaSeparatedSourceQuestionPoolLinks();

            return sprintf($this->lng->txt('tst_qst_skl_cfg_in_pool_hint_rndquestset'), $testMode, $poolLinks);
        }

        return '';
    }
}
