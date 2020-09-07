<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
 * @ilCtrl_Calls ilTestResultsGUI: ilParticipantsTestResultsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilMyTestResultsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestEvalObjectiveOrientedGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilMyTestSolutionsGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestToplistGUI
 * @ilCtrl_Calls ilTestResultsGUI: ilTestSkillEvaluationGUI
 */
class ilTestResultsGUI
{
    const DEFAULT_CMD = 'show';
    
    /**
     * @var ilObjTest
     */
    protected $testObj;
    
    /**
     * @var ilTestQuestionSetConfig
     */
    protected $questionSetConfig;
    
    /**
     * @var ilTestAccess
     */
    protected $testAccess;
    
    /**
     * @var ilTestSession
     */
    protected $testSession;
    
    /**
     * @var ilTestTabsManager
     */
    protected $testTabs;
    
    /**
     * @var ilTestObjectiveOrientedContainer
     */
    protected $objectiveParent;
    
    /**
     * ilTestParticipantsGUI constructor.
     * @param ilObjTest $testObj
     */
    public function __construct(ilObjTest $testObj, ilTestQuestionSetConfig $questionSetConfig)
    {
        $this->testObj = $testObj;
        $this->questionSetConfig = $questionSetConfig;
    }
    
    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveParent()
    {
        return $this->objectiveParent;
    }
    
    /**
     * @param ilTestObjectiveOrientedContainer $objectiveParent
     */
    public function setObjectiveParent($objectiveParent)
    {
        $this->objectiveParent = $objectiveParent;
    }
    
    /**
     * @return ilObjTest
     */
    public function getTestObj()
    {
        return $this->testObj;
    }
    
    /**
     * @param ilObjTest $testObj
     */
    public function setTestObj($testObj)
    {
        $this->testObj = $testObj;
    }
    
    /**
     * @return ilTestQuestionSetConfig
     */
    public function getQuestionSetConfig()
    {
        return $this->questionSetConfig;
    }
    
    /**
     * @param ilTestQuestionSetConfig $questionSetConfig
     */
    public function setQuestionSetConfig($questionSetConfig)
    {
        $this->questionSetConfig = $questionSetConfig;
    }
    
    /**
     * @return ilTestAccess
     */
    public function getTestAccess()
    {
        return $this->testAccess;
    }
    
    /**
     * @param ilTestAccess $testAccess
     */
    public function setTestAccess($testAccess)
    {
        $this->testAccess = $testAccess;
    }
    
    /**
     * @return ilTestSession
     */
    public function getTestSession()
    {
        return $this->testSession;
    }
    
    /**
     * @param ilTestSession $testSession
     */
    public function setTestSession($testSession)
    {
        $this->testSession = $testSession;
    }
    
    /**
     * @return ilTestTabsManager
     */
    public function getTestTabs()
    {
        return $this->testTabs;
    }
    
    /**
     * @param ilTestTabsManager $testTabs
     */
    public function setTestTabs($testTabs)
    {
        $this->testTabs = $testTabs;
    }
    
    /**
     * Execute Command
     */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $this->getTestTabs()->activateTab(ilTestTabsManager::TAB_ID_RESULTS);
        $this->getTestTabs()->getResultsSubTabs();
        
        switch ($DIC->ctrl()->getNextClass()) {
            case 'ilparticipantstestresultsgui':
                
                if (!$this->getTestAccess()->checkManageParticipantsAccess() && !$this->getTestAccess()->checkParticipantsResultsAccess()) {
                    ilObjTestGUI::accessViolationRedirect();
                }
                
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_PARTICIPANTS_RESULTS);
                
                require_once 'Modules/Test/classes/class.ilParticipantsTestResultsGUI.php';
                $gui = new ilParticipantsTestResultsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setQuestionSetConfig($this->getQuestionSetConfig());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;
            
            case 'ilmytestresultsgui':
                
