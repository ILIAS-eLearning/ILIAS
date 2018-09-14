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
	private $order_field = 'login';
	private $order_dir = 'asc';
	private $offset = 0;
	private $limit = 50;
	private $text_filter = '';
	private $activation = '';
	private $last_login = NULL;
	private $limited_access = false;
	private $no_courses = false;
	private $no_groups = false;
	private $crs_grp = 0;
	private $role = 0;
	private $user_folder = 0;
	private $additional_fields = array();
	private $users = array();
	private $first_letter = '';
	private $has_access = false;
	private $authentication_method = '';

	/**
	 * @var array
	 */
	protected $udf_filter = array();

	private $default_fields = array(
		"usr_id", 
		"login", 
		"firstname", 
		"lastname", 
		"email",
		"second_email",
		"time_limit_until", 
		"time_limit_unlimited", 
		"time_limit_owner", 
		"last_login", 
		"active"
	);
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		;
	}

	/**
	 * Set udf filter
	 *
	 * @param array $a_val udf filter array	
	 */
	function setUdfFilter($a_val)
	{
		$this->udf_filter = $a_val;
	}
	
	/**
	 * Get udf filter
	 *
	 * @return array udf filter array
	 */
	function getUdfFilter()
	{
		return $this->udf_filter;
	}
	
	/**
	 * Set order field (column in usr_data)
	 * Default order is 'login'
	 * @param string
	 */
	public function setOrderField($a_order)
	{
		$this->order_field = $a_order;
	}
	
	/**
	 * Set order direction 
	 * 'asc' or 'desc' 
	 * Default is 'asc'
	 * @param string $a_dir
	 */
	public function setOrderDirection($a_dir)
	{
		$this->order_dir = $a_dir;
	}
	
	/**
	 * Set offset
	 * @param int $a_offset
	 */
	public function setOffset($a_offset)
	{
		$this->offset = $a_offset;
	}
	
	/**
	 * Set result limit
	 * Default is 50
	 * @param int $a_limit
	 */
	public function setLimit($a_limit)
	{
		$this->limit = $a_limit;
	}
	
	/**
	 * Text (like) filter in login, firstname, lastname or email
	 * @param string filter
	 */
	public function setTextFilter($a_filter)
	{
		$this->text_filter = $a_filter;
	}
	
	/**
	 * Set activation filter
	 * 'active' or 'inactive' or empty
	 * @param string $a_activation
	 */
	public function setActionFilter($a_activation)
	{
		$this->activation = $a_activation;
	}
	
	/**
	 * Set last login filter
	 * @param ilDateTime $dt
	 */
	public function setLastLogin(ilDateTime $dt = NULL)
	{
		$this->last_login = $dt;
	}
	
	/**
	 * Enable limited access filter
	 * @param bool 
	 */
	public function setLimitedAccessFilter($a_status)
	{
		$this->limited_access = $a_status;
	}
	
	/**
	 * Enable no course filter
	 * @param bool $a_no_course
	 */
	public function setNoCourseFilter($a_no_course)
	{
		$this->no_courses = $a_no_course;
	}
	
	/**
	 * Enable no group filter
	 * @param bool $a_no_group
	 */
	public function setNoGroupFilter($a_no_group)
	{
		$this->no_groups = $a_no_group;
	}
	
	/**
	 * Set course / group filter
	 * object_id of course or group
	 * @param int $a_cg_id
	 */
	public function setCourseGroupFilter($a_cg_id)
	{
		$this->crs_grp = $a_cg_id;
	}
	
	/**
	 * Set role filter
	 * obj_id of role 
	 * @param int $a_role_id
	 */
	public function setRoleFilter($a_role_id)
	{
		$this->role = $a_role_id;
	}
	
	/**
	 * Set user folder filter
	 * reference id of user folder or category (local user administration)
	 * @param int $a_fold_id
	 */
	public function setUserFolder($a_fold_id)
	{
		$this->user_folder = $a_fold_id;
	}
	
	/**
	 * Set additional fields (columns in usr_data or 'online_time')
	 * @param array $additional_fields
	 */
	public function setAdditionalFields($a_add)
	{
		$this->additional_fields = (array) $a_add;
	}
	
	/**
	 * Array with user ids to query against
	 * @param array $a_filter
	 */
	public function setUserFilter($a_filter)
	{
		$this->users = $a_filter;
	}
	
	/**
	 * set first letter lastname filter
	 * @param string $a_fll
	 */
	public function setFirstLetterLastname($a_fll)
	{
		$this->first_letter = $a_fll;
	}

	/**
	 * set filter for user that are limited but has access
	 *
	 * @param $a_access
	 */
	public function setAccessFilter($a_access)
	{
		$this->has_access = (bool) $a_access;
	}

	/**
	 * Set authentication filter
	 * 'default', 'local' or 'lti'
	 * @param string $a_authentication
	 */
	public function setAuthenticationFilter($a_authentication)
	{
		$this->authentication_method = $a_authentication;

	}
	
	/**
	 * Query usr_data
	 * @return array ('cnt', 'set') 
	 */
	public function query()
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];


		$udf_fields = array();

		$join = "";

		if (is_array($this->additional_fields))
		{
			foreach ($this->additional_fields as $f)
			{
				if (!in_array($f, $this->default_fields))
				{
					if($f == "online_time")
					{
						$this->default_fields[] = "ut_online.online_time";						
						$join = " LEFT JOIN ut_online ON (usr_data.usr_id = ut_online.usr_id) ";
					}
					else if (substr($f, 0, 4) == "udf_")
					{
						$udf_fields[] = (int) substr($f, 4);
					}
					else
					{
						$this->default_fields[] = $f;
					}
				}
			}
		}

		// if udf fields are involved we need the definitions
		$udf_def = array();
		if (count($udf_fields) > 0)
		{
			include_once './Services/User/classes/class.ilUserDefinedFields.php';
			$udf_def = ilUserDefinedFields::_getInstance()->getDefinitions();
		}

		// join udf table
		foreach ($udf_fields as $id)
		{
			$udf_table = ($udf_def[$id]["field_type"] != UDF_TYPE_WYSIWYG)
				? "udf_text"
				: "udf_clob";
			$join.= " LEFT JOIN ".$udf_table." ud_".$id." ON (ud_".$id.".field_id=".$ilDB->quote($id)." AND ud_".$id.".usr_id = usr_data.usr_id) ";
		}

		// count query
		$count_query = "SELECT count(usr_data.usr_id) cnt".
			" FROM usr_data";
		
		$all_multi_fields = array("interests_general", "interests_help_offered", "interests_help_looking");
		$multi_fields = array();
		
		$sql_fields = array();
		foreach($this->default_fields as $idx => $field)
		{
			if(!$field)
			{
				continue;
			}
			
			if(in_array($field, $all_multi_fields))
			{
				$multi_fields[] = $field;
			}			
			else if(!stristr($field, "."))
			{
				$sql_fields[] = "usr_data.".$field;
			}
			else
			{
				$sql_fields[] = $field;
			}
		}

		// udf fields
		foreach ($udf_fields as $id)
		{
			$sql_fields[] = "ud_".$id.".value udf_".$id;
		}

		// basic query
		$query = "SELECT ".implode($sql_fields, ",").
			" FROM usr_data".
			$join;

		$count_query = $count_query." ".
			$join;

		// filter
		$query.= " WHERE usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");

		// User filter
		if($this->users and is_array(($this->users)))
		{
			$query .= ' AND '.$ilDB->in('usr_data.usr_id',$this->users,false,'integer');
		}

		$count_query.= " WHERE usr_data.usr_id <> ".$ilDB->quote(ANONYMOUS_USER_ID, "integer");
		$where = " AND";

		if ($this->first_letter != "")
		{
			$add = $where." (".$ilDB->upper($ilDB->substr("usr_data.lastname", 1, 1))." = ".$ilDB->upper($ilDB->quote($this->first_letter, "text")).") ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		
		if ($this->text_filter != "")		// email, name, login
		{
			$add = $where." (".$ilDB->like("usr_data.login", "text", "%".$this->text_filter."%")." ".
				"OR ".$ilDB->like("usr_data.firstname", "text", "%".$this->text_filter."%")." ".
				"OR ".$ilDB->like("usr_data.lastname", "text", "%".$this->text_filter."%")." ".
				"OR ".$ilDB->like("usr_data.second_email", "text", "%".$this->text_filter."%")." ".
				"OR ".$ilDB->like("usr_data.email", "text", "%".$this->text_filter."%").") ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		
		if ($this->activation != "")		// activation
		{
			if ($this->activation == "inactive")
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

		if($this->last_login instanceof ilDateTime)	// last login
		{
			if(ilDateTime::_before($this->last_login, new ilDateTime(time(),IL_CAL_UNIX),IL_CAL_DAY))
			{
				$add = $where." usr_data.last_login < ".
					$ilDB->quote($this->last_login->get(IL_CAL_DATETIME), "timestamp");
				$query.= $add;
				$count_query.= $add;
				$where = " AND";
			}
		}
		if ($this->limited_access)		// limited access
		{
			$add = $where." usr_data.time_limit_unlimited= ".$ilDB->quote(0, "integer");
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		// udf filter
		foreach ($this->getUdfFilter() as $k => $f)
		{
			if ($f != "")
			{
				$udf_id = explode("_", $k)[1];
				if ($udf_def[$udf_id]["field_type"] == UDF_TYPE_TEXT)
				{
					$add = $where ." " .$ilDB->like("ud_" . $udf_id . ".value", "text", "%".$f."%");
				}
				else
				{
					$add = $where . " ud_" . $udf_id . ".value = " . $ilDB->quote($f, "text");
				}
				$query.= $add;
				$count_query.= $add;
				$where = " AND";
			}
		}

		if($this->has_access) //user is limited but has access
		{
			$unlimited = "time_limit_unlimited = ". $ilDB->quote(1, 'integer');
			$from = "time_limit_from < ". $ilDB->quote(time(), 'integer');
			$until = "time_limit_until > ". $ilDB->quote(time(), 'integer');

			$add = $where.' (' .$unlimited.' OR ('.$from.' AND ' .$until.'))';
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($this->no_courses)		// no courses assigned
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
		if ($this->no_groups)		// no groups assigned
		{
			$add = $where." usr_data.usr_id NOT IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) ".
				"WHERE od.title LIKE 'il_grp_%')";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($this->crs_grp > 0)		// members of course/group
		{
			$cgtype = ilObject::_lookupType($this->crs_grp, true);
			$add = $where." usr_data.usr_id IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"JOIN object_data od ON (rbac_ua.rol_id = od.obj_id) ".
				"WHERE od.title = ".$ilDB->quote("il_".$cgtype."_member_".$this->crs_grp, "text").")";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		if ($this->role > 0)		// global role
		{
			$add = $where." usr_data.usr_id IN (".
				"SELECT DISTINCT ud.usr_id ".
				"FROM usr_data ud join rbac_ua ON (ud.usr_id = rbac_ua.usr_id) ".
				"WHERE rbac_ua.rol_id = ".$ilDB->quote($this->role, "integer").")";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}
		
		if($this->user_folder)
		{
			$add = $where." ".$ilDB->in('usr_data.time_limit_owner',$this->user_folder,false,'integer');
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		if ($this->authentication_method != "")		// authentication
		{
			$add = $where." usr_data.auth_mode = ".$ilDB->quote($this->authentication_method, "text")." ";
			$query.= $add;
			$count_query.= $add;
			$where = " AND";
		}

		// order by
		switch($this->order_field)
		{
			case  "access_until":
				if ($this->order_dir == "desc")
				{
					$query.= " ORDER BY usr_data.active DESC, usr_data.time_limit_unlimited DESC, usr_data.time_limit_until DESC";
				}
				else
				{
					$query.= " ORDER BY usr_data.active ASC, usr_data.time_limit_unlimited ASC, usr_data.time_limit_until ASC";
				}	
				break;
				
			case "online_time":
				if ($this->order_dir == "desc")
				{
					$query.= " ORDER BY ut_online.online_time DESC";
				}
				else
				{
					$query.= " ORDER BY ut_online.online_time ASC";
				}	
				break;
				
			default:
				if ($this->order_dir != "asc" && $this->order_dir != "desc")
				{
					$this->order_dir = "asc";
				}
				if (substr($this->order_field, 0, 4) == "udf_")
				{
					$query .= " ORDER BY ud_".((int)substr($this->order_field, 4)).".value " . strtoupper($this->order_dir);
				}
				else
				{
					if (!in_array($this->order_field, $this->default_fields))
					{
						$this->order_field = "login";
					}
					$query .= " ORDER BY usr_data." . $this->order_field . " " . strtoupper($this->order_dir);
				}
				break;
		}

		// count query
		$set = $ilDB->query($count_query);
		$cnt = $ilDB->numRows($set);

		$offset = (int) $this->offset;
		$limit = (int) $this->limit;
		
		// #9866: validate offset against rowcount
		if($offset >= $cnt)
		{
			$offset = 0;
		}
		
		$ilDB->setLimit($limit, $offset);
		
		if(sizeof($multi_fields))
		{
			$usr_ids = array();
		}
		
		// set query
		$set = $ilDB->query($query);
		$result = array();

		while($rec = $ilDB->fetchAssoc($set))
		{
			$result[] = $rec;
			if(sizeof($multi_fields))
			{
				$usr_ids[] = $rec["usr_id"];
			}
		}

		// add multi-field-values to user-data
		if(sizeof($multi_fields) && sizeof($usr_ids))
		{
			$usr_multi = array();
			$set = $ilDB->query("SELECT * FROM usr_data_multi".
				" WHERE ".$ilDB->in("usr_id", $usr_ids, "", "integer"));
			while($row = $ilDB->fetchAssoc($set))
			{
				$usr_multi[$row["usr_id"]][$row["field_id"]][] = $row["value"];
			}			
			foreach($result as $idx => $item)
			{
				if(isset($usr_multi[$item["usr_id"]]))
				{
					$result[$idx] = array_merge($item, $usr_multi[$item["usr_id"]]);				
				}
			}
		}
		return array("cnt" => $cnt, "set" => $result);
	}
	
	
	/**
	 * Get data for user administration list.
	 * @deprecated
	 */
	public static function getUserListData($a_order_field, $a_order_dir, $a_offset, $a_limit,
		$a_string_filter = "", $a_activation_filter = "", $a_last_login_filter = null,
		$a_limited_access_filter = false, $a_no_courses_filter = false,
		$a_course_group_filter = 0, $a_role_filter = 0, $a_user_folder_filter = null,
		$a_additional_fields = '', $a_user_filter = null, $a_first_letter = "", $a_authentication_filter = null)
	{
	
		$query = new ilUserQuery();
		$query->setOrderField($a_order_field);
		$query->setOrderDirection($a_order_dir);
		$query->setOffset($a_offset);
		$query->setLimit($a_limit);
		$query->setTextFilter($a_string_filter);
		$query->setActionFilter($a_activation_filter);
		$query->setLastLogin($a_last_login_filter);
		$query->setLimitedAccessFilter($a_limited_access_filter);
		$query->setNoCourseFilter($a_no_courses_filter);
		$query->setCourseGroupFilter($a_course_group_filter);
		$query->setRoleFilter($a_role_filter);
		$query->setUserFolder($a_user_folder_filter);
		$query->setAdditionalFields($a_additional_fields);
		$query->setUserFilter($a_user_filter);
		$query->setFirstLetterLastname($a_first_letter);
		$query->setAuthenticationFilter($a_authentication_filter);
		return $query->query();
	}
}
