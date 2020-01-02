<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjDataCollectionAccess
 *
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id: class.ilObjDataCollectionAccess.php 15678 2008-01-06 20:40:55Z akill $
 *
 */
class ilObjDataCollectionAccess extends ilObjectAccess
{

    /**
     * get commands
     *
     * this method returns an array of all possible commands/permission combinations
     *
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     */
    public static function _getCommands()
    {
        $commands = array(
            array("permission" => "read", "cmd" => "render", "lang_var" => "show", "default" => true),
            array("permission" => "write", "cmd" => "listRecords", "lang_var" => "edit_content"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings"),
        );

        return $commands;
    }


    /**
     * check whether goto script will succeed
     */
    public static function _checkGoto($a_target)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "dcl" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1])) {
            return true;
        }

        return false;
    }


    /**
     * @param ilDclTable $table
     * @param            $ref_id
     *
     * @return bool
     */
    protected static function isTableInDataCollection($table, $ref_id)
    {
        foreach (ilObjDataCollection::_getAllReferences($table->getObjId()) as $reference) {
            if ($reference == $ref_id) {
                return true;
            }
        }
        return false;
    }


    /**
     * checks wether a user may invoke a command or not
     * (this method is called by ilAccessHandler::checkAccess)
     *
     * @param    string $a_cmd        command (not permission!)
     * @param    string $a_permission permission
     * @param    int    $a_ref_id     reference id
     * @param    int    $a_obj_id     object id
     * @param    int    $a_user_id    user id (if not provided, current user is taken)
     *
     * @return    boolean        true, if everything is ok
     */
    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if ($a_user_id == "") {
            $a_user_id = $ilUser->getId();
        }
        switch ($a_cmd) {
            case "view":

                if (!ilObjDataCollectionAccess::_lookupOnline($a_obj_id)
                    && !$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)
                ) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

                    return false;
                }
                break;

            // for permission query feature
            case "infoScreen":
                if (!ilObjDataCollectionAccess::_lookupOnline($a_obj_id)) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                } else {
                    $ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("online"));
                }
                break;
        }
        switch ($a_permission) {
            case "read":
            case "visible":
                if (!ilObjDataCollectionAccess::_lookupOnline($a_obj_id)
                    && (!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id))
                ) {
                    $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

                    return false;
                }
                break;
        }

        return true;
    }


    /**
     * Check wether datacollection is online
     *
     * @param    int $a_id wiki id
     */
    public static function _lookupOnline($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = "SELECT * FROM il_dcl_data WHERE id = " . $ilDB->quote($a_id, "integer");
        $dcl_set = $ilDB->query($q);
        $dcl_rec = $ilDB->fetchAssoc($dcl_set);

        return $dcl_rec["is_online"];
    }

    //
    // DataCollection specific Access-Checks
    //

    /**
     * @param $data_collection_id
     *
     * @depracated use checkActionForId instead
     * @return bool
     */
    public static function checkAccessForDataCollectionId($data_collection_id)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $perm = false;
        $references = ilObject2::_getAllReferences($data_collection_id);

        if ($ilAccess->checkAccess("add_entry", "", array_shift($references))) {
            $perm = true;
        }

        return $perm;
    }


    /**
     * @param $action
     * @param $obj_id
     *
     * @return bool
     */
    public static function checkActionForObjId($action, $obj_id)
    {
        foreach (ilObject2::_getAllReferences($obj_id) as $ref_id) {
            if (self::checkActionForRefId($action, $ref_id)) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param $action
     * @param $ref_id
     *
     * @return bool
     */
    public static function checkActionForRefId($action, $ref_id)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        /**
         * @var $ilAccess ilAccessHandler
         */

        return $ilAccess->checkAccess($action, "", $ref_id);
    }


    /**
     * @param     $ref int the reference id of the datacollection object to check.
     *
     * @param int $user_id
     *
     * @return bool whether or not the current user has admin/write access to the referenced datacollection
     */
    public static function hasWriteAccess($ref, $user_id = 0)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if ($user_id) {
            return $ilAccess->checkAccessOfUser($user_id, "write", "", $ref);
        }

        return $ilAccess->checkAccess("write", "", $ref);
    }


    /**
     * Has permission to view and edit all entries event when he is not the owner
     *
     * @param     $ref
     * @param int $user_id
     *
     * @return mixed
     */
    public static function hasEditAccess($ref, $user_id = 0)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if ($user_id) {
            return $ilAccess->checkAccessOfUser($user_id, "write", "", $ref);
        }

        return $ilAccess->checkAccess("edit_content", "", $ref);
    }


    /**
     * @param     $ref int the reference id of the datacollection object to check.
     * @param int $user_id
     *
     * @return bool whether or not the current user has admin/write access to the referenced datacollection
     */
    public static function hasAddRecordAccess($ref, $user_id = 0)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if ($user_id) {
            return $ilAccess->checkAccessOfUser($user_id, "write", "", $ref);
        }

        return $ilAccess->checkAccess("add_entry", "", $ref);
    }


    /**
     * @param     $ref int the reference id of the datacollection object to check.
     * @param int $user_id
     *
     * @return bool whether or not the current user has read access to the referenced datacollection
     */
    public static function hasReadAccess($ref, $user_id = 0)
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if ($user_id) {
            return $ilAccess->checkAccessOfUser($user_id, "write", "", $ref);
        }

        return $ilAccess->checkAccess("read", "", $ref);
    }


    /**
     * This only checks access to the tableview - if the full access check is required, use hasAccessTo($ref_id, $table_id, $tableview_id)
     *
     * @param integer|ilDclTableView $tableview can be object or id
     * @param int                    $user_id
     *
     * @return bool
     */
    public static function hasAccessToTableView($tableview, $user_id = 0)
    {
        global $DIC;
        $rbacreview = $DIC['rbacreview'];
        $ilUser = $DIC['ilUser'];
        if (!$tableview) {
            return false;
        }

        if (is_numeric($tableview)) {
            $tableview = ilDclTableView::find($tableview);
        }

        $assigned_roles = $rbacreview->assignedRoles($user_id ? $user_id : $ilUser->getId());
        $allowed_roles = $tableview->getRoles();

        return !empty(array_intersect($assigned_roles, $allowed_roles));
    }


    /**
     * returns true if either the table is visible for all users, or no tables are visible and this is
     * the table with the lowest order (getFirstVisibleTableId())
     *
     * @param $table_id
     *
     * @return bool
     */
    protected static function hasAccessToTable($table_id)
    {
        $table = ilDclCache::getTableCache($table_id);
        return $table->getIsVisible() || ($table_id == $table->getCollectionObject()->getFirstVisibleTableId());
    }


    /**
     * @param $ref_id
     * @param $table_id
     * @param $tableview_id
     *
     * @return bool
     */
    public static function hasAccessTo($ref_id, $table_id, $tableview_id)
    {
        /** @var ilDclTableView $tableview */
        $tableview = ilDclTableView::find($tableview_id);
        $table = ilDclCache::getTableCache($table_id);

        // is tableview in table and is table in datacollection
        if (($tableview->getTableId() != $table_id)
            || !self::isTableInDataCollection($table, $ref_id)) {
            return false;
        }

        // check access
        return self::hasWriteAccess($ref_id) || (
            self::hasReadAccess($ref_id) && self::hasAccessToTable($table_id) && self::hasAccessToTableView($tableview)
        );
    }


    /**
     * @param $ref_id
     * @param $table_id
     *
     * @return bool
     */
    public static function hasAccessToFields($ref_id, $table_id)
    {
        return self::isTableInDataCollection(ilDclCache::getTableCache($table_id), $ref_id)
            && (self::hasWriteAccess($ref_id));
    }


    /**
     * @param $ref_id
     * @param $table_id
     *
     * @return bool
     */
    public static function hasAccessToEditTable($ref_id, $table_id)
    {
        return self::hasAccessToFields($ref_id, $table_id);
    }


    /**
     * @param $ref_id
     * @param $table_id
     * @param $field_id
     *
     * @return bool
     */
    public static function hasAccessToField($ref_id, $table_id, $field_id)
    {
        $table = ilDclCache::getTableCache($table_id);
        return in_array($field_id, $table->getFieldIds()) && self::hasAccessToFields($ref_id, $table_id);
    }

    /**
     * @param int $ref_id
     *
     *
     * @return bool
     */
    public static function hasPermissionToAddRecord($ref_id, $table_id)
    {
        $table = ilDclCache::getTableCache($table_id);
        if (!self::isTableInDataCollection($table, $ref_id)) {
            return false;
        }

        return ilObjDataCollectionAccess::hasWriteAccess($ref_id)
            || (ilObjDataCollectionAccess::hasAddRecordAccess($ref_id) && $table->getAddPerm() && $table->checkLimit());
    }
}
