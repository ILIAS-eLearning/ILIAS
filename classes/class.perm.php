<?php
// TODO: this function collection must cleaned up!!! Many functions belong to other classes
/**
* perm class (actually a function library)
* general object handling functions
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @author	Stefan Meyer <smeyer@databay.de>
* @version	$Id$
* @package	ilias-core
*/

/**
* deprecated: Use new ilObj..($obj_id, false) or 
* $ilias->obj_factory->getInstanceByObjId($obj_id) instead
*/
/*
function getObject ($a_obj_id)
{
}*/

/**
* get an object by reference id
* @access	public
* @param	integer	reference id
* @return	array	object data
*/
function getObjectByReference ($a_ref_id)
{
	global $ilias, $log;

	if (!isset($a_ref_id))
	{
		$message = "perm::getObjectByReference(): No ref_id given!";
		$log->writeWarning($message);
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}

	$q = "SELECT * FROM object_data ".
		 "LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id ".
		 "WHERE object_reference.ref_id='".$a_ref_id."'";
	$r = $ilias->db->query($q);
	
	if ($r->numRows() == 0)
	{
		$message = "perm::getObjectByReference(): Object with ref_id ".$a_ref_id." not found!";
		$log->writeWarning($message);
		$ilias->raiseError($message,$ilias->error_obj->WARNING);
	}

	$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

	$arr = fetchObjectData($row);

	return $arr;
}


/**
* deprecated: use ilObject->clone()
*/
/*
function copyObject ($a_obj_id)
{
}*/


/**
* delete an object from tbl.object_data
* @access	public
* @param	integer		object id
* @return	boolean		returns true if successful otherwise false
*/
function deleteObject ($a_obj_id)
{
	global $ilias;

	$q = "DELETE FROM object_data ".
		 "WHERE obj_id = '".$a_obj_id."'";
	$ilias->db->query($q);

	return true;
}


/**
* updates an object
* @access	public
* @param	integer	object id
* @param	string	object title
* @param	string	object description
* @param	integer	cut length of title string to given value (optional, default: MAXLENGTH_OBJ_TITLE)
* @param	integer	cut length of description string to given value (optional, default: MAXLENGTH_OBJ_DESC)
* @param	boolean	adding 3 dots to shortended string (optional, default: true)
* @return	boolean	true
*/
function updateObject ($a_obj_id,$a_title,$a_desc,$a_len_title=MAXLENGTH_OBJ_TITLE,$a_len_desc=MAXLENGTH_OBJ_DESC,$a_dots=true)
{
	global $ilias;

	if (!isset($a_obj_id))
	{
		$message = "perm::updateObject(): No obj_id given!";
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}
	
	if (empty($a_title))
	{
		$message = "perm::updateObject(): No title given! A title is required!";
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}

	// cut length of text
	$a_title = addslashes(shortenText($a_title,$a_len_title,$a_dots));
	$_desc = addslashes(shortenText($a_desc,$a_len_desc,$a_dots));

	$q = "UPDATE object_data ".
		 "SET ".
		 "title='".$a_title."',".
		 "description='".$a_desc."', ".
		 "last_update=now() ".
		 "WHERE obj_id='".$a_obj_id."'";
	$ilias->db->query($q);

	return true;
}

/**
* updates a single value in a column of object data
* @access	public
* @param	integer	object id of object to change
* @param	string	column name of obj_data table
* @param	string	value to be changed
* @return	boolean	true on success
*/
function updateObjectValue($a_obj_id,$a_column,$a_value)
{
	global $ilias;
	
	if (!isset($a_obj_id))
	{
		$message = "perm::updateObjectValue(): No obj_id given!";
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}
	
	if (!isset($a_column))
	{
		$message = "perm::updateObjectValue(): No table column specified!";
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}

	$q = "UPDATE object_data ".
		 "SET ".$a_column."='".$a_value."',".
		 "last_update=now() ".
		 "WHERE obj_id='".$a_id."'";
	$ilias->db->query($q);
	
	return true;
}

/**
* fetch object data from mysql result set and returns an array
* @access	private
* @param	object	result row of mysql result set
* @return 	array
*/
function fetchObjectData($a_row)
{
	$arr = array (
					"obj_id"		=> $a_row->obj_id,
					"ref_id"		=> $a_row->ref_id,
					"type"			=> $a_row->type,
					"title"			=> stripslashes($a_row->title),
					"description"	=> stripslashes($a_row->description),	// for compability only
					"desc"			=> stripslashes($a_row->description),
					"usr_id"		=> $a_row->owner,	// compability
					"owner"			=> $a_row->owner,
					"create_date"	=> $a_row->create_date,
					"last_update"	=> $a_row->last_update,
					"last_login"	=> $a_row->last_login	// maybe senseless
				);

	return $arr ? $arr : array();	// maybe senseless
}


