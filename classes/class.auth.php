<?php
/**
* Benutzerauthentifizierung
* @package ilias-core
* @version $Id$
*/
class TAuth
{
    function authenticate($ALogin, $APasswd)
    {
        global $ilias;

        $db = $ilias->db;
        
        $encrypted_passwd = md5($APasswd);

        $query = "SELECT DISTINCT * FROM users WHERE login='$ALogin'
                  AND passwd='$encrypted_passwd' LIMIT 1";

        $db->query($query);

        if ($db->next_record())
        {
            //Fetch Account
            $AccountId = $db->f("obj_id");

            // return Account
            return $AccountId;
        }
        else
        {
            return false;
        }
    }

    // update last login
    function setLastLogin($AUserId)
    {
        global $ilias;

        $db = $ilias->db;
        
        $db->query("UPDATE users SET last_login=now() WHERE obj_id='$AUserId'");
    }

    // change password
    function changePasswd($APasswdOld,$APasswdNew,$AUserId="")
    {
        global $ilias;
        
        $db = $ilias->db;

        // Fetch UserId from Global array when not set
		// Muss noch berarbeitet werden.
        if (!$AUserId)
        {
            $AUserId = $ilias->account["Id"];
        }
        
        $encrypted_passwd = md5($APasswdNew);

        $query = "UPDATE users SET passwd='$encrypted_passwd' WHERE obj_id='$AUserId'";
        
        $db->query($query);

		return $APasswdNew;
    }
}

?>