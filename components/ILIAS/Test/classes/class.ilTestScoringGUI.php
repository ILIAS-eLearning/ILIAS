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

require_once 'inc.AssessmentConstants.php';

/**
* Scoring class for tests
*
* @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*/
class ilTestScoringGUI extends ilTestServiceGUI
{
    public const PART_FILTER_ACTIVE_ONLY = 1;
    public const PART_FILTER_INACTIVE_ONLY = 2;
    public const PART_FILTER_ALL_USERS = 3; // default
    public const PART_FILTER_MANSCORING_DONE = 4;
    public const PART_FILTER_MANSCORING_NONE = 5;
    //const PART_FILTER_MANSCORING_PENDING	= 6;

    /**
     * @var ilTestAccess
     */
    protected $testAccess;

    /**
    * ilTestScoringGUI constructor
    *
    * The constructor takes the test object reference as parameter
    *
    * @param object $a_object Associated ilObjTest class
    * @access public
    */
    public function __construct(ilObjTest $a_object)
    {
        parent::__construct($a_object);
    }

    /**
     * @return ilTestAccess
     */
    public function getTestAccess(): ilTestAccess
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
     * @param string $active_sub_tab
     */
    protected function buildSubTabs($active_sub_tab = 'man_scoring_by_qst')
    {
        $this->tabs->addSubTab('man_scoring_by_qst', $this->lng->txt('tst_man_scoring_by_qst'), $this->ctrl->getLinkTargetByClass('ilTestScoringByQuestionsGUI', 'showManScoringByQuestionParticipantsTable'));
        $this->tabs->addSubTab('man_scoring', $this->lng->txt('tst_man_scoring_by_part'), $this->ctrl->getLinkTargetByClass('ilTestScoringGUI', 'showManScoringParticipantsTable'));
        $this->tabs->setSubTabActive($active_sub_tab);
    }

