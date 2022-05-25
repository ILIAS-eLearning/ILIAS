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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilContainerReferenceAccess extends ilObjectAccess
{
    /**
     * Check if target is accessible and not deleted
     */
    public static function _isAccessible(int $a_ref_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();
        $access = $DIC->access();
        
        $obj_id = ilObject::_lookupObjId($a_ref_id);
        $query = "SELECT target_obj_id FROM container_reference " .
            "WHERE obj_id = " . $ilDB->quote($obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $target_id = 0;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $target_id = (int) $row->target_obj_id;
        }
        $target_ref_ids = ilObject::_getAllReferences($target_id);
        $target_ref_id = current($target_ref_ids);
        return
            !$tree->isDeleted($target_ref_id) &&
            $access->checkAccess('read', '', $target_ref_id);
    }
}
