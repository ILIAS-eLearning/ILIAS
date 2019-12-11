<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilMultipleTextsInputGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingTextsInputGUI extends ilMultipleTextsInputGUI
{
    /**
     * ilAssOrderingTextsInputGUI constructor.
     */
    public function __construct(ilAssOrderingFormValuesObjectsConverter $converter, $postVar)
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
        $manipulator = new ilAssOrderingDefaultElementFallback();
        $this->addFormValuesManipulator($manipulator);
        
        parent::__construct('', $postVar);
        
        $this->addFormValuesManipulator($converter);
    }
    
    /**
     * FOR COMPATIBILITY ONLY
     *
     * @param $stylingDisabled
     */
    public function setStylingDisabled($stylingDisabled)
    {
    }
    
    /**
     * FOR COMPATIBILITY ONLY
     *
     * @return bool
     */
    public function getStylingDisabled()
    {
        return false;
    }
    
    /**
     * @param ilAssOrderingElementList $elementList
     */
    public function setElementList(ilAssOrderingElementList $elementList)
    {
        $this->setIdentifiedMultiValues($elementList->getRandomIdentifierIndexedElements());
    }
    
    /**
     * @param integer $questionId
     * @return ilAssOrderingElementList
     */
    public function getElementList($questionId)
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
        return ilAssOrderingElementList::buildInstance($questionId, $this->getIdentifiedMultiValues());
    }
    
    /**
     * @param $value
     * @return bool
     */
    protected function valueHasContentText($value)
    {
        if ($value === null || is_array($value)) {
            return false;
        }
        
        if (is_object($value) && $value instanceof ilAssOrderingElement) {
            return (bool) strlen((string) $value);
        }
        
        return (bool) strlen($value);
    }
}
