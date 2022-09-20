<?php

declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLPStatusWrapper
 * This class is wrapper for all ilLPStatus classes.
 * It caches all function calls using the obj_id as key
 * TODO: get rid of static calls
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesTracking
 */
class ilLPStatusWrapper
{
    private static array $status_cache = array();
    private static array $info_cache = array();
    private static array $failed_cache = array();
    private static array $completed_cache = array();
    private static array $in_progress_cache = array();
    private static array $not_attempted_cache = array();

    /**
     * Static function to read the number of user who have the status 'not_attempted'
     */
    public static function _getCountNotAttempted(int $a_obj_id): int
    {
        return count(ilLPStatusWrapper::_getNotAttempted($a_obj_id));
    }

    /**
     * Static function to read the number of user who have the status 'not_attempted'
     */
    public static function _getNotAttempted(int $a_obj_id): array
    {
        if (isset(self::$not_attempted_cache[$a_obj_id])) {
            return self::$not_attempted_cache[$a_obj_id];
        }

        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        self::$not_attempted_cache[$a_obj_id] = $class::_getNotAttempted(
            $a_obj_id
        );

        return self::$not_attempted_cache[$a_obj_id];
    }

    /**
     * Static function to read the number of user who have the status 'in_progress'
     */
    public static function _getCountInProgress(int $a_obj_id): int
    {
        return count(ilLPStatusWrapper::_getInProgress($a_obj_id));
    }

    /**
     * Static function to read users who have the status 'in_progress'
     */
    public static function _getInProgress(int $a_obj_id): array
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
    public static function _getCountCompleted(int $a_obj_id): int
    {
        return count(ilLPStatusWrapper::_getCompleted($a_obj_id));
    }

    /**
     * Static function to read the users who have the status 'completed'
     */
    public static function _getCompleted(int $a_obj_id): array
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
    public static function _getCountFailed(int $a_obj_id): int
    {
        return count(ilLPStatusWrapper::_getFailed($a_obj_id));
    }

    /**
     * Static function to read the users who have the status 'completed'
     */
    public static function _getFailed(int $a_obj_id): array
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
    public static function _getStatusInfo(int $a_obj_id): array
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
    public static function _getTypicalLearningTime(int $a_obj_id): int
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
    public static function _getCountNotAttemptedByType(
        int $a_obj_id,
        string $a_type
    ): int {
        return count(
            ilLPStatusWrapper::_getNotAttemptedByType($a_obj_id, $a_type)
        );
    }

    public static function _getNotAttemptedByType(
        int $a_obj_id,
        string $a_type
    ): array {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getNotAttempted($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }

    public static function _getCountInProgressByType(
        int $a_obj_id,
        string $a_type
    ): int {
        return count(
            ilLPStatusWrapper::_getInProgressByType($a_obj_id, $a_type)
        );
    }

    public static function _getInProgressByType(
        int $a_obj_id,
        string $a_type
    ): array {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getInProgress($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }

    public static function _getCountCompletedByType(
        int $a_obj_id,
        string $a_type
    ): int {
        return count(
            ilLPStatusWrapper::_getCompletedByType($a_obj_id, $a_type)
        );
    }

    public static function _getCompletedByType(
        int $a_obj_id,
        string $a_type
    ): array {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getCompleted($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }

    public static function _getCountFailedByType(
        int $a_obj_id,
        string $a_type
    ): int {
        return count(ilLPStatusWrapper::_getFailedByType($a_obj_id, $a_type));
    }

    public static function _getFailedByType(
        int $a_obj_id,
        string $a_type
    ): array {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getFailed($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }

    public static function _getStatusInfoByType(
        int $a_obj_id,
        string $a_type
    ): array {
        static $cache = array();

        if (isset($cache[$a_obj_id . '_' . $a_type])) {
            return $cache[$a_obj_id . '_' . $a_type];
        }

        $class = ilLPStatusFactory::_getClassByIdAndType($a_obj_id, $a_type);
        $cache[$a_obj_id . '_' . $a_type] = $class::_getStatusInfo($a_obj_id);

        return $cache[$a_obj_id . '_' . $a_type];
    }

    public static function _updateStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null,
        bool $a_percentage = false,
        bool $a_force_raise = false
    ): void {
        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        if (strtolower(get_class($trac_obj)) != "illpstatus") {
            $trac_obj->_updateStatus(
                $a_obj_id,
                $a_usr_id,
                $a_obj,
                $a_percentage,
                $a_force_raise
            );
        }
    }

    public static function _setDirty(int $a_obj_id): void
    {
        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        $trac_obj->_setDirty($a_obj_id);
    }

    public static function _refreshStatus(
        int $a_obj_id,
        ?array $a_users = null
    ): void {
        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        $trac_obj->refreshStatus($a_obj_id, $a_users);
    }

    public static function _determineStatus(int $a_obj_id, int $a_usr_id): int
    {
        if (isset(self::$status_cache[$a_obj_id][$a_usr_id])) {
            return self::$status_cache[$a_obj_id][$a_usr_id];
        }

        $trac_obj = ilLPStatusFactory::_getInstance($a_obj_id);
        $st = $trac_obj->determineStatus($a_obj_id, $a_usr_id);

        self::$status_cache[$a_obj_id][$a_usr_id] = $st;
        return $st;
    }

    public static function _removeStatusCache(
        int $a_obj_id,
        int $a_usr_id
    ): void {
        unset(self::$status_cache[$a_obj_id][$a_usr_id]);
    }

    public static function _lookupCompletedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        return $class::_lookupCompletedForObject($a_obj_id, $a_user_ids);
    }

    public static function _lookupFailedForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        return $class::_lookupFailedForObject($a_obj_id, $a_user_ids);
    }

    public static function _lookupInProgressForObject(
        int $a_obj_id,
        ?array $a_user_ids = null
    ): array {
        $class = ilLPStatusFactory::_getClassById($a_obj_id);
        return $class::_lookupInProgressForObject($a_obj_id, $a_user_ids);
    }
}
