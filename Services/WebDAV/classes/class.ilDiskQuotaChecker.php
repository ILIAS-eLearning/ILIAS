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
* Class ilObjFileAccessSettingsAccess
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id$
*
* @ingroup webdav
*/
class ilDiskQuotaChecker
{
	function ilDiskQuotaChecker()
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
	*		"disk_quota"=>integer or postive infinity
	*							// the disk quota which is in effect. This is
	*                           // either role_quota or user_quota whichever
	*                           // is higher.
	* }
	*/
	public static function _lookupDiskQuota($a_user_id)
	{
		$info = array();

		global $ilDB;

		$ilDB->setLimit(1);
		$res = $ilDB->queryf("SELECT value ".
			"FROM usr_pref ".
			"WHERE usr_id = %s ".
			"AND keyword = 'disk_quota'",
	        array('integer'),
	        array($a_user_id));

		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		$info['user_disk_quota'] = $row->value;


		// Note: we order by role_id ASC here, in the assumption that
		//       the system role has the lowest ID of all roles.
		//       this way, if a user has the system role, this role
		//       will always returned first.
		$ilDB->setLimit(1);
		$res = $ilDB->queryf("SELECT rd.role_id, rd.disk_quota, od.title ".
			"FROM rbac_ua AS ua ".
			"JOIN rbac_fa AS fa ON fa.rol_id=ua.rol_id AND fa.parent = %s ".
			"JOIN role_data AS rd ON ua.rol_id=rd.role_id ".
			"JOIN object_data AS od ON od.obj_id=rd.role_id ".
			"WHERE ua.usr_id = %s ".
			"ORDER BY disk_quota DESC, role_id ASC",
	        array('integer','integer'),
	        array(ROLE_FOLDER_ID, $a_user_id));

		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
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

		global $ilDB;

		$res = $ilDB->query("SELECT keyword, value ".
			"FROM usr_pref ".
			"WHERE usr_id = ".$ilDB->quote($a_user_id, 'integer')." ".
			"AND keyword LIKE 'disk\\_usage%'"
			);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
			switch ($row->keyword)
			{
				case 'disk_usage.last_update' :
					$info['last_update'] = $row->value;
					break;

				case 'disk_usage' :
					$info['disk_usage'] = $row->value;
					break;

				default :
					// The following preg_match is used to extract the type
					// and the keys 'count' and 'size' from the keywords:
					// disk_usage.type.count
					// disk_usage.type.size
					$matches = array();
					preg_match('/^disk_usage\\.([^.]+)\\.([^.]+)/', $row->keyword, $matches);
					$type = $matches[1];
					$key = $matches[2];
					if ($type)
					{
						$detail_data = $details[$type];
						if ($detail_data == null) 
						{
							$detail_data = array('type'=>$type);
						}
						$detail_data[$key] = $row->value;
					}
					$details[$type] = $detail_data;
					break;
			}
		}
		$info['details'] = $details;


		//ilDiskQuotaChecker::_updateDiskUsageStatistics();

		return $info;
	}

	/**
	* Reads disk quota/disk usage statistics of the user accounts.
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
	public static function _fetchDiskUsageStatistics($a_usage_filter = 3, $a_access_filter = 1,  $a_order_column='disk_usage',$a_order_by='desc')
	{
		$data = array();
		global $ilDB;

		if (! $a_order_column) {
			$a_order_column = 'disk_usage';
		}

		switch ($a_usage_filter) {
			case 1:
				$where_clause = '';
				break;
			case 2:
				$where_clause = 'WHERE (p2.value IS NULL) ';
				break;
			case 3:
			default:
				$where_clause = 'WHERE (p2.value > 0) ';
				break;
			case 4:
				$where_clause = 'WHERE (p2.value > p1.value AND p2.value > rq.role_disk_quota) ';
				break;
		}
		switch ($a_access_filter) {
			case 1:
				$where_clause .= '';
				break;
			case 2:
			default:
				$where_clause .= ($where_clause == '' ? 'WHERE ' : ' AND ' ).
					'(u.active=1 AND (u.time_limit_unlimited = 1 OR UNIX_TIMESTAMP() BETWEEN u.time_limit_from AND u.time_limit_until)) ';
				break;
			case 3:
				$where_clause .= ($where_clause == '' ? 'WHERE ' : ' AND ' ).
					'(u.active=0 OR (u.time_limit_unlimited IS NULL AND UNIX_TIMESTAMP() NOT BETWEEN u.time_limit_from AND u.time_limit_until)) ';
				break;
		}

		$res = $ilDB->queryf(
			// Note: We add 0 to some of the values to convert them into a number
			//       This is needed for correct sorting.
			"SELECT u.usr_id,u.firstname,u.lastname,u.login,u.email,u.last_login,u.active,".
				"u.time_limit_unlimited, FROM_UNIXTIME(u.time_limit_from), FROM_UNIXTIME(u.time_limit_until),".
				"IF(u.active = 0,'0000-00-00',IF (u.time_limit_unlimited=1,'9999-12-31',FROM_UNIXTIME(u.time_limit_until))) AS access_until,".
				"IF(UNIX_TIMESTAMP() BETWEEN u.time_limit_from AND u.time_limit_until,0,1) AS expired,".
				"rq.role_disk_quota, system_role.rol_id AS role_id, ".
				"p1.value+0 AS user_disk_quota,".
				"p2.value+0 AS disk_usage, ".
				"p3.value AS last_update, ".
				"IF(rq.role_disk_quota+0>p1.value+0 OR p1.value IS NULL,rq.role_disk_quota+0,p1.value+0) AS disk_quota	".
			"FROM usr_data AS u  ".

			// Fetch the role with the highest disk quota value.
			"JOIN (SELECT u.usr_id AS usr_id,MAX(rd.disk_quota) AS role_disk_quota ".
				"FROM usr_data AS u ".
				"JOIN rbac_ua AS ua ON ua.usr_id=u.usr_id ".
				"JOIN rbac_fa AS fa ON fa.rol_id=ua.rol_id AND fa.parent=%s  ".
				"JOIN role_data AS rd ON rd.role_id=ua.rol_id WHERE u.usr_id=ua.usr_id GROUP BY usr_id) AS rq ON rq.usr_id=u.usr_id ".

			// Fetch the system role in order to determine whether the user has unlimited disk quota
			"LEFT JOIN rbac_ua AS system_role ON system_role.usr_id=u.usr_id AND system_role.rol_id = %s ".

			// Fetch the user disk quota from table usr_pref
			"LEFT JOIN usr_pref AS p1 ON p1.usr_id=u.usr_id AND p1.keyword = 'disk_quota'  ".

			// Fetch the disk usage from table usr_pref
			"LEFT JOIN usr_pref AS p2 ON p2.usr_id=u.usr_id AND p2.keyword = 'disk_usage'  ".

			// Fetch the last update from table usr_pref
			"LEFT JOIN usr_pref AS p3 ON p3.usr_id=u.usr_id AND p3.keyword = 'disk_usage.last_update'  ".

			$where_clause.
			"ORDER BY ".
				$ilDB->quoteIdentifier($a_order_column).($a_order_by=='asc'?' ASC':' DESC').", ".
				"lastname, firstname, login"
			,
	        array('integer','integer'),
	        array(ROLE_FOLDER_ID, SYSTEM_ROLE_ID)
		);
		$previous_usr_id = null;
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
			if ($previous_usr_id != $row['usr_id'])
			{
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
	public static function _updateDiskUsageStatistics()
	{
		global $ilDB;

		// delete old values
		$ilDB->manipulate("DELETE FROM usr_pref ".
			"WHERE keyword like ('disk\_usage%')");


		require_once 'Modules/File/classes/class.ilObjFileAccess.php';
		self::__updateDiskUsageStatisticsOfType(new ilObjFileAccess(), 'file');

		require_once 'Modules/Forum/classes/class.ilObjForumAccess.php';
		self::__updateDiskUsageStatisticsOfType(new ilObjForumAccess(), 'frm');

		require_once 'Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php';
		self::__updateDiskUsageStatisticsOfType(new ilObjFileBasedLMAccess(), 'htlm');

		require_once 'Modules/MediaCast/classes/class.ilObjMediaCastAccess.php';
		self::__updateDiskUsageStatisticsOfType(new ilObjMediaCastAccess(), 'mcst');

		require_once 'Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php';
		self::__updateDiskUsageStatisticsOfType(new ilObjSAHSLearningModuleAccess(), 'sahs');

		require_once 'Services/Mail/classes/class.ilObjMailAccess.php';
		self::__updateDiskUsageStatisticsOfUsers(new ilObjMailAccess(), 'mail');

		// insert the sum of the disk usage of each user
		$ilDB->manipulate("INSERT INTO usr_pref ".
			"(usr_id, keyword, value) ".
			"SELECT usr_id, 'disk_usage', SUM(value) ".
			"FROM usr_pref ".
			"WHERE keyword LIKE 'disk_usage.%.size' ".
			"GROUP BY usr_id"
			);

		// insert last update
		$ilDB->manipulate("INSERT INTO usr_pref ".
			"(usr_id, keyword, value) ".
			"SELECT usr_id, 'disk_usage.last_update', NOW() ".
			"FROM usr_data");
	}

	/**
	 * Updates the disk usage statistics of the specified object type for
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
	private static function __updateDiskUsageStatisticsOfType($a_access_obj, $a_type)
	{
		global $ilDB;

		// get all objects of the desired type which are in the repository
		// ordered by owner
		$res = $ilDB->query("SELECT DISTINCT d.obj_id, d.owner ".
			"FROM object_data AS d ".
			"JOIN object_reference AS r ON d.obj_id=r.obj_id ".
			"JOIN tree AS t ON t.child=r.ref_id ".
			"WHERE d.type = ".$ilDB->quote($a_type)." ".
			"AND t.tree=1 ".
			"ORDER BY d.owner"
			);

		// for each objects of an owner, count the number of objects and sum up
		// the size
		$count = null;
		$size = null;
		$owner = null;
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
			if ($row->owner != $owner) {
				if ($owner != null) {
					$ilDB->manipulate("INSERT INTO usr_pref ".
						"(usr_id, keyword, value) ".
						"VALUES ".

						"(".$ilDB->quote($owner,'integer').", ".
						$ilDB->quote('disk_usage.'.$a_type.'.size').", ".
						$ilDB->quote($size, 'integer')."),".

						"(".$ilDB->quote($owner,'integer').", ".
						$ilDB->quote('disk_usage.'.$a_type.'.count').", ".
						$ilDB->quote($count, 'integer').")"
						);

				}
				$owner = $row->owner;
				$size = 0;
				$count = 0;
			}
			$size += $a_access_obj->_lookupDiskUsage($row->obj_id);
			$count++;
		}
		if ($owner != null) {
			$ilDB->manipulate("INSERT INTO usr_pref ".
				"(usr_id, keyword, value) ".
				"VALUES ".

				"(".$ilDB->quote($owner,'integer').", ".
				$ilDB->quote('disk_usage.'.$a_type.'.size').", ".
				$ilDB->quote($size, 'integer')."),".

				"(".$ilDB->quote($owner,'integer').", ".
				$ilDB->quote('disk_usage.'.$a_type.'.count').", ".
				$ilDB->quote($count, 'integer').")"
				);
		}

	}

	/**
	 * Updates the disk usage statistics of the specified object type for
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
	private static function __updateDiskUsageStatisticsOfUsers($a_access_obj, $a_type)
	{
		global $ilDB;

		// get all users
		$res = $ilDB->query("SELECT usr_id FROM usr_data");

		// for each user count the number of objects and sum up the size
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT)) {
			$data = $a_access_obj->_lookupDiskUsageOfUser($row->usr_id);

			if ($data['size'] != null && $data['count'] != null)
			{
			$ilDB->manipulate("INSERT INTO usr_pref ".
				"(usr_id, keyword, value) ".
				"VALUES ".
					"(".$ilDB->quote($row->usr_id,'integer').", ".
					$ilDB->quote('disk_usage.'.$a_type.'.size').", ".
					$ilDB->quote($data['size'], 'integer')."),".

					"(".$ilDB->quote($row->usr_id,'integer').", ".
					$ilDB->quote('disk_usage.'.$a_type.'.count').", ".
					$ilDB->quote($data['count'], 'integer').")"
					);
			}
		}
	}
}
?>
