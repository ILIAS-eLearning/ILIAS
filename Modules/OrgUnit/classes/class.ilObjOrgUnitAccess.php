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
 ********************************************************************
 */
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnitAccess
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 */
class ilObjOrgUnitAccess extends ilObjectAccess
{

    /**
     * get commands
     * this method returns an array of all possible commands/permission combinations
     * example:
     * $commands = array
     *    (
     *        array('permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show'),
     *        array('permission' => 'write', 'cmd' => 'edit', 'lang_var' => 'edit'),
     *    );
     */
    public static function _getCommands() : array
    {
        $commands = [
            [
                'permission' => 'read',
                'cmd' => 'view',
                'lang_var' => 'show',
                'default' => true,
            ],
        ];

        return $commands;
    }

    public static function _checkAccessStaff(int $ref_id) : bool
    {
        global $DIC;

        return ($DIC->access()->checkAccess('write', '', $ref_id)
                || $DIC->access()->checkAccess('view_learning_progress', '', $ref_id))
            && $DIC->access()->checkAccess('read', '', $ref_id);
    }

    public static function _checkAccessSettings(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }

    public static function _checkAccessExport(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }

    public static function _checkAccessTypes(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }

    public static function _checkAccessPositions(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }

    public static function _checkAccessStaffRec(int $ref_id) : bool
    {
        global $DIC;

        return ($DIC->access()->checkAccess('write', '', $ref_id)
                || $DIC->access()->checkAccess('view_learning_progress_rec', '', $ref_id))
            && $DIC->access()->checkAccess('read', '', $ref_id);
    }

    public static function _checkAccessAdministrateUsers(int $ref_id) : bool
    {
        global $DIC;

        return ilUserAccountSettings::getInstance()->isLocalUserAdministrationEnabled()
            && $DIC->access()->checkAccess('cat_administrate_users', '', $ref_id);
    }

    public static function _checkAccessToUserLearningProgress(int $ref_id, int $usr_id) : bool
    {
        global $DIC;

        //Permission to view the Learning Progress of an OrgUnit: Employees
        if ($DIC->access()->checkAccess('view_learning_progress', '', $ref_id)
            && in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getEmployees($ref_id, false))
        ) {
            return true;
        }
        //Permission to view the Learning Progress of an OrgUnit: Superiors
        if ($DIC->access()->checkAccess('view_learning_progress', '', $ref_id)
            && in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getSuperiors($ref_id, false))
        ) {
            return true;
        }

        //Permission to view the Learning Progress of an OrgUnit or SubOrgUnit!: Employees
        if ($DIC->access()->checkAccess('view_learning_progress_rec', '', $ref_id)
            && in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getEmployees($ref_id, true))
        ) {
            return true;
        }

        //Permission to view the Learning Progress of an OrgUnit or SubOrgUnit!: Superiors
        if ($DIC->access()->checkAccess('view_learning_progress_rec', '', $ref_id)
            && in_array($usr_id, ilObjOrgUnitTree::_getInstance()->getSuperiors($ref_id, true))
        ) {
            return true;
        }

        return false;
    }


    public static function _checkGoto(string $a_target) : bool
    {
        global $DIC;

        $t_arr = explode('_', $a_target);
        if ($t_arr[0] !== 'orgu' || ((int) $t_arr[1]) <= 0) {
            return false;
        }
        if ($DIC->access()->checkAccess('read', '', $t_arr[1])) {
            return true;
        }

        return false;
    }
}
