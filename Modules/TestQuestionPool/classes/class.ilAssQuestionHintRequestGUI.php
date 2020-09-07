<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintAbstractGUI.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintTracking.php';

/**
 * GUI class for management/output of hint requests during test session
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 *
 * @ilCtrl_Calls ilAssQuestionHintRequestGUI: ilAssQuestionHintsTableGUI
 * @ilCtrl_Calls ilAssQuestionHintRequestGUI: ilConfirmationGUI, ilPropertyFormGUI, ilAssHintPageGUI
 */
class ilAssQuestionHintRequestGUI extends ilAssQuestionHintAbstractGUI
{
    /**
     * command constants
     */
    const CMD_SHOW_LIST = 'showList';
    const CMD_SHOW_HINT = 'showHint';
    const CMD_CONFIRM_REQUEST = 'confirmRequest';
    const CMD_PERFORM_REQUEST = 'performRequest';
    const CMD_BACK_TO_QUESTION = 'backToQuestion';
    
    /**
     * @var mixed
     */
    protected $parentGUI = null;

    /**
     * @var string
     */
    protected $parentCMD = null;
    
    /**
     * @var mixed
     */
    protected $questionHintTracking = null;
    
    /**
     * Constructor
     */
    public function __construct($parentGUI, $parentCMD, assQuestionGUI $questionGUI, $questionHintTracking)
    {
        $this->parentGUI = $parentGUI;
        $this->parentCMD = $parentCMD;
        $this->questionHintTracking = $questionHintTracking;
        
        parent::__construct($questionGUI);
    }
    
    /**
     * Execute Command
     *
     * @access	public
     * @global	ilCtrl	$ilCtrl
     * @return	mixed
     */
    public function executeCommand()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        
        $cmd = $ilCtrl->getCmd(self::CMD_SHOW_LIST);
        $nextClass = $ilCtrl->getNextClass($this);

