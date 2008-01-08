<?php
/**
* Database Session Handling
*
* @module		inc.db_session_handler.php
* @modulegroup	iliascore
* @version		$Id$
*/


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


/*
* open session, normally a db connection would be opened here, but
* we use the standard ilias db connection, so nothing must be done here
*
* @param	string		$save_pathDSN	information about how to access the database, format:
*										dbtype(dbsyntax)://username:password@protocol+hostspec/database
*										eg. mysql://phpsessmgr:topsecret@db.example.com/sessiondb
* @param	string		$name			session name [PHPSESSID]
*/
function db_session_open($save_path, $name)
{
	return true;
}

/**
* close session
*
* for a db nothing has to be done here
*/
function db_session_close()
{
	return true;
}    

/*
* Reads data of the session identified by $session_id and returns it as a
* serialised string. If there is no session with this ID an empty string is
* returned
*
* @param	integer		$session_id		session id
*/
function db_session_read($session_id)
{
	global $ilDB;

	$q = "SELECT data FROM usr_session WHERE session_id = '".addslashes($session_id)."'";
	$r = $ilDB->query($q);
	$data = $r->fetchRow(DB_FETCHMODE_ASSOC);

	return $data["data"];
}

/**
* Writes serialized session data to the database.
*
* @param	integer		$session_id		session id
* @param	string		$data			session data
*/
function db_session_write($session_id, $data)
{
	global $pear_session_db,$ilDB;

	//var_dump("<pre>",session_decode($data),"</pre>");exit;
	$expires = time() + ini_get("session.gc_maxlifetime");
	$q = "REPLACE INTO usr_session (session_id, expires, data, ctime,user_id) ".
		 "VALUES('".ilUtil::prepareDBString($session_id)."','".$expires."','".ilUtil::prepareDBString($data).
		 "','".time()."','".$_SESSION["AccountId"]."')";
	$ilDB->query($q);	 

	return true;
}

/**
* destroy session
*
* @param	integer		$session_id			session id
*/
function db_session_destroy($session_id)
{
	global $ilDB;

	$q = "DELETE FROM usr_session WHERE session_id = '".addslashes($session_id)."'";
	$ilDB->query($q);
  
	return true;
}


/**
* removes sessions that weren't updated for more than gc_maxlifetime seconds
*
* @param	integer		$gc_maxlifetime			max lifetime in seconds
*/
function db_session_gc($gc_maxlifetime)
{
	global $pear_session_db,$ilDB;

	$q = "DELETE FROM usr_session WHERE expires < ".time();
	$ilDB->query($q);
	
	return true;
}


/*
* register callback functions
* session.save_handler must be 'user' or safe mode off to succeed
*/
function db_set_save_handler()
{
	// register save handler functions
	if (ini_get("session.save_handler") == "user")
	{
		session_set_save_handler(
			"db_session_open",
			"db_session_close",
			"db_session_read",
			"db_session_write",
			"db_session_destroy",
			"db_session_gc");
			
			return true;
	}
	
	return false;
}

function duplicate_session($a_session_id) 
{
	global $ilDB;
	
	// Create new session id
	$new_session = $a_session_id;
	do
	{
		$new_session = md5($new_session);
		$query = "SELECT * FROM usr_session WHERE ".
			"session_id = ".$ilDB->quote($new_session);
		$res = $ilDB->query($query);		
	} while($res->numRows());
	
	$query = "SELECT * FROM usr_session ".
		"WHERE session_id = ".$ilDB->quote($a_session_id);
	$res = $ilDB->query($query);
	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	{
		db_session_write($new_session,$row->data);
		return $new_session;
	}
	return false;
}

// needs to be done to assure that $ilDB exists,
// when db_session_write is called
register_shutdown_function("session_write_close");

?>
