<?php
/**
* Class Mail
* this class handles base functions for mail handling mails
* 
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package	common
*/
require_once "classes/class.ilFileDataMail.php";

class Mail
{
	/**
	* database handler
	*
	* @var object ilias
	* @access private
	*/	
	var $ilias;

	/**
	* lng object
	* @var		object language
	* @access	private
	*/
	var $lng;

	/**
	* mail file class object
	* @var		object ilFileDataMail
	* @access	private
	*/
	var $mfile;

	/**
	* actions
	*
	* @var array contains all possible actions
	* @access private
	*/	
	var $actions;

	/**
	* User Id
	* @var integer
	* @access public
	*/
	var $user_id;

	/**
	* table name of mail table
	* @var string
	* @access private
	*/
	var $table_mail;

	/**
	* table name of mail table
	* @var string
	* @access private
	*/
	var $table_mail_saved;

	/**
	* counter of read,unread and total number of mails
	* @var array
	* @access private
	*/
	var $mail_counter;

	/**
	* data of one mail
	* @var array
	* @access private
	*/
	var $mail_data;

	/**
	* all email recipients
	* @var array
	* @access private
	*/
	var $email_rcp;


	/**
	* Constructor
	* setup an mail object
	* @access	public
	* @param	integer	user_id
	*/
	function Mail($a_user_id)
	{
		global $ilias, $lng;
		
		// Initiate variables
		$this->ilias = &$ilias;
		$this->lng   = &$lng;
		$this->table_mail = 'mail';
		$this->table_mail_saved = 'mail_saved';
		$this->actions = array(
			"move"        => $this->lng->txt("mail_move_to"),
			"mark_read"   => $this->lng->txt("mail_mark_read"),
			"mark_unread" => $this->lng->txt("mail_mark_unread"),
			"delete"      => $this->lng->txt("delete"));
		$this->user_id = $a_user_id;
		$this->mfile = new ilFileDataMail($this->user_id);

	}

	/**
	* get all mails of a specific folder
	* @access	public
	* @param	int id of folder
	* @return	array	mails
	*/
	function getMailsOfFolder($a_folder_id)
	{
		$this->mail_counter = array();
		$this->mail_counter["read"] = 0;
		$this->mail_counter["unread"] = 0;

		$query = "SELECT * FROM $this->table_mail ".
			"WHERE user_id = $this->user_id ".
			"AND folder_id = '".$a_folder_id."'";
		
		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp = $this->fetchMailData($row);
			if($tmp["m_status"] == 'read')
			{
				++$this->mail_counter["read"];
			}
			if($tmp["m_status"] == 'unread')
			{
				++$this->mail_counter["unread"];
			}
			$output[] = $tmp;
		}

