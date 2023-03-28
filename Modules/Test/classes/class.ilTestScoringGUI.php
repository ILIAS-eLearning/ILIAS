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

require_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Scoring class for tests
*
* @author	Helmut Schottmüller <helmut.schottmueller@mac.com>
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ModulesTest
* @extends ilTestServiceGUI
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
        /**
         * @var $ilTabs ilTabsGUI
         */
        global $DIC;
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->addSubTab('man_scoring_by_qst', $this->lng->txt('tst_man_scoring_by_qst'), $this->ctrl->getLinkTargetByClass('ilTestScoringByQuestionsGUI', 'showManScoringByQuestionParticipantsTable'));
        $ilTabs->addSubTab('man_scoring', $this->lng->txt('tst_man_scoring_by_part'), $this->ctrl->getLinkTargetByClass('ilTestScoringGUI', 'showManScoringParticipantsTable'));
        $ilTabs->setSubTabActive($active_sub_tab);
    }

    private function fetchActiveIdParameter(): int
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        // fetch active_id

        if (!$this->testrequest->isset('active_id') || !(int) $this->testrequest->raw('active_id')) {
            // allow only write access
            $this->tpl->setOnScreenMessage('failure', 'no active id given!', true);
            $ilCtrl->redirectByClass("ilobjtestgui", "infoScreen");
        } else {
            $activeId = (int) $this->testrequest->raw('active_id');
        }

        return $activeId;
    }

    private function fetchPassParameter($activeId)
    {
        // fetch pass nr

        $maxPass = $this->object->_getMaxPass($activeId);
        if ($this->testrequest->isset("pass") && 0 <= (int) $this->testrequest->raw("pass") && $maxPass >= (int) $this->testrequest->raw("pass")) {
            $pass = $this->testrequest->raw("pass");
        } elseif ($this->object->getPassScoring() == SCORE_LAST_PASS) {
            $pass = $maxPass;
        } else {
            $pass = $this->object->_getResultPass($activeId);
        }

        return $pass;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */

        if (!$this->getTestAccess()->checkScoreParticipantsAccess()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if (!ilObjAssessmentFolder::_mananuallyScoreableQuestionTypesExists()) {
            // allow only if at least one question type is marked for manual scoring
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("manscoring_not_allowed"), true);
            $this->ctrl->redirectByClass("ilobjtestgui", "infoScreen");
        }

        $DIC->tabs()->activateTab(ilTestTabsManager::TAB_ID_MANUAL_SCORING);
        $this->buildSubTabs($this->getActiveSubTabId());

        $nextClass = $this->ctrl->getNextClass($this);
        $command = $this->ctrl->getCmd($this->getDefaultCommand());

        switch ($nextClass) {
            default:
                $this->$command();
                break;
        }
    }

    /**
     * @return string
     */
    protected function getDefaultCommand(): string
    {
        return 'manscoring';
    }

    /**
     * @return string
     */
    protected function getActiveSubTabId(): string
    {
        return 'man_scoring';
    }

    private function showManScoringParticipantsTable()
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        $table = $this->buildManScoringParticipantsTable(true);

        $tpl->setContent($table->getHTML());
    }

    private function applyManScoringParticipantsFilter()
    {
        $table = $this->buildManScoringParticipantsTable(false);

        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showManScoringParticipantsTable();
    }

    private function resetManScoringParticipantsFilter()
    {
        $table = $this->buildManScoringParticipantsTable(false);

        $table->resetOffset();
        $table->resetFilter();

        $this->showManScoringParticipantsTable();
    }

    private function showManScoringParticipantScreen(ilPropertyFormGUI $form = null)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        $activeId = $this->fetchActiveIdParameter();

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($activeId)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $pass = $this->fetchPassParameter($activeId);

        $contentHTML = '';

        $table = new ilTestPassManualScoringOverviewTableGUI($this, 'showManScoringParticipantScreen');

        $userId = $this->object->_getUserIdFromActiveId($activeId);
        $userFullname = $this->object->userLookupFullName($userId, false, true);
        $tableTitle = sprintf($lng->txt('tst_pass_overview_for_participant'), $userFullname);
        $table->setTitle($tableTitle);

        $passOverviewData = $this->service->getPassOverviewData($activeId);
        $table->setData($passOverviewData['passes']);

        $contentHTML .= $table->getHTML() . '<br />';

        // pass scoring form

        if ($form === null) {
            $questionGuiList = $this->service->getManScoringQuestionGuiList($activeId, $pass);
            $form = $this->buildManScoringParticipantForm($questionGuiList, $activeId, $pass, true);
        }

        $contentHTML .= $form->getHTML();

        // set content

        $tpl->setContent($contentHTML);
    }

    /**
     * @param bool $redirect
     * @returns bool Returns a boolean flag, whether or not everything worked fine
     */
    private function saveManScoringParticipantScreen($redirect = true)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $activeId = $this->fetchActiveIdParameter();

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($activeId)) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $pass = $this->fetchPassParameter($activeId);

        $questionGuiList = $this->service->getManScoringQuestionGuiList($activeId, $pass);
        $form = $this->buildManScoringParticipantForm($questionGuiList, $activeId, $pass, false);

        $form->setValuesByPost();

        if (!$form->checkInput()) {
            $this->tpl->setOnScreenMessage('failure', sprintf($lng->txt('tst_save_manscoring_failed'), $pass + 1));
            $this->showManScoringParticipantScreen($form);
            return false;
        }

        $maxPointsByQuestionId = array();
        $maxPointsExceeded = false;
        foreach ($questionGuiList as $questionId => $questionGui) {
            $reachedPoints = $form->getItemByPostVar("question__{$questionId}__points")->getValue();
            $maxPoints = assQuestion::_getMaximumPoints($questionId);

            if ($reachedPoints > $maxPoints) {
                $maxPointsExceeded = true;

                $form->getItemByPostVar("question__{$questionId}__points")->setAlert(sprintf(
                    $lng->txt('tst_manscoring_maxpoints_exceeded_input_alert'),
                    $maxPoints
                ));
            }

            $maxPointsByQuestionId[$questionId] = $maxPoints;
        }

        if ($maxPointsExceeded) {
            $this->tpl->setOnScreenMessage('failure', sprintf($lng->txt('tst_save_manscoring_failed'), $pass + 1));
            $this->showManScoringParticipantScreen($form);
            return false;
        }

        foreach ($questionGuiList as $questionId => $questionGui) {
            $reachedPoints = $form->getItemByPostVar("question__{$questionId}__points")->getValue();

            // fix #35543: save manual points only if they differ from the existing points
            // this prevents a question being set to "answered" if only feedback is entered
            $oldPoints = assQuestion::_getReachedPoints($activeId, $questionId, $pass);
            if ($reachedPoints != $oldPoints) {
                assQuestion::_setReachedPoints(
                    $activeId,
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

            $this->object->saveManualFeedback($activeId, (int) $questionId, (int) $pass, $feedback);

            $notificationData[$questionId] = array(
                'points' => $reachedPoints, 'feedback' => $feedback
            );
        }

        ilLPStatusWrapper::_updateStatus(
            $this->object->getId(),
            ilObjTestAccess::_getParticipantId($activeId)
        );

        $manScoringDone = $form->getItemByPostVar("manscoring_done")->getChecked();
        ilTestService::setManScoringDone($activeId, $manScoringDone);

        $manScoringNotify = $form->getItemByPostVar("manscoring_notify")->getChecked();
        if ($manScoringNotify) {
            $notification = new ilTestManScoringParticipantNotification(
                $this->object->_getUserIdFromActiveId($activeId),
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

        $scorer = new ilTestScoring($this->object);
        $scorer->setPreserveManualScores(true);
        $scorer->recalculateSolutions();

        if ($this->object->getAnonymity() == 0) {
            $user_name = ilObjUser::_lookupName(ilObjTestAccess::_getParticipantId($activeId));
            $name_real_or_anon = $user_name['firstname'] . ' ' . $user_name['lastname'];
        } else {
            $name_real_or_anon = $lng->txt('anonymous');
        }
        $this->tpl->setOnScreenMessage('success', sprintf($lng->txt('tst_saved_manscoring_successfully'), $pass + 1, $name_real_or_anon), true);
        if ($redirect == true) {
            $ilCtrl->redirect($this, 'showManScoringParticipantScreen');
        }
        return true;
    }

    private function saveNextManScoringParticipantScreen()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

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
                $ilCtrl->setParameter($this, 'active_id', $participantData[$nextIndex]['active_id']);
                $ilCtrl->redirect($this, 'showManScoringParticipantScreen');
            }

            $ilCtrl->redirectByClass("iltestscoringgui", "showManScoringParticipantsTable");
        }
    }

    private function saveReturnManScoringParticipantScreen()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        if ($this->saveManScoringParticipantScreen(false)) {
            $ilCtrl->redirectByClass("iltestscoringgui", "showManScoringParticipantsTable");
        }
    }

    private function buildManScoringParticipantForm($questionGuiList, $activeId, $pass, $initValues = false): ilPropertyFormGUI
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilCtrl->setParameter($this, 'active_id', $activeId);
        $ilCtrl->setParameter($this, 'pass', $pass);

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));

        $form->setTitle(sprintf($lng->txt('manscoring_results_pass'), $pass + 1));
        $form->setTableWidth('100%');

        foreach ($questionGuiList as $questionId => $questionGUI) {
            $questionHeader = sprintf($lng->txt('tst_manscoring_question_section_header'), $questionGUI->object->getTitle());
            $questionSolution = $questionGUI->getSolutionOutput($activeId, $pass, false, false, true, false, false, true);
            $bestSolution = $questionGUI->object->getSuggestedSolutionOutput();

            $sect = new ilFormSectionHeaderGUI();
            $sect->setTitle($questionHeader . ' [' . $this->lng->txt('question_id_short') . ': ' . $questionGUI->object->getId() . ']');
            $form->addItem($sect);

            $cust = new ilCustomInputGUI($lng->txt('tst_manscoring_input_question_and_user_solution'));
            $cust->setHtml($questionSolution);
            $form->addItem($cust);

            $text = new ilTextInputGUI($lng->txt('tst_change_points_for_question'), "question__{$questionId}__points");
            if ($initValues) {
                $text->setValue(assQuestion::_getReachedPoints($activeId, $questionId, $pass));
            }
            $form->addItem($text);

            $nonedit = new ilNonEditableValueGUI($lng->txt('tst_manscoring_input_max_points_for_question'), "question__{$questionId}__maxpoints");
            if ($initValues) {
                $nonedit->setValue(assQuestion::_getMaximumPoints($questionId));
            }
            $form->addItem($nonedit);

            $area = new ilTextAreaInputGUI($lng->txt('set_manual_feedback'), "question__{$questionId}__feedback");
            $area->setUseRTE(true);
            if ($initValues) {
                $area->setValue(ilObjTest::getSingleManualFeedback((int) $activeId, (int) $questionId, (int) $pass)['feedback'] ?? '');
            }
            $form->addItem($area);
            if (strlen(trim($bestSolution))) {
                $cust = new ilCustomInputGUI($lng->txt('tst_show_solution_suggested'));
                $cust->setHtml($bestSolution);
                $form->addItem($cust);
            }
        }

        $sect = new ilFormSectionHeaderGUI();
        $sect->setTitle($lng->txt('tst_participant'));
        $form->addItem($sect);

        $check = new ilCheckboxInputGUI($lng->txt('set_manscoring_done'), 'manscoring_done');
        if ($initValues && ilTestService::isManScoringDone((int) $activeId)) {
            $check->setChecked(true);
        }
        $form->addItem($check);

        $check = new ilCheckboxInputGUI($lng->txt('tst_manscoring_user_notification'), 'manscoring_notify');
        $form->addItem($check);

        $form->addCommandButton('saveManScoringParticipantScreen', $lng->txt('save'));
        $form->addCommandButton('saveReturnManScoringParticipantScreen', $lng->txt('save_return'));
        $form->addCommandButton('saveNextManScoringParticipantScreen', $lng->txt('save_and_next'));

        return $form;
    }

    private function sendManScoringParticipantNotification()
    {
    }

    /**
     * @return ilTestManScoringParticipantsTableGUI
     */
    private function buildManScoringParticipantsTable($withData = false): ilTestManScoringParticipantsTableGUI
    {
        $table = new ilTestManScoringParticipantsTableGUI($this);

        if ($withData) {
            $participantStatusFilterValue = $table->getFilterItemByPostVar('participant_status')->getValue();

            $participantList = new ilTestParticipantList($this->object);

            $participantList->initializeFromDbRows(
                $this->object->getTestParticipantsForManualScoring($participantStatusFilterValue)
            );

            $participantList = $participantList->getAccessFilteredList(
                ilTestParticipantAccessFilter::getScoreParticipantsUserFilter($this->ref_id)
            );

            $table->setData($participantList->getParticipantsTableRows());
        }

        return $table;
    }
}
