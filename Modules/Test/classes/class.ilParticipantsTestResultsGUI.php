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
 * Class ilParticipantsTestResultsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 *
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

    protected ilCtrl $ctrl;
    protected ilLanguage $lang;
    protected ilDBInterface $db;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;

    /**
     * @var ilTestObjectiveOrientedContainer
     */
    protected $objectiveParent;
    private ilGlobalTemplateInterface $main_tpl;
    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lang = $DIC->language();
        $this->db = $DIC->database();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->testrequest = $DIC->test()->internal()->request();
    }

    /**
     * @return ilObjTest
     */
    public function getTestObj(): ?ilObjTest
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
    public function getQuestionSetConfig(): ?ilTestQuestionSetConfig
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
    public function getTestAccess(): ?ilTestAccess
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
     * @return ilTestObjectiveOrientedContainer
     */
    public function getObjectiveParent(): ?ilTestObjectiveOrientedContainer
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
     * Execute Command
     */
    public function executeCommand()
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
     * @return ilParticipantsTestResultsTableGUI
     */
    protected function buildTableGUI(): ilParticipantsTestResultsTableGUI
    {
        $tableGUI = new ilParticipantsTestResultsTableGUI($this, self::CMD_SHOW_PARTICIPANTS);
        $tableGUI->setTitle($this->lang->txt('tst_tbl_results_grades'));
        return $tableGUI;
    }

    /**
     * show participants command
     */
    protected function showParticipantsCmd()
    {
        ilSession::clear("show_user_results");

        if ($this->getQuestionSetConfig()->areDepenciesBroken()) {
            $this->main_tpl->setOnScreenMessage('failure', $this->getQuestionSetConfig()->getDepenciesBrokenMessage($this->lang));
        } elseif ($this->getQuestionSetConfig()->areDepenciesInVulnerableState()) {
            $this->main_tpl->setOnScreenMessage('info', $this->questionSetConfig->getDepenciesInVulnerableStateMessage($this->lang));
        }

        $manageParticipantFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());
        $accessResultsFilter = ilTestParticipantAccessFilter::getAccessResultsUserFilter($this->getTestObj()->getRefId());

        $participantList = $this->getTestObj()->getActiveParticipantList();
        $participantList = $participantList->getAccessFilteredList($manageParticipantFilter);
        $participantList = $participantList->getAccessFilteredList($accessResultsFilter);

        $scoredParticipantList = $participantList->getScoredParticipantList();

        $tableGUI = $this->buildTableGUI();

        if (!$this->getQuestionSetConfig()->areDepenciesBroken()) {
            $tableGUI->setAccessResultsCommandsEnabled(
                $this->getTestAccess()->checkParticipantsResultsAccess()
            );

            $tableGUI->setManageResultsCommandsEnabled(
                $this->getTestAccess()->checkManageParticipantsAccess()
            );

            if ($scoredParticipantList->hasScorings()) {
                $this->addDeleteAllTestResultsButton($this->toolbar);
            }
        }

        $tableGUI->setAnonymity($this->getTestObj()->getAnonymity());

        $tableGUI->initColumns();
        $tableGUI->initCommands();

        $tableGUI->setData($participantList->getScoringsTableRows());

        $this->main_tpl->setContent($tableGUI->getHTML());
    }

    /**
     * @param ilToolbarGUI $toolbar
     */
    protected function addDeleteAllTestResultsButton(ilToolbarGUI $toolbar)
    {
        $delete_all_results_btn = ilLinkButton::getInstance();
        $delete_all_results_btn->setCaption('delete_all_user_data');
        $delete_all_results_btn->setUrl($this->ctrl->getLinkTarget($this, 'deleteAllUserResults'));
        $toolbar->addButtonInstance($delete_all_results_btn);
    }

    /**
     * Asks for a confirmation to delete all user data of the test object
     */
    protected function deleteAllUserResultsCmd()
    {
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lang->txt("delete_all_user_data_confirmation"));
        $cgui->setCancel($this->lang->txt("cancel"), self::CMD_SHOW_PARTICIPANTS);
        $cgui->setConfirm($this->lang->txt("proceed"), self::CMD_PERFORM_DELETE_ALL_USER_RESULTS);

        $this->main_tpl->setContent($cgui->getHTML());
    }

    /**
     * Deletes all user data for the test object
     */
    protected function confirmDeleteAllUserResultsCmd()
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

    /**
     * Asks for a confirmation to delete selected user data of the test object
     */
    protected function deleteSingleUserResultsCmd()
    {
        $users = $this->testrequest->raw('chbUser');
        if (!is_array($users) || count($users) === 0) {
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

        $participantData->setActiveIdsFilter((array) $users);

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

    /**
     * Deletes the selected user data for the test object
     */
    protected function confirmDeleteSelectedUserDataCmd()
    {
        if (isset($_POST["chbUser"]) && is_array($_POST["chbUser"]) && count($_POST["chbUser"])) {
            $accessFilter = ilTestParticipantAccessFilter::getManageParticipantsUserFilter($this->getTestObj()->getRefId());

            $participantData = new ilTestParticipantData($this->db, $this->lang);
            $participantData->setParticipantAccessFilter($accessFilter);
            $participantData->setActiveIdsFilter($_POST["chbUser"]);

            $participantData->load($this->getTestObj()->getTestId());

            $this->getTestObj()->removeTestResults($participantData);

            $this->main_tpl->setOnScreenMessage('success', $this->lang->txt("tst_selected_user_data_deleted"), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
    }

    /**
     * Shows the pass overview and the answers of one ore more users for the scored pass
     */
    protected function showDetailedResultsCmd()
    {
        $users = $this->testrequest->raw('chbUser');
        if (is_array($users) && count($users) > 0) {
            ilSession::set('show_user_results', $users);
        }
        $this->showUserResults($show_pass_details = true, $show_answers = true, $show_reached_points = true);
    }

    /**
     * Shows the answers of one ore more users for the scored pass
     */
    protected function showUserAnswersCmd()
    {
        $users = $this->testrequest->raw('chbUser');
        if (is_array($users) && count($users) > 0) {
            ilSession::set('show_user_results', $users);
        }
        $this->showUserResults($show_pass_details = false, $show_answers = true);
    }

    /**
     * Shows the pass overview of the scored pass for one ore more users
     */
    protected function showPassOverviewCmd()
    {
        $users = $this->testrequest->raw('chbUser');
        if (is_array($users) && count($users) > 0) {
            ilSession::set('show_user_results', $users);
        }
        $this->showUserResults($show_pass_details = true, $show_answers = false);
    }

    /**
     * Shows the pass overview of the scored pass for one ore more users
     *
     * @access	public
     */
    protected function showUserResults($show_pass_details, $show_answers, $show_reached_points = false)
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $show_user_results = ilSession::get("show_user_results");

        if (!is_array($show_user_results) || count($show_user_results) == 0) {
            $this->main_tpl->setOnScreenMessage('info', $this->lang->txt("select_one_user"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_PARTICIPANTS);
        }


        $template = $this->createUserResults($show_pass_details, $show_answers, $show_reached_points, $show_user_results);

        if ($template instanceof ilTemplate) {
            $this->main_tpl->setVariable("ADM_CONTENT", $template->get());
            $this->main_tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print.css", "Modules/Test"), "print");
            if ($this->getTestObj()->getShowSolutionAnswersOnly()) {
                $this->main_tpl->addCss(ilUtil::getStyleSheetLocation("output", "test_print_hide_content.css", "Modules/Test"), "print");
            }
        }
    }

    /**
     * @param $show_pass_details
     * @param $show_answers
     * @param $show_reached_points
     * @param $show_user_results
     *
     * @return ilTemplate
     */
    public function createUserResults($show_pass_details, $show_answers, $show_reached_points, $show_user_results): ilTemplate
    {
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

        return $template;
    }
}
