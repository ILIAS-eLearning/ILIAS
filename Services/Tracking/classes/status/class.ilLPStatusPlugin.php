<?php declare(strict_types=0);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * LP handler class for plugins
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ServicesTracking
 */
class ilLPStatusPlugin extends ilLPStatus
{
    /**
     * Get ilObjectPlugin for object id
     * @param int $a_obj_id
     * @return ilObjectPlugin|int
     * @todo refactor return type
     */
    protected static function initPluginObj(int $a_obj_id)
    {
        $olp = ilObjectLP::getInstance($a_obj_id);
        return $olp->getPluginInstance();
    }

    public static function _getNotAttempted(int $a_obj_id) : array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPNotAttempted();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_NOT_ATTEMPTED_NUM
                );
            }
        }
        return array();
    }

    public static function _getInProgress(int $a_obj_id) : array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPInProgress();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_IN_PROGRESS_NUM
                );
            }
        }
        return array();
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPCompleted();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_COMPLETED_NUM
                );
            }
        }
        return array();
    }

    public static function _getFailed(int $a_obj_id) : array
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPFailed();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData(
                    $a_obj_id,
                    self::LP_STATUS_FAILED_NUM
                );
            }
        }
        return array();
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                // :TODO: create read_event here to make sure?
                return $plugin->getLPStatusForUser($a_usr_id);
            } else {
                // re-use existing data for inactive plugin
                return self::getLPDataForUser($a_obj_id, $a_usr_id);
            }
        }
        // #11368
        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ) : int {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                if (method_exists($plugin, "getPercentageForUser")) {
                    return $plugin->getPercentageForUser($a_usr_id);
                }
            }
            // re-use existing data for inactive plugin
            return self::getPercentageForUser($a_obj_id, $a_usr_id);
        }
        // #11368
        return 0;
    }

    /**
     * Read existing LP status data
     */
    protected static function getLPStatusData(
        int $a_obj_id,
        int $a_status
    ) : array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $all = array();
        $set = $ilDB->query(
            "SELECT usr_id" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND status = " . $ilDB->quote($a_status, "integer")
        );
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[] = (int) $row["usr_id"];
        }
        return $all;
    }

    /**
     * Read existing LP status data for user
     */
    protected static function getLPDataForUser(
        int $a_obj_id,
        int $a_user_id
    ) : int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT status" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $row = $ilDB->fetchAssoc($set);
        $status = $row["status"];
        if (!$status) {
            $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
        return $status;
    }

    protected static function getPercentageForUser(
        int $a_obj_id,
        int $a_user_id
    ) : int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT percentage" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer")
        );
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["percentage"];
    }
}
