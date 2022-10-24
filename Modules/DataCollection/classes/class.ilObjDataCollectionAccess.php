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

/**
 * Class ilObjDataCollectionAccess
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id: class.ilObjDataCollectionAccess.php 15678 2008-01-06 20:40:55Z akill $
 */
class ilObjDataCollectionAccess extends ilObjectAccess
{
    /**
     * get commands
     * this method returns an array of all possible commands/permission combinations
     * example:
     * $commands = array
     *    (
     *        array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
     *        array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
     *    );
     */
    public static function _getCommands(): array
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
    public static function _checkGoto(string $target): bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        $t_arr = explode("_", $target);

        if ($t_arr[0] != "dcl" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            return true;
        }

        return false;
    }

    protected static function isTableInDataCollection(ilDclTable $table, int $ref_id): bool
    {
        if ($table->getObjId() !== null) {
            foreach (ilObjDataCollection::_getAllReferences($table->getObjId()) as $reference) {
                if ($reference == $ref_id) {
                    return true;
                }
            }
        }

        return false;
    }

    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null): bool
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if (is_null($user_id) === true) {
            $user_id = $ilUser->getId();
        }

        switch ($cmd) {
            case "view":

                if (!ilObjDataCollectionAccess::_lookupOnline($obj_id)
                    && !$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id)
                ) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

                    return false;
                }
                break;

            // for permission query feature
            case "infoScreen":
                if (!ilObjDataCollectionAccess::_lookupOnline($obj_id)) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
                } else {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_STATUS_MESSAGE, $lng->txt("online"));
                }
                break;
        }
        switch ($permission) {
            case "read":
            case "visible":
                if (!ilObjDataCollectionAccess::_lookupOnline($obj_id)
                    && (!$rbacsystem->checkAccessOfUser($user_id, 'write', $ref_id))
                ) {
                    $ilAccess->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $lng->txt("offline"));

                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Check wether datacollection is online
     * @param int $a_id wiki id
     */
    public static function _lookupOnline(int $a_id): bool
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
     * @depracated use checkActionForId instead
     * @return bool
     */
    public static function checkAccessForDataCollectionId(int $data_collection_id): bool
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

    public static function checkActionForObjId(string $action, int $obj_id): bool
    {
        foreach (ilObject2::_getAllReferences($obj_id) as $ref_id) {
            if (self::checkActionForRefId($action, $ref_id)) {
                return true;
            }
        }

        return false;
    }

    public static function checkActionForRefId(string $action, int $ref_id): bool
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
     * @return bool whether or not the current user has admin/write access to the referenced datacollection
     */
    public static function hasWriteAccess(int $ref, ?int $user_id = 0): bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if ($user_id) {
            return $ilAccess->checkAccessOfUser($user_id, "write", "", $ref);
        }

        return $ilAccess->checkAccess("write", "", $ref);
    }

    public static function hasEditAccess(int $ref, ?int $user_id = 0): bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if ($user_id) {
            return $ilAccess->checkAccessOfUser($user_id, "write", "", $ref);
        }

        return $ilAccess->checkAccess("edit_content", "", $ref);
    }

    /**
     * @param  $ref int the reference id of the datacollection object to check.
     * @return bool whether or not the current user has admin/write access to the referenced datacollection
     */
    public static function hasAddRecordAccess(int $ref, ?int $user_id = 0): bool
    {
        global $DIC;
        $ilAccess = $DIC['ilAccess'];

        if ($user_id) {
            return $ilAccess->checkAccessOfUser($user_id, "write", "", $ref);
        }

        return $ilAccess->checkAccess("add_entry", "", $ref);
    }

    /**
     * @param  $ref int the reference id of the datacollection object to check.
     * @return bool whether or not the current user has read access to the referenced datacollection
     */
    public static function hasReadAccess(int $ref, ?int $user_id = 0): bool
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
     * @param integer|ilDclTableView $tableview can be object or id
     */
    public static function hasAccessToTableView($tableview, ?int $user_id = 0): bool
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

        $assigned_roles = $rbacreview->assignedRoles($user_id ?: $ilUser->getId());
        $allowed_roles = $tableview->getRoles();

        return !empty(array_intersect($assigned_roles, $allowed_roles));
    }

    /**
     * returns true if either the table is visible for all users, or no tables are visible and this is
     * the table with the lowest order (getFirstVisibleTableId())
     * @param $table_id
     * @return bool
     */
    protected static function hasAccessToTable(int $table_id): bool
    {
        $table = ilDclCache::getTableCache($table_id);

        return $table->getIsVisible() || ($table_id == $table->getCollectionObject()->getFirstVisibleTableId());
    }

    public static function hasAccessTo(int $ref_id, int $table_id, int $tableview_id): bool
    {
        /** @var ilDclTableView $tableview */
        $tableview = ilDclTableView::find($tableview_id);
        $table = ilDclCache::getTableCache($table_id);

        // is tableview in table and is table in datacollection
        if (($tableview->getTableId() != $table_id)
            || !self::isTableInDataCollection($table, $ref_id)
        ) {
            return false;
        }

        // check access
        return self::hasWriteAccess($ref_id)
            || (
                self::hasReadAccess($ref_id) && self::hasAccessToTable($table_id) && self::hasAccessToTableView($tableview)
            );
    }

    public static function hasAccessToFields(int $ref_id, int $table_id): bool
    {
        return self::isTableInDataCollection(ilDclCache::getTableCache($table_id), $ref_id)
            && (self::hasWriteAccess($ref_id));
    }

    public static function hasAccessToEditTable(int $ref_id, int $table_id): bool
    {
        return self::hasAccessToFields($ref_id, $table_id);
    }

    public static function hasAccessToField(int $ref_id, int $table_id, int $field_id): bool
    {
        $table = ilDclCache::getTableCache($table_id);

        return in_array($field_id, $table->getFieldIds()) && self::hasAccessToFields($ref_id, $table_id);
    }

    public static function hasPermissionToAddRecord(int $ref_id, int $table_id): bool
    {
        $table = ilDclCache::getTableCache($table_id);
        if (!self::isTableInDataCollection($table, $ref_id)) {
            return false;
        }

        return ilObjDataCollectionAccess::hasWriteAccess($ref_id)
            || (ilObjDataCollectionAccess::hasAddRecordAccess($ref_id) && $table->getAddPerm() && $table->checkLimit());
    }
}
