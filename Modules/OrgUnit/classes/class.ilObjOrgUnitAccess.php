<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnitAccess
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilObjOrgUnitAccess extends ilObjectAccess
{

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
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


    /**
     * @param integer $ref_id
     *
     * @return bool
     */
    public static function _checkAccessStaff($ref_id) : bool
    {
        global $DIC;

        return ($DIC->access()->checkAccess('write', '', $ref_id)
                || $DIC->access()->checkAccess('view_learning_progress', '', $ref_id))
            && $DIC->access()->checkAccess('read', '', $ref_id);
    }

    /**
     * @param int $ref_id
     *
     * @return bool
     */
    public static function _checkAccessSettings(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }

    /**
     * @param int $ref_id
     *
     * @return bool
     */
    public static function _checkAccessExport(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }

    /**
     * @param int $ref_id
     *
     * @return bool
     */
    public static function _checkAccessTypes(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }

    /**
     * @param int $ref_id
     *
     * @return bool
     */
    public static function _checkAccessPositions(int $ref_id) : bool
    {
        global $DIC;

        return $DIC->access()->checkAccess('write', '', $ref_id);
    }


    /**
     * @param integer $ref_id
     *
     * @return bool
     */
    public static function _checkAccessStaffRec($ref_id) : bool
    {
        global $DIC;

        return ($DIC->access()->checkAccess('write', '', $ref_id)
                || $DIC->access()->checkAccess('view_learning_progress_rec', '', $ref_id))
            && $DIC->access()->checkAccess('read', '', $ref_id);
    }


    /**
     * @param integer $ref_id
     *
     * @return bool
     */
    public static function _checkAccessAdministrateUsers($ref_id) : bool
    {
        global $DIC;

        return ilUserAccountSettings::getInstance()->isLocalUserAdministrationEnabled()
            && $DIC->access()->checkAccess('cat_administrate_users', '', $ref_id);
    }


    /**
     * @param integer $ref_id
     * @param integer $usr_id
     *
     * @return bool
     */
    public static function _checkAccessToUserLearningProgress($ref_id, $usr_id) : bool
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


    /**
     * @param string $a_target check whether goto script will succeed
     *
     * @return bool
     */
    public static function _checkGoto($a_target) : bool
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
