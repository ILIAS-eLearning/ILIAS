<?php
// Session handling
function setSessionSaveHandler ()
{

	global $ilias;

    $session_mode = ini_get("session.save_handler");

	if ($ilias->ini["session"]["save_db"])
    {
        // Ok, user wants to store session in a database. Now check the correct configuration in php.ini
        if ($session_mode == "files")
        {
            // Its wrong, should be "user". So we gonna change this
            ini_set("session.save_handler","user");

            // Everything is prepared. Now define
            // jump points for session routines
            session_set_save_handler("s_open","s_close","s_read","s_write","s_destroy","s_gc");
        }
    }
    else
    {
        // User wants to store session in filesystem
        if ($session_mode == "user")
        {
            // ...but uses the wrong configuration. So we change it
            ini_set("session.save_handler","files");
        }
    }
}


// MySql-support for PHP4 sessions
$slife = ini_get("session.gc_maxlifetime");

function s_open ($save_path, $session_name)
{
    return true;
}

function s_close()
{
    return true;
}

function s_read ($key)
{
    global $sdb, $slife;
    
    $query = "SELECT value FROM user_session WHERE sesskey = '$key' AND expiry > " . time();
    $res = $sdb->query($query);
	
	if (DB::isError($res)) {
		die("fhhfjhd" . $res->getMessage());
	}
	
	if ($res->numRows() > 0)
	{
    	$data = $res->fetchRow();
        return $data["value"];
	}
   
	return false;
}

function s_write ($key, $val)
{
    global $sdb, $slife;

    $expiry = time() + $slife;
    $value = addslashes($val);

    $query = "REPLACE INTO user_session VALUES ('$key', $expiry, '$value')";
    $sdb->query($query);

    return true;
}

function s_destroy ($key)
{
    global $sdb;

    $query = "DELETE FROM user_session WHERE sesskey = '$key'";
    $res = $sdb->query($query);
    $qid = $res->result;

    return $qid;
}

function s_gc($maxlifetime)
{
    global $sdb;

    $query = "DELETE FROM user_session WHERE expiry < " . time();
    $sdb->query($query);

	return $sdb->affectedRows();
}

?>
