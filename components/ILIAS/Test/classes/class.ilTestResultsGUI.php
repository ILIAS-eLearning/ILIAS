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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\DI\LoggingServices;
use ILIAS\Skill\Service\SkillService;
use ILIAS\Test\InternalRequestService;

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
    public const DEFAULT_CMD = 'show';
    private \ILIAS\DI\UIServices $ui;

    protected ilTestAccess $testAccess;
    protected ilTestSession $testSession;
    protected ilTestTabsManager $testTabs;
    protected ilTestObjectiveOrientedContainer $objectiveParent;


    public function __construct(
        private ilObjTest $testObj,
        private ilTestQuestionSetConfig $question_set_config,
        private ilCtrl $ctrl,
        private ilAccess $access,
        private ilDBInterface $db,
        private ilObjUser $user,
        private ilLanguage $lng,
        private LoggingServices $logging_services,
        private ilComponentRepository $component_repository,
        private ilTabsGUI $tabs,
        private ilToolbarGUI $toolbar,
        private ilGlobalTemplateInterface $main_tpl,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer,
        private SkillService $skills_service,
        private InternalRequestService $testrequest,
        private \ILIAS\TestQuestionPool\QuestionInfoService $questioninfo
    ) {
    }

    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveParent(): ilTestObjectiveOrientedContainer
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
    public function getTestObj(): ilObjTest
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

    public function getQuestionSetConfig(): ilTestQuestionSetConfig
    {
        return $this->question_set_config;
    }

    public function setQuestionSetConfig(ilTestQuestionSetConfig $question_set_config): void
    {
        $this->question_set_config = $question_set_config;
    }

    public function getTestAccess(): ilTestAccess
    {
        return $this->testAccess;
    }

    public function setTestAccess(ilTestAccess $testAccess): void
    {
        $this->testAccess = $testAccess;
    }

    public function getTestSession(): ilTestSession
    {
        return $this->testSession;
    }

    public function setTestSession(ilTestSession $testSession): void
    {
        $this->testSession = $testSession;
    }

    public function getTestTabs(): ilTestTabsManager
    {
        return $this->testTabs;
    }

    public function setTestTabs(ilTestTabsManager $testTabs): void
    {
        $this->testTabs = $testTabs;
    }

    public function executeCommand(): void
    {
        $this->getTestTabs()->activateTab(ilTestTabsManager::TAB_ID_RESULTS);
        $this->getTestTabs()->getResultsSubTabs();

        switch ($this->ctrl->getNextClass()) {
            case 'ilparticipantstestresultsgui':
                if (!$this->getTestAccess()->checkParticipantsResultsAccess()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_PARTICIPANTS_RESULTS);

                $gui = new ilParticipantsTestResultsGUI(
                    $this->ctrl,
                    $this->lng,
                    $this->db,
                    $this->user,
                    $this->tabs,
                    $this->toolbar,
                    $this->main_tpl,
                    $this->ui_factory,
                    $this->ui_renderer,
                    new ilTestParticipantAccessFilterFactory($this->access),
                    $this->testrequest
                );
                $gui->setTestObj($this->getTestObj());
                $gui->setQuestionSetConfig($this->getQuestionSetConfig());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilmytestresultsgui':
                if (!$this->getTestTabs()->needsMyResultsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_RESULTS);

                $gui = new ilMyTestResultsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setTestSession($this->getTestSession());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestevalobjectiveorientedgui':
                if (!$this->getTestTabs()->needsLoResultsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_LO_RESULTS);

                $gui = new ilTestEvalObjectiveOrientedGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilmytestsolutionsgui':
                if (!$this->getTestTabs()->needsMySolutionsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_MY_SOLUTIONS);

                $gui = new ilMyTestSolutionsGUI();
                $gui->setTestObj($this->getTestObj());
                $gui->setTestAccess($this->getTestAccess());
                $gui->setObjectiveParent($this->getObjectiveParent());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltesttoplistgui':
                if (!$this->getTestTabs()->needsHighSoreSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_HIGHSCORE);

                $gui = new ilTestToplistGUI(
                    $this->getTestObj(),
                    new ilTestTopList($this->getTestObj(), $this->db),
                    $this->ctrl,
                    $this->main_tpl,
                    $this->lng,
                    $this->user,
                    $this->ui_factory,
                    $this->ui_renderer
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestskillevaluationgui':
                $this->getTestTabs()->activateSubTab(ilTestTabsManager::SUBTAB_ID_SKILL_RESULTS);

                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);
                $questionList->setParentObjId($this->getTestObj()->getId());
                $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
                $questionList->load();

                $testSessionFactory = new ilTestSessionFactory($this->getTestObj(), $this->db, $this->user);
                $testSession = $testSessionFactory->getSession();

                $gui = new ilTestSkillEvaluationGUI(
                    $this->getTestObj(),
                    $this->ctrl,
                    $this->main_tpl,
                    $this->lng,
                    $this->db,
                    $this->logging_services,
                    $this->skills_service,
                    $this->testrequest
                );
                $gui->setQuestionList($questionList);
                $gui->setTestSession($testSession);
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());

                $this->ctrl->forwardCommand($gui);
                break;

            case strtolower(__CLASS__):
            default:
                $command = $this->ctrl->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }

    protected function showCmd(): void
    {
        if ($this->testObj->canShowTestResults($this->getTestSession())) {
            if ($this->objectiveParent->isObjectiveOrientedPresentationRequired()) {
                $this->ctrl->redirectByClass('ilTestEvalObjectiveOrientedGUI');
            }

            $this->ctrl->redirectByClass(['ilMyTestResultsGUI', 'ilTestEvaluationGUI']);
        }

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable($this->user->getId(), $this->getTestObj()->getId())) {
            $button = $this->ui->factory()->button()->standard('certficiate', $this->ctrl->getFormActionByClass(ilTestEvaluationGUI::class, 'outCertificate'));
            $this->toolbar->addComponent($button);
        }

        $this->showNoResultsReportingMessage();
    }

    protected function showNoResultsReportingMessage(): void
    {
        $message = $this->lng->txt('tst_res_tab_msg_res_after_taking_test');

        switch ($this->testObj->getScoreReporting()) {
            case ilObjTestSettingsResultSummary::SCORE_REPORTING_FINISHED:
                if ($this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = $this->lng->txt('tst_res_tab_msg_res_after_finish_test');
                }

                break;

            case ilObjTestSettingsResultSummary::SCORE_REPORTING_DATE:
                $date = new ilDateTime($this->testObj->getReportingDate(), IL_CAL_TIMESTAMP);

                if (!$this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = sprintf(
                        $this->lng->txt('tst_res_tab_msg_res_after_date_no_res'),
                        ilDatePresentation::formatDate($date)
                    );
                    break;
                }

                $message = sprintf(
                    $this->lng->txt('tst_res_tab_msg_res_after_date'),
                    ilDatePresentation::formatDate($date)
                );
                break;

            case ilObjTestSettingsResultSummary::SCORE_REPORTING_AFTER_PASSED:
                $message = $this->lng->txt('tst_res_tab_msg_res_after_test_passed');
                break;
        }

        $this->main_tpl->setOnScreenMessage('info', $message);
    }
}
