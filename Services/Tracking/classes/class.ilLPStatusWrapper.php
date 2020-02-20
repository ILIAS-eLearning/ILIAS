<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Tracking/classes/class.ilLPStatusFactory.php';

/**
* Class ilLPStatusWrapper
* This class is wrapper for all ilLPStatus classes.
* It caches all function calls using the obj_id as key
* TODO: substitute all ilStatus calls with this functions
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesTracking
*/
class ilLPStatusWrapper
{
    private static $status_cache = array();
    private static $info_cache = array();
    private static $failed_cache = array();
    private static $completed_cache = array();
    private static $in_progress_cache = array();
    private static $not_attempted_cache = array();
    
    /**
    * Static function to read the number of user who have the status 'not_attempted'
    */
    public static function _getCountNotAttempted($a_obj_id)
    {
        return count(ilLPStatusWrapper::_getNotAttempted($a_obj_id));
    }

    /**
    * Static function to read the number of user who have the status 'not_attempted'
    */
    public static function _getNotAttempted($a_obj_id)
    {
        if (isset(self::$not_attempted_cache[$a_obj_id])) {
            return self::$not_attempted_cache[$a_obj_id];
        }

        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        self::$not_attempted_cache[$a_obj_id] = $class::_getNotAttempted($a_obj_id);
        
        return self::$not_attempted_cache[$a_obj_id];
    }

    /**
    * Static function to read the number of user who have the status 'in_progress'
    */
    public static function _getCountInProgress($a_obj_id)
    {
        return count(ilLPStatusWrapper::_getInProgress($a_obj_id));
    }

    /**
    * Static function to read users who have the status 'in_progress'
    */
    public static function _getInProgress($a_obj_id)
    {
        if (isset(self::$in_progress_cache[$a_obj_id])) {
            return self::$in_progress_cache[$a_obj_id];
        }

        global $DIC;

        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        self::$in_progress_cache[$a_obj_id] = $class::_getInProgress($a_obj_id);

        return self::$in_progress_cache[$a_obj_id];
    }
    
    /**
    * Static function to read the number of user who have the status 'completed'
    */
    public static function _getCountCompleted($a_obj_id)
    {
        return count(ilLPStatusWrapper::_getCompleted($a_obj_id));
    }

    /**
    * Static function to read the users who have the status 'completed'
    */
    public static function _getCompleted($a_obj_id)
    {
        if (isset(self::$completed_cache[$a_obj_id])) {
            return self::$completed_cache[$a_obj_id];
        }
        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        self::$completed_cache[$a_obj_id] = $class::_getCompleted($a_obj_id);

        return self::$completed_cache[$a_obj_id];
    }

    /**
    * Static function to read the number of user who have the status 'failed'
    */
    public static function _getCountFailed($a_obj_id)
    {
        return count(ilLPStatusWrapper::_getFailed($a_obj_id));
    }

    /**
    * Static function to read the users who have the status 'completed'
    */
    public static function _getFailed($a_obj_id)
    {
        if (isset(self::$failed_cache[$a_obj_id])) {
            return self::$failed_cache[$a_obj_id];
        }

        $class = ilLPStatusFactory::_getClassById($a_obj_id);

        self::$failed_cache[$a_obj_id] = $class::_getFailed($a_obj_id);

        return self::$failed_cache[$a_obj_id];
    }

    /**
    * Reads informations about the object e.g test results, tlt, number of visits
    */
    public static function _getStatusInfo($a_obj_id)
    {
        if (isset(self::$info_cache[$a_obj_id])) {
            return self::$info_cache[$a_obj_id];
        }

        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        self::$info_cache[$a_obj_id] = $class::_getStatusInfo($a_obj_id);
        return self::$info_cache[$a_obj_id];
    }

    public static function _resetInfoCaches($a_obj_id)
    {
        unset(self::$info_cache[$a_obj_id]);
        unset(self::$failed_cache[$a_obj_id]);
        unset(self::$completed_cache[$a_obj_id]);
        unset(self::$in_progress_cache[$a_obj_id]);
        unset(self::$not_attempted_cache[$a_obj_id]);
    }

    /**
    * Reads Typical learning time. Mode collection is recursive for all assigned items
    */
    public static function _getTypicalLearningTime($a_obj_id)
    {
        static $cache = array();

        if (isset($cache[$a_obj_id])) {
            return $cache[$a_obj_id];
        }

        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        $cache[$a_obj_id] = $class::_getTypicalLearningTime($a_obj_id);

        return $cache[$a_obj_id];
    }

