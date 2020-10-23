<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Authentication/classes/class.ilSession.php");

/**
* Database Session Handling
*
* @module		inc.db_session_handler.php
* @modulegroup	iliascore
* @version		$Id: inc.db_session_handler.php 18894 2009-02-06 15:24:04Z akill $
*/
class ilSessionDBHandler
{
    /*
    * register callback functions
    * session.save_handler must be 'user' or safe mode off to succeed
    */
    public function setSaveHandler()
    {
        // register save handler functions
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        if (ini_get("session.save_handler") == "user" || version_compare(PHP_VERSION, '7.2.0', '>=')) {
            session_set_save_handler(
                array($this, "open"),
                array($this, "close"),
                array($this, "read"),
                array($this, "write"),
                array($this, "destroy"),
                array($this, "gc")
            );

            return true;
        }

        return false;
    }
    
    /*
    * open session, normally a db connection would be opened here, but
    * we use the standard ilias db connection, so nothing must be done here
    *
    * @param	string		$save_pathDSN	information about how to access the database, format:
    *										dbtype(dbsyntax)://username:password@protocol+hostspec/database
    *										eg. mysql://phpsessmgr:topsecret@db.example.com/sessiondb
    * @param	string		$name			session name [PHPSESSID]
    */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
    * close session
    *
    * for a db nothing has to be done here
    */
    public function close()
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
    public function read($session_id)
    {
        return ilSession::_getData($session_id);
    }

    /**
    * Writes serialized session data to the database.
    *
    * @param	integer		$session_id		session id
    * @param	string		$data			session data
    */
    public function write($session_id, $data)
    {
        $cwd = getcwd();
        chdir(IL_INITIAL_WD);
        include_once("./Services/Authentication/classes/class.ilSession.php");
        $r = ilSession::_writeData($session_id, $data);
        // see bug http://www.ilias.de/mantis/view.php?id=18000
        //chdir($cwd);
        return $r;
    }

    /**
    * destroy session
    *
    * @param	integer		$session_id			session id
    */
    public function destroy($session_id)
    {
        return ilSession::_destroy($session_id);
    }

    /**
    * removes sessions that weren't updated for more than gc_maxlifetime seconds
    *
    * @param	integer		$gc_maxlifetime			max lifetime in seconds
    */
    public function gc($gc_maxlifetime)
    {
        return ilSession::_destroyExpiredSessions();
    }
}

// needs to be done to assure that $ilDB exists,
// when db_session_write is called
register_shutdown_function("session_write_close");
