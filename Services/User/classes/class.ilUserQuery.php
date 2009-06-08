<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* User query class. Put any complex that queries for a set of users into
* this class and keep ilObjUser "small". 
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesUser
*/
class ilUserQuery
{
	/**
	* Get data for user administration list.
	*/
	static function getUserListData($a_order_field, $a_order_dir, $a_offset, $a_limit,
		$a_string_filter = "", $a_activation_filter = "", $a_last_login_filter = null,
		$a_limited_access_filter = false, $a_no_courses_filter = false,
		$a_course_group_filter = 0, $a_global_role_filter = 0)
	{
		global $ilDB, $rbacreview;
		
		$fields = array("usr_id", "login", "firstname", "lastname", "email",
			"time_limit_until", "time_limit_unlimited", "last_login", "active");
		
		// count query
		$count_query = "SELECT count(usr_id) cnt".
			" FROM usr_data";
			
		// basic query
		$query = "SELECT ".implode($fields, ",").
			" FROM usr_data";
			
		// filter
		$where = " WHERE";
		if ($a_string_filter != "")		// email, name, login
		{
			$add = $where." (".$ilDB->like("usr_data.login", "text", "%".$a_string_filter."%")." ".
				"OR ".$ilDB->like("usr_data.firstname", "text", "%".$a_string_filter."%")." ".
				"OR ".$ilDB->like("usr_data.lastname", "text", "%".$a_string_filter."%")." ".
				"OR ".$ilDB->like("usr_data.email", "text", "%".$a_string_filter."%").") ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($a_activation_filter != "")		// activation
		{
			if ($a_activation_filter == "inactive")
			{
				$add = $where." usr_data.active = ".$ilDB->quote(0, "integer")." ";
			}
			else
			{
				$add = $where." usr_data.active = ".$ilDB->quote(1, "integer")." ";
			}
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		if (is_object($a_last_login_filter))	// last login
		{
			if ($a_last_login_filter->get(IL_CAL_UNIX) < time())
			{
				$add = $where." last_login < ".
					$ilDB->quote($a_last_login_filter->get(IL_CAL_DATETIME), "timestamp");
				$query.= $add;
				$count_query.= $add;
				$where = " AND";
			}
		}
		if ($a_limited_access_filter)		// limited access
		{
			$add = $where." time_limit_unlimited= ".$ilDB->quote(0, "integer");
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($a_no_courses_filter)		// no courses assigned
		{
			$add = $where." usr_id NOT IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) ".
				"WHERE od.title LIKE 'il_crs_%')";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($a_course_group_filter > 0)		// members of course/group
		{
			$cgtype = ilObject::_lookupType($a_course_group_filter, true);
			$add = $where." usr_id IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) ".
				"WHERE od.title = ".$ilDB->quote("il_".$cgtype."_member_".$a_course_group_filter, "text").")";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($a_global_role_filter > 0)		// global role
		{
			$add = $where." usr_id IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"WHERE rbac_ua.rol_id = ".$ilDB->quote($a_global_role_filter, "integer").")";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		// order by
		if ($a_order_field != "access_until")
		{
			if (!in_array($a_order_field, $fields))
			{
				$a_order_field = "login";
			}
			if ($a_order_dir != "asc" && $a_order_dir != "desc")
			{
				$a_order_dir = "asc";
			}
			$query.= " ORDER BY ".$a_order_field." ".strtoupper($a_order_dir);
		}
		else
		{
			if ($a_order_dir == "desc")
			{
				$query.= " ORDER BY active DESC, time_limit_unlimited DESC, time_limit_until DESC";
			}
			else
			{
				$query.= " ORDER BY active ASC, time_limit_unlimited ASC, time_limit_until ASC";
			}
		}

		// count query
		$set = $ilDB->query($count_query);
		$cnt = 0;
		if ($rec = $ilDB->fetchAssoc($set))
		{
			$cnt = $rec["cnt"];
		}
		
		$offset = (int) $a_offset;
		$limit = (int) $a_limit;
		$ilDB->setLimit($limit, $offset);
		
		// set query
		$set = $ilDB->query($query);
		$result = array();
		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
		}
		return array("cnt" => $cnt, "set" => $result);
	}
	
	function old()
	{
		$result_arr = array();
		$types = array();
		$values = array();

		if ($a_fields !== NULL and is_array($a_fields))
		{
			if (count($a_fields) == 0)
			{
				$select = "*";
			}
			else
			{
			if (($usr_id_field = array_search("usr_id",$a_fields)) !== false)
				unset($a_fields[$usr_id_field]);

				$select = implode(",",$a_fields).",usr_data.usr_id";
				// online time
				if(in_array('online_time',$a_fields))
				{
					$select .= ",ut_online.online_time ";
				}
			}

			$q = "SELECT ".$select." FROM usr_data ";

			// Add online_time if desired
			// Need left join here to show users that never logged in
			if(in_array('online_time',$a_fields))
			{
				$q .= "LEFT JOIN ut_online ON usr_data.usr_id = ut_online.usr_id ";
			}

			switch ($active)
			{
				case 0:
				case 1:
					$q .= "WHERE active = ".$ilDB->quote($active, "integer");
					break;
				case 2:
					$q .= "WHERE time_limit_unlimited= ".$ilDB->quote(0, "integer");;
					break;
				case 3:
					$qtemp = $q . ", rbac_ua, object_data WHERE rbac_ua.rol_id = object_data.obj_id AND ".
						$ilDB->like("object_data.title", "text", "%crs%")." AND usr_data.usr_id = rbac_ua.usr_id";
					$r = $ilDB->query($qtemp);
					$course_users = array();
					while ($row = $ilDB->fetchAssoc($r))
					{
						array_push($course_users, $row["usr_id"]);
					}
					if (count($course_users))
					{
						$q .= " WHERE ".$ilDB->in("usr_data.usr_id", $course_users, true, "integer")." ";
					}
					else
					{
						return $result_arr;
					}
					break;
				case 4:
					$date = strftime("%Y-%m-%d %H:%I:%S", mktime(0, 0, 0, $_SESSION["user_filter_data"]["m"], $_SESSION["user_filter_data"]["d"], $_SESSION["user_filter_data"]["y"]));
					$q.= " AND last_login < ".$ilDB->quote($date, "timestamp");
					break;
				case 5:
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$q .= " LEFT JOIN crs_members ON usr_data.usr_id = crs_members.usr_id ".
							"WHERE crs_members.obj_id = (SELECT obj_id FROM object_reference ".
							"WHERE ref_id = ".$ilDB->quote($ref_id, "integer").") ";
					}
					break;
				case 6:
					global $rbacreview;
					$ref_id = $_SESSION["user_filter_data"];
					if ($ref_id)
					{
						$rolf = $rbacreview->getRoleFolderOfObject($ref_id);
						$local_roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
						if (is_array($local_roles) && count($local_roles))
					{
							$q.= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE ".
								$ilDB->in("rbac_ua.rol_id", $local_roles, false, "integer")." ";
						}
					}
					break;
				case 7:
					$rol_id = $_SESSION["user_filter_data"];
					if ($rol_id)
					{
						$q .= " LEFT JOIN rbac_ua ON usr_data.usr_id = rbac_ua.usr_id WHERE rbac_ua.rol_id = ".
							$ilDB->quote($rol_id, "integer");
					}
					break;
			}

			$r = $ilDB->query($q);

			while ($row = $ilDB->fetchAssoc($r))
			{
				$result_arr[] = $row;
			}
		}

		return $result_arr;
	}

}