/**
* get list of object, optional only a list of a particular type
* @access	public
* @param	string	object type
* @param	integer	start row of result set
* @param	integer	maximum rows in result set
* @param	string	order column
* @param	string	order direction (possible values: ASC or DESC)
* @return	array/boolean	returns array of objects or false if no objects found
*/
function getObjectList ($a_obj_type = "",$a_order = "", $a_direction = "ASC", $a_offset = "",$a_limit = "")
{
	global $ilias;
	
	// order
	if (!$a_order)
	{
		$a_order = "title";
	}
	
	$order = "ORDER BY ".$a_order." ".$a_direction;

	// limit clause
	if ($a_limit && $a_offset)
	{
		$limit_clause = " LIMIT ".$a_offset.",".$a_limit;
	}

	// where clause
	if ($a_obj_type)
	{
		$where_clause = "WHERE type = '".$a_obj_type."' ";
	}

	$q = "SELECT * FROM object_data ".$where_clause.$order.$limit_clause;
	$r = $ilias->db->query($q);

	if ($r->numRows() > 0)
	{
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = fetchObjectData($row);
		}

		return $arr;
	}

	return false;
}

/**
* get operation list by object type
* TODO: rename function to: getOperationByType
* @access	public
* @param	string	object type you want to have the operation list
* @param	string	order column
* @param	string	order direction (possible values: ASC or DESC)
* @return	array	returns array of operations
*/
function getOperationList ($a_type = "",$a_order= "",$a_direction = "")
 {
	global $ilias;

	if (!$a_order)
	{
		$a_order = "operation";
	}

	if ($a_type)
	{
		$q = "SELECT * FROM rbac_operations ".
			 "LEFT JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id ".
			 "LEFT JOIN object_data ON rbac_ta.typ_id = object_data.obj_id ".
			 "WHERE object_data.title='".$a_type."' AND object_data.type='typ' ".
			 "ORDER BY rbac_operations.".$a_order." ".$a_direction; 
	}
	else
	{
		$q = "SELECT * FROM rbac_operations ".
			 "ORDER BY ".$a_order." ".$a_direction;
	}
	
	$r = $ilias->db->query($q);

	while ($row = $r->fetchRow())
	{
		$arr[] = array(
					"ops_id"	=> $row[0],
					"operation"	=> $row[1],
					"desc"		=> $row[2]
					);
	}

	return $arr;
}

/**
* creates a new object
* @access	public
* @param	string	operation name
* @param	string	operation description
* @return 	integer	returns operation id
*/
function createNewOperation ($a_operation,$a_description)
{
	global $ilias;
	
	if (!isset($a_operation))
	{
		$message = "perm::createNewOperation(): No operation name given!";
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}
	
	// check if operation exists
	$ops_id = getOperationId($a_operation);
	// quit in case operation already exists 	
	if (!empty($ops_id))
	{
		$message = "perm::createNewOperation(): An operation '".$a_operation."' is already defined!";
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}

	$q = "INSERT INTO operations ".
		 "(operation,description) ".
		 "VALUES ".
		 "('".$a_operation."','".$a_description."')";
	$ilias->db->query($q);
	
	return getLastInsertId();
}

/**
* get operation id by name of operation
* @access	public
* @param	string	operation name
* @return	integer	operation id
*/
function getOperationId($a_operation)
{
	global $ilias, $log;

	if (!isset($a_operation))
	{
		$message = "perm::getOperationId(): No operation given!";
		$log->writeWarning($message);
		$ilias->raiseError($message,$ilias->error_obj->WARNING);	
	}

	$q = "SELECT DISTINCT ops_id FROM rbac_operations ".
		 "WHERE operation ='".$a_operation."'";		    
	$row = $ilias->db->getRow($q);

	return $row->ops_id;
}

/*
* get last insert id of a mysql query
* @access	public
* @return	integer	last insert id
*/
function getLastInsertId()
{
	global $ilias;

	$r = $ilias->db->query("SELECT LAST_INSERT_ID()");
	$row = $r->fetchRow();
	
	return $row[0];
}

/**
* POSSIBLE DEPRECATED. IF NOT, FUNCTION BELONGS TO class.user
* check if user is logged in
* @access	public
* @return	boolean	true if logged in
*/
function isUserLoggedIn ()
{
	global $ilias;

	$user_id = $ilias->account->getId();

	if (empty($user_id))
	{
		return false;
	}

	return true;
}

