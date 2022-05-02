<?php declare(strict_types=1);

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
 * Class ilObjObjectFolder
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjObjectFolder extends ilObject
{
    /**
     * @param int  $id                reference_id or object_id
     * @param bool $call_by_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct(int $id, bool $call_by_reference = true)
    {
        $this->type = "objf";
        parent::__construct($id, $call_by_reference);
    }


    /**
    * delete objectfolder and all related data
    * DISABLED
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete() : bool
    {
        // DISABLED
        return false;

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // put here objectfolder specific stuff

        // always call parent delete function at the end!!
        return true;
    }
}
