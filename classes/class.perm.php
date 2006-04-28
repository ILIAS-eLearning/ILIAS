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
* deprecated: Use new ilObj..($ref_id, true) or 
* $ilias->obj_factory->getInstanceByRefId($ref_id) instead
*//*
function getObjectByReference ($a_ref_id)
{
}*/


/**
* deprecated: use ilObject->clone() instead
*/
/*
function copyObject ($a_obj_id)
{
}*/


/**
* deprecated: use ilObject->delete() instead
*//*
function deleteObject ($a_obj_id)
{
}*/

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
   /*
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
	$a_title = ilUtil::shortenText($a_title,$a_len_title,$a_dots);
	$_desc = ilUtil::shortenText($a_desc,$a_len_desc,$a_dots);

	$q = "UPDATE object_data ".
		 "SET ".
		 "title='".ilUtil::addSlashes($a_title)."',".
		 "description='".ilUtil::addSlashes($a_desc)."', ".
		 "last_update=now() ".
		 "WHERE obj_id='".$a_obj_id."'";
	$ilias->db->query($q);

	return true;
}
*/


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
		 "SET ".$a_column."='".ilUtil::addSlashes($a_value)."',".
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
					"title"			=> $a_row->title,
					"description"	=> $a_row->description,	// for compability only
					"desc"			=> $a_row->description,
					"usr_id"		=> $a_row->owner,	// compability
					"owner"			=> $a_row->owner,
					"create_date"	=> $a_row->create_date,
					"last_update"	=> $a_row->last_update,
					"last_login"	=> $a_row->last_login,	// maybe senseless
					"assign"		=> $a_row->assign,	// maybe senseless
					"protected"		=> $a_row->protected,
					"parent"		=> $a_row->parent	// role folder 
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
			$arr[$row->obj_id] = fetchObjectData($row);
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
function getOperationList ($a_type = null)
 {
	global $ilias;

	$arr = array();

	if ($a_type)
	{
		$q = "SELECT * FROM rbac_operations ".
			 "LEFT JOIN rbac_ta ON rbac_operations.ops_id = rbac_ta.ops_id ".
			 "LEFT JOIN object_data ON rbac_ta.typ_id = object_data.obj_id ".
			 "WHERE object_data.title='".$a_type."' AND object_data.type='typ' ".
			 "ORDER BY 'order' ASC"; 
	}
	else
	{
		$q = "SELECT * FROM rbac_operations ".
			 "ORDER BY 'order' ASC";
	}
	
	$r = $ilias->db->query($q);

	while ($row = $r->fetchRow())
	{
		$arr[] = array(
					"ops_id"	=> $row[0],
					"operation"	=> $row[1],
					"desc"		=> $row[2],
					"class"		=> $row[3],
					"order"		=> $row[4]
					);
	}

	return $arr;
}

function groupOperationsByClass($a_ops_arr)
{
	$arr = array();

	foreach ($a_ops_arr as $ops)
	{
		$arr[$ops['class']][] = array ('ops_id'	=> $ops['ops_id'],
									   'name'	=> $ops['operation']
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
		 "('".ilUtil::addSlashes($a_operation)."','".ilUtil::addSlashes($a_description)."')";
	$ilias->db->query($q);

	return $ilias->db->getLastInsertId();
}

/**
* get operation id by name of operation
* @access	public
* @param	string	operation name
* @return	integer	operation id
*/
function getOperationId($a_operation)
{
	global $ilias;

	if (!isset($a_operation))
	{
		$message = "perm::getOperationId(): No operation given!";
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
/*

This function is deprecated !!!
Use $ilDB->getLastInsertId()

function getLastInsertId()
{
	global $ilias;

	$r = $ilias->db->query("SELECT LAST_INSERT_ID()");
	$row = $r->fetchRow();

	return $row[0];
}*/

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
* deprecated: moved to class ilUtil
*/
/*
function shortenText ($a_str, $a_len, $a_dots = "false")
{
}*/

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
		 "WHERE login = '".ilUtil::stripSlashes($a_login)."' ".$clause;
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
#		$tpl->setCurrentBlock("message");
		$tpl->setVariable("INFO",$_SESSION["info"]);
#		$tpl->parseCurrentBlock();
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
			$link = "<a href=\"".$dir.$_SESSION["infopanel"]["link"]."\" target=\"".
				ilFrameTargetInfo::_getFrame("MainContent").
				"\">";
			$link .= $lng->txt($_SESSION["infopanel"]["text"]);
			$link .= "</a>";
		}

		// deactivated
		if (!empty($_SESSION["infopanel"]["img"]))
		{
			$link .= "<td><a href=\"".$_SESSION["infopanel"]["link"]."\" target=\"".
				ilFrameTargetInfo::_getFrame("MainContent").
				"\">";
			$link .= "<img src=\"".$ilias->tplPath.$ilias->account->prefs["skin"]."/images/".
				$_SESSION["infopanel"]["img"]."\" border=\"0\" vspace=\"0\"/>";
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
?>