    //////////////////////////////////////////////////////////////////////////////
    // Special functions for 'objects' that have not an entry in object_data
    // E.g. events
    ////////////////////////////////////////////////////////////////////////////

    /**
    * Static function to read the number of user who have the status 'not_attempted'
    */
    public static function _getCountNotAttemptedByType($a_obj_id, $a_type)
    {
        return count(ilLPStatusWrapper::_getNotAttemptedByType($a_obj_id, $a_type));
    }
    
    public static function _getNotAttemptedByType($a_obj_id, $a_type)
    {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getNotAttempted($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }
    
    public static function _getCountInProgressByType($a_obj_id, $a_type)
    {
        return count(ilLPStatusWrapper::_getInProgressByType($a_obj_id, $a_type));
    }
    
    public static function _getInProgressByType($a_obj_id, $a_type)
    {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getInProgress($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }
    
    public static function _getCountCompletedByType($a_obj_id, $a_type)
    {
        return count(ilLPStatusWrapper::_getCompletedByType($a_obj_id, $a_type));
    }
    
    public static function _getCompletedByType($a_obj_id, $a_type)
    {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getCompleted($a_obj_id);
        
        return $cache[$a_obj_id . '_' . $a_type];
    }
    
    public static function _getCountFailedByType($a_obj_id, $a_type)
    {
        return count(ilLPStatusWrapper::_getFailedByType($a_obj_id, $a_type));
    }
    
    public static function _getFailedByType($a_obj_id, $a_type)
    {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getFailed($a_obj_id);
        
        return $cache[$a_obj_id . '_' . $a_type];
    }
    
    public static function _getStatusInfoByType($a_obj_id, $a_type)
    {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getStatusInfo($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }
    
    /**
     * Update status
     *
     * @param
     * @return
     */
    public static function _updateStatus($a_obj_id, $a_usr_id, $a_obj = null, $a_percentage = false, $a_force_raise = false)
    {
        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        if (strtolower(get_class($trac_obj)) != "illpstatus") {
            $trac_obj->_updateStatus($a_obj_id, $a_usr_id, $a_obj, $a_percentage, $a_force_raise);
        }
    }
    
    /**
     * Set dirty
     *
     * @param	integer		object id
     */
    public static function _setDirty($a_obj_id)
    {
        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        $trac_obj->_setDirty($a_obj_id);
    }

    /**
     * Set dirty
     *
     * @param	integer		object id
     * @param	array		user ids
     */
    public static function _refreshStatus($a_obj_id, $a_users = null)
    {
        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        $trac_obj->refreshStatus($a_obj_id, $a_users);
    }

    /**
     * Determine status
     *
     * @param
     * @return
     */
    public static function _determineStatus($a_obj_id, $a_usr_id)
    {
        if (isset(self::$status_cache[$a_obj_id][$a_usr_id])) {
            return self::$status_cache[$a_obj_id][$a_usr_id];
        }

        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        $st = $trac_obj->determineStatus($a_obj_id, $a_usr_id);
        
        self::$status_cache[$a_obj_id][$a_usr_id] = $st;
        
        return $st;
    }
    
    public static function _removeStatusCache($a_obj_id, $a_usr_id)
    {
        unset(self::$status_cache[$a_obj_id][$a_usr_id]);
    }
    
    /**
     * Get completed users for object
     *
     * @param int $a_obj_id
     * @param array $a_user_ids
     * @return array
     */
    public static function _lookupCompletedForObject($a_obj_id, $a_user_ids = null)
    {
        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        return $class::_lookupCompletedForObject($a_obj_id, $a_user_ids);
    }
    
    /**
     * Get failed users for object
     *
     * @param int $a_obj_id
     * @param array $a_user_ids
     * @return array
     */
    public static function _lookupFailedForObject($a_obj_id, $a_user_ids = null)
    {
        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        return $class::_lookupFailedForObject($a_obj_id, $a_user_ids);
    }
    
    /**
     * Get in progress users for object
     *
     * @param int $a_obj_id
     * @param array $a_user_ids
     * @return array
     */
    public static function _lookupInProgressForObject($a_obj_id, $a_user_ids = null)
    {
        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        return $class::_lookupInProgressForObject($a_obj_id, $a_user_ids);
    }
}
