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
    private \ILIAS\TestQuestionPool\InternalRequestService $request;

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
     * @var ilGlobalTemplateInterface
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
     * @param ilGlobalTemplate $tpl
     * @param ilTabsGUI $tabs
     * @param ilLanguage $lng
     */
    public function __construct(assQuestionGUI $questionGUI, ilCtrl $ctrl, ilAccessHandler $access, ilGlobalTemplateInterface $tpl, ilTabsGUI $tabs, ilLanguage $lng)
    {
        $this->questionGUI = $questionGUI;
        $this->questionOBJ = $questionGUI->object;
        $this->feedbackOBJ = $questionGUI->object->feedbackOBJ;
        global $DIC;
        $this->request = $DIC->testQuestionPool()->internal()->request();
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
    public function executeCommand() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilHelp = $DIC['ilHelp']; /* @var ilHelpGUI $ilHelp */
        $ilHelp->setScreenIdComponent('qpl');

        $cmd = $this->ctrl->getCmd(self::CMD_SHOW);
        $nextClass = $this->ctrl->getNextClass($this);
        
        $this->ctrl->setParameter($this, 'q_id', $this->request->getQuestionId());

        $this->setContentStyle();

        switch ($nextClass) {
            case 'ilassspecfeedbackpagegui':
            case 'ilassgenfeedbackpagegui':
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
     * Set content style
     */
    protected function setContentStyle() : void
    {
        $this->tpl->addCss(ilObjStyleSheet::getContentStylePath(0));
    }

    /**
     * command for rendering the feedback editing form to the content area
     *
     * @access private
     */
    private function showFeedbackFormCmd() : void
    {
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
    private function saveFeedbackFormCmd() : void
    {
        $form = $this->buildForm();
        
        $form->setValuesByPost();
        
        if ($form->checkInput()) {
            $this->feedbackOBJ->saveGenericFormProperties($form);
            $this->feedbackOBJ->saveSpecificFormProperties($form);
            
            $this->questionOBJ->cleanupMediaObjectUsage();
            $this->questionOBJ->updateTimestamp();

            if ($this->isSyncAfterSaveRequired()) {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
                $this->ctrl->redirect($this, self::CMD_SHOW_SYNC);
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW);
        }
        
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    /**
     * builds the feedback editing form object
     *
     * @access private
     * @return \ilPropertyFormGUI
     */
    private function buildForm() : ilPropertyFormGUI
    {
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
    private function isFormSaveable() : bool
    {
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()
            && !($this->feedbackOBJ->isSaveableInPageObjectEditingMode())) {
            return false;
        }
        
        $hasWriteAccess = $this->access->checkAccess("write", "", $this->request->getRefId());
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
    private function isSyncAfterSaveRequired() : bool
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        
        if (!$this->request->isset("calling_test")) {
            return false;
        }
        
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            return false;
        }

        if($this->questionOBJ->getOriginalId() === null) {
            return false;
        }

        if (!$this->questionOBJ->_questionExistsInPool($this->questionOBJ->getOriginalId())) {
            return false;
        }
        
        if (!assQuestion::_isWriteable($this->questionOBJ->getOriginalId(), $ilUser->getId())) {
            return false;
        }
        
        return true;
    }
    
    public function showSyncCmd() : void
    {
        $this->questionGUI->originalSyncForm('', 'true');
    }
}
