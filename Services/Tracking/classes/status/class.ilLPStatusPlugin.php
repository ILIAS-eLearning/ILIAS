<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
 * LP handler class for plugins
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @package ServicesTracking
 */
class ilLPStatusPlugin extends ilLPStatus
{
    /**
     * Get ilObjectPlugin for object id
     *
     * @param int $a_obj_id
     * @return ilObjectPlugin
     */
    protected static function initPluginObj($a_obj_id)
    {
        include_once "Services/Object/classes/class.ilObjectLP.php";
        $olp = ilObjectLP::getInstance($a_obj_id);
        return $olp->getPluginInstance();
    }

    public static function _getNotAttempted($a_obj_id)
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPNotAttempted();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData($a_obj_id, self::LP_STATUS_NOT_ATTEMPTED_NUM);
            }
        }
        return array();
    }

    public static function _getInProgress($a_obj_id)
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPInProgress();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData($a_obj_id, self::LP_STATUS_IN_PROGRESS_NUM);
            }
        }
        return array();
    }

    public static function _getCompleted($a_obj_id)
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPCompleted();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData($a_obj_id, self::LP_STATUS_COMPLETED_NUM);
            }
        }
        return array();
    }
    
    public static function _getFailed($a_obj_id)
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                return (array) $plugin->getLPFailed();
            } else {
                // re-use existing data for inactive plugin
                return self::getLPStatusData($a_obj_id, self::LP_STATUS_FAILED_NUM);
            }
        }
        return array();
    }
    
    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                // :TODO: create read_event here to make sure?
                return $plugin->getLPStatusForUser($a_user_id);
            } else {
                // re-use existing data for inactive plugin
                return self::getLPDataForUser($a_obj_id, $a_user_id);
            }
        }
        // #11368
        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }
    
    public function determinePercentage($a_obj_id, $a_user_id, $a_obj = null)
    {
        $plugin = self::initPluginObj($a_obj_id);
        if ($plugin) {
            if ($plugin !== ilPluginLP::INACTIVE_PLUGIN) {
                if (method_exists($plugin, "getPercentageForUser")) {
                    return $plugin->getPercentageForUser($a_user_id);
                }
            }
            // re-use existing data for inactive plugin
            return self::getPercentageForUser($a_obj_id, $a_user_id);
        }
        // #11368
        return 0;
    }
    
    /**
     * Read existing LP status data
     *
     * @param int $a_obj_id
     * @param int $a_status
     * @return array user ids
     */
    protected static function getLPStatusData($a_obj_id, $a_status)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $all = array();
        
        $set = $ilDB->query("SELECT usr_id" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND status = " . $ilDB->quote($a_status, "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $all[] = $row["usr_id"];
        }
        return $all;
    }
    
    /**
     * Read existing LP status data for user
     *
     * @param int $a_obj_id
     * @param int $a_user_id
     * @return int
     */
    protected static function getLPDataForUser($a_obj_id, $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT status" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        $status = $row["status"];
        if (!$status) {
            $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        }
        return $status;
    }
    
    protected static function getPercentageForUser($a_obj_id, $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT percentage" .
            " FROM ut_lp_marks" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND usr_id = " . $ilDB->quote($a_user_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["percentage"];
    }
}
