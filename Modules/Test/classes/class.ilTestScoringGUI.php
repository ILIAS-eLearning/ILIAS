<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";

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
    const PART_FILTER_ACTIVE_ONLY = 1;
    const PART_FILTER_INACTIVE_ONLY = 2;
    const PART_FILTER_ALL_USERS = 3; // default
    const PART_FILTER_MANSCORING_DONE = 4;
    const PART_FILTER_MANSCORING_NONE = 5;
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
    
    private function fetchActiveIdParameter()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        // fetch active_id
        
        if (!isset($_GET['active_id']) || !(int) $_GET['active_id']) {
            // allow only write access
            ilUtil::sendFailure('no active id given!', true);
            $ilCtrl->redirectByClass("ilobjtestgui", "infoScreen");
        } else {
            $activeId = (int) $_GET['active_id'];
        }
        
        return $activeId;
    }
    
    private function fetchPassParameter($activeId)
    {
        // fetch pass nr
        
        $maxPass = $this->object->_getMaxPass($activeId);
        if (isset($_GET["pass"]) && 0 <= (int) $_GET["pass"] && $maxPass >= (int) $_GET["pass"]) {
            $pass = $_GET["pass"];
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

        require_once 'Modules/Test/classes/class.ilObjAssessmentFolder.php';
        if (!ilObjAssessmentFolder::_mananuallyScoreableQuestionTypesExists()) {
            // allow only if at least one question type is marked for manual scoring
            ilUtil::sendFailure($this->lng->txt("manscoring_not_allowed"), true);
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
    protected function getDefaultCommand()
    {
        return 'manscoring';
    }
    
    /**
     * @return string
     */
    protected function getActiveSubTabId()
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
        
        // pass overview table
        require_once 'Modules/Test/classes/tables/class.ilTestPassManualScoringOverviewTableGUI.php';
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
            ilUtil::sendFailure(sprintf($lng->txt('tst_save_manscoring_failed'), $pass + 1));
            $this->showManScoringParticipantScreen($form);
            return false;
        }

        include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
        
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
            ilUtil::sendFailure(sprintf($lng->txt('tst_save_manscoring_failed'), $pass + 1));
            $this->showManScoringParticipantScreen($form);
            return false;
        }
        
        include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
        
        foreach ($questionGuiList as $questionId => $questionGui) {
            $reachedPoints = $form->getItemByPostVar("question__{$questionId}__points")->getValue();

            $finalized = (bool) $form->getItemByPostVar("{$questionId}__evaluated")->getchecked();

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
                    1,
                    $this->object->areObligationsEnabled()
                );
            }

            $feedback = ilUtil::stripSlashes(
                $form->getItemByPostVar("question__{$questionId}__feedback")->getValue(),
                false,
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
            );

            $this->object->saveManualFeedback($activeId, $questionId, $pass, $feedback, $finalized, true);

            $notificationData[$questionId] = array(
                'points' => $reachedPoints, 'feedback' => $feedback
            );
        }

        include_once "./Modules/Test/classes/class.ilObjTestAccess.php";
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_updateStatus(
            $this->object->getId(),
            ilObjTestAccess::_getParticipantId($activeId)
        );

        $manScoringDone = $form->getItemByPostVar("manscoring_done")->getChecked();
        ilTestService::setManScoringDone($activeId, $manScoringDone);

        $manScoringNotify = $form->getItemByPostVar("manscoring_notify")->getChecked();
        if ($manScoringNotify) {
            require_once 'Modules/Test/classes/notifications/class.ilTestManScoringParticipantNotification.php';

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

        require_once './Modules/Test/classes/class.ilTestScoring.php';
        $scorer = new ilTestScoring($this->object);
        $scorer->setPreserveManualScores(true);
        $scorer->recalculateSolutions();
        
        if ($this->object->getAnonymity() == 0) {
            $user_name = ilObjUser::_lookupName(ilObjTestAccess::_getParticipantId($activeId));
            $name_real_or_anon = $user_name['firstname'] . ' ' . $user_name['lastname'];
        } else {
            $name_real_or_anon = $lng->txt('anonymous');
        }
        ilUtil::sendSuccess(sprintf($lng->txt('tst_saved_manscoring_successfully'), $pass + 1, $name_real_or_anon), true);
        if ($redirect == true) {
            $ilCtrl->redirect($this, 'showManScoringParticipantScreen');
        } else {
            return true;
        }
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
                if ($participant['active_id'] == $_GET['active_id']) {
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

    private function buildManScoringParticipantForm($questionGuiList, $activeId, $pass, $initValues = false)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        require_once 'Services/Form/classes/class.ilFormSectionHeaderGUI.php';
        require_once 'Services/Form/classes/class.ilCustomInputGUI.php';
        require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
        require_once 'Services/Form/classes/class.ilTextInputGUI.php';
        require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
        
        $ilCtrl->setParameter($this, 'active_id', $activeId);
        $ilCtrl->setParameter($this, 'pass', $pass);
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        
        $form->setTitle(sprintf($lng->txt('manscoring_results_pass'), $pass + 1));
        $form->setTableWidth('100%');

        /** @var  assQuestionGUI $questionGUI */
        foreach ($questionGuiList as $questionId => $questionGUI) {
            $questionHeader = sprintf($lng->txt('tst_manscoring_question_section_header'), $questionGUI->object->getTitle());
            $questionSolution = $questionGUI->getSolutionOutput($activeId, $pass, false, false, true, false, false, true);
            $bestSolution = $questionGUI->object->getSuggestedSolutionOutput();
            $feedback = $this->object->getSingleManualFeedback($activeId, $questionId, $pass);
            $disabled = false;
            if (isset($feedback['finalized_evaluation']) && $feedback['finalized_evaluation'] == 1) {
                $disabled = true;
            }
        
            $sect = new ilFormSectionHeaderGUI();
            $sect->setTitle($questionHeader . ' [' . $this->lng->txt('question_id_short') . ': ' . $questionGUI->object->getId() . ']');
            $form->addItem($sect);

            $cust = new ilCustomInputGUI($lng->txt('tst_manscoring_input_question_and_user_solution'));
            $cust->setHtml($questionSolution);
            $form->addItem($cust);

            if ($questionGUI->supportsIntermediateSolutionOutput() && $questionGUI->hasIntermediateSolution($activeId, $pass)) {
                $questionGUI->setUseIntermediateSolution(true);
                $intermediateSolution = $questionGUI->getSolutionOutput($activeId, $pass, false, false, true, false, false, true);
                $questionGUI->setUseIntermediateSolution(false);
                $cust = new ilCustomInputGUI($lng->txt('autosavecontent'));
                $cust->setHtml($intermediateSolution);
                $form->addItem($cust);
            }

            $text = new ilTextInputGUI($lng->txt('tst_change_points_for_question'), "question__{$questionId}__points");
            if ($initValues) {
                $text->setValue(assQuestion::_getReachedPoints($activeId, $questionId, $pass));
            }
            if($disabled){
                $text->setDisabled($disabled);
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
                $area->setValue($this->object->getSingleManualFeedback($activeId, $questionId, $pass)['feedback']);
            }
            if($disabled){
                $area->setDisabled($disabled);
            }
            $form->addItem($area);

            $check = new ilCheckboxInputGUI($lng->txt('finalized_evaluation'), "{$questionId}__evaluated");
            if ($disabled) {
                $check->setChecked(true);
            }
            $form->addItem($check);

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
        if ($initValues && ilTestService::isManScoringDone($activeId)) {
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
    private function buildManScoringParticipantsTable($withData = false)
    {
        require_once 'Modules/Test/classes/tables/class.ilTestManScoringParticipantsTableGUI.php';
        $table = new ilTestManScoringParticipantsTableGUI($this);
        
        if ($withData) {
            $participantStatusFilterValue = $table->getFilterItemByPostVar('participant_status')->getValue();
            
            require_once 'Modules/Test/classes/class.ilTestParticipantList.php';
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
