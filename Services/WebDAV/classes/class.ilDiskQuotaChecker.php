<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilDiskQuotaChecker.
 *
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id$
*
* @ingroup webdav
*/
class ilDiskQuotaChecker
{
    public function __construct()
    {
    }

    /**
    * Gets the disk quota info for the specified user account.
    *
    * Returns an associative array with information about the disk quota
    * of this user account.
    *
    * @param integer user id
    * @return array {
    *		"role"=>role_id,	// the id of the role which defines the highest
    *							// quota for the specified user account
    *		"role_title"=>string,	// the title of the role
    *
    *		"role_disk_quota"=>integer or positive infinity,
    *                           // the disk quota specified by the role in bytes.
    *                           // the disk quota is positive infinity if
    *							// the user has the system administrator role
    *
    *		"user_disk_quota"=>integer
    *							// the disk quota specified on the user account
    *                           // form.
    *
    *		"disk_quota"=>integer or positive infinity
    *							// the disk quota which is in effect. This is
    *                           // either role_quota or user_quota whichever
    *                           // is higher.
    *
    *		"last_reminder"=>string or null
    *							// the SQL datetime the last time a disk quota
    *							// reminder was sent to the user
    * }
    */
    public static function _lookupDiskQuota($a_user_id)
    {
        $info = array();

        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->queryf(
            "SELECT keyword, value " .
            "FROM usr_pref " .
            "WHERE usr_id = %s " .
            "AND keyword IN ('disk_quota', 'disk_quota_last_reminder')",
            array('integer'),
            array($a_user_id)
        );

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            switch ($row->keyword) {
                case 'disk_quota':
                    $info['user_disk_quota'] = $row->value;
                    break;
                case 'disk_quota_last_reminder':
                    $info['last_reminder'] = $row->value;
                    break;
            }
        }


        // Note: we order by role_id ASC here, in the assumption that
        //       the system role has the lowest ID of all roles.
        //       this way, if a user has the system role, this role
        //       will always returned first.
        $ilDB->setLimit(1);
        $res = $ilDB->queryf(
            "SELECT rd.role_id, rd.disk_quota, od.title " .
            "FROM rbac_ua ua " .
            "JOIN rbac_fa fa ON fa.rol_id=ua.rol_id AND fa.parent = %s " .
            "JOIN role_data rd ON ua.rol_id=rd.role_id " .
            "JOIN object_data od ON od.obj_id=rd.role_id " .
            "WHERE ua.usr_id = %s " .
            "ORDER BY disk_quota DESC, role_id ASC",
            array('integer','integer'),
            array(ROLE_FOLDER_ID, $a_user_id)
        );

        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $info['role_id'] = $row->role_id;
        $info['role_title'] = $row->title;

        // Note: Users with the system role have an infinite disk quota
        //       We calculate positive infinity by negating the logarithm of 0.
        $info['role_disk_quota']  = ($row->role_id == SYSTEM_ROLE_ID) ? -log(0) : $row->disk_quota;
        $info['disk_quota'] = max($info['user_disk_quota'], $info['role_disk_quota']);

