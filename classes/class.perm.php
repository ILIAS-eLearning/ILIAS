<?php
// TODO: this function collection must cleaned up!!! Many functions belong to other classes
// or could be simplified and merged. I.E. all getXXXList-Functions.

/**
* get all Roles
* @return array/boolean	returns array of Roles or false if no Roles found
*/
function getRoleList ()
{
	global $ilias;
	
	$query = "SELECT * FROM object_data ".
			 "WHERE type = 'role' ".
			 "ORDER BY title ASC";
	$res = $ilias->db->query($query);
	
	if ($res->numRows())
	{
		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = array(
						"obj_id"	 	=> $row->obj_id,
						"title"			=> $row->title,
						"description"	=> $row->description
							);
		}
		
		return $arr;
	}
	
	return false;
}

/**
* get user list
* @return array/boolean	returns array of ssers or false if no users found
*/
function getUserList ($a_order = '',$a_direction = '')
{
	global $ilias;

	if (!$a_order)
	{
		$a_order = 'title';
	}

	$query = "SELECT * FROM object_data ".
			 "WHERE type = 'usr' ".
			 "ORDER BY ".$a_order." ".$a_direction;
	$res = $ilias->db->query($query);

	while ($data = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$arr[] = array(
					"obj_id"		=> $data->obj_id,
					"title"			=> $data->title,
					"desc"			=> $data->description,
					"usr_id"		=> $data->owner,
					"create_date"	=> $data->create_date,
					"last_update"	=> $data->last_update
					);
	}

	return $arr;
}

/**
* get user list
* @param	none
* @return array/boolean	returns array of types or false if no types found
*/
function getTypeList ($a_order = '',$a_direction = '')
{
	global $ilias;

	if (!$a_order)
	{
		$a_order = 'title';
	}

	$query = "SELECT * FROM object_data ".
			 "WHERE type = 'typ' ".
	 		 "ORDER BY ".$a_order." ".$a_direction;
	$res = $ilias->db->query($query);

	while ($data = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$arr[] = array(
					"obj_id"		=> $data->obj_id,
					"type"			=> $data->typ,
					"title"			=> $data->title,
					"desc"			=> $data->description,
					"usr_id"		=> $data->owner,
					"create_date"	=> $data->create_date,
					"last_update"	=> $data->last_update
					);
	}
	
	return $arr;
}	

/**
* get object list
* @param	string	$AObjType
* @return array/boolean	returns array of objects or false if no objects found
*/
function getObjectList ($AObjType = "",$AOffset = "",$ALimit = "")
{
	global $ilias;

	if (!empty($ALimit))
	{
		$limit_clause = " LIMIT $AOffset,$ALimit";
	}

	if (empty($AObjType))
	{
		$query = "SELECT * FROM object_data ".
				 "ORDER BY obj_id ASC".$limit_clause;
	}
	else
	{
		$query = "SELECT * FROM object_data ".
				 "WHERE type = '".$AObjType."' ".
				 "ORDER BY obj_id ASC".$limit_clause;
	}
	
	$res = $ilias->db->query($query);

	if ($res->numRows() > 0)
	{
		while ($data = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = array (
							"obj_id"		=> $data->obj_id,
							"type"			=> $data->type,
							"title"			=> $data->title,
							"desc"			=> $data->description,
							"usr_id"		=> $data->owner,
							"create_date"	=> $data->create_date,
							"last_update"	=> $data->last_update,
							"last_login"	=> $data->last_login
							);
		}

		return $arr;
	}

	return false;
}

/**
* get language list
* @param	none
* @return array/boolean	returns array of ssers or false if no languages found
*/
function getLangList ($a_order = '',$a_direction = '')
{
	global $ilias;

	if (!$a_order)
	{
		$a_order = 'title';
	}

	$query = "SELECT * FROM object_data ".
			 "WHERE type = 'lng' ".
			 "ORDER BY ".$a_order." ".$a_direction;
	$res = $ilias->db->query($query);

	while ($data = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		$arr[] = array(
					"obj_id"		=> $data->obj_id,
					"title"			=> $data->title,
					"desc"			=> $data->description,
					"usr_id"		=> $data->owner,
					"create_date"	=> $data->create_date,
					"last_update"	=> $data->last_update
					);
	}

	return $arr;
}

