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
use ILIAS\Test\Results\Data\Factory as ResultsDataFactory;
use ILIAS\Test\Results\Presentation\Factory as ResultsPresentationFactory;
use ILIAS\Test\Results\Presentation\TitlesBuilder as ResultsTitlesBuilder;
use ILIAS\Test\Questions\Properties\Repository as TestQuestionsRepository;
use ILIAS\Test\Results\Data\Repository as TestResultRepository;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\GlobalScreen\Services as GlobalScreenServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Skill\Service\SkillService;

/**
* Service GUI class for tests. This class is the parent class for all
* service classes which are called from ilObjTestGUI. This is mainly
* done to reduce the size of ilObjTestGUI to put command service functions
* into classes that could be called by ilCtrl.
*
* @ilCtrl_IsCalledBy ilTestServiceGUI: ilObjTestGUI
*
* @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup components\ILIASTest
*/
class ilTestServiceGUI
{
    protected readonly RequestDataCollector $testrequest;
    protected readonly GeneralQuestionPropertiesRepository $questionrepository;
    protected readonly TestQuestionsRepository $testquestionsrepository;
    protected ?ilTestService $service = null;
    protected readonly ilDBInterface $db;
    protected readonly ilLanguage $lng;
    protected readonly TestLogger $logger;
    protected readonly ilHelpGUI $help;
    protected readonly ilRbacSystem $rbac_system;

    /**
     * sk 2023-08-01: We need this union type, even if it is wrong! To change this
     * @todo we have to fix the rendering of the feedback modal in
     * `ilTestPlayerAbstractGUI::populateIntantResponseModal()`.
     */
    protected ilGlobalTemplateInterface|ilTemplate $tpl;
    protected readonly ilErrorHandling $error;
    protected ilAccess $access;
    protected readonly HTTPServices $http;
    protected readonly ilCtrlInterface $ctrl;
    protected readonly ilToolbarGUI $toolbar;
    protected readonly ilTabsGUI $tabs;
    protected readonly ilObjectDataCache $obj_cache;
    protected readonly ilComponentRepository $component_repository;
    protected readonly ilObjUser $user;
    protected readonly ArrayBasedRequestWrapper $post_wrapper;
    protected readonly ilNavigationHistory $navigation_history;
    protected readonly Refinery $refinery;
    protected readonly UIFactory $ui_factory;
    protected readonly UIRenderer $ui_renderer;
    protected readonly SkillService $skills_service;
    protected readonly ilTestShuffler $shuffler;
    protected readonly ResultsDataFactory $results_data_factory;
    protected readonly ResultsPresentationFactory $results_presentation_factory;

    protected readonly ILIAS $ilias;
    protected readonly ilSetting $settings;
    protected readonly GlobalScreenServices $global_screen;
    protected readonly ilTree $tree;
    protected int $ref_id;

    protected ?ilTestSessionFactory $test_session_factory = null;
    protected ?ilTestSequenceFactory $test_sequence_factory = null;
    protected ?ilTestParticipantData $participantData = null;
    protected TestResultRepository $test_pass_result_repository;

    protected ilTestParticipantAccessFilterFactory $participant_access_filter;

    private ?ilTestObjectiveOrientedContainer $objective_oriented_container;

    private bool $contextResultPresentation = true;

    public function isContextResultPresentation(): bool
    {
        return $this->contextResultPresentation;
    }

    public function setContextResultPresentation(bool $contextResultPresentation)
    {
        $this->contextResultPresentation = $contextResultPresentation;
    }

