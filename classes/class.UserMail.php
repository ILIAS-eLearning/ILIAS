<?php
/**
 * user mail
 * 
 * this class handles user mails 
 * 
 * explanation of flags:
 * 1 : new/unread mail
 * 2 : read mail
 * 3 : deleted mail
 *  
 * @author Peter Gabriel <pgabriel@databay.de>
 * 
 * @package application
 * @version $Id$
 */
class UserMail extends PEAR
{
    /**
     * User Id
     *
	 * @var integer
     */
    var $id;					

    /**
     * database handler
     *
     * @var object DB
     */	
    var $db;

    /**
     * Constructor
     *
     * setup an usermail object
     *
     * @param object database handler
     * @param string UserID
     */
    function UserMail(&$dbhandle, $aUsrId = "")
    {

		// Initiate variables
		$this->db =& $dbhandle;

		if (!empty($aUsrId) and ($aUsrId > 0))
		{
		    $this->id = $aUsrId;
		}
    }


    /**
	* delete a mail
    * @param int
	* @return boolean
	* @access public
    */
	 function rcpDelete ($aMsgId)
	 {
		 if (empty($aMsgId))
		 {
		 	return true;
		 }
		 else
		 {
		 	//delete mail here
			//TODO: security, only delete if it is allowed
			
			$sql = "UPDATE mail SET rcp_folder='trash' WHERE id=".$aMsgId;

			$this->db->query($sql);

			return true;
		 }
	 }
	 
	
    /**
	* set mailstatus
	* 
	* set the status of a mail. valid stati are
	* 1: new, 2: read, 3: deleted, 4: erased, 5: saved, 6: sent
	* 
    * @param int
	* @param string rcp or snd
	* @param string
	* @return boolean
	* @access public
    */
	 function setStatus($aMsgId, $who, $status)
	 {
		 if (empty($aMsgId) || ($who != "rcp" && $who != "snd") || $status=="")
		 {
		 	return false;
		 }
		 else
		 {
			//TODO: security, only perform an action if allowed to
			switch ($status)
			{
				case "unread":
				case "new":
					$st = 1;
					break;
				case "read":
					$st = 2;
					break;
				case "deleted":
					$st = 3;
					break;
				case "erased":
					$st = 4;
					break;
				case "saved":
					$st = 5;
					break;
				case "sent":
					$st = 6;
					break;
			}

			//perform query
			$sql = "UPDATE mail SET ".$who."_flag=".$st." WHERE id=".$aMsgId;

			$this->db->query($sql);

			return true;
		 }
	 }
	 
		 
	 /**
	  * get mail
	  *
	  * @param string
	  * @return array mails
	  * @access public
	  */
	 function getMail($folder = "inbox")
	 {
		 global $lng;

		 //initialize array
		 $mails = array();
		 $mails["count"] = 0;
		 $mails["unread"] = 0;
		 $mails["read"] = 0;
		 //initialize msg-array
		 $mails["msg"] = array();
		 //query
		 $sql = "SELECT * FROM mail
                 WHERE rcp='".$this->id."'
				 AND rcp_folder='".$folder."'
				 AND (rcp_flag=1 OR rcp_flag=2)";
		 $r = $this->db->query($sql);

		 
		 while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		 {
			 if ($row["rcp_flag"]==1)
			 	$mails["unread"]++;
			 if ($row["rcp_flag"]==2)
			 	$mails["read"]++;

			 $mails["msg"][] = array(
				 "id" => 1,
				 "from" => $row["snd"],
				 "email" => $row["email"],
				 "subject" => $row["subject"],
				 "body" => $row["body"],
				 "datetime" => $lng->fmtDateTime($row["date_send"]),
				 "new" => $row["new"]
			 );
		 }
		 		 
		 $mails["count"] = $mails["read"] + $mails["unread"];
		 return $mails;
	 }
	 	 
		 
 	 function sendMail($rcp, $subject, $body)
	 {
		$sql = "INSERT INTO mail
				(snd, rcp, subject, body, snd_flag, rcp_flag, date_send)
				VALUES
				('".$this->id."', '".$rcp."', '".$subject."', '".$body."','6', '1', NOW())";

		$this->db->query($sql);
	 }
	 
} // END class user
?>
