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

include_once("./Services/Authentication/classes/class.ilSession.php");

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
	return ilSession::_getData($session_id);
}

/**
* Writes serialized session data to the database.
*
* @param	integer		$session_id		session id
* @param	string		$data			session data
*/
function db_session_write($session_id, $data)
{
	return ilSession::_writeData($session_id, $data);
}

/**
* destroy session
*
* @param	integer		$session_id			session id
*/
function db_session_destroy($session_id)
{
	return ilSession::_destroy($session_id);
}


/**
* removes sessions that weren't updated for more than gc_maxlifetime seconds
*
* @param	integer		$gc_maxlifetime			max lifetime in seconds
*/
function db_session_gc($gc_maxlifetime)
{
	return ilSession::_destroyExpiredSessions();
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
	return ilSession::_duplicate($a_session_id);
}

// needs to be done to assure that $ilDB exists,
// when db_session_write is called
register_shutdown_function("session_write_close");

?>
