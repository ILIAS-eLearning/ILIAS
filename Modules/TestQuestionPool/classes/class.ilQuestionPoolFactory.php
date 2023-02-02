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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilQuestionPoolFactory
{
    public function createNewInstance($parentRef = null): ilObjQuestionPool
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