    /**
     * The constructor takes the test object reference as parameter
     *
     * @param object $a_object Associated ilObjTest class
     * @access public
     */
    public function __construct(
        protected ilObjTest $object
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->tpl = $DIC['tpl'];
        $this->error = $DIC['ilErr'];
        $this->access = $DIC['ilAccess'];
        $this->http = $DIC['http'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->user = $DIC->user();
        $this->ilias = $DIC['ilias'];
        $this->settings = $DIC['ilSetting'];
        $this->global_screen = $DIC['global_screen'];
        $this->tree = $DIC['tree'];
        $this->db = $DIC['ilDB'];
        $this->component_repository = $DIC['component.repository'];
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->tabs = $DIC['ilTabs'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->help = $DIC['ilHelp'];
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->obj_cache = $DIC['ilObjDataCache'];
        $this->skills_service = $DIC->skills();
        $this->post_wrapper = $DIC->http()->wrapper()->post();

        $local_dic = $object->getLocalDIC();
        $this->testrequest = $local_dic['request_data_collector'];
        $this->logger = $local_dic['logging.logger'];
        $this->participant_access_filter = $local_dic['participant.access_filter.factory'];
        $this->shuffler = $local_dic['shuffler'];
        $this->results_data_factory = $local_dic['results.data.factory'];
        $this->results_presentation_factory = $local_dic['results.presentation.factory'];
        $this->questionrepository = $local_dic['question.general_properties.repository'];
        $this->testquestionsrepository = $local_dic['questions.properties.repository'];
        $this->test_pass_result_repository = $local_dic['results.data.test_result_repository'];

        $this->service = new ilTestService($this->object, $this->db, $this->questionrepository);

        $this->lng->loadLanguageModule('cert');
        $this->ref_id = $this->object->getRefId();
        $this->test_session_factory = new ilTestSessionFactory($this->object, $this->db, $this->user);
        $this->test_sequence_factory = new ilTestSequenceFactory($this->object, $this->db, $this->questionrepository);
        $this->objective_oriented_container = null;
    }

    public function setParticipantData(ilTestParticipantData $participantData): void
    {
        $this->participantData = $participantData;
    }

    public function getParticipantData(): ilTestParticipantData
    {
        return $this->participantData;
    }

    /**
     * @param array<int> $passes An integer array of test runs
     * @return array<mixed>
     */
    public function getPassOverviewTableData(
        ilTestSession $test_session,
        array $passes,
        bool $with_results
    ): array {
        $data = [];

        $objective_oriented_presentation = $this->getObjectiveOrientedContainer()
            ?->isObjectiveOrientedPresentationRequired() ?? false;

        if ($objective_oriented_presentation) {
            $consider_hidden_questions = false;

            $objectives_adapter = ilLOTestQuestionAdapter::getInstance($test_session);
        } else {
            $consider_hidden_questions = true;
        }

        $scored_pass = \ilObjTest::_getResultPass($test_session->getActiveId());

        $question_hint_request_register = ilAssQuestionHintTracking::getRequestRequestStatisticDataRegisterByActiveId(
            $test_session->getActiveId()
        );

        foreach ($passes as $pass) {
            $row = [
                'scored' => false,
                'pass' => $pass,
                'date' => ilObjTest::lookupLastTestPassAccess($test_session->getActiveId(), $pass)
            ];
            $considerOptionalQuestions = true;

            if ($objective_oriented_presentation) {
                $test_sequence = $this->test_sequence_factory->getSequenceByActiveIdAndPass($test_session->getActiveId(), $pass);
                $test_sequence->loadFromDb();
                $test_sequence->loadQuestions();

                if ($this->object->isRandomTest() && !$test_sequence->isAnsweringOptionalQuestionsConfirmed()) {
                    $considerOptionalQuestions = false;
                }

                $test_sequence->setConsiderHiddenQuestionsEnabled($consider_hidden_questions);
                $test_sequence->setConsiderOptionalQuestionsEnabled($considerOptionalQuestions);

                $objectives_list = $this->buildQuestionRelatedObjectivesList($objectives_adapter, $test_sequence);
                $objectives_list->loadObjectivesTitles();

                $row['objectives'] = $objectives_list->getUniqueObjectivesStringForQuestions($test_sequence->getUserSequenceQuestions());
            }

            if (!$with_results) {
                $data[] = $row;
                continue;
            }

            $result_array = $this->object->getTestResult(
                $test_session->getActiveId(),
                $pass,
                false,
                $consider_hidden_questions,
                $considerOptionalQuestions
            );

            foreach ($result_array as $result_struct_key => $question) {
                if ($result_struct_key === 'test'
                    || $result_struct_key === 'pass'
                    || $result_array[$result_struct_key]['requested_hints'] !== null) {
                    continue;
                }

                $request_data = $question_hint_request_register->getRequestByTestPassIndexAndQuestionId($pass, $question['qid']);

                if ($request_data === null) {
                    continue;
                }

                $result_array['pass']['total_requested_hints'] += $request_data->getRequestsCount();
                $result_array[$result_struct_key]['requested_hints'] = $request_data->getRequestsCount();
                $result_array[$result_struct_key]['hint_points'] = $request_data->getRequestsPoints();
            }

            if (!$result_array['pass']['total_max_points']) {
                $row['percentage'] = 0;
            } else {
                $row['percentage'] = ($result_array['pass']['total_reached_points'] / $result_array['pass']['total_max_points']) * 100;
            }

            $row['max_points'] = $result_array['pass']['total_max_points'];
            $row['reached_points'] = $result_array['pass']['total_reached_points'];
            $row['scored'] = ($pass == $scored_pass);
            $row['num_workedthrough_questions'] = $result_array['pass']['num_workedthrough'];
            $row['num_questions_total'] = $result_array['pass']['num_questions_total'];

            if ($this->object->isOfferingQuestionHintsEnabled()) {
                $row['hints'] = $result_array['pass']['total_requested_hints'];
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * @param ilTestObjectiveOrientedContainer $objective_oriented_container
     */
    public function setObjectiveOrientedContainer(ilTestObjectiveOrientedContainer $objective_oriented_container)
    {
        $this->objective_oriented_container = $objective_oriented_container;
    }

    /**
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveOrientedContainer(): ?ilTestObjectiveOrientedContainer
    {
        return $this->objective_oriented_container;
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                $ret = &$this->$cmd();
                break;
        }
        return $ret;
    }

    public function buildPassOverviewTableGUI(ilTestEvaluationGUI $target_gui): ilTestPassOverviewTableGUI
    {
        $table = new ilTestPassOverviewTableGUI($target_gui, '');

        $table->setPdfPresentationEnabled(
            $this->testrequest->isset('pdf') && $this->testrequest->raw('pdf') == 1
        );

        $table->setObjectiveOrientedPresentationEnabled(
            $this->getObjectiveOrientedContainer()?->isObjectiveOrientedPresentationRequired() ?? false
        );

        return $table;
    }

    /**
     * Returns the list of answers of a users test pass
     *
     * @param array $result_array An array containing the results of the users test pass (generated by ilObjTest::getTestResult)
     * @param integer $active_id Active ID of the active user
     * @param integer $pass Test pass
     * @param boolean $show_solutions TRUE, if the solution output should be shown in the answers, FALSE otherwise
     * @return string HTML code of the list of answers
     * @access public
     */
    public function getPassListOfAnswers(
        &$result_array,
        $active_id,
        $pass,
        $show_solutions = false,
        $only_answered_questions = false,
        $show_question_only = false,
        $show_reached_points = false,
        $anchorNav = false,
        ?ilTestQuestionRelatedObjectivesList $objectives_list = null,
        ?ResultsTitlesBuilder $testResultHeaderLabelBuilder = null
    ): string {
        $maintemplate = new ilTemplate('tpl.il_as_tst_list_of_answers.html', true, true, 'components/ILIAS/Test');

        $counter = 1;
        // output of questions with solutions
        foreach ($result_array as $question_data) {
            if (!array_key_exists('workedthrough', $question_data)) {
                $question_data['workedthrough'] = 0;
            }
            if (!array_key_exists('qid', $question_data)) {
                $question_data['qid'] = -1;
            }

            if (($question_data['workedthrough'] == 1) || ($only_answered_questions == false)) {
                $template = new ilTemplate('tpl.il_as_qpl_question_printview.html', true, true, 'components/ILIAS/TestQuestionPool');
                $question_id = $question_data["qid"] ?? null;
                if ($question_id !== null
                    && $question_id !== -1
                    && is_numeric($question_id)) {
                    $maintemplate->setCurrentBlock('printview_question');
                    $question_gui = $this->object->createQuestionGUI("", $question_id);

                    $question_gui->getObject()->setShuffler($this->shuffler->getAnswerShuffleFor(
                        (int) $question_id,
                        (int) $active_id,
                        (int) $pass
                    ));
                    if (is_object($question_gui)) {
                        if ($anchorNav) {
                            $template->setCurrentBlock('block_id');
                            $template->setVariable('BLOCK_ID', "detailed_answer_block_act_{$active_id}_qst_{$question_id}");
                            $template->parseCurrentBlock();

                            $template->setCurrentBlock('back_anchor');
                            $template->setVariable('HREF_BACK_ANCHOR', "#pass_details_tbl_row_act_{$active_id}_qst_{$question_id}");
                            $template->setVariable('TXT_BACK_ANCHOR', $this->lng->txt('tst_back_to_question_list'));
                            $template->parseCurrentBlock();
                        }

                        if ($show_reached_points) {
                            $template->setCurrentBlock("result_points");
                            $template->setVariable("RESULT_POINTS", $this->lng->txt("tst_reached_points") . ": " . $question_gui->getObject()->getReachedPoints($active_id, $pass) . " " . $this->lng->txt("of") . " " . $question_gui->getObject()->getMaximumPoints());
                            $template->parseCurrentBlock();
                        }
                        $template->setVariable("COUNTER_QUESTION", $counter . ". ");
                        $template->setVariable("TXT_QUESTION_ID", $this->lng->txt('question_id_short'));
                        $template->setVariable("QUESTION_ID", $question_gui->getObject()->getId());
                        $template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->getObject()->getTitle()));

                        if ($objectives_list !== null) {
                            $objectives = $this->lng->txt('tst_res_lo_objectives_header') . ': ';
                            $objectives .= $objectives_list->getQuestionRelatedObjectiveTitles($question_gui->getObject()->getId());
                            $template->setVariable("OBJECTIVES", $objectives);
                        }

                        $show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? true : false;

                        $show_feedback = $this->isContextResultPresentation() && $this->object->getShowSolutionFeedback();
                        $show_best_solution = $this->isContextResultPresentation() && $show_solutions;
                        $show_graphical_output = $this->isContextResultPresentation();

                        if ($show_best_solution) {
                            $compare_template = new ilTemplate('tpl.il_as_tst_answers_compare.html', true, true, 'components/ILIAS/Test');
                            $test_session = $this->test_session_factory->getSession($active_id);
                            if ($pass <= $test_session->getLastFinishedPass()) {
                                $compare_template->setVariable("HEADER_PARTICIPANT", $this->lng->txt('tst_header_participant'));
                            } else {
                                $compare_template->setVariable("HEADER_PARTICIPANT", $this->lng->txt('tst_header_participant_no_answer'));
                            }

                            $compare_template->setVariable("HEADER_SOLUTION", $this->lng->txt('tst_header_solution'));
                            $result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_graphical_output, false, $show_question_only, $show_feedback);
                            $best_output = $question_gui->getSolutionOutput($active_id, $pass, false, false, $show_question_only, false, true);

                            $compare_template->setVariable('PARTICIPANT', $result_output);
                            $compare_template->setVariable('SOLUTION', $best_output);
                            $template->setVariable('SOLUTION_OUTPUT', $compare_template->get());
                        } else {
                            $result_output = $question_gui->getSolutionOutput($active_id, $pass, $show_graphical_output, false, $show_question_only, $show_feedback);
                            $template->setVariable('SOLUTION_OUTPUT', $result_output);
                        }

                        $maintemplate->setCurrentBlock('printview_question');
                        $maintemplate->setVariable("QUESTION_PRINTVIEW", $template->get());
                        $maintemplate->parseCurrentBlock();
                        $counter++;
                    }
                }
            }
        }

        if ($testResultHeaderLabelBuilder !== null) {
            if ($pass !== null) {
                $headerText = $testResultHeaderLabelBuilder->getListOfAnswersHeaderLabel($pass + 1);
            } else {
                $headerText = $testResultHeaderLabelBuilder->getVirtualListOfAnswersHeaderLabel();
            }
        } else {
            $headerText = '';
        }

        $maintemplate->setVariable('RESULTS_OVERVIEW', $headerText);
        return $maintemplate->get();
    }

    protected function getPassDetailsOverviewTableGUI(
        array $result_array,
        int $active_id,
        int $pass,
        ilTestServiceGUI $target_gui,
        string $target_cmd,
        ?ilTestQuestionRelatedObjectivesList $objectives_list = null,
        bool $multiple_objectives_involved = true
    ): ilTestPassDetailsOverviewTableGUI {
        $this->ctrl->setParameter($target_gui, 'active_id', $active_id);
        $this->ctrl->setParameter($target_gui, 'pass', $pass);

        $table_gui = $this->buildPassDetailsOverviewTableGUI($target_gui, $target_cmd);
        $table_gui->setShowHintCount($this->object->isOfferingQuestionHintsEnabled());

        if ($objectives_list !== null) {
            $table_gui->setQuestionRelatedObjectivesList($objectives_list);
            $table_gui->setObjectiveOrientedPresentationEnabled(true);
        }

        $table_gui->setMultipleObjectivesInvolved($multiple_objectives_involved);

        $table_gui->setActiveId($active_id);
        $table_gui->setShowSuggestedSolution(false);

        $users_question_solutions = [];

        foreach ($result_array as $key => $val) {
            if ($key === 'test' || $key === 'pass') {
                continue;
            }

            if ($this->object->getShowSolutionSuggested() && strlen($val['solution'])) {
                $table_gui->setShowSuggestedSolution(true);
            }

            if (isset($val['pass'])) {
                $table_gui->setPassColumnEnabled(true);
            }

            $users_question_solutions[$key] = $val;
        }

        $table_gui->initColumns();

        $table_gui->setFilterCommand($target_cmd . 'SetTableFilter');
        $table_gui->setResetCommand($target_cmd . 'ResetTableFilter');

        $table_gui->setData($users_question_solutions);

        return $table_gui;
    }

    /**
     * Returns HTML code for a signature field
     *
     * @return string HTML code of the date and signature field for the test results
     * @access public
     */
    public function getResultsSignature(): string
    {
        if ($this->object->getShowSolutionSignature() && !$this->object->getAnonymity()) {
            $template = new ilTemplate("tpl.il_as_tst_results_userdata_signature.html", true, true, "components/ILIAS/Test");
            $template->setVariable("TXT_DATE", $this->lng->txt("date"));
            $old_value = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);
            $template->setVariable("VALUE_DATE", ilDatePresentation::formatDate(new ilDate(time(), IL_CAL_UNIX)));
            ilDatePresentation::setUseRelativeDates($old_value);
            $template->setVariable("TXT_SIGNATURE", $this->lng->txt("tst_signature"));
            $template->setVariable("IMG_SPACER", ilUtil::getImagePath("media/spacer.png"));
            return $template->get();
        } else {
            return "";
        }
    }

    /**
     * Returns the user data for a test results output
     *
     * @param ilTestSession
     * @param integer $user_id The user ID of the user
     * @param boolean $overwrite_anonymity TRUE if the anonymity status should be overwritten, FALSE otherwise
     * @return string HTML code of the user data for the test results
     * @access public
     */
    public function getAdditionalUsrDataHtmlAndPopulateWindowTitle($testSession, $active_id, $overwrite_anonymity = false): string
    {
        if (!is_object($testSession)) {
            throw new InvalidArgumentException('Not an object, expected ilTestSession');
        }
        $template = new ilTemplate("tpl.il_as_tst_results_userdata.html", true, true, "components/ILIAS/Test");
        $user_id = $this->object->_getUserIdFromActiveId($active_id);
        if (strlen(ilObjUser::_lookupLogin($user_id)) > 0) {
            $user = new ilObjUser($user_id);
        } else {
            $user = new ilObjUser();
            $user->setLastname($this->lng->txt('deleted_user'));
        }
        $t = $testSession->getSubmittedTimestamp();
        if (!$t) {
            $t = $this->object->_getLastAccess($testSession->getActiveId());
        }

        if ($this->getObjectiveOrientedContainer()?->isObjectiveOrientedPresentationRequired()) {
            $uname = $this->object->userLookupFullName($user_id, $overwrite_anonymity);
            $template->setCurrentBlock('name');
            $template->setVariable('TXT_USR_NAME', $this->lng->txt("name"));
            $template->setVariable('VALUE_USR_NAME', $uname);
            $template->parseCurrentBlock();
        }

        $title_matric = "";
        if (strlen($user->getMatriculation()) && (($this->object->getAnonymity() == false) || ($overwrite_anonymity))) {
            $template->setCurrentBlock("matriculation");
            $template->setVariable("TXT_USR_MATRIC", $this->lng->txt("matriculation"));
            $template->setVariable("VALUE_USR_MATRIC", $user->getMatriculation());
            $template->parseCurrentBlock();
            $title_matric = " - " . $this->lng->txt("matriculation") . ": " . $user->getMatriculation();
        }

        $invited_user = array_pop($this->object->getInvitedUsers($user_id));
        $title_client = '';
        $template->setVariable("TXT_TEST_TITLE", $this->lng->txt("title"));
        $template->setVariable("VALUE_TEST_TITLE", $this->object->getTitle());

        // change the pagetitle (tab title or title in title bar of window)
        $pagetitle = $this->object->getTitle() . $title_matric . $title_client;
        $this->tpl->setHeaderPageTitle($pagetitle);

        return $template->get();
    }

    /**
     * Returns an output of the solution to an answer compared to the correct solution
     *
     * @param integer $question_id Database ID of the question
     * @param integer $active_id Active ID of the active user
     * @param integer $pass Test pass
     * @return string HTML code of the correct solution comparison
     * @access public
     */
    public function getCorrectSolutionOutput($question_id, $active_id, $pass, ?ilTestQuestionRelatedObjectivesList $objectives_list = null): string
    {
        $ilUser = $this->user;

        $test_id = $this->object->getTestId();
        $question_gui = $this->object->createQuestionGUI("", $question_id);

        $template = new ilTemplate("tpl.il_as_tst_correct_solution_output.html", true, true, "components/ILIAS/Test");
        $show_question_only = ($this->object->getShowSolutionAnswersOnly()) ? true : false;
        $result_output = $question_gui->getSolutionOutput($active_id, $pass, true, false, $show_question_only, $this->object->getShowSolutionFeedback(), false, false, true);
        $best_output = $question_gui->getSolutionOutput($active_id, $pass, false, false, $show_question_only, false, true, false, false);
        if ($this->object->getShowSolutionFeedback() && $this->testrequest->raw('cmd') != 'outCorrectSolution') {
            $specificAnswerFeedback = $question_gui->getSpecificFeedbackOutput(
                $question_gui->getObject()->fetchIndexedValuesFromValuePairs(
                    $question_gui->getObject()->getSolutionValues($active_id, $pass)
                )
            );
            if (strlen($specificAnswerFeedback)) {
                $template->setCurrentBlock("outline_specific_feedback");
                $template->setVariable("OUTLINE_SPECIFIC_FEEDBACK", $specificAnswerFeedback);
                $template->parseCurrentBlock();
            }
        }
        $template->setVariable("TEXT_YOUR_SOLUTION", $this->lng->txt("tst_your_answer_was"));
        $template->setVariable("TEXT_SOLUTION_OUTPUT", $this->lng->txt("tst_your_answer_was")); // Mantis 28646. I don't really know why Ingmar renamed the placeholder, so
        // I set both old and new since the old one is set as well in several places.
        $maxpoints = $question_gui->getObject()->getMaximumPoints();
        if ($maxpoints == 1) {
            $template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->getObject()->getTitle()) . " (" . $maxpoints . " " . $this->lng->txt("point") . ")");
        } else {
            $template->setVariable("QUESTION_TITLE", $this->object->getQuestionTitle($question_gui->getObject()->getTitle()) . " (" . $maxpoints . " " . $this->lng->txt("points") . ")");
        }
        if ($objectives_list !== null) {
            $objectives = $this->lng->txt('tst_res_lo_objectives_header') . ': ';
            $objectives .= $objectives_list->getQuestionRelatedObjectiveTitles($question_gui->getObject()->getId());
            $template->setVariable('OBJECTIVES', $objectives);
        }
        $template->setVariable("SOLUTION_OUTPUT", $result_output);
        $template->setVariable("RECEIVED_POINTS", sprintf($this->lng->txt("you_received_a_of_b_points"), $question_gui->getObject()->getReachedPoints($active_id, $pass), $maxpoints));
        $template->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $template->setVariable("BACKLINK_TEXT", "&lt;&lt; " . $this->lng->txt("back"));
        return $template->get();
    }

    protected function buildPassDetailsOverviewTableGUI(
        ilTestServiceGUI $target_gui,
        string $target_cmd
    ): ilTestPassDetailsOverviewTableGUI {
        return new ilTestPassDetailsOverviewTableGUI($this->ctrl, $target_gui, $target_cmd);
    }

    protected function isGradingMessageRequired(): bool
    {
        $session = $this->test_session_factory->getSession();
        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()
            || $this->object->getScoreSettings()->getScoringSettings()->getPassScoring() === ilObjTest::SCORE_LAST_PASS
                && $session->getLastFinishedPass() < $session->getLastStartedPass()) {
            return false;
        }

        if ($this->object->isShowGradingStatusEnabled()
            || $this->object->isShowGradingMarkEnabled()) {
            return true;
        }

        return false;
    }

    protected function getGradingMessageBuilder(int $active_id): ilTestGradingMessageBuilder
    {
        $gradingMessageBuilder = new ilTestGradingMessageBuilder($this->lng, $this->tpl, $this->object);

        $gradingMessageBuilder->setActiveId($active_id);

        return $gradingMessageBuilder;
    }

    protected function buildQuestionRelatedObjectivesList(
        ilLOTestQuestionAdapter $objectives_adapter,
        ilTestQuestionSequence $test_sequence
    ): ilTestQuestionRelatedObjectivesList {
        $questionRelatedObjectivesList = new ilTestQuestionRelatedObjectivesList();

        $objectives_adapter->buildQuestionRelatedObjectiveList($test_sequence, $questionRelatedObjectivesList);

        return $questionRelatedObjectivesList;
    }

    protected function getFilteredTestResult(
        int $active_id,
        int $pass,
        bool $considerHiddenQuestions,
        bool $considerOptionalQuestions
    ): array {
        $ilDB = $this->db;
        $component_repository = $this->component_repository;

        $table_gui = $this->buildPassDetailsOverviewTableGUI($this, 'outUserPassDetails');

        $questionList = new ilAssQuestionList($ilDB, $this->lng, $this->refinery, $component_repository);

        $questionList->setParentObjIdsFilter([$this->object->getId()]);
        $questionList->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES);

        foreach ($table_gui->getFilterItems() as $item) {
            if (substr($item->getPostVar(), 0, strlen('tax_')) == 'tax_') {
                $v = $item->getValue();

                if (is_array($v) && count($v) && !(int) $v[0]) {
                    continue;
                }

                $taxId = substr($item->getPostVar(), strlen('tax_'));
                $questionList->addTaxonomyFilter($taxId, $item->getValue(), $this->object->getId(), 'tst');
            } elseif ($item->getValue() !== false) {
                $questionList->addFieldFilter($item->getPostVar(), $item->getValue());
            }
        }

        $questionList->load();

        $filteredTestResult = [];

        $resultData = $this->object->getTestResult($active_id, $pass, false, $considerHiddenQuestions, $considerOptionalQuestions);

        foreach ($resultData as $resultItemKey => $resultItemValue) {
            if ($resultItemKey === 'test' || $resultItemKey === 'pass') {
                continue;
            }

            if (!$questionList->isInList($resultItemValue['qid'])) {
                continue;
            }

            $filteredTestResult[] = $resultItemValue;
        }

        return $filteredTestResult;
    }

    protected function populateContent(string $content): void
    {
        $this->tpl->setContent($content);
    }

    protected function outCorrectSolutionCmd(): void
    {
        $this->outCorrectSolution(); // cannot be named xxxCmd, because it's also called from context without Cmd in names
    }

    protected function outCorrectSolution(): void
    {
        if (!$this->object->getShowSolutionDetails()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_permission"), true);
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }

        $testSession = $this->test_session_factory->getSession();
        $active_id = $testSession->getActiveId();

        if (!($active_id > 0)) {
            $this->ctrl->redirectByClass([ilRepositoryGUI::class, ilObjTestGUI::class, ilInfoScreenGUI::class]);
        }

        $this->ctrl->saveParameter($this, "pass");
        $pass = (int) $this->testrequest->raw("pass");

        $active_id = (int) $this->testrequest->raw('evaluation');

        $test_sequence = $this->test_sequence_factory->getSequenceByActiveIdAndPass($active_id, $pass);
        $test_sequence->loadFromDb();
        $test_sequence->loadQuestions();

        if (!$test_sequence->questionExists($active_id)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if ($this->getObjectiveOrientedContainer()->isObjectiveOrientedPresentationRequired()) {
            $test_sequence = $this->test_sequence_factory->getSequenceByActiveIdAndPass($active_id, $pass);
            $test_sequence->loadFromDb();
            $test_sequence->loadQuestions();

            $objectives_adapter = ilLOTestQuestionAdapter::getInstance($testSession);
            $objectives_list = $this->buildQuestionRelatedObjectivesList($objectives_adapter, $test_sequence);
            $objectives_list->loadObjectivesTitles();
        } else {
            $objectives_list = null;
        }

        $ilTabs = $this->tabs;

        if ($this instanceof ilTestEvalObjectiveOrientedGUI) {
            $ilTabs->setBackTarget(
                $this->lng->txt("tst_back_to_virtual_pass"),
                $this->ctrl->getLinkTarget($this, 'showVirtualPass')
            );
        } else {
            $ilTabs->setBackTarget(
                $this->lng->txt("tst_back_to_pass_details"),
                $this->ctrl->getLinkTarget($this, 'outUserPassDetails')
            );
        }
        $ilTabs->clearSubTabs();

        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("SyntaxStyle");
        $this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
        $this->tpl->parseCurrentBlock();

        $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css"), "print");
        if ($this->object->getShowSolutionAnswersOnly()) {
            $this->tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css"), "print");
        }

        $solution = $this->getCorrectSolutionOutput($active_id, $active_id, $pass, $objectives_list);

        $this->tpl->setContent($solution);
    }

    protected function populatePassFinishDate(ilTemplate $tpl, ?int $pass_finish_date): void
    {
        if ($pass_finish_date === null) {
            return;
        }
        $old_value = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        $pass_finish_date_string = ilDatePresentation::formatDate(new ilDateTime($pass_finish_date, IL_CAL_UNIX));
        ilDatePresentation::setUseRelativeDates($old_value);
        $tpl->setVariable("PASS_FINISH_DATE_LABEL", $this->lng->txt('tst_pass_finished_on'));
        $tpl->setVariable("PASS_FINISH_DATE_VALUE", $pass_finish_date_string);
    }

    protected function populateExamId(ilTemplate $tpl, int $active_id, int $pass): void
    {
        if ($this->object->isShowExamIdInTestResultsEnabled()) {
            $tpl->setVariable('EXAM_ID_TXT', $this->lng->txt('exam_id'));
            $tpl->setVariable('EXAM_ID', ilObjTest::lookupExamId(
                $active_id,
                $pass
            ));
        }
    }

    public function getObject(): ilObjTest
    {
        return $this->object;
    }
}
