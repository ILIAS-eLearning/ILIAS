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


require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionAbstractPageObjectCommandForwarder.php';
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php';

/**
 * class can be used as forwarder for hint page object contexts
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionHintPageObjectCommandForwarder extends ilAssQuestionAbstractPageObjectCommandForwarder
{
    /**
     * presentation mode for authoring
     */
    const PRESENTATION_MODE_AUTHOR = 'PRESENTATION_MODE_AUTHOR';
    
    /**
     * presentation mode for authoring
     */
    const PRESENTATION_MODE_PREVIEW = 'PRESENTATION_MODE_PREVIEW';
    
    /**
     * presentation mode for requesting
     */
    const PRESENTATION_MODE_REQUEST = 'PRESENTATION_MODE_REQUEST';
    
    /**
     * currently set presentation mode
     *
     * @var string
     */
    protected $presentationMode;
    
    /**
     * object instance of question hint
     *
     * @access protected
     * @var ilAssQuestionHint
     */
    protected $questionHint;
    
    /**
     * Constructor
     *
     * @access public
     * @param assQuestion $questionOBJ
     * @param ilCtrl $ctrl
     * @param ilTabsGUI $tabs
     * @param ilLanguage $lng
     */
    public function __construct(assQuestion $questionOBJ, ilCtrl $ctrl, ilTabsGUI $tabs, ilLanguage $lng)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        parent::__construct($questionOBJ, $ctrl, $tabs, $lng);
        
        $this->questionHint = new ilAssQuestionHint();

        if (!$this->request->isset('hint_id') || !(int) $this->request->raw('hint_id') || !$this->questionHint->load((int) $this->request->raw('hint_id'))) {
            $main_tpl->setOnScreenMessage('failure', 'invalid hint id given: ' . (int) $this->request->raw('hint_id'), true);
            $this->ctrl->redirectByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST);
        }
    }
    
    /**
     * forward method
     *
     * @throws ilTestQuestionPoolException
     */
    public function forward() : void
    {
        switch ($this->getPresentationMode()) {
            case self::PRESENTATION_MODE_AUTHOR:
                
                $pageObjectGUI = $this->buildAuthorPresentationPageObjectGUI();
                break;
            
            case self::PRESENTATION_MODE_PREVIEW:
                
                $pageObjectGUI = $this->buildPreviewPresentationPageObjectGUI();
                break;
            
            case self::PRESENTATION_MODE_REQUEST:
                
                $pageObjectGUI = $this->buildRequestPresentationPageObjectGUI();
                break;
        }

        $this->ctrl->setParameter($pageObjectGUI, 'hint_id', $this->questionHint->getId());
        
        $this->ctrl->forwardCommand($pageObjectGUI);
    }
    
    /**
     * forwards the command to page object gui for author presentation
     */
    private function buildPreviewPresentationPageObjectGUI() : ilAssHintPageGUI
    {
        $this->tabs->setBackTarget(
            $this->lng->txt('tst_question_hints_back_to_hint_list'),
            $this->ctrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST)
        );
        
        $pageObjectGUI = $this->getPageObjectGUI(
            $this->questionHint->getPageObjectType(),
            $this->questionHint->getId()
        );
        
        $pageObjectGUI->setEnabledTabs(false);
        
        $pageObjectGUI->setPresentationTitle(
            ilAssQuestionHint::getHintIndexLabel($this->lng, $this->questionHint->getIndex())
        );
        
        return $pageObjectGUI;
    }

    /**
     * forwards the command to page object gui for author presentation
     */
    private function buildRequestPresentationPageObjectGUI() : ilAssHintPageGUI
    {
        $this->tabs->setBackTarget(
            $this->lng->txt('tst_question_hints_back_to_hint_list'),
            $this->ctrl->getLinkTargetByClass('ilAssQuestionHintRequestGUI', ilAssQuestionHintRequestGUI::CMD_SHOW_LIST)
        );
        
        $pageObjectGUI = $this->getPageObjectGUI(
            $this->questionHint->getPageObjectType(),
            $this->questionHint->getId()
        );
        
        $pageObjectGUI->setEnabledTabs(false);
        $pageObjectGUI->setEnableEditing(false);
        
        $pageObjectGUI->setPresentationTitle(
            ilAssQuestionHint::getHintIndexLabel($this->lng, $this->questionHint->getIndex())
        );
        
        return $pageObjectGUI;
    }
    
    /**
     * forwards the command to page object gui for author presentation
     */
    private function buildAuthorPresentationPageObjectGUI() : ilAssHintPageGUI
    {
        $this->tabs->setBackTarget(
            $this->lng->txt('tst_question_hints_back_to_hint_list'),
            $this->ctrl->getLinkTargetByClass('ilAssQuestionHintsGUI', ilAssQuestionHintsGUI::CMD_SHOW_LIST)
        );
        
        $this->ensurePageObjectExists(
            $this->questionHint->getPageObjectType(),
            $this->questionHint->getId()
        );
        
        $pageObjectGUI = $this->getPageObjectGUI(
            $this->questionHint->getPageObjectType(),
            $this->questionHint->getId()
        );
        
        $pageObjectGUI->setEnabledTabs(true);
        
        return $pageObjectGUI;
    }
    
    /**
     * getter for presentation mode
     *
     * @return string
     */
    public function getPresentationMode() : ?string
    {
        return $this->presentationMode;
    }
    
    /**
     * setter for presentation mode
     *
     * @param string $presentationMode
     * @throws ilTestQuestionPoolException
     */
    public function setPresentationMode($presentationMode) : void
    {
        switch ($presentationMode) {
            case self::PRESENTATION_MODE_AUTHOR:
            case self::PRESENTATION_MODE_PREVIEW:
            case self::PRESENTATION_MODE_REQUEST:
                
                $this->presentationMode = $presentationMode;
                break;
            
            default: throw new ilTestQuestionPoolException('invalid presentation mode given: ' . $presentationMode);
        }
    }
    
    /**
     * instantiates, initialises and returns a page object gui object
     */
    protected function getPageObjectGUI($pageObjectType, $pageObjectId) : ilAssHintPageGUI
    {
        include_once("./Modules/TestQuestionPool/classes/class.ilAssHintPageGUI.php");
        $pageObjectGUI = new ilAssHintPageGUI($pageObjectId);
        $pageObjectGUI->obj->addUpdateListener(
            $this->questionOBJ,
            'updateTimestamp'
        );
        return $pageObjectGUI;
    }
    
    /**
     * ensures an existing page object with giben type/id
     *
     * @access protected
     */
    protected function ensurePageObjectExists($pageObjectType, $pageObjectId) : void
    {
        include_once("./Modules/TestQuestionPool/classes/class.ilAssHintPage.php");
        if (!ilAssHintPage::_exists($pageObjectType, $pageObjectId)) {
            $pageObject = new ilAssHintPage();
            $pageObject->setParentId($this->questionOBJ->getId());
            $pageObject->setId($pageObjectId);
            $pageObject->createFromXML();
        }
    }
}
