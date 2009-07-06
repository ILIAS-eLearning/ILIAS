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
			"FROM role_data AS rd ".
			"JOIN rbac_ua AS ua ON ua.rol_id=rd.role_id ".
			"JOIN object_data AS od ON od.obj_id=rd.role_id ".
			"WHERE usr_id = %s ".
			"ORDER BY disk_quota DESC, role_id ASC",
	        array('integer'),
	        array($a_user_id));

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
	*		"last_update"=>datetime,	// SQL datetime of the last update
	*								// if this is null, the disk usage is unkown
	*
	*		"disk_usage"=>integer,	// the disk usage in bytes
	*
	*		"details" array(array('type'=>string,'count'=>integer,'size'=>integer),...)
	*                            // an associative array with the disk
	*                            // usage in bytes for each object type
	* }
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
		self::_updateDiskUsageStatisticsOfType(new ilObjFileAccess(), 'file');

		require_once 'Modules/Forum/classes/class.ilObjForumAccess.php';
		self::_updateDiskUsageStatisticsOfType(new ilObjForumAccess(), 'frm');

		require_once 'Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMAccess.php';
		self::_updateDiskUsageStatisticsOfType(new ilObjFileBasedLMAccess(), 'htlm');

		require_once 'Modules/MediaCast/classes/class.ilObjMediaCastAccess.php';
		self::_updateDiskUsageStatisticsOfType(new ilObjMediaCastAccess(), 'mcst');

		require_once 'Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php';
		self::_updateDiskUsageStatisticsOfType(new ilObjSAHSLearningModuleAccess(), 'sahs');

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
	public static function _updateDiskUsageStatisticsOfType($a_access_obj, $a_type)
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

}
?>
