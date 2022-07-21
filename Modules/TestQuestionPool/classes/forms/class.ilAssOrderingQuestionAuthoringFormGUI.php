<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssQuestionAuthoringFormGUI.php';
/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingQuestionAuthoringFormGUI extends ilAssQuestionAuthoringFormGUI
{
    const COMMAND_BUTTON_PREFIX = 'assOrderingQuestionBtn_';

    protected $availableCommandButtonIds = null;
    
    public function __construct()
    {
        $this->setAvailableCommandButtonIds(
            [
                $this->buildCommandButtonId(assOrderingQuestionGUI::CMD_SWITCH_TO_TERMS),
                $this->buildCommandButtonId(assOrderingQuestionGUI::CMD_SWITCH_TO_PICTURESS)
            ]
        );
        parent::__construct();
    }
    
    protected function setAvailableCommandButtonIds($availableCommandButtonIds) : void
    {
        $this->availableCommandButtonIds = $availableCommandButtonIds;
    }
    
    protected function getAvailableCommandButtonIds()
    {
        return $this->availableCommandButtonIds;
    }
    
    public function addSpecificOrderingQuestionCommandButtons(assOrderingQuestion $questionOBJ) : void
    {
        if ($questionOBJ->isImageOrderingType()) {
            $cmd = assOrderingQuestionGUI::CMD_SWITCH_TO_TERMS;
            $label = $this->lng->txt("oq_btn_use_order_terms");
        } else {
            $cmd = assOrderingQuestionGUI::CMD_SWITCH_TO_PICTURESS;
            $label = $this->lng->txt("oq_btn_use_order_pictures");
        }

        $id = $this->buildCommandButtonId($cmd);
        $this->addCommandButton($cmd, $label, $id);
    }

    public function getOrderingElementInputField()
    {
        return $this->getItemByPostVar(
            assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
        );
    }
    
    public function prepareValuesReprintable(assOrderingQuestion $questionOBJ) : void
    {
        $this->getOrderingElementInputField()->prepareReprintable($questionOBJ);
    }
    
    public function ensureReprintableFormStructure(assOrderingQuestion $questionOBJ) : void
    {
        $this->renewOrderingElementInput($questionOBJ);
        $this->renewOrderingCommandButtons($questionOBJ);
    }
    
    /**
     * @param assOrderingQuestion $questionOBJ
     * @throws ilTestQuestionPoolException
     */
    protected function renewOrderingElementInput(assOrderingQuestion $questionOBJ) : void
    {
        $replacingInput = $questionOBJ->buildOrderingElementInputGui();
        $questionOBJ->initOrderingElementAuthoringProperties($replacingInput);
        $dodgingInput = $this->getItemByPostVar($replacingInput->getPostVar());
        $replacingInput->setElementList($dodgingInput->getElementList($questionOBJ->getId()));
        $this->replaceFormItemByPostVar($replacingInput);
    }

    protected function buildCommandButtonId($id) : string
    {
        return self::COMMAND_BUTTON_PREFIX . $id;
    }

    protected function renewOrderingCommandButtons(assOrderingQuestion $questionOBJ) : void
    {
        $this->clearCommandButtons();
        $this->addSpecificOrderingQuestionCommandButtons($questionOBJ);
        $this->addGenericAssessmentQuestionCommandButtons($questionOBJ);
    }
}
