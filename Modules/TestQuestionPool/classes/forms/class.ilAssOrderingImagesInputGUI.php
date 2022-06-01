<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingImagesInputGUI extends ilMultipleImagesInputGUI
{
    const POST_VARIABLE_NAME = 'ordering';
    
    /**
     * ilAssOrderingImagesInputGUI constructor.
     *
     * @param assOrderingQuestion $questionOBJ
     * @param string $postVar
     */
    public function __construct(ilAssOrderingFormValuesObjectsConverter $converter, $postVar)
    {
        require_once 'Modules/TestQuestionPool/classes/forms/class.ilAssOrderingDefaultElementFallback.php';
        $manipulator = new ilAssOrderingDefaultElementFallback();
        $this->addFormValuesManipulator($manipulator);
        
        parent::__construct('', $postVar);
        
        $this->addFormValuesManipulator($converter);
        
        self::$instanceCounter++;
    }
    
    public static $instanceCounter = 0;
    
    /**
     * FOR COMPATIBILITY ONLY
     *
     * @param $stylingDisabled
     */
    public function setStylingDisabled($stylingDisabled) : void
    {
    }
    
    /**
     * FOR COMPATIBILITY ONLY
     *
     * @return bool
     */
    public function getStylingDisabled() : bool
    {
        return false;
    }
    
    /**
     * @param ilAssOrderingElementList $elementList
     */
    public function setElementList(ilAssOrderingElementList $elementList) : void
    {
        $this->setIdentifiedMultiValues($elementList->getRandomIdentifierIndexedElements());
    }
    
    /**
     * @param integer $questionId
     * @return ilAssOrderingElementList
     */
    public function getElementList($questionId) : ilAssOrderingElementList
    {
        require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
        return ilAssOrderingElementList::buildInstance($questionId, $this->getIdentifiedMultiValues());
    }
    
    /**
     * @param string $filenameInput
     * @return bool
     */
    protected function isValidFilenameInput($filenameInput) : bool
    {
        /* @var ilAssOrderingElement $filenameInput */
        return (bool) strlen($filenameInput->getContent());
    }
}
