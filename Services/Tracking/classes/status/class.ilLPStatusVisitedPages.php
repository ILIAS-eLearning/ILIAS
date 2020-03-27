<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tracking/classes/class.ilLPStatus.php';

/**
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @version $Id$
 *
 * @ingroup	ServicesTracking
 *
 */
class ilLPStatusVisitedPages extends ilLPStatus
{
    public static function _getInProgress($a_obj_id)
    {
        include_once './Services/Tracking/classes/class.ilChangeEvent.php';
        $users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
        
        $users = array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));
        
        return $users;
    }

    public static function _getCompleted($a_obj_id)
    {
        $users = array();
        
        $all_page_ids = self::getLMPages($a_obj_id);
        foreach (self::getVisitedPages($a_obj_id) as $user_id => $user_page_ids) {
            if (!(bool) sizeof(array_diff($all_page_ids, $user_page_ids))) {
                $users[] = $user_id;
            }
        }
        
        return $users;
    }

    /**
     * Determine status
     *
     * @param	integer		object id
     * @param	integer		user id
     * @param	object		object (optional depends on object type)
     * @return	integer		status
     */
    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        /* once completed will not be changed anymore
        if(ilLPStatus::_hasUserCompleted($a_obj_id, $a_user_id))
        {
            return self::LP_STATUS_COMPLETED_NUM;
        }
        */
        
        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        switch (ilObject::_lookupType($a_obj_id)) {
            case 'lm':
                if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id)) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;
                
                    if (self::hasVisitedAllPages($a_obj_id, $a_user_id)) {
                        $status = self::LP_STATUS_COMPLETED_NUM;
                    }
                }
                break;
        }
        
        return $status;
    }

    /**
     * Determine percentage
     *
     * @param	integer		object id
     * @param	integer		user id
     * @param	object		object (optional depends on object type)
     * @return	integer		percentage
     */
    public function determinePercentage($a_obj_id, $a_user_id, $a_obj = null)
    {
        /* once completed will not be changed anymore
        if(ilLPStatus::_hasUserCompleted($a_obj_id, $a_user_id))
        {
            return 100;
        }
        */
        
        $all_page_ids = sizeof(self::getLMPages($a_obj_id));
        if (!$all_page_ids) {
            return 0;
        }
        
        $user_page_ids = sizeof(self::getVisitedPages($a_obj_id, $a_user_id));
                    
        return floor($user_page_ids / $all_page_ids * 100);
    }
        
    
    //
    // HELPER
    //
    
    protected static function hasVisitedAllPages($a_obj_id, $a_user_id)
    {
        $all_page_ids = self::getLMPages($a_obj_id);
        if (!sizeof($all_page_ids)) {
            return false;
        }
        
        $user_page_ids = self::getVisitedPages($a_obj_id, $a_user_id);
        return !(bool) sizeof(array_diff($all_page_ids, $user_page_ids));
    }
                
    protected static function getLMPages($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = array();
        
        include_once "Services/COPage/classes/class.ilPageObject.php";
        
        $set = $ilDB->query("SELECT lm_data.obj_id" .
            " FROM lm_data" .
            " JOIN lm_tree ON (lm_tree.child = lm_data.obj_id)" .
            " WHERE lm_tree.lm_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND lm_data.type = " . $ilDB->quote("pg", "text"));
        while ($row = $ilDB->fetchAssoc($set)) {
            // only active pages (time-based activation not supported)
            if (ilPageObject::_lookupActive($row["obj_id"], "lm")) {
                $res[] = $row["obj_id"];
            }
        }
        
        return $res;
    }
    
    protected static function getVisitedPages($a_obj_id, $a_user_id = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $res = array();
        
        $all_page_ids = self::getLMPages($a_obj_id);
        if (!sizeof($all_page_ids)) {
            return $res;
        }
        
        $sql = "SELECT obj_id, usr_id" .
            " FROM lm_read_event" .
            " WHERE " . $ilDB->in("obj_id", $all_page_ids, "", "integer");
        
        if ($a_user_id) {
            $sql .= " AND usr_id = " . $ilDB->quote($a_user_id, "integer");
        }
        
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["usr_id"]][] = $row["obj_id"];
        }
        
        if ($a_user_id) {
            $res = (array) $res[$a_user_id];
        }
        
        return $res;
    }
}
