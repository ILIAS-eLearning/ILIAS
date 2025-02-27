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

use ILIAS\Test\Results\Presentation\TitlesBuilder as ResultsTitlesBuilder;
use ILIAS\Test\Presentation\PrintLayoutProvider;
use ILIAS\UI\Component\ViewControl\Mode as ViewControlMode;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Component\Panel\Sub as SubPanel;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Output class for assessment test evaluation
 *
 * The ilTestEvaluationGUI class creates the output for the ilObjTestGUI
 * class when authors evaluate a test. This saves some heap space because
 * the ilObjTestGUI class will be much smaller then
 *
 * @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author	Björn Heyser <bheyser@databay.de>
 * @author	Maximilian Becker <mbecker@databay.de>
 *
 * @version		$Id$
 *
 * @ingroup components\ILIASTest
 *
 * @ilCtrl_Calls ilTestEvaluationGUI: ilTestPassDetailsOverviewTableGUI
 * @ilCtrl_Calls ilTestEvaluationGUI: ilTestPassDeletionConfirmationGUI
 */
class ilTestEvaluationGUI extends ilTestServiceGUI
{
    private const DEFAULT_CMD = 'outUserListOfAnswerPasses';
    protected ilTestAccess $testAccess;
    protected ilTestProcessLockerFactory $processLockerFactory;

    public function __construct(ilObjTest $object)
    {
        parent::__construct($object);
        $this->participant_access_filter = new ilTestParticipantAccessFilterFactory($this->access);

        $this->processLockerFactory = new ilTestProcessLockerFactory(
            new ilSetting('assessment'),
            $this->db
        );
    }

    public function getTestAccess(): ilTestAccess
    {
        return $this->testAccess;
    }

    public function setTestAccess($testAccess): void
    {
        $this->testAccess = $testAccess;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::DEFAULT_CMD);
        $next_class = $this->ctrl->getNextClass($this);
        $this->ctrl->saveParameter($this, 'sequence');
        $this->ctrl->saveParameter($this, 'active_id');