/**
* TODO: move to class.util
* removes spaces and tabs within text strings
* @access	public
* @param	string	string to be trimmed
* @return	string 	trimmed string
*/
function trimDeluxe ($a_text)
{
	str_replace("\t"," ",$a_text);

	for ($i=0;$i<50;$i++)
	{
		str_replace("  "," ",$a_text);
	}

	$a_text = trim($a_text);

	return $a_text;
}

/**
* TODO: move to class.util
* shorten a string to given length.
* Adds 3 dots at the end of string (optional)
* TODO: do not cut within words (->wordwrap function)
* @access	public
* @param	string	string to be shortened
* @param	integer	string length in chars
* @param	boolean	adding 3 dots (true) or not (false, default)
* @return	string 	shortended string
*/
function shortenText ($a_str, $a_len, $a_dots = "false")
{
	if (strlen($a_str) > $a_len)
	{

		$a_str = substr($a_str,0,$a_len); 

		if ($a_dots)
		{
			$a_str .= "...";
		}
	}

	return $a_str;
}

/**
* check if a login name already exists
* You may exclude a user from the check by giving his user id as 2nd paramter
* @access	public
* @param	string	login name
* @param	integer	user id of user to exclude (optional)
* @return	boolean
*/
function loginExists($a_login,$a_user_id = 0)
{
	global $ilias;

	if ($a_user_id == 0)
	{
		$clause = "";
	}
	else
	{
		$clause = "AND usr_id != '".$a_user_id."'";
	}

	$q = "SELECT DISTINCT login FROM usr_data ".
		 "WHERE login = '".$a_login."' ".$clause;
	$r = $ilias->db->query($q);
	
	if ($r->numRows() == 1)
	{
		return true;
	}
	
	return false;
}

/**
* sends a message to the recent page
* if you call sendInfo without any parameter, function will display a stored message
* in session and delete it afterwards
* @access	public
* @param	string	message
* @param	boolean	if true message is kept in session
*/
function sendInfo($a_info = "",$a_keep = false)
{
	global $tpl;

	if (!empty($a_info))
	{
		$_SESSION["info"] = $a_info;
	}

	if (!empty($_SESSION["info"]))
	{
		$tpl->addBlockFile("MESSAGE", "message", "tpl.message.html");
		$tpl->setCurrentBlock("message");
		$tpl->setVariable("INFO",$_SESSION["info"]);
		$tpl->parseCurrentBlock();
	}

	if (!$a_keep)
	{
			session_unregister("info");
	}
}

function infoPanel($a_keep = true)
{
	global $tpl,$ilias,$lng;

	if (!empty($_SESSION["infopanel"]) and is_array($_SESSION["infopanel"]))
	{
		$tpl->addBlockFile("INFOPANEL", "infopanel", "tpl.infopanel.html");
		$tpl->setCurrentBlock("infopanel");
		
		if (!empty($_SESSION["infopanel"]["text"]))
		{
			$link = "<td><a href=\"".$_SESSION["infopanel"]["link"]."\" target=\"bottom\">";
			$link .= $lng->txt($_SESSION["infopanel"]["text"]);
			$link .= "</a></td>";
		}

		if (!empty($_SESSION["infopanel"]["img"]))
		{
			$link .= "<td><a href=\"".$_SESSION["infopanel"]["link"]."\" target=\"bottom\">";
			$link .= "<img src=\"".$ilias->tplPath.$ilias->account->prefs["skin"]."/images/".$_SESSION["infopanel"]["img"]."\" border=\"0\" vspace=\"0\"/>";
			$link .= "</a></td>";
		}

		$tpl->setVariable("INFO_ICONS",$link);
		$tpl->parseCurrentBlock();
	}

	//if (!$a_keep)
	//{
			session_unregister("infopanel");
	//}
}
/**
* check if user has unread mail(s) in inbox
* @access public
* @return boolean
*/
function hasNewMail()
{
	global $ilias;

	$query = "SELECT m.mail_id FROM mail AS m,mail_obj_data AS mo ".
		"WHERE m.user_id = mo.user_id ".
		"AND m.folder_id = mo.obj_id ".
		"AND mo.type = 'inbox' ".
		"AND m.user_id = '".$_SESSION["AccountId"]."' ".
		"AND m.m_status = 'unread'";
	
	$row = $ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
	return $row ? $row->mail_id : 0;
}

function getMailFolderId()
{
	global $ilias;

	$query = "SELECT obj_id FROM mail_obj_data ".
		"WHERE user_id = '".$_SESSION["AccountId"]."' ".
		"AND type = 'inbox'";
	$row = $ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

	return $row->obj_id;
}
?>
