<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilQuestionPoolFactory
{
    public function createNewInstance($parentRef = null)
    {
        // create new questionpool object
        $newObj = new ilObjQuestionPool(0, true);

        // set title of questionpool object to "dummy"
        $newObj->setTitle("dummy");
        
        // set description of questionpool object
        $newObj->setDescription("derived questionpool");

        // create the questionpool class in the ILIAS database (object_data table)
        $newObj->create(true);

        if ($parentRef) {
            // create a reference for the questionpool object in the ILIAS database (object_reference table)
            $newObj->createReference();
            
            // put the questionpool object in the administration tree
            $newObj->putInTree($parentRef);
            
            // get default permissions and set the permissions for the questionpool object
            $newObj->setPermissions($parentRef);
        }
        
        return $newObj;
    }
}