		$this->mail_counter["total"] = count($output);
		return $output ? $output : array();
	}

	/**
	* get mail counter data
	* returns data array with indexes "total","read","unread"
	* @access	public
	* @return	array	mail_counter data
	*/
	function getMailCounterData()
	{
		return is_array($this->mail_counter) ? $this->mail_counter : array(
			"total"  => 0,
			"read"   => 0,
			"unread" => 0);
	}

	/**
	* get data of one mail
	* @access	public
	* @param	int mail_id
	* @return	array	mail_data
	*/
	function getMail($a_mail_id)
	{
		$query = "SELECT * FROM $this->table_mail ".
			"WHERE user_id = $this->user_id ".
			"AND mail_id = '".$a_mail_id."'";
		
		$this->mail_data = $this->fetchMailData($this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT));
		
		return $this->mail_data; 
	}

	/**
	* mark mails as read
	* @access	public
	* @param	array mail ids
	* @return	bool
	*/
	function markRead($a_mail_ids)
	{
		// CREATE IN STATEMENT
		$in = "(". implode(",",$a_mail_ids) . ")";
		
		$query = "UPDATE $this->table_mail ".
			"SET m_status = 'read' ".
			"WHERE user_id = '".$this->user_id."' ".
			"AND mail_id IN $in";

		$res = $this->ilias->db->query($query);
		return true;
	}
	/**
	* mark mails as unread
	* @access	public
	* @param	array mail ids
	* @return	bool
	*/
	function markUnread($a_mail_ids)
	{
		// CREATE IN STATEMENT
		$in = "(". implode(",",$a_mail_ids) . ")";
		
		$query = "UPDATE $this->table_mail ".
			"SET m_status = 'unread' ".
			"WHERE user_id = '".$this->user_id."' ".
			"AND mail_id IN $in";

		$res = $this->ilias->db->query($query);
		return true;
	}
	/**
	* move mail to folder
	* @access	public
	* @param	array mail ids
	* @param    int folder_id
	* @return	bool
	*/
	function moveMailsToFolder($a_mail_ids,$a_folder_id)
	{
		// CREATE IN STATEMENT
		$in = "(". implode(",",$a_mail_ids) . ")";

		$query = "UPDATE $this->table_mail ".
			"SET folder_id = '".$a_folder_id."' ".
			"WHERE user_id = '".$this->user_id."' ".
			"AND mail_id IN $in";

		$res = $this->ilias->db->query($query);
		return true;
	}
	/**
	* delete mail
	* @access	public
	* @param	array mail ids
	* @return	bool
	*/
	function deleteMails($a_mail_ids)
	{

		foreach($a_mail_ids as $id)
		{
			$query = "DELETE FROM $this->table_mail ".
				"WHERE user_id = '".$this->user_id."' ".
				"AND mail_id = '".$id."'";
			$res = $this->ilias->db->query($query);
			$this->mfile->deassignAttachmentFromDirectory($id);
		}
		return true;
	}

	/**
	* get all possible actions
	* @access	public
	* @return	array possible actions
	*/
	function getActions()
	{
		return $this->actions;
	}

	/**
	* fetch all query data from table mail
	* @access	public
	* @param	object object of query
	* @return	array	array of query data
	*/
	function fetchMailData($a_row)
	{
		return array(
			"mail_id"         => $a_row->mail_id,
			"user_id"         => $a_row->user_id,
			"folder_id"       => $a_row->folder_id,
			"sender_id"       => $a_row->sender_id,
			"attachments"     => unserialize(stripslashes($a_row->attachments)), 
			"send_time"       => $a_row->send_time,
			"timest"          => $a_row->timest,
			"rcp_to"          => stripslashes($a_row->rcp_to),
			"rcp_cc"          => stripslashes($a_row->rcp_cc),
			"rcp_bcc"         => stripslashes($a_row->rcp_bcc),
			"m_status"        => $a_row->m_status,
			"m_type"          => $a_row->m_type,
			"m_email"         => $a_row->m_email,
			"m_subject"       => stripslashes($a_row->m_subject),
			"m_message"       => stripslashes($a_row->m_message));
	}

	/**
	* save mail in folder
	* @access	public
	* @param	int id of folder
	* @param    int sender_id
	* @param    array attachments
	* @param    string to
	* @param    string cc
	* @param    string bcc
	* @param    string status
	* @param    string type of mail (system,normal)
	* @param    int as email (1,0)
	* @param    string subject
	* @param    string message
	* @param    int user_id
	* @return	int mail_id
	*/
	function sendInternalMail($a_folder_id,
							  $a_sender_id,
							  $a_attachments,
							  $a_rcp_to,
							  $a_rcp_cc,
							  $a_rcp_bcc,
							  $a_status,
							  $a_m_type,
							  $a_m_email,
							  $a_m_subject,
							  $a_m_message,
							  $a_user_id = 0)
	{
		$a_user_id = $a_user_id ? $a_user_id : $this->user_id;

		$query = "INSERT INTO $this->table_mail ".
			"SET user_id = '".$a_user_id."',".
			"folder_id = '".$a_folder_id."',".
			"sender_id = '".$a_sender_id."',".
			"attachments = '".addslashes(serialize($a_attachments))."',".
			"send_time = now(),".
			"rcp_to = '".addslashes($a_rcp_to)."',".
			"rcp_cc = '".addslashes($a_rcp_cc)."',".
			"rcp_bcc = '".addslashes($a_rcp_bcc)."',".
			"m_status = '".$a_status."',".
			"m_type = '".$a_m_type."',".
			"m_email = '".$a_m_email."',".
			"m_subject = '".addslashes($a_m_subject)."',".
			"m_message = '".addslashes($a_m_message)."'";

		$res = $this->ilias->db->query($query);

		$query = "SELECT LAST_INSERT_ID() FROM $this->table_mail";
		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_ASSOC);

		return $row["last_insert_id()"];
	}
	/**
	* send internal message to recipients
	* @access	public
	* @param    array attachments
	* @param    int sender_id
	* @param    string to
	* @param    string cc
	* @param    string bcc
	* @param    string subject
	* @param    string message
	* @param    int id of mail which is stored in sentbox
	* @param    string 'normal' or 'system'
	* @return	bool
	*/
	function distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_subject,$a_message,$a_attachments,$sent_mail_id,$a_type = 'normal')
	{
		require_once "classes/class.Mailbox.php";

		$mbox = new Mailbox();

		$rcp_ids = $this->getUserIds(trim($a_rcp_to).",".trim($a_rcp_cc).",".trim($a_rcp_bcc));
		foreach($rcp_ids as $id)
		{
			if($a_type == 'normal')
			{
				$mbox->setUserId($id);
				$inbox_id = $mbox->getInboxFolder();
			}
			else
			{
				$inbox_id = 0;
			}
			$mail_id = $this->sendInternalMail($inbox_id,$this->user_id,
								  $a_attachments,$a_rcp_to,
								  $a_rcp_cc,'','unread','normal',
								  0,$a_subject,$a_message,$id);
			if($a_attachments)
			{
				$this->mfile->assignAttachmentsToDirectory($mail_id,$sent_mail_id,$a_attachments);
			}
	}		
		return true;
	}
	
	/**
	* check if all recipients have a valid email address
	* and stores all recipients in member variable email_recipients
	* @access	public
	* @param    string to
	* @param    string cc
	* @param    string bcc
	* @return	array user login which have no valid email
	*/
	function checkEmailRecipients($a_rcp_to,$a_rcp_cc,$a_rcp_bcc)
	{
		$login_names = array();
		$this->email_rcp_to = array();
		$this->email_rcp_cc = array();
		$this->email_rcp_bcc = array();
		
		require_once "classes/class.User.php";

		// TO
		$rcp_ids_to = $this->getUserIds(trim($a_rcp_to));
		if(is_array($rcp_ids_to))
		{
			foreach($rcp_ids_to as $id)
			{
				$tmp_user = new User($id);
				if(!TUtil::is_email($tmp_user->getEmail()))
				{
					$login_names[] = $tmp_user->getLogin();
				}
				$this->email_rcp_to["$id"] = $tmp_user->getEmail();
			}
		}
		// CC
		$rcp_ids_cc = $this->getUserIds(trim($a_rcp_cc));
		if(is_array($rcp_ids_cc))
		{
			foreach($rcp_ids_cc as $id)
			{
				$tmp_user = new User($id);
				if(!TUtil::is_email($tmp_user->getEmail()))
				{
					$login_names[] = $tmp_user->getLogin();
				}
				$this->email_rcp_cc["$id"] = $tmp_user->getEmail();
			}
		}
		// BCC
		$rcp_ids_bcc = $this->getUserIds(trim($a_rcp_bcc));
		if(is_array($rcp_ids_bcc))
		{
			foreach($rcp_ids_bcc as $id)
			{
				$tmp_user = new User($id);
				if(!TUtil::is_email($tmp_user->getEmail()))
				{
					$login_names[] = $tmp_user->getLogin();
				}
				$this->email_rcp_bcc["$id"] = $tmp_user->getEmail();
			}
		}
		return $login_names;
	}

	/**
	* get user_ids
	* @param    string recipients seperated by ','
	* @return	string error message
	*/
	function getUserIds($a_recipients)
	{
		require_once "classes/class.User.php";
		require_once "classes/class.Group.php";

		$user = new User();

		$tmp_names = explode(',',$a_recipients);
		
		for($i = 0;$i < count($tmp_names); $i++)
		{
			if(substr($tmp_names[$i],0,1) == '#')
			{
				// GET GROUP MEMBER IDS
			}
			else if(!empty($tmp_names[$i]))
			{
				$ids[] = $user->getUserId(addslashes($tmp_names[$i]));
			}
		}
		return $ids;
	}
	/**
	* check if mail is complete, recipients are valid
	* @access	public
	* @param	string rcp_to
	* @param    string rcp_cc
	* @param    string rcp_bcc
	* @param    string m_subject
	* @param    string m_message
	* @param    int m_email 
	* @return	string error message
	*/
	function checkMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message)
	{
		$error_message = '';

		if(empty($a_m_subject))
		{
			$error_message = $this->lng->txt("mail_add_subject");
		}
		if(empty($a_rcp_to))
		{
			$error_message .= $error_message ? "<br>" : '';
			$error_message .= $this->lng->txt("mail_add_recipient");
		}
		else if(!$this->checkRecipients($a_rcp_to))
		{
			$error_message .= $error_message ? "<br>" : '';
			$error_message .= $this->lng->txt("mail_recipient_not_valid");
		}
		if(!empty($a_rcp_cc))
		{
			if(!$this->checkRecipients($a_rcp_cc))
			{
				$error_message .= $error_message ? "<br>" : '';
				$error_message .= $this->lng->txt("mail_cc_not_valid");
			}
		}
		if(!empty($a_rcp_bcc))
		{
			if(!$this->checkRecipients($a_rcp_bcc))
			{
				$error_message .= $error_message ? "<br>" : '';
				$error_message .= $this->lng->txt("mail_bcc_not_valid");
			}
		}
		return $error_message;
	}

	/**
	* check if mail can be send as valid email
	* @access	public
	* @param	string rcp_to
	* @param    string rcp_cc
	* @param    string rcp_bcc
	* @param    string m_subject
	* @param    string m_message
	* @param    int m_email 
	* @return	string error message
	*/
	function checkOnlyEmail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message)
	{
		$error_message = '';

		if(empty($a_m_subject))
		{
			$error_message = $this->lng->txt("mail_add_subject");
		}
		if(empty($a_rcp_to))
		{
			$error_message .= $error_message ? "<br>" : '';
			$error_message .= $this->lng->txt("mail_add_recipient");
		}
		$arr_rcp = explode(',',trim($a_rcp_to));
		$valid = true;
		foreach($arr_rcp as $rcp)
		{
			if(!TUtil::is_email($rcp))
			{
				$valid = false;
			}
		}
		if(!$valid)
		{
			$error_message .= $error_message ? "<br>" : '';
			$error_message .= $this->lng->txt("mail_recipient_not_valid");
		}
		if(!empty($a_rcp_cc))
		{
			$arr_rcp = explode(',',trim($a_rcp_cc));
			$valid = true;
			foreach($arr_rcp as $rcp)
			{
				if(!TUtil::is_email($rcp))
				{
					$valid = false;
				}
			}
			if(!$valid)
			{
				$error_message .= $error_message ? "<br>" : '';
				$error_message .= $this->lng->txt("mail_cc_not_valid");
			}
		}
		if(!empty($a_rcp_bcc))
		{
			$arr_rcp = explode(',',trim($a_rcp_bcc));
			$valid = true;
			foreach($arr_rcp as $rcp)
			{
				if(!TUtil::is_email($rcp))
				{
					$valid = false;
				}
			}
			if(!$valid)
			{
				$error_message .= $error_message ? "<br>" : '';
				$error_message .= $this->lng->txt("mail_bcc_not_valid");
			}
		}
		return $error_message;
	}

	/**
	* check if recipients are valid
	* @access	public
	* @param    string string with login names or group names (start with #)
	* @param    int as email 
	* @return	bool
	*/
	function checkRecipients($a_recipients)
	{
		require_once "classes/class.User.php";
		require_once "classes/class.Group.php";
		
		$user = new User();
		$group = new Group();

		$tmp_rcp = explode(',',$a_recipients);

		foreach($tmp_rcp as $rcp)
		{
			if(empty($rcp))
			{
				return false;
			}
			if(substr($rcp,0,1) != '#')
			{
				if(!$user->getUserId(addslashes($rcp)))
				{
					return false;
				}
			}
			else
			{
				if(!$group->groupNameExists(addslashes(substr($rcp,1))))
					return false;
			}
		}
		return true;
	}
	/**
	* save post data in table
	* @access	public
	* @param    int user_id
	* @param    array attachments
	* @param    string to
	* @param    string cc
	* @param    string bcc
	* @param    string type of mail (system,normal)
	* @param    int as email (1,0)
	* @param    string subject
	* @param    string message
	* @return	bool
	*/
	function savePostData($a_user_id,
						  $a_attachments,
						  $a_rcp_to,
						  $a_rcp_cc,
						  $a_rcp_bcc,
						  $a_m_type,
						  $a_m_email,
						  $a_m_subject,
						  $a_m_message)
	{
		$query = "DELETE FROM $this->table_mail_saved ".
			"WHERE user_id = '".$this->user_id."'";
		$res = $this->ilias->db->query($query);

		$query = "INSERT INTO $this->table_mail_saved ".
			"SET user_id = '".$a_user_id."',".
			"attachments = '".addslashes(serialize($a_attachments))."',".
			"rcp_to = '".addslashes($a_rcp_to)."',".
			"rcp_cc = '".addslashes($a_rcp_cc)."',".
			"rcp_bcc = '".addslashes($a_rcp_bcc)."',".
			"m_type = '".$a_m_type."',".
			"m_email = '".$a_m_email."',".
			"m_subject = '".addslashes($a_m_subject)."',".
			"m_message = '".addslashes($a_m_message)."'";

		$res = $this->ilias->db->query($query);

		return true;
	}
	/**
	* get saved data 
	* @access	public
	* @return	array of saved data
	*/
	function getSavedData()
	{
		$query = "SELECT * FROM $this->table_mail_saved ".
			"WHERE user_id = '".$this->user_id."'";

		$this->mail_data = $this->fetchMailData($this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT));
		return $this->mail_data;
	}

	/**
	* send external mail using class.MimeMail.php
	* @param string to
	* @param string cc
	* @param string bcc
	* @param string subject
	* @param string message
	* @param array attachments
	* @access	public
	* @return	array of saved data
	*/
	function sendMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachment,$a_type,$a_as_email)
	{
		global $lng;
		$error_message = '';

		if($a_attachment)
		{
			if(!$this->mfile->checkFilesExist($a_attachment))
			{
				return "YOUR LIST OF ATTACHMENTS IS NOT VALID, PLEASE EDIT THE LIST";
			}
		}

		switch($a_type)
		{
			case 'normal':
				if($error_message = $this->checkMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message))
				{
					return $error_message;
				}
				if($a_as_email)
				{
					if(!$this->getEmailOfSender())
					{
						return "You have no valid email address";
					}
					if($logins = $this->checkEmailRecipients($a_rcp_to,$a_rcp_cc,$a_rcp_bcc))
					{
						$error_message = "The following users have no valid email address:<BR>";
						$error_message .= implode("<BR>",$logins);

						return $error_message;
					}
					$this->sendMimeMail(implode(',',$this->email_rcp_to),implode(',',$this->email_rcp_cc),
										implode(',',$this->email_rcp_bcc),$a_m_subject,$a_m_message,$a_attachment);
				}
				// SAVE MAIL IN SENT BOX
				$sent_id = $this->saveInSentbox($a_attachment,$a_rcp_to,$a_rcp_cc,$a_rcp_bcc,'normal',
												$a_as_email,$a_m_subject,$a_m_message);
				
				if(!$this->distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachment,$sent_id))
				{
					return "Error sending mail";
				}
				// SAVE ATTACHMENTS
				if($error = $this->mfile->saveFiles($sent_id,$a_attachment))
				{
					return $error;
				}
				break;

			case 'email':
				if(!$this->getEmailOfSender())
				{
					return "You have no valid email address";
				}
				if($error_message = $this->checkOnlyEmail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message))
				{
					return $error_message;
				}
				$this->sendMimeMail($a_rcp_to,$a_rcp_cc,
									$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachment);
				// SAVE IN SENTBOX
				$sent_id = $this->saveInSentbox(array(),$a_rcp_to,$a_rcp_cc,$a_rcp_bcc,'email',
												$a_as_email,$a_m_subject,$a_m_message);
				break;

			case 'system':
				if($error_message = $this->checkMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message))
				{
					return $error_message;
				}
				if(!empty($a_attachment))
				{
					return $lng->txt("mail_no_attach_allowed");
				}
				if($a_as_email)
				{
					if(!$this->getEmailOfSender())
					{
						return "You have no valid email address";
					}
					if($logins = $this->checkEmailRecipients($a_rcp_to,$a_rcp_cc,$a_rcp_bcc))
					{
						$error_message = "The following users have no valid email address:<BR>";
						$error_message .= implode("<BR>",$logins);

						return $error_message;
					}
					$this->sendMimeMail(implode(',',$this->email_rcp_to),implode(',',$this->email_rcp_cc),
										implode(',',$this->email_rcp_bcc),$a_m_subject,$a_m_message,$a_attachment);
				}
				$sent_id = $this->saveInSentbox($a_attachment,$a_rcp_to,$a_rcp_cc,$a_rcp_bcc,'system',
												$a_as_email,$a_m_subject,$a_m_message);
				
				if(!$this->distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachment,$sent_id,'system'))
				{
					return "Error sending mail";
				}
				break;
		}
		return $error_message;
	}


	/**
	* send mime mail using class.MimeMail.php
	* @param array attachments
	* @param string to
	* @param string cc
	* @param string bcc
	* @param string type
	* @param int as email
	* @param string subject
	* @param string message
	* @access	public
	* @return	int mail id
	*/
	function saveInSentbox($a_attachment,$a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_type,
						   $a_as_email,$a_m_subject,$a_m_message)
	{
		require_once "classes/class.Mailbox.php";

		$mbox = new Mailbox($this->user_id);
		$sent_id = $mbox->getSentFolder();
		return $this->sendInternalMail($sent_id,$this->user_id,$a_attachment,$a_rcp_to,$a_rcp_cc,
										$a_rcp_bcc,'read',$a_type,$a_as_email,$a_m_subject,$a_m_message,$this->user_id);
	}

	/**
	* send mime mail using class.MimeMail.php
	* @param string to
	* @param string cc
	* @param string bcc
	* @param string subject
	* @param string message
	* @param array attachments
	* @access	public
	* @return	array of saved data
	*/
	function sendMimeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachments)
	{
		require_once "classes/class.MimeMail.php";

		$sender = $this->getEmailOfSender();

		$mmail = new MimeMail();
		$mmail->From($sender);
		$mmail->To($a_rcp_to);
		$mmail->Subject($a_m_subject);
		$mmail->Body($a_m_message);
		if($a_rcp_cc)
		{
			$mmail->Cc($a_rcp_cc);
		}
		if($a_rcp_bcc)
		{
			$mmail->Bcc($a_rcp_bcc);
		}
		foreach($a_attachments as $attachment)
		{
			$mmail->Attach($this->mfile->getAbsolutePath($attachment));
		}
		$mmail->Send();
	}
	/**
	* get email of sender
	* @access	public
	* @return	string email
	*/
	function getEmailOfSender()
	{
		require_once "classes/class.User.php";

		$umail = new User($this->user_id);
		$sender = $umail->getEmail();
		if(TUtil::is_email($sender))
		{
			return $sender;
		}
		else
		{
			return '';
		}
	}
	/**
	* set attachments
	* @param array array of attachments
	* @access	public
	* @return bool
	*/
	function saveAttachments($a_attachments)
	{
		$query = "UPDATE $this->table_mail_saved ".
			"SET attachments = '".addslashes(serialize($a_attachments))."' ".
			"WHERE user_id = '".$this->user_id."'";

		$res = $this->ilias->db->query($query);
		return true;
	}

	/**
	* get attachments
	* @access	public
	* @return array array of attachments
	*/
	function getAttachments()
	{
		return $this->mail_data["attachments"] ? $this->mail_data["attachments"] : array();
	}
			
} // END class.UserMail
?>