                if (!$this->getTestTabs()->needsMyResultsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }
                
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_RESULTS);
                
                require_once 'Modules/Test/classes/class.ilMyTestResultsGUI.php';
                $gui = new ilMyTestResultsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setTestSession($this->getTestSession());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;
            
            case 'iltestevalobjectiveorientedgui':
                
                if (!$this->getTestTabs()->needsLoResultsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }
                
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_LO_RESULTS);
                
                require_once 'Modules/Test/classes/class.ilTestEvalObjectiveOrientedGUI.php';
                $gui = new ilTestEvalObjectiveOrientedGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;
            
            case 'ilmytestsolutionsgui':
                
                if (!$this->getTestTabs()->needsMySolutionsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }
                
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_SOLUTIONS);
                
                require_once 'Modules/Test/classes/class.ilMyTestSolutionsGUI.php';
                $gui = new ilMyTestSolutionsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $DIC->ctrl()->forwardCommand($gui);
                break;
            
            case 'iltesttoplistgui':
                
                if (!$this->getTestTabs()->needsHighSoreSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }
                
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_HIGHSCORE);
                
                require_once './Modules/Test/classes/class.ilTestToplistGUI.php';
                $gui = new ilTestToplistGUI($this->getTestObj());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case 'iltestskillevaluationgui':
                
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_SKILL_RESULTS);
                
                global $DIC; /* @var ILIAS\DI\Container $DIC */
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionList.php';
                if ($this->getTestObj()->isDynamicTest()) {
                    require_once 'Modules/Test/classes/class.ilObjTestDynamicQuestionSetConfig.php';
                    $dynamicQuestionSetConfig = new ilObjTestDynamicQuestionSetConfig(
                        $DIC->repositoryTree(),
                        $DIC->database(),
                        $DIC['ilPluginAdmin'],
                        $this->getTestObj()
                    );
                    $dynamicQuestionSetConfig->loadFromDb();
                    $questionList = new ilAssQuestionList($DIC->database(), $DIC->language(), $DIC['ilPluginAdmin']);
                    $questionList->setParentObjId($dynamicQuestionSetConfig->getSourceQuestionPoolId());
                    $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS);
                } else {
                    $questionList = new ilAssQuestionList($DIC->database(), $DIC->language(), $DIC['ilPluginAdmin']);
                    $questionList->setParentObjId($this->getTestObj()->getId());
                    $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
                }
                $questionList->load();
                
                require_once 'Modules/Test/classes/class.ilTestSessionFactory.php';
                $testSessionFactory = new ilTestSessionFactory($this->getTestObj());
                $testSession = $testSessionFactory->getSession();
                
                require_once 'Modules/Test/classes/class.ilTestSkillEvaluationGUI.php';
                $gui = new ilTestSkillEvaluationGUI(
                    $DIC->ctrl(),
                    $DIC->tabs(),
                    $DIC->ui()->mainTemplate(),
                    $DIC->language(),
                    $DIC->database(),
                    $this->getTestObj()
                );
                $gui->setQuestionList($questionList);
                $gui->setTestSession($testSession);
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                
                $DIC->ctrl()->forwardCommand($gui);
                break;
                
            case strtolower(__CLASS__):
            default:
                
                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }
    
    protected function showCmd()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        if ($this->testObj->canShowTestResults($this->getTestSession())) {
            if ($this->objectiveParent->isObjectiveOrientedPresentationRequired()) {
                $DIC->ctrl()->redirectByClass('ilTestEvalObjectiveOrientedGUI');
            }

            $DIC->ctrl()->redirectByClass(array('ilMyTestResultsGUI', 'ilTestEvaluationGUI'));
        }

        $toolbar = $DIC->toolbar();
        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable($DIC->user()->getId(), $this->getTestObj()->getId())) {
            $button = ilLinkButton::getInstance();
            $button->setCaption('certificate');
            $button->setUrl($DIC->ctrl()->getFormActionByClass(ilTestEvaluationGUI::class, 'outCertificate'));
            $toolbar->addButtonInstance($button);
        }

        $this->showNoResultsReportingMessage();
    }
    
    protected function showNoResultsReportingMessage()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        
        $message = $DIC->language()->txt('tst_res_tab_msg_res_after_taking_test');
        
        switch ($this->testObj->getScoreReporting()) {
            case ilObjTest::SCORE_REPORTING_FINISHED:
                
                if ($this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = $DIC->language()->txt('tst_res_tab_msg_res_after_finish_test');
                }
                
                break;
            
            case ilObjTest::SCORE_REPORTING_DATE:
                
                $date = new ilDateTime($this->testObj->getReportingDate(), IL_CAL_TIMESTAMP);
                
                if (!$this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = sprintf(
                        $DIC->language()->txt('tst_res_tab_msg_res_after_date_no_res'),
                        ilDatePresentation::formatDate($date)
                    );
                    break;
                }
                
                $message = sprintf(
                    $DIC->language()->txt('tst_res_tab_msg_res_after_date'),
                    ilDatePresentation::formatDate($date)
                );
                break;
                
            case ilObjTest::SCORE_REPORTING_AFTER_PASSED:
                $message = $DIC->language()->txt('tst_res_tab_msg_res_after_test_passed');
                break;
        }
        
        ilUtil::sendInfo($message);
    }
}
