<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for feedback editing of assessment questions
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilAssQuestionFeedbackEditingGUI: ilAssGenFeedbackPageGUI, ilAssSpecFeedbackPageGUI
 * @ilCtrl_Calls ilAssQuestionFeedbackEditingGUI: ilPropertyFormGUI
 */
class ilAssQuestionFeedbackEditingGUI
{
    /**
     * command constants
     */
    const CMD_SHOW = 'showFeedbackForm';
    const CMD_SAVE = 'saveFeedbackForm';
    const CMD_SHOW_SYNC = 'showSync';
    
    /**
     * gui instance of current question
     *
     * @access protected
     * @var assQuestionGUI
     */
    protected $questionGUI = null;
    
    /**
     * object instance of current question
     *
     * @access protected
     * @var assQuestion
     */
    protected $questionOBJ = null;
    
    /**
     * object instance of current question's feedback
     *
     * @access protected
     * @var ilAssQuestionFeedback
     */
    protected $feedbackOBJ = null;
    
    /**
     * global $ilCtrl
     *
     * @access protected
     * @var ilCtrl
     */
    protected $ctrl = null;
    
    /**
     * global $ilAccess
     *
     * @access protected
     * @var ilAccess
     */
    protected $access = null;

    /**
     * global $tpl
     *
     * @access protected
     * @var ilTemplate
     */
    protected $tpl = null;

    /**
     * global $ilTabs
     *
     * @access protected
     * @var ilTabsGUI
     */
    protected $tabs = null;

    /**
     * global $lng
     *
     * @access protected
     * @var ilLanguage
     */
    protected $lng = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param assQuestionGUI $questionGUI
     * @param ilCtrl $ctrl
     * @param ilAccessHandler $access
     * @param ilTemplate $tpl
     * @param ilTabsGUI $tabs
     * @param ilLanguage $lng
     */
    public function __construct(assQuestionGUI $questionGUI, ilCtrl $ctrl, ilAccessHandler $access, ilTemplate $tpl, ilTabsGUI $tabs, ilLanguage $lng)
    {
        $this->questionGUI = $questionGUI;
        $this->questionOBJ = $questionGUI->object;
        $this->feedbackOBJ = $questionGUI->object->feedbackOBJ;
        
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tpl = $tpl;
        $this->tabs = $tabs;
        $this->lng = $lng;
    }
    
    /**
     * Execute Command
     *
     * @access public
     */
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */
        $ilHelp->setScreenIdComponent('qpl');

        $cmd = $this->ctrl->getCmd(self::CMD_SHOW);
        $nextClass = $this->ctrl->getNextClass($this);
        
        $this->ctrl->setParameter($this, 'q_id', (int) $_GET['q_id']);

        switch ($nextClass) {
            case 'ilassspecfeedbackpagegui':
            case 'ilassgenfeedbackpagegui':
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackPageObjectCommandForwarder.php';
                $forwarder = new ilAssQuestionFeedbackPageObjectCommandForwarder($this->questionOBJ, $this->ctrl, $this->tabs, $this->lng);
                $forwarder->forward();
                break;

            default:
                
                $cmd .= 'Cmd';
                $this->$cmd();
                break;
        }
    }
    
    /**
     * command for rendering the feedback editing form to the content area
     *
     * @access private
     */
    private function showFeedbackFormCmd()
    {
        require_once "./Services/Style/Content/classes/class.ilObjStyleSheet.php";
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
        $this->tpl->parseCurrentBlock();
        
        $form = $this->buildForm();

        $this->feedbackOBJ->initGenericFormProperties($form);
        $this->feedbackOBJ->initSpecificFormProperties($form);
        
        $this->tpl->setContent($this->ctrl->getHTML($form));
    }
    
    /**
     * command for processing the submitted feedback editing form.
     * first it validates the submitted values.
     * - in case of successfull validation it saves the properties and redirects to either form presentation again,
     *   or to the syncWithOriginal form for question
     * - in case of failed validation it renders the form with post values and error info to the content area again
     *
     * @access private
     */
    private function saveFeedbackFormCmd()
    {
        $form = $this->buildForm();
        
        $form->setValuesByPost();
        
        if ($form->checkInput()) {
            $this->feedbackOBJ->saveGenericFormProperties($form);
            $this->feedbackOBJ->saveSpecificFormProperties($form);
            
            $this->questionOBJ->cleanupMediaObjectUsage();
            $this->questionOBJ->updateTimestamp();

            if ($this->isSyncAfterSaveRequired()) {
                ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
                $this->ctrl->redirect($this, self::CMD_SHOW_SYNC);
            }

            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW);
        }
        
        ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    /**
     * builds the feedback editing form object
     *
     * @access private
     * @return \ilPropertyFormGUI
     */
    private function buildForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('feedback_generic'));
        $form->setTableWidth("100%");
        $form->setId("feedback");
        
        $this->feedbackOBJ->completeGenericFormProperties($form);
        $this->feedbackOBJ->completeSpecificFormProperties($form);
        
        if ($this->isFormSaveable()) {
            $form->addCommandButton(self::CMD_SAVE, $this->lng->txt("save"));
        }
        
        return $form;
    }
    
    /**
     * returns the fact wether the feedback editing form has to be saveable or not.
     * this depends on the additional content editing mode and the current question type,
     * as well as on fact wether the question is writable for current user or not,
     * or the fact if we are in self assessment mode or not
     *
     * @access private
     * @return boolean $isFormSaveable
     */
    private function isFormSaveable()
    {
        $isAdditionalContentEditingModePageObject = $this->questionOBJ->isAdditionalContentEditingModePageObject();
        $isSaveableInPageObjectEditingMode = $this->feedbackOBJ->isSaveableInPageObjectEditingMode();
        
        if ($isAdditionalContentEditingModePageObject && !$isSaveableInPageObjectEditingMode) {
            return false;
        }
        
        $hasWriteAccess = $this->access->checkAccess("write", "", $_GET['ref_id']);
        $isSelfAssessmentEditingMode = $this->questionOBJ->getSelfAssessmentEditingMode();
        
        return $hasWriteAccess || $isSelfAssessmentEditingMode;
    }
    
    /**
     * returns the fact wether the presentation of the question sync2pool form
     * is required after saving the form or not
     *
     * @access private
     * @return boolean $isSyncAfterSaveRequired
     */
    private function isSyncAfterSaveRequired()
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        
        if (!$_GET["calling_test"]) {
            return false;
        }
        
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            return false;
        }
        
        if (!$this->questionOBJ->_questionExistsInPool($this->questionOBJ->original_id)) {
            return false;
        }
        
        if (!assQuestion::_isWriteable($this->questionOBJ->original_id, $ilUser->getId())) {
            return false;
        }
        
        return true;
    }
    
    public function showSyncCmd()
    {
        $this->questionGUI->originalSyncForm('', 'true');
    }
}
