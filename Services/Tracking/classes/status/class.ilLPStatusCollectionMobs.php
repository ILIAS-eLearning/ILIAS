<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Tracking/classes/class.ilLPStatus.php';
require_once 'Services/Tracking/classes/class.ilLearningProgress.php';

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @package ServicesTracking
 */
class ilLPStatusCollectionMobs extends ilLPStatus
{
    public static function _getInProgress($a_obj_id)
    {
        $users = array();
        
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        if (is_array($status_info["user_status"]["in_progress"])) {
            $users = $status_info["user_status"]["in_progress"];
        }
        return $users;
    }

    public static function _getCompleted($a_obj_id)
    {
        $users = array();
        
        $status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
        if (is_array($status_info["user_status"]["completed"])) {
            $users = $status_info["user_status"]["completed"];
        }
        
        return $users;
    }
    
    public static function _getStatusInfo($a_parent_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = array();
        
        $coll_items = self::getCollectionItems($a_parent_obj_id, true);
        
        $res["items"] = array_keys($coll_items);
        if (sizeof($res["items"])) {
            // titles
            foreach ($coll_items as $mob_id => $item) {
                $res["item_titles"][$mob_id] = $item["title"];
            }
            
            // status per item
            foreach ($res["items"] as $mob_id) {
                $res["completed"][$mob_id] = array();
                $res["in_progress"][$mob_id] = array();
            }
            
            $set = $ilDB->query("SELECT obj_id, usr_id FROM read_event" .
                " WHERE " . $ilDB->in("obj_id", $res["items"], "", "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $res["completed"][$row["obj_id"]][] = $row["usr_id"];
            }
            
            // status per user
            $tmp = array();
            foreach ($res["items"] as $mob_id) {
                foreach ($res["completed"][$mob_id] as $user_id) {
                    $tmp[$user_id][] = $mob_id;
                }
            }
            foreach ($tmp as $user_id => $completed_items) {
                if (sizeof($completed_items) == sizeof($res["items"])) {
                    $res["user_status"]["completed"][] = $user_id;
                } else {
                    $res["user_status"]["in_progress"][] = $user_id;
                }
            }
        }

        include_once './Services/Tracking/classes/class.ilChangeEvent.php';
        $users = ilChangeEvent::lookupUsersInProgress($a_parent_obj_id);
        foreach ($users as $user_id) {
            if ((!is_array($res["user_status"]["in_progress"]) || !in_array($user_id, $res["user_status"]["in_progress"])) &&
                (!is_array($res["user_status"]["completed"]) || !in_array($user_id, $res["user_status"]["completed"]))) {
                $res["user_status"]["in_progress"][] = $user_id;
            }
        }

        return $res;
    }
    
    protected static function getCollectionItems($a_obj_id, $a_include_titles = false)
    {
        $res = array();
        
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($a_obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection) {
            $possible = $collection->getPossibleItems();
                
            // there could be invalid items in the selection
            $valid = array_intersect(
                $collection->getItems(),
                array_keys($possible)
            );
            
            if ($a_include_titles) {
                foreach ($valid as $item_id) {
                    $res[$item_id] = $possible[$item_id];
                }
            } else {
                $res = $valid;
            }
        }
        
        return $res;
    }
    
    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;

        if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id)) {
            $status = self::LP_STATUS_IN_PROGRESS_NUM;
        }

        // an empty collection is always not attempted
        $items = self::getCollectionItems($a_obj_id);
        if (sizeof($items)) {
            // process mob status for user
                                    
            $found = array();
            
            $set = $ilDB->query("SELECT obj_id FROM read_event" .
                " WHERE usr_id = " . $ilDB->quote($a_user_id, "integer") .
                " AND " . $ilDB->in("obj_id", $items, "", "integer"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $found[] = $row["obj_id"];
            }
                    
            if (sizeof($found)) {
                $status = self::LP_STATUS_IN_PROGRESS_NUM;
                
                if (sizeof($found) == sizeof($items)) {
                    $status = self::LP_STATUS_COMPLETED_NUM;
                }
            }
        }
        
        return $status;
    }
}
