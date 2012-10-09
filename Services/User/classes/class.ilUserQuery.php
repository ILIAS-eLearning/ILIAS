<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	public static function getUserListData($a_order_field, $a_order_dir, $a_offset, $a_limit,
		$a_string_filter = "", $a_activation_filter = "", $a_last_login_filter = null,
		$a_limited_access_filter = false, $a_no_courses_filter = false,
		$a_course_group_filter = 0, $a_role_filter = 0, $a_user_folder_filter = null,
		$a_additional_fields = '', $a_user_filter = null, $a_first_letter = "")
	{
		global $ilDB, $rbacreview;
		
		$fields = array("usr_id", "login", "firstname", "lastname", "email",
			"time_limit_until", "time_limit_unlimited", "time_limit_owner", "last_login", "active");
		
		$ut_join = "";
		if (is_array($a_additional_fields))
		{
			foreach ($a_additional_fields as $f)
			{
				if (!in_array($f, $fields))
				{
					if($f == "online_time")
					{
						$fields[] = "ut_online.online_time";						
						$ut_join = " LEFT JOIN ut_online ON usr_data.usr_id = ut_online.usr_id";
					}
					else
					{
						$fields[] = $f;
					}
				}
			}
		}				
		
		// count query
		$count_query = "SELECT count(usr_id) cnt".
			" FROM usr_data";
		
		$sql_fields = array();
		foreach($fields as $idx => $field)
		{
			if(!stristr($field, "."))
			{
				$sql_fields[] = "usr_data.".$field;
			}
			else
			{
				$sql_fields[] = $field;
			}
		}
			
		// basic query
		$query = "SELECT ".implode($sql_fields, ",").
			" FROM usr_data".
			$ut_join;
			
		// filter
		$query.= " WHERE usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");

		// User filter
		if($a_user_filter and is_array(($a_user_filter)))
		{
			$query .= ' AND '.$ilDB->in('usr_data.usr_id',$a_user_filter,false,'integer');
		}

		$count_query.= " WHERE usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");
		$where = " AND";

		if ($a_first_letter != "")
		{
			$add = $where." (".$ilDB->upper($ilDB->substr("usr_data.lastname", 1, 1))." = ".$ilDB->upper($ilDB->quote($a_first_letter, "text")).") ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		
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
				$add = $where." usr_data.last_login < ".
					$ilDB->quote($a_last_login_filter->get(IL_CAL_DATETIME), "timestamp");
				$query.= $add;
				$count_query.= $add;
				$where = " AND";
			}
		}
		if ($a_limited_access_filter)		// limited access
		{
			$add = $where." usr_data.time_limit_unlimited= ".$ilDB->quote(0, "integer");
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($a_no_courses_filter)		// no courses assigned
		{
			$add = $where." usr_data.usr_id NOT IN (".
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
			$add = $where." usr_data.usr_id IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) ".
				"WHERE od.title = ".$ilDB->quote("il_".$cgtype."_member_".$a_course_group_filter, "text").")";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($a_role_filter > 0)		// global role
		{
			$add = $where." usr_data.usr_id IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"WHERE rbac_ua.rol_id = ".$ilDB->quote($a_role_filter, "integer").")";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		
		if(!is_null($a_user_folder_filter))
		{
			$add = $where." ".$ilDB->in('usr_data.time_limit_owner',$a_user_folder_filter,false,'integer');
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		// order by
		switch($a_order_field)
		{
			case  "access_until":
				if ($a_order_dir == "desc")
				{
					$query.= " ORDER BY usr_data.active DESC, usr_data.time_limit_unlimited DESC, usr_data.time_limit_until DESC";
				}
				else
				{
					$query.= " ORDER BY usr_data.active ASC, usr_data.time_limit_unlimited ASC, usr_data.time_limit_until ASC";
				}	
				break;
				
			case "online_time":
				if ($a_order_dir == "desc")
				{
					$query.= " ORDER BY ut_online.online_time DESC";
				}
				else
				{
					$query.= " ORDER BY ut_online.online_time ASC";
				}	
				break;
				
			default:
				if (!in_array($a_order_field, $fields))
				{
					$a_order_field = "login";
				}
				if ($a_order_dir != "asc" && $a_order_dir != "desc")
				{
					$a_order_dir = "asc";
				}
				$query .= " ORDER BY usr_data.".$a_order_field." ".strtoupper($a_order_dir);
				break;
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
		
		// #9866: validate offset against rowcount
		if($offset >= $cnt)
		{
			$offset = 0;
		}
		
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
}
