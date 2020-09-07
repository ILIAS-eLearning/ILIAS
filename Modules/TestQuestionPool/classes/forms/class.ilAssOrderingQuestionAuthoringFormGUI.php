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
        $this->setAvailableCommandButtonIds(array(
            $this->buildCommandButtonId(OQ_TERMS),
            $this->buildCommandButtonId(OQ_PICTURES),
            $this->buildCommandButtonId(OQ_NESTED_TERMS),
            $this->buildCommandButtonId(OQ_NESTED_PICTURES)
        ));
        
        parent::__construct();
    }
    
    protected function setAvailableCommandButtonIds($availableCommandButtonIds)
    {
        $this->availableCommandButtonIds = $availableCommandButtonIds;
    }
    
    protected function getAvailableCommandButtonIds()
    {
        return $this->availableCommandButtonIds;
    }
    
    public function addSpecificOrderingQuestionCommandButtons(assOrderingQuestion $questionOBJ)
    {
        switch ($questionOBJ->getOrderingType()) {
            case OQ_TERMS:
                
                $this->addCommandButton(
                    "changeToPictures",
                    $this->lng->txt("oq_btn_use_order_pictures"),
                    $this->buildCommandButtonId(OQ_PICTURES)
                );
                $this->addCommandButton(
                    "orderNestedTerms",
                    $this->lng->txt("oq_btn_nest_terms"),
                    $this->buildCommandButtonId(OQ_NESTED_TERMS)
                );
                break;
            
            case OQ_PICTURES:
                
                $this->addCommandButton(
                    "changeToText",
                    $this->lng->txt("oq_btn_use_order_terms"),
                    $this->buildCommandButtonId(OQ_TERMS)
                );
                $this->addCommandButton(
                    "orderNestedPictures",
                    $this->lng->txt("oq_btn_nest_pictures"),
                    $this->buildCommandButtonId(OQ_NESTED_PICTURES)
                );
                break;
            
            case OQ_NESTED_TERMS:
                
                $this->addCommandButton(
                    "changeToPictures",
                    $this->lng->txt("oq_btn_use_order_pictures"),
                    $this->buildCommandButtonId(OQ_PICTURES)
                );
                $this->addCommandButton(
                    "changeToText",
                    $this->lng->txt("oq_btn_define_terms"),
                    $this->buildCommandButtonId(OQ_TERMS)
                );
                break;
            
            case OQ_NESTED_PICTURES:
                
                $this->addCommandButton(
                    "changeToText",
                    $this->lng->txt("oq_btn_use_order_terms"),
                    'assOrderingQuestionBtn_' . OQ_TERMS
                );
                $this->addCommandButton(
                    "changeToPictures",
                    $this->lng->txt("oq_btn_define_pictures"),
                    'assOrderingQuestionBtn_' . OQ_PICTURES
                );
                break;
        }
    }
    
    /**
     * @return ilIdentifiedMultiValuesInputGUI
     */
    public function getOrderingElementInputField()
    {
        return $this->getItemByPostVar(
            assOrderingQuestion::ORDERING_ELEMENT_FORM_FIELD_POSTVAR
        );
    }
    
    public function prepareValuesReprintable(assOrderingQuestion $questionOBJ)
    {
        $this->getOrderingElementInputField()->prepareReprintable($questionOBJ);
    }
    
    public function ensureReprintableFormStructure(assOrderingQuestion $questionOBJ)
    {
        $this->renewOrderingElementInput($questionOBJ);
        $this->renewOrderingCommandButtons($questionOBJ);
    }
    
    /**
     * @param assOrderingQuestion $questionOBJ
     * @throws ilTestQuestionPoolException
     */
    protected function renewOrderingElementInput(assOrderingQuestion $questionOBJ)
    {
        $replacingInput = $questionOBJ->buildOrderingElementInputGui();
        $questionOBJ->initOrderingElementAuthoringProperties($replacingInput);
        $dodgingInput = $this->getItemByPostVar($replacingInput->getPostVar());
        $replacingInput->setElementList($dodgingInput->getElementList($questionOBJ->getId()));
        $this->replaceFormItemByPostVar($replacingInput);
    }
    
    protected function buildCommandButtonId($orderingType)
    {
        return self::COMMAND_BUTTON_PREFIX . $orderingType;
    }
    
    protected function renewOrderingCommandButtons(assOrderingQuestion $questionOBJ)
    {
        $this->clearCommandButtons();
        $this->addSpecificOrderingQuestionCommandButtons($questionOBJ);
        $this->addGenericAssessmentQuestionCommandButtons($questionOBJ);
    }
}
