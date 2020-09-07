<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilMultipleImagesInputGUI.php';

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
     * @param string $filenameInput
     * @return bool
     */
    protected function isValidFilenameInput($filenameInput)
    {
        /* @var ilAssOrderingElement $filenameInput */
        return (bool) strlen($filenameInput->getContent());
    }
}
