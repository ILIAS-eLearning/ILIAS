<?php
/**
* Database Session Handling for the password assistance use case.
*
* @module		inc.db_pwassist_session_handler.php
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
* @param	string		$name			session name [session_name()]
*/
function db_pwassist_session_open($save_path, $name)
{
    return true;
}

/**
* close session
*
* for a db nothing has to be done here
*/
function db_pwassist_session_close()
{
    return true;
}

/*
* Creates a new secure id.
*
* The secure id has the following characteristics:
* - It is unique
* - It is a non-uniformly distributed (pseudo) random value
* - Only a non-substantial number of bits can be predicted from
*   previously generated id's.
*/
function db_pwassist_create_id()
{
    // #26009 we use ilSession to duplicate the existing session
    return \ilSession::_duplicate(session_id());
}

/*
* Reads data of the session identified by $pwassist_id and returns it as a
* associative array. If there is no session with this ID an empty array is
* returned
*
* @param	integer		$pwassist_id	secure id
*/
function db_pwassist_session_read($pwassist_id)
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = "SELECT * FROM usr_pwassist " .
        "WHERE pwassist_id = " . $ilDB->quote($pwassist_id, "text");
    $r = $ilDB->query($q);
    $data = $ilDB->fetchAssoc($r);

    return $data;
}

/*
* Reads data of the session identified by $user_id.
* Teturns the data as an associative array.
* If there is no session for the specified user_id, an
* empty array is returned
*
* @param	integer		$user_id		user id
**/
function db_pwassist_session_find($user_id)
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = "SELECT * FROM usr_pwassist " .
        "WHERE user_id = " . $ilDB->quote($user_id, "integer");
    $r = $ilDB->query($q);
    $data = $ilDB->fetchAssoc($r);

    return $data;
}

/**
* Writes serialized session data to the database.
*
* @param	integer		$pwassist_id	secure id
* @param	integer		$maxlifetime	session max lifetime in seconds
* @param	integer		$user_id		user id
*/
function db_pwassist_session_write($pwassist_id, $maxlifetime, $user_id)
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = "DELETE FROM usr_pwassist " .
         "WHERE pwassist_id = " . $ilDB->quote($pwassist_id, "text") . " " .
         "OR user_id = " . $ilDB->quote($user_id, 'integer');
    $ilDB->manipulate($q);

    $ctime = time();
    $expires = $ctime + $maxlifetime;
    $ilDB->manipulateF(
        "INSERT INTO usr_pwassist " .
        "(pwassist_id, expires, user_id,  ctime) " .
        "VALUES (%s,%s,%s,%s)",
        array("text", "integer", "integer", "integer"),
        array($pwassist_id, $expires, $user_id, $ctime)
    );

    return true;
}

/**
* destroy session
*
* @param	integer		$pwassist_id			secure id
*/
function db_pwassist_session_destroy($pwassist_id)
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = "DELETE FROM usr_pwassist " .
         "WHERE pwassist_id = " . $ilDB->quote($pwassist_id, "text");
    $ilDB->manipulate($q);
  
    return true;
}


/**
* removes all expired sessions
*/
function db_pwassist_session_gc()
{
    global $DIC;

    $ilDB = $DIC->database();

    $q = "DELETE FROM usr_pwassist " .
         "WHERE expires < " . $ilDB->quote(time(), "integer");
    $ilDB->manipulate($q);
    
    return true;
}