        switch ($nextClass) {
            case 'ilasshintpagegui':
                
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintPageObjectCommandForwarder.php';
                $forwarder = new ilAssQuestionHintPageObjectCommandForwarder($this->questionOBJ, $ilCtrl, $ilTabs, $lng);
                $forwarder->setPresentationMode(ilAssQuestionHintPageObjectCommandForwarder::PRESENTATION_MODE_REQUEST);
                $forwarder->forward();
                break;

            default:
                
                $cmd .= 'Cmd';
                return $this->$cmd();
                break;
        }
    }
    
    /**
     * shows the list of allready requested hints
     *
     * @access	private
     */
    private function showListCmd()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        
        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsTableGUI.php';

        $questionHintList = $this->questionHintTracking->getRequestedHintsList();

        $table = new ilAssQuestionHintsTableGUI(
            $this->questionOBJ,
            $questionHintList,
            $this,
            self::CMD_SHOW_LIST
        );

        $this->populateContent($ilCtrl->getHtml($table));
    }
    
    /**
     * shows an allready requested hint
     *
     * @access	private
     * @global	ilCtrl $ilCtrl
     * @global	ilTemplate $tpl
     * @global	ilLanguage $lng
     */
    private function showHintCmd()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        
        if (!isset($_GET['hintId']) || !(int) $_GET['hintId']) {
            throw new ilTestException('no hint id given');
        }
        
        $isRequested = $this->questionHintTracking->isRequested((int) $_GET['hintId']);
        
        if (!$isRequested) {
            throw new ilTestException('hint with given id is not yet requested for given testactive and testpass');
        }
        
        $questionHint = ilAssQuestionHint::getInstanceById((int) $_GET['hintId']);
        
        require_once 'Services/Utilities/classes/class.ilUtil.php';
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        require_once 'Services/Form/classes/class.ilNonEditableValueGUI.php';

        // build form
        
        $form = new ilPropertyFormGUI();
        
        $form->setFormAction($ilCtrl->getFormAction($this));
        
        $form->setTableWidth('100%');

        $form->setTitle(sprintf(
            $lng->txt('tst_question_hints_form_header_edit'),
            $questionHint->getIndex(),
            $this->questionOBJ->getTitle()
        ));
        
        $form->addCommandButton(self::CMD_BACK_TO_QUESTION, $lng->txt('tst_question_hints_back_to_question'));
        
        $numExistingRequests = $this->questionHintTracking->getNumExistingRequests();
                
        if ($numExistingRequests > 1) {
            $form->addCommandButton(self::CMD_SHOW_LIST, $lng->txt('button_show_requested_question_hints'));
        }
        
        // form input: hint text
        
        $nonEditableHintText = new ilNonEditableValueGUI($lng->txt('tst_question_hints_form_label_hint_text'), 'hint_text', true);
        $nonEditableHintText->setValue(ilUtil::prepareTextareaOutput($questionHint->getText(), true));
        $form->addItem($nonEditableHintText);
        
        // form input: hint points
        
        $nonEditableHintPoints = new ilNonEditableValueGUI($lng->txt('tst_question_hints_form_label_hint_points'), 'hint_points');
        $nonEditableHintPoints->setValue($questionHint->getPoints());
        $form->addItem($nonEditableHintPoints);
        
        $this->populateContent($ilCtrl->getHtml($form));
    }
    
    /**
     * shows a confirmation screen for a hint request
     *
     * @access	private
     * @global	ilCtrl $ilCtrl
     * @global	ilTemplate $tpl
     * @global	ilLanguage $lng
     */
    private function confirmRequestCmd()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        try {
            $nextRequestableHint = $this->questionHintTracking->getNextRequestableHint();
        } catch (ilTestNoNextRequestableHintExistsException $e) {
            $ilCtrl->redirect($this, self::CMD_BACK_TO_QUESTION);
        }
        
        require_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        
        $confirmation = new ilConfirmationGUI();
        
        $formAction = ilUtil::appendUrlParameterString(
            $ilCtrl->getFormAction($this),
            "hintId={$nextRequestableHint->getId()}"
        );
                
        $confirmation->setFormAction($formAction);
        
        $confirmation->setConfirm($lng->txt('tst_question_hints_confirm_request'), self::CMD_PERFORM_REQUEST);
        $confirmation->setCancel($lng->txt('tst_question_hints_cancel_request'), self::CMD_BACK_TO_QUESTION);
        
        $confirmation->setHeaderText(sprintf(
            $lng->txt('tst_question_hints_request_confirmation'),
            $nextRequestableHint->getIndex(),
            $nextRequestableHint->getPoints()
        ));
        
        $this->populateContent($ilCtrl->getHtml($confirmation));
    }
    
    /**
     * Performs a hint request and invokes the (re-)saving the question solution.
     * Redirects to local showHint command
     *
     * @access	private
     * @global	ilCtrl $ilCtrl
     */
    private function performRequestCmd()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        if (!isset($_GET['hintId']) || !(int) $_GET['hintId']) {
            throw new ilTestException('no hint id given');
        }
        
        try {
            $nextRequestableHint = $this->questionHintTracking->getNextRequestableHint();
        } catch (ilTestNoNextRequestableHintExistsException $e) {
            $ilCtrl->redirect($this, self::CMD_BACK_TO_QUESTION);
        }
        
        if ($nextRequestableHint->getId() != (int) $_GET['hintId']) {
            throw new ilTestException('given hint id does not relate to the next requestable hint');
        }

        $this->questionHintTracking->storeRequest($nextRequestableHint);
        
        $redirectTarget = $this->getHintPresentationLinkTarget($nextRequestableHint->getId(), false);
        
        ilUtil::redirect($redirectTarget);
    }
    
    /**
     * gateway command method to jump back to test session output
     *
     * @access	private
     * @global	ilCtrl $ilCtrl
     */
    private function backToQuestionCmd()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        $ilCtrl->redirect($this->parentGUI, $this->parentCMD);
    }
    
    /**
     * populates the rendered questin hint relating output content to global template
     * depending on possibly active kiosk mode
     *
     * @global ilTemplate $tpl
     * @param string $content
     */
    private function populateContent($content)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        
        if (!$this->isQuestionPreview() && $this->parentGUI->object->getKioskMode()) {
            $tpl->setBodyClass('kiosk');
            $tpl->setAddFooter(false);

            $tpl->addBlockFile(
                'CONTENT',
                'content',
                'tpl.il_tst_question_hints_kiosk_page.html',
                'Modules/TestQuestionPool'
            );
            
            $tpl->setVariable('KIOSK_HEAD', $this->parentGUI->getKioskHead());
            
            $tpl->setVariable('KIOSK_CONTENT', $content);
        } else {
            $tpl->setContent($content);
        }
    }
    
    private function isQuestionPreview()
    {
        if ($this->questionHintTracking instanceof ilAssQuestionPreviewHintTracking) {
            return true;
        }
        
        return false;
    }
    
    /**
     * returns the link target for hint request presentation
     *
     * @global ilCtrl $ilCtrl
     * @param integer $hintId
     * @param boolean $xmlStyle
     * @return string $linkTarget
     */
    public function getHintPresentationLinkTarget($hintId, $xmlStyle = true)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        
        if ($this->questionOBJ->isAdditionalContentEditingModePageObject()) {
            $ilCtrl->setParameterByClass('ilasshintpagegui', 'hint_id', $hintId);
            $linkTarget = $ilCtrl->getLinkTargetByClass('ilAssHintPageGUI', '', '', false, $xmlStyle);
        } else {
            $ilCtrl->setParameter($this, 'hintId', $hintId);
            $linkTarget = $ilCtrl->getLinkTarget($this, self::CMD_SHOW_HINT, '', false, $xmlStyle);
        }
        
        return $linkTarget;
    }
}
