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
use ILIAS\Test\Presentation\TabsManager;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Settings\ScoreReporting\SettingsResultSummary;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
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

    public function __construct(
        private ilObjTest $test_object,
        private ilTestQuestionSetConfig $question_set_config,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilAccess $access,
        private readonly ilTestAccess $test_access,
        private readonly ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly ilObjUser $user,
        private readonly ilLanguage $lng,
        private readonly TestLogger $logger,
        private readonly ilComponentRepository $component_repository,
        private ilTabsGUI $tabs,
        private TabsManager $test_tabs,
        private readonly ilToolbarGUI $toolbar,
        private readonly ilGlobalTemplateInterface $main_tpl,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly SkillService $skills_service,
        private readonly GeneralQuestionPropertiesRepository $questionrepository,
        private readonly RequestDataCollector $testrequest,
        private readonly GlobalHttpState $http,
        private readonly DataFactory $data_factory,
        private readonly ilTestSession $test_session,
        private readonly ilTestObjectiveOrientedContainer $objective_parent
    ) {
    }

    public function executeCommand(): void
    {
        $this->test_tabs->activateTab(TabsManager::TAB_ID_YOUR_RESULTS);
        $this->test_tabs->getYourResultsSubTabs();

        switch ($this->ctrl->getNextClass()) {
            case 'ilmytestresultsgui':
                if (!$this->test_tabs->needsYourResultsTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->test_tabs->activateSubTab(TabsManager::SUBTAB_ID_MY_RESULTS);

                $gui = new ilMyTestResultsGUI(
                    $this->test_object,
                    $this->test_access,
                    $this->objective_parent,
                    $this->user,
                    $this->lng,
                    $this->ctrl,
                    $this->main_tpl,
                    $this->questionrepository,
                    $this->testrequest
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestevalobjectiveorientedgui':
                if (!$this->test_tabs->needsLoResultsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->test_tabs->activateSubTab(TabsManager::SUBTAB_ID_LO_RESULTS);

                $gui = new ilTestEvalObjectiveOrientedGUI($this->test_object);
                $gui->setObjectiveOrientedContainer($this->objective_parent);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilmytestsolutionsgui':
                if (!$this->test_tabs->needsYourSolutionsSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->test_tabs->activateSubTab(TabsManager::SUBTAB_ID_MY_SOLUTIONS);

                $gui = new ilMyTestSolutionsGUI(
                    $this->test_object,
                    $this->test_access,
                    $this->objective_parent,
                    $this->lng,
                    $this->ctrl,
                    $this->main_tpl,
                    $this->questionrepository,
                    $this->testrequest
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltesttoplistgui':
                if (!$this->test_tabs->needsHighSoreSubTab()) {
                    ilObjTestGUI::accessViolationRedirect();
                }

                $this->test_tabs->activateSubTab(TabsManager::SUBTAB_ID_HIGHSCORE);

                $gui = new ilTestToplistGUI(
                    $this->test_object,
                    new ilTestTopList($this->test_object, $this->db),
                    $this->ctrl,
                    $this->main_tpl,
                    $this->lng,
                    $this->user,
                    $this->ui_factory,
                    $this->ui_renderer,
                    $this->data_factory,
                    $this->http
                );
                $this->ctrl->forwardCommand($gui);
                break;

            case 'iltestskillevaluationgui':
                $this->test_tabs->activateSubTab(TabsManager::SUBTAB_ID_SKILL_RESULTS);

                $questionList = new ilAssQuestionList($this->db, $this->lng, $this->refinery, $this->component_repository);
                $questionList->setParentObjId($this->test_object->getId());
                $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);
                $questionList->load();

                $testSessionFactory = new ilTestSessionFactory($this->test_object, $this->db, $this->user);
                $testSession = $testSessionFactory->getSession();

                $gui = new ilTestSkillEvaluationGUI(
                    $this->test_object,
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
                $gui->setObjectiveOrientedContainer($this->objective_parent);

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
        if ($this->test_object->canShowTestResults($this->test_session)) {
            if ($this->objective_parent->isObjectiveOrientedPresentationRequired()) {
                $this->ctrl->redirectByClass('ilTestEvalObjectiveOrientedGUI');
            }

            $this->ctrl->redirectByClass(['ilMyTestResultsGUI', 'ilTestEvaluationGUI']);
        }

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable($this->user->getId(), $this->test_object->getId())) {
            $button = $this->ui->factory()->button()->standard('certficiate', $this->ctrl->getFormActionByClass(ilTestEvaluationGUI::class, 'outCertificate'));
            $this->toolbar->addComponent($button);
        }

        $this->showNoResultsReportingMessage();
    }

    protected function showNoResultsReportingMessage(): void
    {
        $message = $this->lng->txt('tst_res_tab_msg_res_after_taking_test');

        switch ($this->test_object->getScoreReporting()) {
            case SettingsResultSummary::SCORE_REPORTING_FINISHED:
                if ($this->test_object->hasAnyTestResult($this->test_session)) {
                    $message = $this->lng->txt('tst_res_tab_msg_res_after_finish_test');
                }

                break;

            case SettingsResultSummary::SCORE_REPORTING_DATE:
                $date = $this->test_object->getScoreSettings()->getResultSummarySettings()->getReportingDate()
                    ->setTimezone(new \DateTimeZone($this->user->getTimeZone()));

                if (!$this->test_object->hasAnyTestResult($this->test_session)) {
                    $message = sprintf(
                        $this->lng->txt('tst_res_tab_msg_res_after_date_no_res'),
                        $date->format($this->user->getDateTimeFormat()->toString())
                    );
                    break;
                }

                $message = sprintf(
                    $this->lng->txt('tst_res_tab_msg_res_after_date'),
                    $date->format($this->user->getDateTimeFormat()->toString())
                );
                break;

            case SettingsResultSummary::SCORE_REPORTING_AFTER_PASSED:
                $message = $this->lng->txt('tst_res_tab_msg_res_after_test_passed');
                break;
        }

        $this->main_tpl->setOnScreenMessage('info', $message);
    }
}
