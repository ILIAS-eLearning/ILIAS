<?php
/**
* Class UserMail
* this class handles user mails 
* 
* explanation of flags:
* 1 : new/unread mail
* 2 : read mail
* 3 : deleted mail
*  
* @author	Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package	application
*/
class UserMail
{
	/**
	* User Id
	* @var integer
	* @access public
	*/
	var $id;

	/**
	* database handler
	*
	* @var object ilias
	* @access private
	*/	
	var $ilias;

	/**
	* Constructor
	* setup an usermail object
	* @access	public
	* @param	integer	user_id
	*/
	function UserMail($a_user_id = 0)
	{
		global $ilias;
		
		// Initiate variables
		$this->ilias =& $ilias;

		if (!empty($a_user_id))
		{
			$this->id = $a_user_id;
		}
	}

	/**
	* delete a mail
	* @access	public
	* @param	integer		message_id
	* @return	boolean
	*/
	function rcpDelete ($a_msg_id)
	{
		if (empty($a_msg_id))
		{
			return true;
		}
		else
		{
			//delete mail here
			//TODO: security, only delete if it is allowed
			
			$sql = "UPDATE mail SET rcp_folder='trash' WHERE id=".$a_msg_id;

			$this->ilias->db->query($sql);

			return true;
		}
	}

	/**
	* set mailstatus
	* 
	* set the status of a mail. valid stati are
	* 1: new, 2: read, 3: deleted, 4: erased, 5: saved, 6: sent
	* 
	* @access	public
	* @param	integer		message_id
	* @param	string		rcp or snd
	* @param	string		status ????
	* @return	boolean
	*/
	function setStatus($a_msg_id, $a_who, $a_status)
	{
		if (empty($a_msg_id) || ($a_who != "rcp" && $a_who != "snd") || $a_status=="")
		{
			return false;
		}
		else
		{
			//TODO: security, only perform an action if allowed to
			switch ($a_status)
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
			$sql = "UPDATE mail SET ".$a_who."_flag=".$st." WHERE id=".$a_msg_id;

			$this->ilias->db->query($sql);

			return true;
		}
	}

	/**
	* get mail
	* @access	public
	* @param	string
	* @return	array	mails
	*/
	function getMail($a_folder = "inbox")
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
				AND rcp_folder='".$a_folder."'
				AND (rcp_flag=1 OR rcp_flag=2)";

		$res = $this->ilias->db->query($sql);

		
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row["rcp_flag"]==1)
				$mails["unread"]++;
			if ($row["rcp_flag"]==2)
				$mails["read"]++;

			$mails["msg"][] = array(
				"id"		=> $row["id"],
				"from"		=> $row["snd"],
				"email"		=> $row["email"],
				"subject"	=> $row["subject"],
				"body"		=> $row["body"],
				"datetime"	=> $lng->fmtDateTime($row["date_send"]),
				"new"		=> $row["new"]
			);
		}

		$mails["count"] = $mails["read"] + $mails["unread"];
		return $mails;
	}

	/**
	* get one mail
	* @access	public
	* @param	integer
	* @return	array		mail
	*/
	function getOneMail($a_id)
	{
		global $lng;

		//initialize array
		$mail = array();

		//query
		$sql = "SELECT * FROM mail
				WHERE rcp='".$this->id."'
				AND id='".$a_id."'";
		$r = $this->ilias->db->query($sql);

		$row = $r->fetchRow(DB_FETCHMODE_ASSOC);
		//if mail is marked as unread mark it as read now
		if ($row["rcp_flag"]==1)
			$this->setStatus($a_id, "rcp", "read");

		$mail = array(
					"id"		=> $row["id"],
					"from"		=> $row["snd"],
					"email"		=> $row["email"],
					"subject"	=> $row["subject"],
					"body"		=> $row["body"],
					"datetime"	=> $lng->fmtDateTime($row["date_send"]),
					"new"		=> 0
					);
			
		return $mail;
	}

	/**
	* get MailFolder of the User
	* @access	public
	* @return	array
	*/
	function getMailFolders()
	{
		$folders = array();
		$folders[] = array(
			"name" => "inbox"
		);
		$folders[] = array(
			"name" => "archive"
		);
		$folders[] = array(
			"name" => "sent"
		);
		$folders[] = array(
			"name" => "drafts"
		);
		$folders[] = array(
			"name" => "trash"
		);
		return $folders;
	}

	/**
	* send mail to recipient
	* @access	public
	* @param	integer		user_id of recipient
	* @param	string		subject
	* @param	string		message text
	*/
	function sendMail($a_rcp, $a_subject, $a_body)
	{
		$sql = "INSERT INTO mail
				(snd, rcp, subject, body, snd_flag, rcp_flag, date_send)
				VALUES
				('".$this->id."', '".$a_rcp."', '".$a_subject."', '".$a_body."','6', '1', NOW())";

		$this->ilias->db->query($sql);
	}
} // END class.UserMail
?>
