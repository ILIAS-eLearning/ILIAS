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
 * Helper class for local user accounts (in categories)
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLocalUser
{
    public ilDBInterface $db;
    public int $parent_id;

    public function __construct(
        int $a_parent_id
    ) {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->parent_id = $a_parent_id;
    }

    public function setParentId(int $a_parent_id): void
    {
        $this->parent_id = $a_parent_id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    /**
     * @param bool $access_with_orgunit
     * @return int[]
     */
    public static function _getFolderIds(
        bool $access_with_orgunit = false
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $access = $DIC->access();
        $rbacsystem = $DIC['rbacsystem'];
        $parent = [];

        $query = "SELECT DISTINCT(time_limit_owner) as parent_id FROM usr_data ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // Workaround for users with time limit owner "0".
            if (!$row->parent_id) {
                if ($rbacsystem->checkAccess('read_users', USER_FOLDER_ID) ||
                    ($access_with_orgunit && $access->checkPositionAccess(\ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, USER_FOLDER_ID))) {
                    $parent[] = (int) $row->parent_id;
                }
                continue;
            }

            if ($rbacsystem->checkAccess('read_users', $row->parent_id) ||
                ($access_with_orgunit && $access->checkPositionAccess(ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS, $row->parent_id))
                || $rbacsystem->checkAccess('cat_administrate_users', $row->parent_id)) {
                if ($row->parent_id) {
                    $parent[] = (int) $row->parent_id;
                }
            }
        }
        return $parent ?: [];
    }

    /**
     * @param int $a_filter
     * @return int[]
     */
    public static function _getAllUserIds(
        int $a_filter = 0
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        switch ($a_filter) {
            case 0:
                if (self::_getFolderIds()) {
                    $where = "WHERE " . $ilDB->in("time_limit_owner", self::_getFolderIds(), false, "integer") . " ";
                //$where .= '(';
                //$where .= implode(",",ilUtil::quoteArray(ilLocalUser::_getFolderIds()));
                //$where .= ')';
                } else {
                    //$where = "WHERE time_limit_owner IN ('')";
                    return [];
                }

                break;

            default:
                $where = "WHERE time_limit_owner = " . $ilDB->quote($a_filter, "integer") . " ";

                break;
        }

        $query = "SELECT usr_id FROM usr_data " . $where;
        $res = $ilDB->query($query);

        $users = [];
        while ($row = $ilDB->fetchObject($res)) {
            $users[] = (int) $row->usr_id;
        }

        return $users;
    }

    public static function _getUserFolderId(): int
    {
        return 7;
    }
}
