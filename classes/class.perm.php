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
*/



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
