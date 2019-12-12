<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/interfaces/interface.ilFormValuesManipulator.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssOrderingDefaultElementFallback implements ilFormValuesManipulator
{
    public function manipulateFormInputValues($inputValues)
    {
        if (!count($inputValues)) {
            require_once 'Modules/TestQuestionPool/classes/questions/class.ilAssOrderingElementList.php';
            $defaultElement = ilAssOrderingElementList::getFallbackDefaultElement();

            $inputValues[$defaultElement->getRandomIdentifier()] = $defaultElement;
        }
        
        return $inputValues;
    }
    
    public function manipulateFormSubmitValues($submitValues)
    {
        return $submitValues;
    }
}