        switch ($next_class) {
            case 'iltestpassdetailsoverviewtablegui':
                $tableGUI = new ilTestPassDetailsOverviewTableGUI($this->ctrl, $this, 'outUserPassDetails');
                $this->ctrl->forwardCommand($tableGUI);
                break;

            default:
                if (in_array($cmd, ['excel_scored_test_run', 'excel_all_test_runs'])) {
                    $ret = $this->exportEvaluation($cmd);
                } elseif (in_array($cmd, ['excel_all_test_runs_a'])) {
                    $ret = $this->exportAggregatedResults($cmd);
                } else {
                    $ret = $this->$cmd();
                }
                break;
        }
        return $ret;
    }

    /**
     * Returns the ID of a question for evaluation purposes. If a question id and the id of the
     * original question are given, this function returns the original id, otherwise the  question id
     *
     * @return int question or original id
     **/
    public function getEvaluationQuestionId($question_id, $original_id = '')
    {
        if ($original_id > 0) {
            return $original_id;
        } else {
            return $question_id;
        }
    }

    protected function setCss(): void
    {
        $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_print.css'), 'print');
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation('output', 'test_print_hide_content.css'), 'print');
        }
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
    }

    public function printResults(): void
    {
        $this->ctrl->saveParameterByClass(self::class, 'active_ids');
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            PrintLayoutProvider::TEST_CONTEXT_PRINT,
            true
        );

        $selected_active_ids = explode(',', $this->testrequest->strVal('active_ids'));
        $results_panel = $this->ui_factory->panel()->report(
            $this->lng->txt('tst_results'),
            array_map(
                function (string $v): SubPanel {
                    $value = (int) $v;
                    $attempt_id = ilObjTest::_getResultPass($value);
                    $components = $this->buildAttemptComponents($value, $attempt_id, false, true);
                    return $this->ui_factory->panel()->sub(
                        $this->buildResultsTitle(
                            ilObjUser::_lookupFullname($this->object->_getUserIdFromActiveId($value)),
                            $attempt_id
                        ),
                        $components
                    );
                },
                $selected_active_ids
            )
        );

        $this->tpl->setVariable(
            'ADM_CONTENT',
            $this->ui_renderer->render([
                $results_panel,
                $this->ui_factory->legacy()->content('')->withAdditionalOnLoadCode(
                    fn(string $id): string => 'setTimeout(() => {window.print();}, 50)'
                )
            ])
        );
    }

    public function showResults(): void
    {
        $this->ctrl->saveParameterByClass(self::class, 'active_ids');
        $selected_active_ids = explode(',', $this->testrequest->strVal('active_ids'));

        $this->addPrintResultsButtonToToolbar();
        $this->addToggleBestSolutionButtonToToolbar();

        $current_active_id = (int) $selected_active_ids[0];
        if (count($selected_active_ids) > 1
            && ($selected_active_id = $this->testrequest->getActiveId()) > 0
            && array_search($selected_active_id, $selected_active_ids) !== false) {
            $current_active_id = $selected_active_id;
        }

        if ($this->testrequest->isset('attempt')) {
            $attempt_id = $this->testrequest->int('attempt');
        } else {
            $attempt_id = ilObjTest::_getResultPass($current_active_id);
        }

        $results_panel = $this->ui_factory->panel()->report(
            $this->buildResultsTitle(
                ilObjUser::_lookupFullname($this->object->_getUserIdFromActiveId($current_active_id)),
                $attempt_id
            ),
            $this->buildAttemptComponents($current_active_id, $attempt_id, true, false)
        );

        $attempts_ids_array = $this->results_data_factory->getAttemptIdsArrayFor(
            $this->object,
            $current_active_id
        );

        if (count($attempts_ids_array) > 1) {
            $results_panel = $results_panel->withViewControls([
                $this->buildAttemptSwitchingViewControl(
                    $attempts_ids_array,
                    ++$attempt_id
                )
            ]);
        }

        if (count($selected_active_ids) > 1) {
            $this->addParticipantSelectorToToolbar($selected_active_ids, $current_active_id);
        }

        $this->tpl->setVariable(
            'ADM_CONTENT',
            $this->ui_renderer->render($results_panel)
        );

        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTargetByClass(['ilTestParticipantsGUI'])
        );
    }

    public function outUserPassDetails(): void
    {
        $this->tabs->clearSubTabs();
        $this->tabs->setBackTarget($this->lng->txt('tst_results_back_overview'), $this->ctrl->getLinkTarget($this));

        $test_session = $this->test_session_factory->getSession();

        if (!$this->object->getShowPassDetails()) {
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }

        $active_id = $test_session->getActiveId();
        $user_id = $test_session->getUserId();

        $this->ctrl->saveParameter($this, 'pass');
        $pass = $this->testrequest->int('pass');

        $test_result_header_label_builder = new ResultsTitlesBuilder($this->lng, $this->obj_cache);

        $objectives_list = null;

        $consider_hidden_questions = true;
        $consider_optional_questions = true;

        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $consider_hidden_questions = false;
            $consider_optional_questions = true;

            $test_sequence = $this->test_sequence_factory->getSequenceByActiveIdAndPass($active_id, $pass);
            $test_sequence->loadFromDb();
            $test_sequence->loadQuestions();

            if ($this->object->isRandomTest() && !$test_sequence->isAnsweringOptionalQuestionsConfirmed()) {
                $consider_optional_questions = false;
            }

            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($test_session);

            $objectives_list = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $test_sequence);
            $objectives_list->loadObjectivesTitles();

            $test_result_header_label_builder->setObjectiveOrientedContainerId($test_session->getObjectiveOrientedContainerId());
            $test_result_header_label_builder->setUserId($test_session->getUserId());
            $test_result_header_label_builder->setTestObjId($this->object->getId());
            $test_result_header_label_builder->setTestRefId($this->object->getRefId());
            $test_result_header_label_builder->initObjectiveOrientedMode();
        }

        $command_solution_details = '';
        if ($this->object->getShowSolutionListComparison()) {
            $command_solution_details = 'outCorrectSolution';
        }

        $tpl = new ilTemplate('tpl.il_as_tst_pass_details_overview_participants.html', true, true, 'components/ILIAS/Test');

        $this->addPrintButtonToToolbar();

        if ((new ilCertificateDownloadValidator())->isCertificateDownloadable($user_id, $this->object->getId())) {
            $this->addCertificateDownloadButtonToToolbar();
        }

        $tpl->setCurrentBlock('signature');
        $tpl->setVariable('SIGNATURE', $this->getResultsSignature());
        $tpl->parseCurrentBlock();

        if ($this->object->isShowExamIdInTestResultsEnabled()) {
            if ($this->object->isShowExamIdInTestResultsEnabled()) {
                $tpl->setVariable('EXAM_ID', ilObjTest::lookupExamId(
                    $test_session->getActiveId(),
                    $pass
                ));
                $tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            }
        }

        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired() &&
            $this->isGradingMessageRequired() && $this->object->getNrOfTries() == 1) {
            $grading_message_builder = $this->getGradingMessageBuilder($active_id);
            $grading_message_builder->buildMessage();
            $grading_message_builder->sendMessage();
        }

        $data = $this->object->getCompleteEvaluationData();
        $reached = $data->getParticipant($active_id)->getPass($pass)->getReachedPoints();
        $max = $data->getParticipant($active_id)->getPass($pass)->getMaxPoints();
        $percent = $max ? $reached / $max * 100.0 : 0;
        $result = $data->getParticipant($active_id)->getPass($pass)->getReachedPoints() . ' ' . strtolower($this->lng->txt('of')) . ' ' . $data->getParticipant($active_id)->getPass($pass)->getMaxPoints() . ' (' . sprintf('%2.2f', $percent) . ' %' . ')';
        $tpl->setCurrentBlock('total_score');
        $tpl->setVariable('TOTAL_RESULT_TEXT', $this->lng->txt('tst_stat_result_resultspoints'));
        $tpl->setVariable('TOTAL_RESULT', $result);
        $tpl->parseCurrentBlock();

        $tpl->setVariable('TEXT_RESULTS', $test_result_header_label_builder->getPassDetailsHeaderLabel($pass + 1));
        $tpl->setVariable('FORMACTION', $this->ctrl->getFormAction($this));

        $this->populateExamId($tpl, $active_id, (int) $pass);
        $this->populatePassFinishDate($tpl, ilObjTest::lookupLastTestPassAccess($active_id, $pass));

        $this->setCss();

        $settings = $this->results_presentation_factory->getAttemptResultsSettings(
            $this->object,
            true
        );
        $table = $this->results_presentation_factory->getAttemptResultsPresentationTable(
            $this->results_data_factory->getAttemptResultsFor(
                $settings,
                $this->object,
                $active_id,
                $pass,
                true
            ),
            $settings,
            $this->buildResultsTitle($this->user->getFullname(), $pass),
            false
        );

        $tpl->setVariable('LIST_OF_ANSWERS', $table->render());

        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));

        $this->tpl->setContent(
            $tpl->get()
        );
    }

    public function outUserResultsOverview()
    {
        $test_session = $this->test_session_factory->getSession();
        $active_id = $test_session->getActiveId();
        $user_id = $this->user->getId();
        $uname = $this->object->userLookupFullName($user_id, true);

        if (!$this->object->canShowTestResults($test_session)) {
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }

        $templatehead = new ilTemplate('tpl.il_as_tst_results_participants.html', true, true, 'components/ILIAS/Test');
        $template = new ilTemplate('tpl.il_as_tst_results_participant.html', true, true, 'components/ILIAS/Test');

        $this->addPrintButtonToToolbar();

        if ((new ilCertificateDownloadValidator())->isCertificateDownloadable($user_id, $this->object->getId())) {
            $this->addCertificateDownloadButtonToToolbar();
        }

        $test_result_header_label_builder = new ResultsTitlesBuilder($this->lng, $this->obj_cache);
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $test_result_header_label_builder->setObjectiveOrientedContainerId($test_session->getObjectiveOrientedContainerId());
            $test_result_header_label_builder->setUserId($test_session->getUserId());
            $test_result_header_label_builder->setTestObjId($this->object->getId());
            $test_result_header_label_builder->setTestRefId($this->object->getRefId());
            $test_result_header_label_builder->initObjectiveOrientedMode();
        }

        $template->setCurrentBlock('pass_overview');

        $test_passes_selector = new ilTestPassesSelector($this->db, $this->object);
        $test_passes_selector->setActiveId($test_session->getActiveId());
        $test_passes_selector->setLastFinishedPass($test_session->getLastFinishedPass());

        $pass_overview_table_gui = $this->buildPassOverviewTableGUI($this);
        $pass_overview_table_gui->setActiveId($test_session->getActiveId());
        $pass_overview_table_gui->setResultPresentationEnabled(true);
        if ($this->object->getShowPassDetails()) {
            $pass_overview_table_gui->setPassDetailsCommand('outUserPassDetails');
        }
        if ($this->object->isPassDeletionAllowed()) {
            $pass_overview_table_gui->setPassDeletionCommand('confirmDeletePass');
        }
        $pass_overview_table_gui->init();
        $pass_overview_table_gui->setData($this->getPassOverviewTableData($test_session, $test_passes_selector->getReportablePasses(), true));
        $pass_overview_table_gui->setTitle($test_result_header_label_builder->getPassOverviewHeaderLabel());
        $overview = $pass_overview_table_gui->getHTML();
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $lo_status = new ilTestLearningObjectivesStatusGUI(
                $this->lng,
                $this->ctrl,
                $this->ui_factory,
                $this->ui_renderer,
                $this->testrequest
            );
            $lo_status->setCrsObjId($this->getObjectiveOrientedContainer()->getObjId());
            $lo_status->setUsrId($test_session->getUserId());
            $overview .= '<br />' . $lo_status->getHTML();
        }
        $template->setVariable('PASS_OVERVIEW', $overview);
        $template->parseCurrentBlock();

        if ($this->isGradingMessageRequired()) {
            $grading_message_builder = $this->getGradingMessageBuilder($active_id);
            $grading_message_builder->buildMessage();
            $grading_message_builder->sendMessage();
        }

        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($test_session, $active_id, true);

        if (!$this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            if ($this->object->getAnonymity()) {
                $template->setVariable('TEXT_HEADING', $this->lng->txt('tst_result'));
            } else {
                $template->setVariable('TEXT_HEADING', sprintf($this->lng->txt('tst_result_user_name'), $uname));
                $template->setVariable('USER_DATA', $user_data);
            }
        }

        $this->setCss();
        $templatehead->setVariable('RESULTS_PARTICIPANT', $template->get());
        $this->tpl->setContent($templatehead->get());
    }

    public function outUserListOfAnswerPasses()
    {
        if (!$this->object->getShowSolutionPrintview()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('no_permission'), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }

        $template = new ilTemplate('tpl.il_as_tst_info_list_of_answers.html', true, true, 'components/ILIAS/Test');

        $user_id = $this->user->getId();

        $test_session = $this->test_session_factory->getSession();
        $active_id = $test_session->getActiveId();

        if ($this->testrequest->isset('attempt')) {
            $attempt = $this->testrequest->int('attempt');
        } else {
            $attempt = \ilObjTest::_getResultPass($test_session->getActiveId());
        }

        $test_passes_selector = new ilTestPassesSelector($this->db, $this->object);
        $test_passes_selector->setActiveId($test_session->getActiveId());
        $test_passes_selector->setLastFinishedPass($test_session->getLastFinishedPass());

        if (count($test_passes_selector->getClosedPasses()) > 1) {
            $this->addAttemptSwitchingViewControlToToolbar($test_passes_selector->getClosedPasses(), $attempt);
        }

        $this->addPrintButtonToToolbar();

        $test_result_header_label_builder = new ResultsTitlesBuilder($this->lng, $this->obj_cache);
        $test_result_header_label_builder->setAttemptLastAccessDate(
            (new \DateTimeImmutable(
                '@' . ilObjTest::lookupLastTestPassAccess($test_session->getActiveId(), $attempt)
            ))->setTimezone(new \DateTimeZone($this->user->getTimeZone()))
            ->format($this->user->getDateTimeFormat()->toString())
        );

        $objectives_list = null;
        if ($this->getObjectiveOrientedContainer()?->isObjectiveOrientedPresentationRequired()) {
            $test_sequence = $this->test_sequence_factory->getSequenceByActiveIdAndPass($active_id, $attempt);
            $test_sequence->loadFromDb();
            $test_sequence->loadQuestions();

            $objectivesAdapter = ilLOTestQuestionAdapter::getInstance($test_session);

            $objectives_list = $this->buildQuestionRelatedObjectivesList($objectivesAdapter, $test_sequence);
            $objectives_list->loadObjectivesTitles();

            $test_result_header_label_builder->setObjectiveOrientedContainerId($test_session->getObjectiveOrientedContainerId());
            $test_result_header_label_builder->setUserId($test_session->getUserId());
            $test_result_header_label_builder->setTestObjId($this->object->getId());
            $test_result_header_label_builder->setTestRefId($this->object->getRefId());
            $test_result_header_label_builder->initObjectiveOrientedMode();
        }

        $result_array = $this->object->getTestResult(
            $active_id,
            $attempt,
            false,
            !$this->getObjectiveOrientedContainer()?->isObjectiveOrientedPresentationRequired()
        );

        $signature = $this->getResultsSignature();
        $show_all_answers = true;
        if ($this->object->isExecutable($test_session, $user_id)) {
            $show_all_answers = false;
        }
        $this->setContextResultPresentation(false);
        $answers = $this->getPassListOfAnswers(
            $result_array,
            $active_id,
            $attempt,
            false,
            $show_all_answers,
            false,
            false,
            false,
            $objectives_list,
            $test_result_header_label_builder
        );
        $template->setVariable('PASS_DETAILS', $answers);

        $user_data = $this->getAdditionalUsrDataHtmlAndPopulateWindowTitle($test_session, $active_id, true);
        $template->setVariable('USER_DATA', $user_data);
        if (strlen($signature)) {
            $template->setVariable('SIGNATURE', $signature);
        }
        if (!is_null($attempt) && $this->object->isShowExamIdInTestResultsEnabled()) {
            $template->setCurrentBlock('exam_id_footer');
            $template->setVariable('EXAM_ID_VAL', ilObjTest::lookupExamId(
                $test_session->getActiveId(),
                $attempt
            ));
            $template->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $template->parseCurrentBlock();
        }

        $this->setCss();
        $this->tpl->setVariable('ADM_CONTENT', $template->get());
    }

    public function outCertificate()
    {
        $ilUserCertificateRepository = new ilUserCertificateRepository($this->db, $this->logger->getComponentLogger());
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository);

        $pdfAction = new ilCertificatePdfAction(
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf($this->user->getId(), $this->object->getId());
    }

    public function confirmDeletePass()
    {
        if ($this->testrequest->isset('context') && strlen($this->testrequest->raw('context'))) {
            $context = $this->testrequest->raw('context');
        } else {
            $context = ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW;
        }

        if (!$this->object->isPassDeletionAllowed()) {
            $this->redirectToPassDeletionContext($context);
        }

        $confirm = new ilTestPassDeletionConfirmationGUI($this->ctrl, $this->lng, $this);
        $confirm->build($this->testrequest->getActiveId('active_id'), $this->testrequest->int('pass'), $context);

        $this->tpl->setContent($this->ctrl->getHTML($confirm));
    }

    public function cancelDeletePass(): void
    {
        $this->redirectToPassDeletionContext($this->testrequest->strVal('context'));
    }

    private function redirectToPassDeletionContext(string $context): void
    {
        switch ($context) {
            case ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW:

                $this->ctrl->redirect($this, 'outUserResultsOverview');

                // no break
            case ilTestPassDeletionConfirmationGUI::CONTEXT_INFO_SCREEN:
                $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }
    }

    public function performDeletePass(): void
    {
        $context = $this->testrequest->strVal('context') ?? ilTestPassDeletionConfirmationGUI::CONTEXT_PASS_OVERVIEW;
        $active_fi = $this->testrequest->int('active_id');
        $pass = $this->testrequest->int('pass');

        if (!$this->object->isPassDeletionAllowed()) {
            $this->redirectToPassDeletionContext($context);
        }

        $ilDB = $this->db;

        if ($active_fi === 0 || !$this->testrequest->isset('pass')) {
            $this->ctrl->redirect($this, 'outUserResultsOverview');
        }

        if ($pass === ilObjTest::_getResultPass($active_fi)) {
            $this->ctrl->redirect($this, 'outUserResultsOverview');
        }

        // Get information
        $result = $ilDB->query("
				SELECT tst_active.tries, tst_active.last_finished_pass, tst_sequence.pass
				FROM tst_active
				LEFT JOIN tst_sequence
				ON tst_sequence.active_fi = tst_active.active_id
				AND tst_sequence.pass = tst_active.tries
				WHERE tst_active.active_id = {$ilDB->quote($active_fi, 'integer')}
			");

        $row = $ilDB->fetchAssoc($result);

        $tries = $row['tries'];
        $lastFinishedPass = is_numeric($row['last_finished_pass']) ? $row['last_finished_pass'] : -1;

        if ($pass < $lastFinishedPass) {
            $isActivePass = false;
            $must_renumber = true;
        } elseif ($pass == $lastFinishedPass) {
            $isActivePass = false;

            if ($tries == $row['pass']) {
                $must_renumber = true;
            } else {
                $must_renumber = false;
            }
        } elseif ($pass == $row['pass']) {
            $isActivePass = true;
            $must_renumber = false;
        } else {
            throw new ilTestException('This should not happen, please contact Bjoern Heyser to clean up this pass salad!');
        }

        if ($isActivePass) {
            $this->ctrl->redirect($this, 'outUserResultsOverview');
        }

        if ($pass == 0 && (
            ($lastFinishedPass == 0 && $tries == 1 && $tries != $row['pass'])
                    || ($isActivePass == true) // should be equal to || ($lastFinishedPass == -1 && $tries == 0)
        )) {
            $last_pass = true;
        } else {
            $last_pass = false;
        }

        // Work on tables:
        // tst_active
        if ($last_pass) {
            $ilDB->manipulate(
                'DELETE
					FROM tst_active
					WHERE active_id = ' . $ilDB->quote($active_fi, 'integer')
            );
        } elseif (!$isActivePass) {
            $ilDB->manipulate(
                'UPDATE tst_active
					SET tries = ' . $ilDB->quote($tries - 1, 'integer') . ',
					last_finished_pass = ' . $ilDB->quote($lastFinishedPass - 1, 'integer') . '
					WHERE active_id = ' . $ilDB->quote($active_fi, 'integer')
            );
        }
        // tst_manual_fb
        $ilDB->manipulate(
            'DELETE
				FROM tst_manual_fb
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_manual_fb
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
            );
        }

        // tst_mark -> nothing to do
        //
        // tst_pass_result
        $ilDB->manipulate(
            'DELETE
				FROM tst_pass_result
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_pass_result
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
            );
        }

        $ilDB->manipulate(
            'DELETE
				FROM tst_sequence
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_sequence
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
            );
        }

        // tst_solutions
        $ilDB->manipulate(
            'DELETE
				FROM tst_solutions
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_solutions
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
            );
        }

        // tst_test_result
        $ilDB->manipulate(
            'DELETE
				FROM tst_test_result
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_test_result
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
            );
        }

        // qpl_hint_tracking
        $ilDB->manipulate(
            'DELETE
				FROM qpl_hint_tracking
				WHERE qhtr_active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND qhtr_pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE qpl_hint_tracking
				SET qhtr_pass = qhtr_pass - 1
				WHERE qhtr_active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND qhtr_pass > ' . $ilDB->quote($pass, 'integer')
            );
        }

        // tst_test_rnd_qst -> nothing to do

        // tst_times
        $ilDB->manipulate(
            'DELETE
				FROM tst_times
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass = ' . $ilDB->quote($pass, 'integer')
        );

        if ($must_renumber) {
            $ilDB->manipulate(
                'UPDATE tst_times
				SET pass = pass - 1
				WHERE active_fi = ' . $ilDB->quote($active_fi, 'integer') . '
				AND pass > ' . $ilDB->quote($pass, 'integer')
            );
        }

        $this->object->updateTestResultCache((int) $active_fi);

        $this->redirectToPassDeletionContext($context);
    }

    protected function getFilteredTestResult(
        int $active_id,
        int $pass,
        bool $consider_hidden_questions,
        bool $consider_optional_questions = true
    ): array {
        $component_repository = $this->component_repository;
        $ilDB = $this->db;

        $result_data = $this->object->getTestResult($active_id, $pass, false, $consider_hidden_questions);
        $question_ids = [];
        foreach ($result_data as $result_item_key => $result_item_value) {
            if ($result_item_key === 'test' || $result_item_key === 'pass') {
                continue;
            }

            $question_ids[] = $result_item_value['qid'];
        }

        $table_gui = $this->buildPassDetailsOverviewTableGUI($this, 'outUserPassDetails');

        $question_list = new ilAssQuestionList($ilDB, $this->lng, $this->refinery, $component_repository);
        $question_list->setParentObjId($this->object->getId());
        $question_list->setParentObjectType($this->object->getType());
        $question_list->setIncludeQuestionIdsFilter($question_ids);

        foreach ($table_gui->getFilterItems() as $item) {
            if (substr($item->getPostVar(), 0, strlen('tax_')) == 'tax_') {
                $v = $item->getValue();

                if (is_array($v) && count($v) && !(int) $v[0]) {
                    continue;
                }

                $tax_id = substr($item->getPostVar(), strlen('tax_'));
                $question_list->addTaxonomyFilter($tax_id, $item->getValue(), $this->object->getId(), 'tst');
            } elseif ($item->getValue() !== false) {
                $question_list->addFieldFilter($item->getPostVar(), $item->getValue());
            }
        }

        $question_list->load();

        $filtered_test_result = [];

        foreach ($result_data as $result_item_key => $result_item_value) {
            if ($result_item_key === 'test' || $result_item_key === 'pass') {
                continue;
            }

            if (!$question_list->isInList($result_item_value['qid'])) {
                continue;
            }

            $filtered_test_result[] = $result_item_value;
        }

        return $filtered_test_result;
    }

    protected function redirectBackToParticipantsScreen()
    {
        $this->ctrl->redirectByClass(ilTestParticipantsGUI::class);
    }

    protected function sendPage(string $page)
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($page)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    protected function buildResultsTitle(string $fullname, int $pass): string
    {
        if ($this->object->getAnonymity()) {
            return sprintf(
                $this->lng->txt('tst_eval_results_by_pass_lo'),
                $pass + 1
            );
        }
        return sprintf(
            $this->lng->txt('tst_result_user_name_pass'),
            $pass + 1,
            $fullname
        );
    }

    private function buildAttemptComponents(
        int $active_id,
        int $attempt_id,
        bool $with_test_results_overview,
        bool $for_print
    ): array {
        $settings = $this->results_presentation_factory->getAttemptResultsSettings(
            $this->object,
            false
        );
        $attempt_overview = $this->ui_factory->panel()->sub(
            $this->lng->txt('question_summary'),
            $this->results_data_factory->getAttemptOverviewFor(
                $settings,
                $this->object,
                $active_id,
                $attempt_id
            )->getAsDescriptiveListing(
                $this->lng,
                $this->ui_factory,
                [
                    'timezone' => new DateTimeZone($this->user->getTimeZone()),
                    'datetimeformat' => $this->user->getDateTimeFormat()->toString()
                ]
            )
        );

        if ($with_test_results_overview) {
            $attempt_overview = $attempt_overview->withFurtherInformation(
                $this->ui_factory->card()->standard($this->lng->txt('overview'))->withSections([
                    $this->results_data_factory->getOverviewDataForTest($this->object)
                        ->getAsDescriptiveListing(
                            $this->lng,
                            $this->ui_factory
                        )
                ])
            );
        }

        $results_presentation_table = $this->results_presentation_factory->getAttemptResultsPresentationTable(
            $this->results_data_factory->getAttemptResultsFor(
                $settings,
                $this->object,
                $active_id,
                $attempt_id,
                false
            ),
            $settings,
            '',
            $for_print
        )->getTableComponent();

        if ($for_print) {
            $signal = $results_presentation_table->getExpandAllSignal();
            $results_presentation_table = [
                $results_presentation_table,
                $this->ui_factory->legacy('')->withAdditionalOnLoadCode(
                    fn(string $id): string => "$(document).trigger('{$signal->getId()}',"
                        . '{"options" : ' . json_encode($signal->getOptions()) . '}); '
                )
            ];
        }



        $attempt_details = $this->ui_factory->panel()->sub(
            $this->lng->txt('details'),
            $results_presentation_table
        );

        return [$attempt_overview, $attempt_details];
    }

    private function addPrintResultsButtonToToolbar(): void
    {
        $link = $this->ctrl->getLinkTargetByClass(self::class, 'printResults');
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('print'),
                ''
            )->withOnLoadCode(
                fn($id): string => "document.getElementById('{$id}').addEventListener('click', "
                    . "(e) => {window.open('{$link}');}"
                    . ');'
            )
        );
    }

    private function addPrintButtonToToolbar(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('print'),
                ''
            )->withOnLoadCode(
                fn($id): string => "document.getElementById('{$id}').addEventListener('click', "
                    . "()=>{window.print();}"
                    . ');'
            )
        );
    }

    private function addCertificateDownloadButtonToToolbar(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('certificate'),
                $this->ctrl->getLinkTargetByClass(self::class, 'outCertificate')
            )
        );
    }

    private function addToggleBestSolutionButtonToToolbar(): void
    {
        if ($this->testrequest->isset('show_best_solutions')) {
            ilSession::set(
                'tst_results_show_best_solutions',
                $this->testrequest->int('show_best_solutions') === 1
            );
        }

        if (ilSession::get('tst_results_show_best_solutions')) {
            $this->ctrl->setParameter($this, 'show_best_solutions', '0');
            $label = $this->lng->txt('tst_btn_hide_best_solutions');
        } else {
            $this->ctrl->setParameter($this, 'show_best_solutions', '1');
            $label = $this->lng->txt('tst_btn_show_best_solutions');
        }

        $this->toolbar->addSeparator();
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $label,
                $this->ctrl->getLinkTargetByClass(self::class, $this->ctrl->getCmd(self::DEFAULT_CMD))
            )
        );
        $this->ctrl->clearParameters($this, 'show_best_solutions');
    }

    private function addParticipantSelectorToToolbar(
        array $selected_active_ids,
        int $current_active_id
    ): void {
        $this->toolbar->addSeparator();
        $this->toolbar->addComponent(
            $this->ui_factory->dropdown()
                ->standard(
                    $this->buildParticipantSelectorArray($selected_active_ids, $current_active_id)
                )->withLabel($this->lng->txt('tst_res_jump_to_participant_hint_opt'))
        );
    }

    private function buildParticipantSelectorArray(
        array $selected_active_ids,
        int $current_active_id
    ): array {
        $this->ctrl->setParameterByClass(self::class, 'active_ids', implode(',', $selected_active_ids));
        unset($selected_active_ids[array_search($current_active_id, $selected_active_ids)]);
        $available_user_links = array_map(
            function (int $v): StandardLink {
                $this->ctrl->setParameterByClass(self::class, 'active_id', $v);
                return $this->ui_factory->link()->standard(
                    ilObjUser::_lookupFullname($this->object->_getUserIdFromActiveId($v)),
                    $this->ctrl->getLinkTargetByClass(self::class, 'showResults')
                );
            },
            $selected_active_ids
        );
        $this->ctrl->clearParameterByClass(self::class, 'active_id');
        $this->ctrl->clearParameterByClass(self::class, 'active_ids');
        return $available_user_links;
    }

    private function addAttemptSwitchingViewControlToToolbar(
        array $available_attempts,
        int $selected_attempt
    ): void {
        $selected_attempt++;
        $this->toolbar->addComponent(
            $this->buildAttemptSwitchingViewControl(
                $available_attempts,
                $selected_attempt
            )
        );
        $this->ctrl->clearParameterByClass(self::class, 'attempt');
    }

    private function buildAttemptSwitchingViewControl(
        array $available_attempts,
        int $selected_attempt
    ): ViewControlMode {
        return $this->ui_factory->viewControl()->mode(
            array_reduce(
                $available_attempts,
                function (array $c, int $v): array {
                    $this->ctrl->setParameterByClass(self::class, 'attempt', $v);
                    $attempt = $v + 1;
                    $c["{$this->lng->txt('tst_attempt')} {$attempt}"] = $this
                        ->ctrl->getLinkTargetByClass(self::class, $this->ctrl->getCmd(self::DEFAULT_CMD));
                    return $c;
                },
                []
            ),
            $this->lng->txt('select_attempt')
        )->withActive("{$this->lng->txt('tst_attempt')} {$selected_attempt}");
    }
}