        return $info;
    }

    /**
    * Gets the disk usage info for the specified user account.
    *
    * Returns an associative array with information about the disk usage
    * of this user account.
    *
    * @param integer user id
    * @return array {
    *		'last_update'=>datetime,	// SQL datetime of the last update
    *								// if this is null, the disk usage is unkown
    *
    *		'disk_usage'=>integer,	// the disk usage in bytes
    *
    *		'details' array(array('type'=>string,'count'=>integer,'size'=>integer),...)
    *                            // an associative array with the disk
    *                            // usage in bytes for each object type
    */
    public static function _lookupDiskUsage($a_user_id)
    {
        $info = array();
        $details = array();

        global $DIC;
        $ilDB = $DIC['ilDB'];

        $res = $ilDB->query(
            "SELECT keyword, value " .
            "FROM usr_pref " .
            "WHERE usr_id = " . $ilDB->quote($a_user_id, 'integer') . " " .
            "AND " . $ilDB->like("keyword", "text", 'disk\\_usage%')
        );

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            switch ($row->keyword) {
                case 'disk_usage.last_update':
                    $info['last_update'] = $row->value;
                    break;

                case 'disk_usage':
                    $info['disk_usage'] = $row->value;
                    break;

                default:
                    // The following preg_match is used to extract the type
                    // and the keys 'count' and 'size' from the keywords:
                    // disk_usage.type.count
                    // disk_usage.type.size
                    $matches = array();
                    preg_match('/^disk_usage\\.([^.]+)\\.([^.]+)/', $row->keyword, $matches);
                    $type = $matches[1];
                    $key = $matches[2];
                    if ($type) {
                        $detail_data = $details[$type];
                        if ($detail_data == null) {
                            $detail_data = array('type'=>$type);
                        }
                        $detail_data[$key] = $row->value;
                    }
                    $details[$type] = $detail_data;
                    break;
            }
        }
        $info['details'] = $details;


        //ilDiskQuotaChecker::_updateDiskUsageReport();

        return $info;
    }

    /**
    * Reads disk quota/disk usage report of the user accounts.
    *
    * Returns an array or associative arrays with information about the disk
    *  usage of each user account.
    *
    * @param string usage filter   values:
    *	1   = all users
    *	2   = only users who don't use disk space
    *	3   = only users who use disk space
    *	4   = only users who have exceeded their quota
    * @param string access filter   values:
    *	1   = all users
    *	2   = only users who have access
    *	4   = only users who have no access

    * @return array { array {
    *	'usr_id'=>integer,	 // the user id
    *	'login'=>string,     // the login
    *	'...'=>...,          // all other fields of table usr_data
    *
    *	'disk_usage'=>integer,	// the disk usage in bytes
    *	'disk_quota'=>integer,	// the disk quota in bytes
    * } }
    */
    public static function _fetchDiskQuotaReport($a_usage_filter = 3, $a_access_filter = 1, $a_order_column='disk_usage', $a_order_by='desc')
    {
        $data = array();
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (!$a_order_column) {
            $a_order_column = 'disk_usage';
        }

        switch ($a_usage_filter) {
            case 1: // all users
                $where_clause = '';
                break;
            case 2: // only users who don't use disk space
                $where_clause = 'WHERE (p2.value IS NULL) ';
                break;
            case 3: // only users who use disk space
            default:
                $where_clause = 'WHERE (p2.value+0 > 0) ';
                break;
            case 4: // only users who have exceeded their disk quota
                // #8554 / #10301
                $where_clause = 'WHERE (((p1.value+0 > rq.role_disk_quota OR rq.role_disk_quota IS NULL) AND p2.value+0 > p1.value+0) OR 
					((rq.role_disk_quota > p1.value+0 OR p1.value IS NULL) AND p2.value+0 > rq.role_disk_quota)) ';
                break;
        }
        switch ($a_access_filter) {
            case 1: // all users
                $where_clause .= '';
                break;
            case 2: // only users who have access
            default:
                $where_clause .= ($where_clause == '' ? 'WHERE ' : ' AND ') .
                    '(u.active=1 AND (u.time_limit_unlimited = 1 OR ' . $ilDB->unixTimestamp() . ' BETWEEN u.time_limit_from AND u.time_limit_until)) ';
                break;
            case 3: // only users who don't have access
                $where_clause .= ($where_clause == '' ? 'WHERE ' : ' AND ') .
                    '(u.active=0 OR (u.time_limit_unlimited IS NULL AND ' . $ilDB->unixTimestamp() . ' NOT BETWEEN u.time_limit_from AND u.time_limit_until)) ';
                break;
        }

        $res = $ilDB->queryf(
            "SELECT u.usr_id,u.firstname,u.lastname,u.login,u.email,u.last_login,u.active," .
                "u.time_limit_unlimited, " . $ilDB->fromUnixtime("u.time_limit_from") . ", " . $ilDB->fromUnixtime("u.time_limit_until") . "," .

                // Inactive users get the date 0001-01-01 so that they appear
                // first when the list is sorted by this field. Users with
                // unlimited access get the date 9999-12-31 so that they appear
                // last.
                "CASE WHEN u.active = 0 THEN '0001-01-01' ELSE CASE WHEN u.time_limit_unlimited=1 THEN '9999-12-31' ELSE " . $ilDB->fromUnixtime("u.time_limit_until") . " END END access_until," .

                "CASE WHEN " . $ilDB->unixTimestamp() . " BETWEEN u.time_limit_from AND u.time_limit_until THEN 0 ELSE 1 END expired," .
                "rq.role_disk_quota, system_role.rol_id role_id, " .
                "p1.value+0 user_disk_quota," .
                "p2.value+0 disk_usage, " .
                "p3.value last_update, " .
                "p4.value last_reminder, " .

                // We add 0 to some of the values to convert them into a number.
                // This is needed for correct sorting.
                "CASE WHEN rq.role_disk_quota>p1.value+0 OR p1.value IS NULL THEN rq.role_disk_quota ELSE p1.value+0 END disk_quota	" .
            "FROM usr_data u  " .

            // Fetch the role with the highest disk quota value.
            "JOIN (SELECT u.usr_id usr_id,MAX(rd.disk_quota) role_disk_quota " .
                "FROM usr_data u " .
                "JOIN rbac_ua ua ON ua.usr_id=u.usr_id " .
                "JOIN rbac_fa fa ON fa.rol_id=ua.rol_id AND fa.parent=%s  " .
                "JOIN role_data rd ON rd.role_id=ua.rol_id WHERE u.usr_id=ua.usr_id GROUP BY u.usr_id) rq ON rq.usr_id=u.usr_id " .

            // Fetch the system role in order to determine whether the user has unlimited disk quota
            "LEFT JOIN rbac_ua system_role ON system_role.usr_id=u.usr_id AND system_role.rol_id = %s " .

            // Fetch the user disk quota from table usr_pref
            "LEFT JOIN usr_pref p1 ON p1.usr_id=u.usr_id AND p1.keyword = 'disk_quota'  " .

            // Fetch the disk usage from table usr_pref
            "LEFT JOIN usr_pref p2 ON p2.usr_id=u.usr_id AND p2.keyword = 'disk_usage'  " .

            // Fetch the last update from table usr_pref
            "LEFT JOIN usr_pref p3 ON p3.usr_id=u.usr_id AND p3.keyword = 'disk_usage.last_update'  " .

            // Fetch the date when the last disk quota reminder was sent from table usr_pref
            "LEFT JOIN usr_pref p4 ON p4.usr_id=u.usr_id AND p4.keyword = 'disk_quota_last_reminder'  " .

            $where_clause .
            "ORDER BY " . $a_order_column . " " . ($a_order_by=='asc'?' ASC':' DESC') . ", " .
                "lastname, firstname, login",
            array('integer','integer'),
            array(ROLE_FOLDER_ID, SYSTEM_ROLE_ID)
        );
        $previous_usr_id = null;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            if ($previous_usr_id != $row['usr_id']) {
                $data[] = $row;
                $previous_usr_id = $row['usr_id'];
            }
        }
        return $data;
    }

    /**
     * Updates the disk usage info of all user accounts.
     *
     * The result is stored in usr_pref of each user.
     * The following keywords are used:
     *
     * 'disk_usage_last_update' - the unix timestamp of the last update.
     * 'disk_usage.file'		- the disk usage of file objects owned by the
     *							  user.
     */
    public static function _updateDiskUsageReport()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // delete old values
        $ilDB->manipulate("DELETE FROM usr_pref " .
            "WHERE " . $ilDB->like("keyword", "text", 'disk_usage%'));


        require_once 'Modules/File/classes/class.ilObjFileAccess.php';
        self::__updateDiskUsageReportOfType(new ilObjFileAccess(), 'file');

        self::__updateDiskUsageReportOfType(new ilObjForumAccess(), 'frm');

        require_once 'Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php';
        self::__updateDiskUsageReportOfType(new ilObjFileBasedLMAccess(), 'htlm');

        require_once 'Modules/MediaCast/classes/class.ilObjMediaCastAccess.php';
        self::__updateDiskUsageReportOfType(new ilObjMediaCastAccess(), 'mcst');

        require_once 'Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php';
        self::__updateDiskUsageReportOfType(new ilObjSAHSLearningModuleAccess(), 'sahs');

        require_once 'Services/Mail/classes/class.ilObjMailAccess.php';
        self::__updateDiskUsageReportOfUsers(new ilObjMailAccess(), 'mail_attachment');

        // insert the sum of the disk usage of each user
        // note: second % is needed to not fail on oracle char field
        $ilDB->manipulate(
            "INSERT INTO usr_pref " .
            "(usr_id, keyword, value) " .
            "SELECT usr_id, 'disk_usage', SUM(value) " .
            "FROM usr_pref " .
            "WHERE " . $ilDB->like("keyword", "text", 'disk_usage.%.size%') .
            "GROUP BY usr_id"
        );

        // insert last update
        $ilDB->manipulate("INSERT INTO usr_pref " .
            "(usr_id, keyword, value) " .
            "SELECT usr_id, 'disk_usage.last_update', " . $ilDB->now() . " " .
            "FROM usr_data");
    }

    /**
     * Updates the disk usage report of the specified object type for
     * all user accounts.
     * The results are stored in table usr_pref.
     * For each user which owns files the following rows are inserted:
     *
     * @param $a_access_obj Object		A access object, such as ilObjFileAccess.
     * @param $a_type		string	The type of the access object, such as 'file'.
     *
     * Keyword					Value	Description
     * 'disk_usage.file.count'	integer	The number of files owned by the user
     * 'disk_usage.file.usage'	integer	The disk usage of the files
     */
    private static function __updateDiskUsageReportOfType($a_access_obj, $a_type)
    {
        $data = array();
        
        // repository
        $res = self::__getRepositoryObjectsByType($a_type);
        self::__updateDiskUsageReportOfTypeHelper($a_access_obj, $res, $data);
        
        // personal workspace
        $res = self::__getWorkspaceObjectsByType($a_type);
        self::__updateDiskUsageReportOfTypeHelper($a_access_obj, $res, $data);
        
        // saving result to DB
        if ($data) {
            foreach ($data as $owner => $item) {
                self::__saveUserData($owner, $a_type, $item["size"], $item["count"]);
            }
        }
    }
    
    /**
     * Save disk quota for user
     *
     * @param int $a_user_id
     * @param string $a_type
     * @param int $a_size
     * @param int $a_count
     */
    private static function __saveUserData($a_user_id, $a_type, $a_size, $a_count)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        if ($a_user_id && $a_size != null && $a_count != null) {
            $ilDB->manipulate("INSERT INTO usr_pref " .
                "(usr_id, keyword, value) " .
                "VALUES " .
                "(" . $ilDB->quote($a_user_id, 'integer') . ", " .
                $ilDB->quote('disk_usage.' . $a_type . '.size') . ", " .
                $ilDB->quote($a_size, 'integer') . ")");

            $ilDB->manipulate(
                "INSERT INTO usr_pref " .
                "(usr_id, keyword, value) " .
                "VALUES " .
                "(" . $ilDB->quote($a_user_id, 'integer') . ", " .
                $ilDB->quote('disk_usage.' . $a_type . '.count') . ", " .
                $ilDB->quote($a_count, 'integer') . ")"
            );
        }
    }
    
    /**
     * for each objects of an owner, count the number of objects and sum up
     * the size
     *
     * @param object $a_access_obj
     * @param object $a_objects
     * @param array $a_result
     */
    private static function __updateDiskUsageReportOfTypeHelper($a_access_obj, $a_objects, &$a_result)
    {
        while ($row = $a_objects->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->owner != null) {
                if (!array_key_exists($row->owner, $a_result)) {
                    // initialize values
                    $a_result[$row->owner] = array("size" => 0, "count" => 0);
                }

                $a_result[$row->owner]["size"] += $a_access_obj->_lookupDiskUsage($row->obj_id);
                $a_result[$row->owner]["count"]++;
            }
        }
    }

    /**
     * get all objects of the desired type which are in the repository
     * ordered by owner
     *
     * @param type $a_type
     * @return DB result object
     */
    private static function __getRepositoryObjectsByType($a_type)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        return $ilDB->query(
            "SELECT DISTINCT d.obj_id, d.owner " .
            "FROM object_data d " .
            "JOIN object_reference r ON d.obj_id=r.obj_id " .
            "JOIN tree t ON t.child=r.ref_id " .
            "WHERE d.type = " . $ilDB->quote($a_type, "text") . " " .
            "AND t.tree=1 " .
            "ORDER BY d.owner"
        );
    }
    
    /**
     * get all objects of the desired type which are in the personal workspace
     * ordered by owner
     *
     * @param type $a_type
     * @return DB result object
     */
    private static function __getWorkspaceObjectsByType($a_type)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        return $ilDB->query(
            "SELECT DISTINCT d.obj_id, d.owner " .
            "FROM object_data d " .
            "JOIN object_reference_ws r ON d.obj_id=r.obj_id " .
            "JOIN tree_workspace t ON t.child=r.wsp_id " .
            "WHERE d.type = " . $ilDB->quote($a_type, "text") . " " .
            "AND t.tree=d.owner " .
            "ORDER BY d.owner"
        );
    }

    /**
     * Updates the disk usage report of the specified object type for
     * all user accounts.
     * The results are stored in table usr_pref.
     * For each user which owns files the following rows are inserted:
     *
     * @param $a_access_obj Object		A access object, such as ilObjFileAccess.
     * @param $a_type		string	The type of the access object, such as 'file'.
     *
     * Keyword					Value	Description
     * 'disk_usage.file.count'	integer	The number of files owned by the user
     * 'disk_usage.file.usage'	integer	The disk usage of the files
     */
    private static function __updateDiskUsageReportOfUsers($a_access_obj, $a_type)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // get all users
        $res = $ilDB->query("SELECT usr_id FROM usr_data");

        // for each user count the number of objects and sum up the size
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data = $a_access_obj->_lookupDiskUsageOfUser($row->usr_id);
            self::__saveUserData($row->usr_id, $a_type, $data["size"], $data["count"]);
        }
    }
    
    public static function _sendSummaryMails()
    {
        global $DIC;
        $ilSetting = $DIC['ilSetting'];
        
        $lastStart = $ilSetting->get('last_cronjob_disk_quota_sum_start_ts', 0);
        if (!$lastStart || date('dmY', $lastStart) != date('dmY')) {
            $ilSetting->set('last_cronjob_disk_quota_sum_start_ts', time());
            
            include_once 'Services/Mail/classes/class.ilDiskQuotaSummaryNotification.php';
            $dqsn = new ilDiskQuotaSummaryNotification();
            $dqsn->send();
        }
    }
    
    /**
     * Sends reminder e-mails to all users who have access and who have exceeded
     * their disk quota and who haven't received a reminder mail in the past 7
     * days.
     */
    public static function _sendReminderMails()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        require_once 'Services/Mail/classes/class.ilDiskQuotaReminderMail.php';
        $mail = new ilDiskQuotaReminderMail();

        $res = $ilDB->queryf(
            "SELECT u.usr_id,u.gender,u.firstname,u.lastname,u.login,u.email,u.last_login,u.active," .
                "u.time_limit_unlimited, " . $ilDB->fromUnixtime("u.time_limit_from") . ", " . $ilDB->fromUnixtime("u.time_limit_until") . "," .

                // Inactive users get the date 0001-01-01 so that they appear
                // first when the list is sorted by this field. Users with
                // unlimited access get the date 9999-12-31 so that they appear
                // last.
                "CASE WHEN u.active = 0 THEN '0001-01-01' ELSE CASE WHEN u.time_limit_unlimited=1 THEN '9999-12-31' ELSE " . $ilDB->fromUnixtime("u.time_limit_until") . " END END access_until," .

                " CASE WHEN " . $ilDB->unixTimestamp() . " BETWEEN u.time_limit_from AND u.time_limit_until THEN 0 ELSE 1 END expired," .
                "rq.role_disk_quota, system_role.rol_id role_id, " .
                "p1.value+0 user_disk_quota," .
                "p2.value+0 disk_usage, " .
                "p3.value last_update, " .
                "p4.value last_reminder, " .
                "p5.value language, " .

                // We add 0 to some of the values to convert them into a number.
                // This is needed for correct sorting.
                "CASE WHEN rq.role_disk_quota>p1.value+0 OR p1.value IS NULL THEN rq.role_disk_quota ELSE p1.value+0 END disk_quota	" .
            "FROM usr_data u  " .

            // Fetch the role with the highest disk quota value.
            "JOIN (SELECT u.usr_id usr_id,MAX(rd.disk_quota) role_disk_quota " .
                "FROM usr_data u " .
                "JOIN rbac_ua ua ON ua.usr_id=u.usr_id " .
                "JOIN rbac_fa fa ON fa.rol_id=ua.rol_id AND fa.parent=%s  " .
                "JOIN role_data rd ON rd.role_id=ua.rol_id WHERE u.usr_id=ua.usr_id GROUP BY u.usr_id) rq ON rq.usr_id=u.usr_id " .

            // Fetch the system role in order to determine whether the user has unlimited disk quota
            "LEFT JOIN rbac_ua system_role ON system_role.usr_id=u.usr_id AND system_role.rol_id = %s " .

            // Fetch the user disk quota from table usr_pref
            "LEFT JOIN usr_pref p1 ON p1.usr_id=u.usr_id AND p1.keyword = 'disk_quota'  " .

            // Fetch the disk usage from table usr_pref
            "LEFT JOIN usr_pref p2 ON p2.usr_id=u.usr_id AND p2.keyword = 'disk_usage'  " .

            // Fetch the last update from table usr_pref
            "LEFT JOIN usr_pref p3 ON p3.usr_id=u.usr_id AND p3.keyword = 'disk_usage.last_update'  " .

            // Fetch the date when the last disk quota reminder was sent from table usr_pref
            "LEFT JOIN usr_pref p4 ON p4.usr_id=u.usr_id AND p4.keyword = 'disk_quota_last_reminder'  " .

            // Fetch the language of the user
            "LEFT JOIN usr_pref p5 ON p5.usr_id=u.usr_id AND p5.keyword = 'language'  " .

            // Fetch only users who have exceeded their quota, and who have
            // access, and who have not received a reminder in the past seven days
            // #8554 / #10301
            'WHERE (((p1.value+0 >= rq.role_disk_quota OR rq.role_disk_quota IS NULL) AND p2.value+0 > p1.value+0) OR 
				((rq.role_disk_quota > p1.value+0 OR p1.value IS NULL) AND p2.value+0 > rq.role_disk_quota)) ' .
            'AND (u.active=1 AND (u.time_limit_unlimited = 1 OR ' . $ilDB->unixTimestamp() . ' BETWEEN u.time_limit_from AND u.time_limit_until)) ' .
            'AND (p4.value IS NULL OR p4.value < DATE_SUB(NOW(), INTERVAL 7 DAY)) ',
            array('integer','integer'),
            array(ROLE_FOLDER_ID, SYSTEM_ROLE_ID)
        );

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $details = self::_lookupDiskUsage($row['usr_id']);
            $row['disk_usage_details'] = $details['details'];

            // Send reminder e-mail
            $mail->setData($row);
            $mail->send();

            // Store the date the last reminder was sent in the table usr_pref.
            if ($row['last_reminder'] != null) {
                $ilDB->manipulatef(
                    "UPDATE usr_pref SET value= " . $ilDB->now() . " " .
                    "WHERE usr_id=%s AND keyword = 'disk_quota_last_reminder'",
                    array('integer'),
                    array($row['usr_id'])
                );
            } else {
                $ilDB->manipulatef(
                    "INSERT INTO usr_pref (usr_id, keyword, value) " .
                    "VALUES (%s, 'disk_quota_last_reminder', " . $ilDB->now() . ")",
                    array('integer'),
                    array($row['usr_id'])
                );
            }
        }
    }

    /**
    * Returns the SQL datetime of the last update of the disk usage report.
    * Returns null, if the disk usage report has never been made.
    */
    public static function _lookupDiskUsageReportLastUpdate()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        require_once 'Services/Mail/classes/class.ilDiskQuotaReminderMail.php';
        $mail = new ilDiskQuotaReminderMail();

        $res = $ilDB->query("SELECT MAX(value) last_update " .
            "FROM usr_pref WHERE keyword='disk_usage.last_update'");
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return ($row != null) ? $row['last_update'] : null;
    }
    
    public static function _lookupPersonalWorkspaceDiskQuota($a_user_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $info = array();

        $res = $ilDB->queryf(
            "SELECT keyword, value " .
            "FROM usr_pref " .
            "WHERE usr_id = %s " .
            "AND keyword = %s ",
            array('integer', 'text'),
            array($a_user_id, "wsp_disk_quota")
        );

        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $info['user_wsp_disk_quota'] = $row->value;
                

        // Note: we order by role_id ASC here, in the assumption that
        //       the system role has the lowest ID of all roles.
        //       this way, if a user has the system role, this role
        //       will always returned first.
        $ilDB->setLimit(1);
        $res = $ilDB->queryf(
            "SELECT rd.role_id, rd.wsp_disk_quota, od.title " .
            "FROM rbac_ua ua " .
            "JOIN rbac_fa fa ON fa.rol_id=ua.rol_id AND fa.parent = %s " .
            "JOIN role_data rd ON ua.rol_id=rd.role_id " .
            "JOIN object_data od ON od.obj_id=rd.role_id " .
            "WHERE ua.usr_id = %s " .
            "ORDER BY wsp_disk_quota DESC, role_id ASC",
            array('integer','integer'),
            array(ROLE_FOLDER_ID, $a_user_id)
        );

        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        $info['role_id'] = $row->role_id;
        $info['role_title'] = $row->title;

        // Note: Users with the system role have an infinite disk quota
        //       We calculate positive infinity by negating the logarithm of 0.
        $info['role_wsp_disk_quota']  = ($row->role_id == SYSTEM_ROLE_ID) ? -log(0) : $row->wsp_disk_quota;
        $info['disk_quota'] = max($info['user_wsp_disk_quota'], $info['role_wsp_disk_quota']);

        return $info;
    }
}
