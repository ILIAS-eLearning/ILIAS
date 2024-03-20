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
 * @ilCtrl_Calls ilParticipantsTestResultsGUI: ilTestEvaluationGUI
 * @ilCtrl_Calls ilParticipantsTestResultsGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilParticipantsTestResultsGUI: ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilParticipantsTestResultsGUI: ilAssGenFeedbackPageGUI
 */
class ilParticipantsTestResultsGUI
{
    public const CMD_SHOW_PARTICIPANTS = 'showParticipants';
    public const CMD_CONFIRM_DELETE_ALL_USER_RESULTS = 'deleteAllUserResults';
    public const CMD_PERFORM_DELETE_ALL_USER_RESULTS = 'confirmDeleteAllUserResults';
    public const CMD_CONFIRM_DELETE_SELECTED_USER_RESULTS = 'deleteSingleUserResults';
    public const CMD_PERFORM_DELETE_SELECTED_USER_RESULTS = 'confirmDeleteSelectedUserData';
    private \ILIAS\Test\InternalRequestService $testrequest;

    private ?ilObjTest $testObj = null;
    private ?ilTestQuestionSetConfig $questionSetConfig = null;
    private ?ilTestAccess $testAccess = null;
    private ilCtrlInterface $ctrl;
    private ilLanguage $lang;
    private ilDBInterface $db;
    private ilTabsGUI $tabs;
    private ilToolbarGUI $toolbar;
    private \ILIAS\HTTP\GlobalHttpState $http;
    private \ILIAS\Refinery\Factory $refinery;
    private ?ilTestObjectiveOrientedContainer $objectiveParent = null;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ctrl = $DIC->ctrl();
        $this->lang = $DIC->language();
        $this->db = $DIC->database();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->testrequest = $DIC->test()->internal()->request();
    }

    public function getTestObj(): ?ilObjTest
    {
        return $this->testObj;
    }

    public function setTestObj(ilObjTest $testObj): void
    {
        $this->testObj = $testObj;
    }

    public function getQuestionSetConfig(): ?ilTestQuestionSetConfig
    {
        return $this->questionSetConfig;
    }

    public function setQuestionSetConfig(ilTestQuestionSetConfig $questionSetConfig): void
    {
        $this->questionSetConfig = $questionSetConfig;
    }

    public function getTestAccess(): ?ilTestAccess
    {
        return $this->testAccess;
    }

    public function setTestAccess(ilTestAccess $testAccess): void
    {
        $this->testAccess = $testAccess;
    }

    public function getObjectiveParent(): ?ilTestObjectiveOrientedContainer
    {
        return $this->objectiveParent;
    }

    public function setObjectiveParent(ilTestObjectiveOrientedContainer $objectiveParent): void
    {
        $this->objectiveParent = $objectiveParent;
    }

    public function executeCommand(): void
    {
        switch ($this->ctrl->getNextClass($this)) {
            case "iltestevaluationgui":
                $gui = new ilTestEvaluationGUI($this->getTestObj());
                $gui->setObjectiveOrientedContainer($this->getObjectiveParent());
                $gui->setTestAccess($this->getTestAccess());
                $this->tabs->clearTargets();
                $this->tabs->clearSubTabs();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilassquestionpagegui':
                $forwarder = new ilAssQuestionPageCommandForwarder();
                $forwarder->setTestObj($this->getTestObj());
                $forwarder->forward();
                break;

            default:

                $command = $this->ctrl->getCmd(self::CMD_SHOW_PARTICIPANTS) . 'Cmd';
                $this->{$command}();
        }
    }

    /**
     * @return list<int>
     */
    private function getUserIdsFromPost(): array
    {
        return $this->http->wrapper()->post()->retrieve(
            'chbUser',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );
    }

    private function buildTableGUI(): ilParticipantsTestResultsTableGUI
    {
        $tableGUI = new ilParticipantsTestResultsTableGUI($this, self::CMD_SHOW_PARTICIPANTS);
        $tableGUI->setTitle($this->lang->txt('tst_tbl_results_grades'));
        return $tableGUI;
    }

    private function showParticipantsCmd(): void
    {
        ilSession::clear("show_user_results");

        if ($this->getQuestionSetConfig()->areDepenciesBroken()) {
            $this->main_tpl->setOnScreenMessage(
                'failure',
                $this->getQuestionSetConfig()->getDepenciesBrokenMessage($this->lang)
            );
        } elseif ($this->getQuestionSetConfig()->areDepenciesInVulnerableState()) {
            $this->main_tpl->setOnScreenMessage(
                'info',
                $this->questionSetConfig->getDepenciesInVulnerableStateMessage($this->lang)
            );
        }

        $manageParticipantFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter(
            $this->getTestObj()->getRefId()
        );
        $accessResultsFilter = ilTestParticipantAccessFilter::getAccessResultsUserFilter(
            $this->getTestObj()->getRefId()
        );

        $full_participant_list = $this->getTestObj()->getActiveParticipantList();
        $participantList = $full_participant_list->getAccessFilteredList($manageParticipantFilter);
        $access_to_results_participants = $full_participant_list->getAccessFilteredList($accessResultsFilter);
        foreach ($access_to_results_participants as $participant) {
            if (!$participantList->isActiveIdInList($participant->getActiveId())) {
                $participantList->addParticipant($participant);
            }
        }

        $scoredParticipantList = $participantList->getScoredParticipantList();

        $tableGUI = $this->buildTableGUI();

        if (!$this->getQuestionSetConfig()->areDepenciesBroken()) {
            $tableGUI->setAccessResultsCommandsEnabled(
                $this->getTestAccess()->checkParticipantsResultsAccess()
            );

            $tableGUI->setManageResultsCommandsEnabled(
                $this->getTestAccess()->checkManageParticipantsAccess()
            );

            if ($this->testAccess->checkManageParticipantsAccess()
                && $scoredParticipantList->hasScorings()) {
                $this->addDeleteAllTestResultsButton($this->toolbar);
            }
        }

        $tableGUI->setAnonymity($this->getTestObj()->getAnonymity());

        $tableGUI->initColumns();
        $tableGUI->initCommands();

        $tableGUI->setData($participantList->getScoringsTableRows());

        $this->main_tpl->setContent($tableGUI->getHTML());
    }

    private function addDeleteAllTestResultsButton(ilToolbarGUI $toolbar): void
    {
        $delete_all_results_btn = ilLinkButton::getInstance();
        $delete_all_results_btn->setCaption('delete_all_user_data');
        $delete_all_results_btn->setUrl($this->ctrl->getLinkTarget($this, 'deleteAllUserResults'));
        $toolbar->addButtonInstance($delete_all_results_btn);
    }

    private function deleteAllUserResultsCmd(): void
    {
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lang->txt("delete_all_user_data_confirmation"));
        $cgui->setCancel($this->lang->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
        $cgui->setConfirm($this->lang->txt("proceed"), self::CMD_PERFORM_DELETE_ALL_USER_RESULTS);

        $this->main_tpl->setContent($cgui->getHTML());
    }

    private function confirmDeleteAllUserResultsCmd(): void
    {
        $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter(
            $this->getTestObj()->getRefId()
        );

        $participantData = new ilTestParticipantData($this->db, $this->lang);
        $participantData->setParticipantAccessFilter($accessFilter);
        $participantData->load($this->getTestObj()->getTestId());

        $this->getTestObj()->removeTestResults($participantData);

        $this->main_tpl->setOnScreenMessage('success', $this->lang->txt("tst_all_user_data_deleted"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    protected function deleteSingleUserResultsCmd(): void
    {
        $usr_ids = $this->getUserIdsFromPost();
        if ($usr_ids === []) {
            $this->main_tpl->setOnScreenMessage('info', $this->lang->txt("select_one_user"), true);
            $this->ctrl->redirect($this);
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lang->txt("confirm_delete_single_user_data"));

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lang->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
        $cgui->setConfirm($this->lang->txt("confirm"), self::CMD_PERFORM_DELETE_SELECTED_USER_RESULTS);

        $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());

        $participantData = new ilTestParticipantData($this->db, $this->lang);
        $participantData->setParticipantAccessFilter($accessFilter);

        $participantData->setActiveIdsFilter($usr_ids);

        $participantData->load($this->getTestObj()->getTestId());

        foreach ($participantData->getActiveIds() as $activeId) {
            if ($this->testObj->getAnonymity()) {
                $username = $this->lang->txt('anonymous');
            } else {
                $username = $participantData->getFormatedFullnameByActiveId($activeId);
            }

            $cgui->addItem(
                "chbUser[]",
                $activeId,
                $username,
                ilUtil::getImagePath("icon_usr.svg"),
                $this->lang->txt("usr")
            );
        }

        $this->main_tpl->setContent($cgui->getHTML());
    }

    protected function confirmDeleteSelectedUserDataCmd(): void
    {
        $usr_ids = $this->getUserIdsFromPost();
        if ($usr_ids !== []) {
            $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter(
                $this->getTestObj()->getRefId()
            );

            $participantData = new ilTestParticipantData($this->db, $this->lang);
            $participantData->setParticipantAccessFilter($accessFilter);
            $participantData->setActiveIdsFilter($usr_ids);

            $participantData->load($this->getTestObj()->getTestId());

            $this->getTestObj()->removeTestResults($participantData);

            $this->main_tpl->setOnScreenMessage('success', $this->lang->txt("tst_selected_user_data_deleted"), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    protected function showDetailedResultsCmd(): void
    {
        $usr_ids = $this->getUserIdsFromPost();
        if ($usr_ids !== []) {
            ilSession::set('show_user_results', $usr_ids);
        }
        $this->showUserResults($show_pass_details = true, $show_answers = true, $show_reached_points = true);
    }

    protected function showUserAnswersCmd(): void
    {
        $usr_ids = $this->getUserIdsFromPost();
        if ($usr_ids !== []) {
            ilSession::set('show_user_results', $usr_ids);
        }
        $this->showUserResults($show_pass_details = false, $show_answers = true);
    }

    protected function showPassOverviewCmd(): void
    {
        $usr_ids = $this->getUserIdsFromPost();
        if ($usr_ids !== []) {
            ilSession::set('show_user_results', $usr_ids);
        }
        $this->showUserResults($show_pass_details = true, $show_answers = false);
    }

    protected function showUserResults($show_pass_details, $show_answers, $show_reached_points = false): void
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $show_user_results = ilSession::get("show_user_results");

        if (!is_array($show_user_results) || count($show_user_results) === 0) {
            $this->main_tpl->setOnScreenMessage('info', $this->lang->txt("select_one_user"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
        }

        $template = $this->createUserResults(
            $show_pass_details,
            $show_answers,
            $show_reached_points,
            $show_user_results
        );

        if ($template instanceof ilTemplate) {
            $this->main_tpl->setVariable("ADM_CONTENT", $template->get());
            $this->main_tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
            if ($this->getTestObj()->getShowSolutionAnswersOnly()) {
                $this->main_tpl->addCss(
                    ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"),
                    "print"
                );
            }
        }
    }

    /**
     * @param $show_pass_details
     * @param $show_answers
     * @param $show_reached_points
     * @param $show_user_results
     */
    public function createUserResults(
        $show_pass_details,
        $show_answers,
        $show_reached_points,
        $show_user_results
    ): ilTemplate {
        // prepare generation before contents are processed (needed for mathjax)
        if ($this->isPdfDeliveryRequest()) {
            ilPDFGeneratorUtils::prepareGenerationRequest("Test", PDF_USER_RESULT);
        }

        $this->tabs->setBackTarget(
            $this->lang->txt('back'),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_PARTICIPANTS)
        );

        if ($this->getObjectiveParent()->isObjectiveOrientedPresentationRequired()) {
            $courseLink = ilLink::_getLink($this->getObjectiveParent()->getRefId());
            $this->tabs->setBack2Target($this->lang->txt('back_to_objective_container'), $courseLink);
        }

        $template = new ilTemplate("tpl.il_as_tst_participants_result_output.html", true, true, "Modules/Test");

        $toolbar = new ilTestResultsToolbarGUI($this->ctrl, $this->main_tpl, $this->lang);

        $this->ctrl->setParameter($this, 'pdf', '1');
        $toolbar->setPdfExportLinkTarget($this->ctrl->getLinkTarget($this, $this->ctrl->getCmd()));
        $this->ctrl->setParameter($this, 'pdf', '');

        if ($show_answers) {
            if ($this->testrequest->isset('show_best_solutions')) {
                ilSession::set('tst_results_show_best_solutions', true);
            } elseif ($this->testrequest->isset('hide_best_solutions')) {
                ilSession::set('tst_results_show_best_solutions', false);
            } elseif (ilSession::get('tst_results_show_best_solutions') !== null) {
                ilSession::set('tst_results_show_best_solutions', false);
            }

            if (ilSession::get('tst_results_show_best_solutions')) {
                $this->ctrl->setParameter($this, 'hide_best_solutions', '1');
                $toolbar->setHideBestSolutionsLinkTarget($this->ctrl->getLinkTarget($this, $this->ctrl->getCmd()));
                $this->ctrl->setParameter($this, 'hide_best_solutions', '');
            } else {
                $this->ctrl->setParameter($this, 'show_best_solutions', '1');
                $toolbar->setShowBestSolutionsLinkTarget($this->ctrl->getLinkTarget($this, $this->ctrl->getCmd()));
                $this->ctrl->setParameterByClass('', 'show_best_solutions', '');
            }
        }

        $participantData = new ilTestParticipantData($this->db, $this->lang);
        $participantData->setParticipantAccessFilter(
            ilTestParticipantAccessFilter::getAccessResultsUserFilter($this->getTestObj()->getRefId())
        );

        $participantData->setActiveIdsFilter($show_user_results);

        $participantData->load($this->getTestObj()->getTestId());
        $toolbar->setParticipantSelectorOptions($participantData->getOptionArray());

        $toolbar->build();
        $template->setVariable('RESULTS_TOOLBAR', $toolbar->getHTML());

        $serviceGUI = new ilTestServiceGUI($this->getTestObj());
        $serviceGUI->setObjectiveOrientedContainer($this->getObjectiveParent());
        $serviceGUI->setParticipantData($participantData);

        $testSessionFactory = new ilTestSessionFactory($this->getTestObj());

        $count = 0;
        foreach ($show_user_results as $key => $active_id) {
            if (!in_array($active_id, $participantData->getActiveIds())) {
                continue;
            }

            $count++;
            $results = "";
            if ($active_id > 0) {
                $results = $serviceGUI->getResultsOfUserOutput(
                    $testSessionFactory->getSession($active_id),
                    $active_id,
                    $this->getTestObj()->_getResultPass($active_id),
                    $this,
                    $show_pass_details,
                    $show_answers,
                    false,
                    $show_reached_points
                );
            }
            if ($count < count($show_user_results)) {
                $template->touchBlock("break");
            }
            $template->setCurrentBlock("user_result");
            $template->setVariable("USER_RESULT", $results);
            $template->parseCurrentBlock();
        }

        if ($this->isPdfDeliveryRequest()) {
            ilTestPDFGenerator::generatePDF(
                $template->get(),
                ilTestPDFGenerator::PDF_OUTPUT_DOWNLOAD,
                $this->getTestObj()->getTitleFilenameCompliant(),
                PDF_USER_RESULT
            );
        }
        return $template;
    }

    protected function isPdfDeliveryRequest(): bool
    {
        if (!$this->testrequest->isset('pdf')) {
            return false;
        }

        if (!$this->testrequest->raw('pdf')) {
            return false;
        }

        return true;
    }
}
