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

use ILIAS\Test\RequestDataCollector;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultSummary;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Skill\Service\SkillService;

/**
 * Class ilTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
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
        private readonly ilCtrlInterface $ctrl,
        private readonly ilAccess $access,
        private readonly ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly ilObjUser $user,
        private readonly ilLanguage $lng,
        private readonly TestLogger $logger,
        private readonly ilComponentRepository $component_repository,
        private readonly ilTabsGUI $tabs,
        private readonly ilToolbarGUI $toolbar,
        private readonly ilGlobalTemplateInterface $main_tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly SkillService $skills_service,
        private readonly GeneralQuestionPropertiesRepository $questionrepository,
        private readonly RequestDataCollector $testrequest,
        private readonly GlobalHttpState $http
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
                    $this->questionrepository,
                    $this->testrequest,
                    $this->http,
                    $this->refinery
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

                $gui = new ilMyTestResultsGUI(
                    $this->getTestObj(),
                    $this->access,
                    $this->testSession,
                    $this->objectiveParent,
                    $this->lng,
                    $this->ctrl,
                    $this->main_tpl,
                    $this->questionrepository,
                    $this->testrequest
                );
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

                $gui = new ilMyTestSolutionsGUI(
                    $this->getTestObj(),
                    $this->access,
                    $this->objectiveParent,
                    $this->lng,
                    $this->ctrl,
                    $this->main_tpl,
                    $this->questionrepository,
                    $this->testrequest
                );
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

                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->refinery, $this->component_repository);
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
                    $this->logger,
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
            case SettingsResultSummary::SCORE_REPORTING_FINISHED:
                if ($this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = $this->lng->txt('tst_res_tab_msg_res_after_finish_test');
                }

                break;

            case SettingsResultSummary::SCORE_REPORTING_DATE:
                $date = $this->getTestObj()->getScoreSettings()->getResultSummarySettings()->getReportingDate()
                    ->setTimezone(new \DateTimeZone($this->user->getTimeZone()));
                $date_format = $this->user->getDateFormat();
                if ($this->user->getTimeFormat() === (string) ilCalendarSettings::TIME_FORMAT_12) {
                    $format = (new DataFactory())->dateFormat()->withTime12($date_format)->toString();
                } else {
                    $format = (new DataFactory())->dateFormat()->withTime24($date_format)->toString();
                }

                if (!$this->testObj->hasAnyTestResult($this->getTestSession())) {
                    $message = sprintf(
                        $this->lng->txt('tst_res_tab_msg_res_after_date_no_res'),
                        $date->format($format)
                    );
                    break;
                }

                $message = sprintf(
                    $this->lng->txt('tst_res_tab_msg_res_after_date'),
                    $date->format($format)
                );
                break;

            case SettingsResultSummary::SCORE_REPORTING_AFTER_PASSED:
                $message = $this->lng->txt('tst_res_tab_msg_res_after_test_passed');
                break;
        }

        $this->main_tpl->setOnScreenMessage('info', $message);
    }
}