/**
* creates a new object
* @param	string	$AObjType
* @param	array	 $AObjData
* @return int	 returns object id
*/
function createNewObject ($AObjType,$AObjData)
{
	global $ilias;

	$query = "INSERT INTO object_data ".
			 "(type,title,description,owner,create_date,last_update) ".
			 "VALUES ".
			 "('".$AObjType."','".$AObjData["title"]."','".$AObjData["desc"]."',".
			 "'".$ilias->account->Id."',now(),now())";
	$res = $ilias->db->query($query);
	
 	// get last insert id and return this
	$query = "SELECT LAST_INSERT_ID()";
	$res = $ilias->db->query($query);
	$data = $res->fetchRow();
	
	return $data[0];
}

/**
* creates a new object
* @param	array	 $AObjData
* @return int	 returns object id
*/
function createNewOperation ($AOpsData)
{
	global $ilias;

	$query = "INSERT INTO operations ".
			 "(operation,description) ".
			 "VALUES ".
			 "('".$AOpsData["title"]."','".$AOpsData["desc"]."')";
	$res = $ilias->db->query($query);
	
 	// get last insert id and return this
	$query = "SELECT LAST_INSERT_ID()";
	$res = $ilias->db->query($query);
	$data = $res->fetchRow();
	
	return $data[0];
 }	

/**
* delete an object from tbl.object_data
* @param	integer		$a_obj_id
* @return	boolean		returns true if successful otherwise false
*/
function deleteObject ($a_obj_id)
{
	global $ilias;

	$query = "DELETE FROM object_data ".
			 "WHERE obj_id = '".$a_obj_id."'";
	$ilias->db->query($query);

	return true;
}

/**
* updates an object
* @param	int	 $AObjId
* @param	string	$AObjType
* @param	array	 $AObjData
* @return boolean	returns true if successful otherwise false
*/
function updateObject ($AObjId,$AObjType,$AObjData)
{
	global $ilias;

	$query = "UPDATE object_data ".
			 "SET ".
			 "title = '".$AObjData["title"]."',".
			 "description = '".$AObjData["desc"]."' ".
			 "WHERE obj_id = '".$AObjId."'";
	$res = $ilias->db->query($query);

	return true;
}

/**
* get an object
* @param	int	 $AObjId
* @return array	 returns the object
*/
function getObject ($AObjId)
{
	global $ilias;

	$query = "SELECT * FROM object_data ".
			 "WHERE obj_id = '".$AObjId."'";
	$res = $ilias->db->query($query);
	
	$data = $res->fetchRow(DB_FETCHMODE_OBJECT);
	$obj = array(
				"obj_id"		=> $data->obj_id,
				"type"			=> $data->type,
				"title"			=> $data->title,
				"desc"			=> $data->description,
				"owner"			=> $data->owner,
				"create_date"	=> $data->create_date,
				"last_update"	=> $data->last_update
				);
	return $obj;
}

/**
* get operation list
* @return	array	returns array of operations
*/
function getOperationList ($Aobj_type = "",$a_order= '',$a_direction = '')
 {
	global $ilias;

	if (!$a_order)
	{
		$a_order = 'operation';
	}

	if ($Aobj_type)
	{
		$query = "SELECT * FROM rbac_operations ".
				 "LEFT JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id ".
				 "LEFT JOIN object_data ON rbac_ta.typ_id = object_data.obj_id ".
				 "WHERE object_data.title='".$Aobj_type."' AND object_data.type='typ' ".
				 "ORDER BY rbac_operations.".$a_order." ".$a_direction; 
	}
	else
	{
		$query = "SELECT * FROM rbac_operations ".
				 "ORDER BY ".$a_order." ".$a_direction;
	}
	
	$res = $ilias->db->query($query);

	while ($row = $res->fetchRow())
	{
		$arr[] = array(
					"ops_id"		 => $row[0],
					"operation"	=> $row[1],
					"desc"		 => $row[2]
					);
	}

	return $arr;
}	

/**
* check if user is logged in
* @return	boolean 	true if logged in
*/
function isUserLoggedIn ()
{
	global $ilias;

	if (empty($ilias->account->Id))
	{
		return false;
	}

	return true;
}
?>