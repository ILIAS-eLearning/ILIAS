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
 * class can be used as forwarder for feedback page object contexts
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/TestQuestionPool
 */
class ilAssQuestionFeedbackPageObjectCommandForwarder extends ilAssQuestionAbstractPageObjectCommandForwarder
{
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

        if (!$this->request->isset('feedback_id') || !(int) $this->request->raw('feedback_id') || !$questionOBJ->feedbackOBJ->checkFeedbackParent((int) $this->request->raw('feedback_id'))) {
            $main_tpl->setOnScreenMessage('failure', 'invalid feedback id given: ' . (int) $this->request->raw('feedback_id'), true);
            $this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
        }

        if (!$this->request->isset('feedback_type') || !ilAssQuestionFeedback::isValidFeedbackPageObjectType($this->request->raw('feedback_type'))) {
            $main_tpl->setOnScreenMessage('failure', 'invalid feedback type given: ' . $this->request->raw('feedback_type'), true);
            $this->ctrl->redirectByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW);
        }
    }

    /**
     * forward method
     */
    public function forward(): void
    {
        $pageObjectGUI = $this->getPageObjectGUI($this->request->raw('feedback_type'), $this->request->raw('feedback_id'));
        $pageObjectGUI->setEnabledTabs(true);

        $this->tabs->setBackTarget(
            $this->lng->txt('tst_question_feedback_back_to_feedback_form'),
            $this->ctrl->getLinkTargetByClass('ilAssQuestionFeedbackEditingGUI', ilAssQuestionFeedbackEditingGUI::CMD_SHOW)
        );

        $this->ctrl->setParameter($pageObjectGUI, 'feedback_id', $this->request->raw('feedback_id'));
        $this->ctrl->setParameter($pageObjectGUI, 'feedback_type', $this->request->raw('feedback_type'));

        $this->ctrl->forwardCommand($pageObjectGUI);
    }

    /**
     * ensures an existing page object with giben type/id
     *
     * @access protected
     */
    public function ensurePageObjectExists($pageObjectType, $pageObjectId): void
    {
        if ($pageObjectType == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK
            && !ilAssGenFeedbackPage::_exists($pageObjectType, $pageObjectId, '', true)) {
            $pageObject = new ilAssGenFeedbackPage();
            $pageObject->setParentId($this->questionOBJ->getId());
            $pageObject->setId($pageObjectId);
            $pageObject->createFromXML();
        }
        if ($pageObjectType == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_SPECIFIC_FEEDBACK
            && !ilAssSpecFeedbackPage::_exists($pageObjectType, $pageObjectId, '', true)) {
            $pageObject = new ilAssSpecFeedbackPage();
            $pageObject->setParentId($this->questionOBJ->getId());
            $pageObject->setId($pageObjectId);
            $pageObject->createFromXML();
        }
    }

    /**
     * instantiates, initialises and returns a page object gui class
     *
     * @access protected
     * @return ilAssGenFeedbackPageGUI|ilAssSpecFeedbackPageGUI
     */
    public function getPageObjectGUI($pageObjectType, $pageObjectId)
    {
        include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssQuestionFeedback.php");
        if ($pageObjectType == ilAssQuestionFeedback::PAGE_OBJECT_TYPE_GENERIC_FEEDBACK) {
            include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssGenFeedbackPageGUI.php");
            $pageObjectGUI = new ilAssGenFeedbackPageGUI($pageObjectId);
            $pageObjectGUI->obj->addUpdateListener(
                $this->questionOBJ,
                'updateTimestamp'
            );
            return $pageObjectGUI;
        }
        include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssSpecFeedbackPageGUI.php");
        $pageObjectGUI = new ilAssSpecFeedbackPageGUI($pageObjectId);
        $pageObjectGUI->obj->addUpdateListener(
            $this->questionOBJ,
            'updateTimestamp'
        );
        return $pageObjectGUI;
    }
}
