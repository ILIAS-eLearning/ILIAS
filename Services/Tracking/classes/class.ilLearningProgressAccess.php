<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * Learning progress access checks
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesTracking
 */
class ilLearningProgressAccess
{
    /**
     * wrapper for rbac access checks
     */
    public static function checkPermission($a_permission, $a_ref_id, $a_user_id = null)
    {
        if (is_null($a_user_id)) {
            $a_user_id = $GLOBALS['DIC']['ilUser']->getId();
        }
        
        // position access
        if ($a_permission == 'read_learning_progress') {
            return $GLOBALS['DIC']->access()->checkRbacOrPositionPermissionAccess(
                'read_learning_progress',
                'read_learning_progress',
                $a_ref_id
            );
        }
        return $GLOBALS['DIC']['ilAccess']->checkAccessOfUser($a_user_id, $a_permission, '', $a_ref_id);
    }


    /**
     * check access to learning progress
     *
     * @param int  $a_ref_id          reference id of object
     * @param bool $a_allow_only_read read access is sufficient (see courses/groups)
     *
     * @return bool
     */
    public static function checkAccess($a_ref_id, $a_allow_only_read = true)
    {
        global $DIC;

        if ($DIC->user()->getId() == ANONYMOUS_USER_ID) {
            return false;
        }

        if (!ilObjUserTracking::_enabledLearningProgress()) {
            return false;
        }
        
        $olp = ilObjectLP::getInstance(ilObject::_lookupObjId($a_ref_id));
        if (
            $DIC->access()->checkAccess('read_learning_progress', '', $a_ref_id) ||
            (
                $DIC->access()->checkRbacOrPositionPermissionAccess(
                    'read_learning_progress',
                    ilOrgUnitOperation::OP_READ_LEARNING_PROGRESS,
                    $a_ref_id
                ) && $olp->isActive()
            )
        ) {
            return true;
        }
        
        if (!ilObjUserTracking::_hasLearningProgressLearner()) {
            return false;
        }

        if (!$olp->isActive()) {
            return false;
        }
        
        if (!$DIC->access()->checkAccess('read', '', $a_ref_id)) {
            return false;
        }
        
        if ($a_allow_only_read) {
            return true;
        }
        
        return false;
    }
}