    private function fetchActiveIdParameter(): int
    {
        if (!$this->testrequest->isset('active_id') || $this->testrequest->int('active_id') === 0) {
            $this->tpl->setOnScreenMessage('failure', 'no active id given!', true);
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        return $this->testrequest->int('active_id');
    }

    private function fetchPassParameter(int $active_id): int
    {
        $max_pass = $this->object->_getMaxPass($active_id);
        $pass_from_request = $this->testrequest->int('pass');
        if ($pass_from_request !== null
            && $pass_from_request >= 0
            && $pass_from_request <= $max_pass) {
            return $pass_from_request;
        }

        if ($this->object->getPassScoring() == ilObjTest::SCORE_LAST_PASS) {
            return $max_pass;
        }

        return $this->object->_getResultPass($active_id);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        if (!$this->getTestAccess()->checkScoreParticipantsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if (!ilObjAssessmentFolder::_mananuallyScoreableQuestionTypesExists()) {
            // allow only if at least one question type is marked for manual scoring
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("manscoring_not_allowed"), true);
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_MANUAL_SCORING);
        $this->buildSubTabs($this->getActiveSubTabId());

        $nextClass = $this->ctrl->getNextClass($this);
        $command = $this->ctrl->getCmd($this->getDefaultCommand());

        switch ($nextClass) {
            default:
                $this->$command();
                break;
        }
    }

    protected function getDefaultCommand(): string
    {
        return 'manscoring';
    }

    protected function getActiveSubTabId(): string
    {
        return 'man_scoring';
    }

    private function showManScoringParticipantsTable(): void
    {
        $table = $this->buildManScoringParticipantsTable(true);
        $this->tpl->setContent($table->getHTML());
    }

    private function applyManScoringParticipantsFilter(): void
    {
        $table = $this->buildManScoringParticipantsTable(false);
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showManScoringParticipantsTable();
    }

    private function resetManScoringParticipantsFilter(): void
    {
        $table = $this->buildManScoringParticipantsTable(false);
        $table->resetOffset();
        $table->resetFilter();

        $this->showManScoringParticipantsTable();
    }

    private function showManScoringParticipantScreen(ilPropertyFormGUI $form = null): void
    {
        $active_id = $this->fetchActiveIdParameter();

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($active_id)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $pass = $this->fetchPassParameter($active_id);

        $content_html = '';

        $table = new ilTestPassManualScoringOverviewTableGUI($this, 'showManScoringParticipantScreen');

        $user_id = $this->object->_getUserIdFromActiveId($active_id);
        $user_fullname = $this->object->userLookupFullName($user_id, false, true);
        $table_title = sprintf($this->lng->txt('tst_pass_overview_for_participant'), $user_fullname);
        $table->setTitle($table_title);

        $passOverviewData = $this->service->getPassOverviewData($active_id);
        $table->setData($passOverviewData['passes']);

        $content_html .= $table->getHTML() . '<br />';

        if ($form === null) {
            $question_gui_list = $this->service->getManScoringQuestionGuiList($active_id, $pass);
            $form = $this->buildManScoringParticipantForm($question_gui_list, $active_id, $pass, true);
        }

        $content_html .= $form->getHTML();

        $this->tpl->setContent($content_html);
    }

    private function saveManScoringParticipantScreen(bool $redirect = true): bool
    {
        $active_id = $this->fetchActiveIdParameter();

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($active_id)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $pass = $this->fetchPassParameter($active_id);

        $questionGuiList = $this->service->getManScoringQuestionGuiList($active_id, $pass);
        $form = $this->buildManScoringParticipantForm($questionGuiList, $active_id, $pass, false);

        $form->setValuesByPost();

        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt('tst_save_manscoring_failed'), $pass + 1));
            $this->showManScoringParticipantScreen($form);
            return false;
        }

        $maxPointsByQuestionId = [];
        $maxPointsExceeded = false;
        foreach ($questionGuiList as $questionId => $questionGui) {
            $reachedPoints = $form->getItemByPostVar("question__{$questionId}__points")->getValue();
            $maxPoints = $this->questioninfo->getMaximumPoints($questionId);

            if ($reachedPoints > $maxPoints) {
                $maxPointsExceeded = true;

                $form->getItemByPostVar("question__{$questionId}__points")->setAlert(sprintf(
                    $this->lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'),
                    $maxPoints
                ));
            }

            $maxPointsByQuestionId[$questionId] = $maxPoints;
        }

        if ($maxPointsExceeded) {
            $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt('tst_save_manscoring_failed'), $pass + 1));
            $this->showManScoringParticipantScreen($form);
            return false;
        }

        foreach ($questionGuiList as $questionId => $questionGui) {
            $reachedPoints = (float) $form->getItemByPostVar("question__{$questionId}__points")->getValue();

            $finalized = (bool) $form->getItemByPostVar("{$questionId}__evaluated")->getchecked();

            // fix #35543: save manual points only if they differ from the existing points
            // this prevents a question being set to "answered" if only feedback is entered
            $oldPoints = assQuestion::_getReachedPoints($active_id, $questionId, $pass);
            if ($reachedPoints != $oldPoints) {
                assQuestion::_setReachedPoints(
                    $active_id,
                    $questionId,
                    $reachedPoints,
                    $maxPointsByQuestionId[$questionId],
                    $pass,
                    true,
                    $this->object->areObligationsEnabled()
                );
            }

            $feedback = ilUtil::stripSlashes(
                (string) $form->getItemByPostVar("question__{$questionId}__feedback")->getValue(),
                false,
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
            );

            $this->object->saveManualFeedback($active_id, (int) $questionId, (int) $pass, $feedback, $finalized, true);

            $notificationData[$questionId] = [
                'points' => $reachedPoints, 'feedback' => $feedback
            ];
        }

        ilLPStatusWrapper::_updateStatus(
            $this->object->getId(),
            ilObjTestAccess::_getParticipantId($active_id)
        );

        $manScoringDone = $form->getItemByPostVar("manscoring_done")->getChecked();
        ilTestService::setManScoringDone($active_id, $manScoringDone);

        $manScoringNotify = $form->getItemByPostVar("manscoring_notify")->getChecked();
        if ($manScoringNotify) {
            $notification = new ilTestManScoringParticipantNotification(
                $this->object->_getUserIdFromActiveId($active_id),
                $this->object->getRefId()
            );

            $notification->setAdditionalInformation(array(
                'test_title' => $this->object->getTitle(),
                'test_pass' => $pass + 1,
                'questions_gui_list' => $questionGuiList,
                'questions_scoring_data' => $notificationData
            ));

            $notification->send();
        }

        $scorer = new ilTestScoring($this->object, $this->db);
        $scorer->setPreserveManualScores(true);
        $scorer->recalculateSolutions();

        if ($this->object->getAnonymity() == 0) {
            $user_name = ilObjUser::_lookupName(ilObjTestAccess::_getParticipantId($active_id));
            $name_real_or_anon = $user_name['firstname'] . ' ' . $user_name['lastname'];
        } else {
            $name_real_or_anon = $this->lng->txt('anonymous');
        }
        $this->tpl->setOnScreenMessage('success', sprintf($this->lng->txt('tst_saved_manscoring_successfully'), $pass + 1, $name_real_or_anon), true);
        if ($redirect == true) {
            $this->ctrl->redirect($this, 'showManScoringParticipantScreen');
        }
        return true;
    }

    private function saveNextManScoringParticipantScreen(): void
    {
        $table = $this->buildManScoringParticipantsTable(true);

        if ($this->saveManScoringParticipantScreen(false)) {
            $participantData = $table->getInternalyOrderedDataValues();

            $nextIndex = null;
            foreach ($participantData as $index => $participant) {
                if ($participant['active_id'] == $this->testrequest->raw('active_id')) {
                    $nextIndex = $index + 1;
                    break;
                }
            }

            if ($nextIndex && isset($participantData[$nextIndex])) {
                $this->ctrl->setParameter($this, 'active_id', $participantData[$nextIndex]['active_id']);
                $this->ctrl->redirect($this, 'showManScoringParticipantScreen');
            }

            $this->ctrl->redirectByClass("iltestscoringgui", "showManScoringParticipantsTable");
        }
    }

    private function saveReturnManScoringParticipantScreen(): void
    {
        if ($this->saveManScoringParticipantScreen(false)) {
            $this->ctrl->redirectByClass("iltestscoringgui", "showManScoringParticipantsTable");
        }
    }

    private function buildManScoringParticipantForm(
        array $questionGuiList,
        int $active_id,
        int $pass,
        bool $initValues = false
    ): ilPropertyFormGUI {
        $this->ctrl->setParameter($this, 'active_id', $active_id);
        $this->ctrl->setParameter($this, 'pass', $pass);

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTitle(sprintf($this->lng->txt('manscoring_results_pass'), $pass + 1));
        $form->setTableWidth('100%');

        foreach ($questionGuiList as $questionId => $questionGUI) {
            $questionHeader = sprintf($this->lng->txt('tst_manscoring_question_section_header'), $questionGUI->object->getTitle());
            $questionSolution = $questionGUI->getSolutionOutput($active_id, $pass, false, false, true, false, false, true);
            $bestSolution = $questionGUI->object->getSuggestedSolutionOutput();

            $feedback = $this->object->getSingleManualFeedback($active_id, $questionId, $pass);

            $disabled = false;
            if (isset($feedback['finalized_evaluation']) && $feedback['finalized_evaluation'] == 1) {
                $disabled = true;
            }

            $sect = new ilFormSectionHeaderGUI();
            $sect->setTitle($questionHeader . ' [' . $this->lng->txt('question_id_short') . ': ' . $questionGUI->object->getId() . ']');
            $form->addItem($sect);

            $cust = new ilCustomInputGUI($this->lng->txt('tst_manscoring_input_question_and_user_solution'));
            $cust->setHtml($questionSolution);
            $form->addItem($cust);

            $text = new ilTextInputGUI($this->lng->txt('tst_change_points_for_question'), "question__{$questionId}__points");
            if ($initValues) {
                $text->setValue((string) assQuestion::_getReachedPoints($active_id, $questionId, $pass));
            }
            if ($disabled) {
                $text->setDisabled($disabled);
            }
            $form->addItem($text);

            $nonedit = new ilNonEditableValueGUI($this->lng->txt('tst_manscoring_input_max_points_for_question'), "question__{$questionId}__maxpoints");
            if ($initValues) {
                $nonedit->setValue($this->questioninfo->getMaximumPoints($questionId));
            }
            $form->addItem($nonedit);

            $area = new ilTextAreaInputGUI($this->lng->txt('set_manual_feedback'), "question__{$questionId}__feedback");
            $area->setUseRTE(true);
            if ($initValues) {
                $area->setValue(ilObjTest::getSingleManualFeedback((int) $active_id, (int) $questionId, (int) $pass)['feedback'] ?? '');
            }
            if ($disabled) {
                $area->setDisabled($disabled);
            }
            $form->addItem($area);

            $check = new ilCheckboxInputGUI($this->lng->txt('finalized_evaluation'), "{$questionId}__evaluated");
            if ($disabled) {
                $check->setChecked(true);
            }
            $form->addItem($check);

            if (strlen(trim($bestSolution))) {
                $cust = new ilCustomInputGUI($this->lng->txt('tst_show_solution_suggested'));
                $cust->setHtml($bestSolution);
                $form->addItem($cust);
            }
        }

        $sect = new ilFormSectionHeaderGUI();
        $sect->setTitle($this->lng->txt('tst_participant'));
        $form->addItem($sect);

        $check = new ilCheckboxInputGUI($this->lng->txt('set_manscoring_done'), 'manscoring_done');
        if ($initValues && ilTestService::isManScoringDone($active_id)) {
            $check->setChecked(true);
        }
        $form->addItem($check);

        $check = new ilCheckboxInputGUI($this->lng->txt('tst_manscoring_user_notification'), 'manscoring_notify');
        $form->addItem($check);

        $form->addCommandButton('saveManScoringParticipantScreen', $this->lng->txt('save'));
        $form->addCommandButton('saveReturnManScoringParticipantScreen', $this->lng->txt('save_return'));
        $form->addCommandButton('saveNextManScoringParticipantScreen', $this->lng->txt('save_and_next'));

        return $form;
    }

    private function sendManScoringParticipantNotification(): void
    {
    }

    private function buildManScoringParticipantsTable(bool $withData = false): ilTestManScoringParticipantsTableGUI
    {
        $table = new ilTestManScoringParticipantsTableGUI($this);

        if ($withData) {
            $participantStatusFilterValue = $table->getFilterItemByPostVar('participant_status')->getValue();

            $participant_list = new ilTestParticipantList($this->object, $this->user, $this->lng, $this->db);

            $participant_list->initializeFromDbRows(
                $this->object->getTestParticipantsForManualScoring($participantStatusFilterValue)
            );

            $filtered_participant_list = $participant_list->getAccessFilteredList(
                $this->participant_access_filter->getScoreParticipantsUserFilter($this->ref_id)
            );

            $table->setData($filtered_participant_list->getParticipantsTableRows());
        }

        return $table;
    }
}
