<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionSkillAssignmentsGUI.php';
require_once 'Modules/Test/classes/class.ilTestSkillLevelThresholdsGUI.php';

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
     * @var ilTemplate
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

    /**
     * @var ilTree
     */
    private $tree;

    /**
     * @var ilPluginAdmin
     */
    private $pluginAdmin;

    /**
     * @var ilObjTest
     */
    private $testOBJ;

    public function __construct(ILIAS $ilias, ilCtrl $ctrl, ilAccessHandler $access, ilTabsGUI $tabs, ilTemplate $tpl, ilLanguage $lng, ilDBInterface $db, ilTree $tree, ilPluginAdmin $pluginAdmin, ilObjTest $testOBJ, $refId)
    {
        $this->ilias = $ilias;
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->db = $db;
        $this->tree = $tree;
        $this->pluginAdmin = $pluginAdmin;
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
                
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->pluginAdmin);
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
    
    private function isAssignmentEditingRequired()
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

    private function isAccessDenied()
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
    
    private function getQuestionContainerId()
    {
        if ($this->testOBJ->isDynamicTest()) {
            $questionSetConfigFactory = new ilTestQuestionSetConfigFactory(
                $this->tree,
                $this->db,
                $this->pluginAdmin,
                $this->testOBJ
            );

            $questionSetConfig = $questionSetConfigFactory->getQuestionSetConfig();

            return $questionSetConfig->getSourceQuestionPoolId();
        }

        return $this->testOBJ->getId();
    }
    
    private function getRequiredQuestionInstanceTypeFilter()
    {
        if ($this->testOBJ->isDynamicTest()) {
            return ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS;
        }
        
        if ($this->testOBJ->isRandomTest()) {
            return ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES;
        }
        
        return null;
    }
    
    private function buildAssignmentConfigurationInPoolHintMessage()
    {
        $questionSetConfigFactory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->pluginAdmin,
            $this->testOBJ
        );
        
        $questionSetConfig = $questionSetConfigFactory->getQuestionSetConfig();
        
        if ($this->testOBJ->isRandomTest()) {
            $testMode = $this->lng->txt('tst_question_set_type_random');
            $poolLinks = $questionSetConfig->getCommaSeparatedSourceQuestionPoolLinks();

            return sprintf($this->lng->txt('tst_qst_skl_cfg_in_pool_hint_rndquestset'), $testMode, $poolLinks);
        } elseif ($this->testOBJ->isDynamicTest()) {
            $testMode = $this->lng->txt('tst_question_set_type_dynamic');
            $poolLink = $questionSetConfig->getSourceQuestionPoolLink($questionSetConfig->getSourceQuestionPoolId());
            
            return sprintf($this->lng->txt('tst_qst_skl_cfg_in_pool_hint_dynquestset'), $testMode, $poolLink);
        }

        return '';
    }
}
