<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Services/Form
 */
interface ilFormValuesManipulator
{
    /**
     * @param array $inputValues
     * @return array $inputValues
     */
    public function manipulateFormInputValues($inputValues);
    
    /**
     * @param array $submitValues
     * @return array $submitValues
     */
    public function manipulateFormSubmitValues($submitValues);
}
