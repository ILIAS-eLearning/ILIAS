<?php
/**
* Class Mail
* this class handles base functions for mail handling
* 
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package	ilias-mail
*/
class ilMail
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
	* mail object id used for check access
	* @var integer
	* @access private
	*/
	var $mail_obj_ref_id;

	/**
	* variable for sending mail
	* @var array of send type usally 'normal','system','email'
	* @access private
	*/
	var $mail_send_type;

	/**
	* variable for sending mail
	* @var string 
	* @access private
	*/
	var $mail_rcp_to;
	var $mail_rcp_cc;
	var $mail_rcp_bc;
	var $mail_subject;
	var $mail_message;


	/**
	* Constructor
	* setup an mail object
	* @access	public
	* @param	integer	user_id
	*/
	function ilMail($a_user_id)
	{
		require_once "classes/class.ilFileDataMail.php";
		global $ilias, $lng;
		$lng->loadLanguageModule("mail");
		
		// Initiate variables
		$this->ilias = &$ilias;
		$this->lng   = &$lng;
		$this->table_mail = 'mail';
		$this->table_mail_saved = 'mail_saved';
		$this->user_id = $a_user_id;
		$this->mfile = new ilFileDataMail($this->user_id);
		
		// GET REFERENCE ID OF MAIL OBJECT
		$this->readMailObjectReferenceId();

	}
	/**
	* set mail send type
	* @var array of send types ('system','normal','email')
	* @access	public
	*/
	function setMailSendType($a_types)
	{
		$this->mail_send_type = $a_types;
	}

	/**
	* set mail recipient to
	* @var string rcp_to
	* @access	public
	*/
	function setMailRcpTo($a_rcp_to)
	{
		$this->mail_rcp_to = $a_rcp_to;
	}

	/**
	* set mail recipient cc
	* @var string rcp_to
	* @access	public
	*/
	function setMailRcpCc($a_rcp_cc)
	{
		$this->mail_rcp_cc = $a_rcp_cc;
	}

	/**
	* set mail recipient bc
	* @var string rcp_to
	* @access	public
	*/
	function setMailRcpBc($a_rcp_bc)
	{
		$this->mail_rcp_bc = $a_rcp_bc;
	}

	/**
	* set mail subject
	* @var string subject
	* @access	public
	*/
	function setMailSubject($a_subject)
	{
		$this->mail_subject = $a_subject;
	}

	/**
	* set mail message
	* @var string message
	* @access	public
	*/
	function setMailMessage($a_message)
	{
		$this->mail_message = $a_message;
	}

	/**
	* read and set mail object id
	* @access	private
	*/
	function readMailObjectReferenceId()
	{
		$query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data ".
			"WHERE tree.parent = '".SYSTEM_FOLDER_ID."' ".
			"AND object_data.type = 'mail' ".
			"AND object_data.obj_id = tree.child ".
			"AND object_reference.obj_id = tree.child";
		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->mail_obj_ref_id = $row["ref_id"];
		}
	}
	/**
	* get mail object reference id
	* @return integer mail_obj_ref_id
	* @access	public
	*/
	function getMailObjectReferenceId()
	{
		return $this->mail_obj_ref_id;
	}

	/**
	* get all mails of a specific folder
	* @access	public
	* @param	integer id of folder
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
			"m_type"          => unserialize(stripslashes($a_row->m_type)),
			"m_email"         => $a_row->m_email,
			"m_subject"       => stripslashes($a_row->m_subject),
			"m_message"       => stripslashes($a_row->m_message));
	}

	/**
	* save mail in folder
	* @access	public
	* @param	integer id of folder
	* @param    integer sender_id
	* @param    array attachments
	* @param    string to
	* @param    string cc
	* @param    string bcc
	* @param    string status
	* @param    string type of mail (system,normal)
	* @param    integer as email (1,0)
	* @param    string subject
	* @param    string message
	* @param    integer user_id
	* @return	integer mail_id
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
			"m_type = '".addslashes(serialize($a_m_type))."',".
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
	* @access	private
	* @param    string to
	* @param    string cc
	* @param    string bcc
	* @param    string subject
	* @param    string message
	* @param    array attachments
	* @param    integer id of mail which is stored in sentbox
	* @param    array 'normal' and/or 'system' and/or 'email'
	* @return	bool
	*/
	function distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_subject,$a_message,$a_attachments,$sent_mail_id,$a_type,$a_action)
	{
		require_once "classes/class.ilMailbox.php";

		$mbox = new ilMailbox();

		$rcp_ids = $this->getUserIds(trim($a_rcp_to).",".trim($a_rcp_cc).",".trim($a_rcp_bcc));
		foreach($rcp_ids as $id)
		{
			if($a_action == 'system')
			{
				$inbox_id = 0;
			}
			else
			{
				$mbox->setUserId($id);
				$inbox_id = $mbox->getInboxFolder();
			}
			$mail_id = $this->sendInternalMail($inbox_id,$this->user_id,
								  $a_attachments,$a_rcp_to,
								  $a_rcp_cc,'','unread',$a_type,
								  0,$a_subject,$a_message,$id);
			if($a_attachments)
			{
				$this->mfile->assignAttachmentsToDirectory($mail_id,$sent_mail_id,$a_attachments);
			}
		}		
		return true;
	}
	

	/**
	* get user_ids
	* @param    string recipients seperated by ','
	* @return	string error message
	*/
	function getUserIds($a_recipients)
	{
		require_once "classes/class.ilObjUser.php";
		require_once "classes/class.ilGroup.php";

		$tmp_names = $this->explodeRecipients($a_recipients);
		
		for($i = 0;$i < count($tmp_names); $i++)
		{
			if(substr($tmp_names[$i],0,1) == '#')
			{
				// GET GROUP MEMBER IDS
			}
			else if(!empty($tmp_names[$i]))
			{
				if($id = ilObjUser::getUserIdByLogin(addslashes($tmp_names[$i])))
				{
					$ids[] = $id;
				}
				else if($id = ilObjUser::getUserIdByEmail(addslashes($tmp_names[$i])))
				{
					$ids[] = $id;
				}
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
	* @return	string error message
	*/
	function checkMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_type)
	{
		$error_message = '';

		if(!is_array($a_type))
		{
			$error_message = $this->lng->txt("mail_add_type");
		}
		if(empty($a_m_subject))
		{
			$error_message .= $error_message ? "<br>" : '';
			$error_message .= $this->lng->txt("mail_add_subject");
		}
		if(empty($a_rcp_to))
		{
			$error_message .= $error_message ? "<br>" : '';
			$error_message .= $this->lng->txt("mail_add_recipient");
		}
		return $error_message;
	}

	/**
	* get email addresses of recipients
	* @access	public
	* @param    string string with login names or group names (start with #) or email address
	* @return	string seperated by ','
	*/
	function getEmailsOfRecipients($a_rcp)
	{
		$addresses = array();

		require_once "classes/class.ilObjUser.php";
		require_once "classes/class.ilGroup.php";

		$group = new ilGroup();

		$tmp_rcp = $this->explodeRecipients($a_rcp);

		foreach($tmp_rcp as $rcp)
		{
			// NO GROUP
			if(substr($rcp,0,1) != '#')
			{
				if(strpos($rcp,'@'))
				{
					$addresses[] = $rcp;
					continue;
				}
				if($id = ilObjUser::getUserIdByLogin(addslashes($rcp)))
				{
					$tmp_user = new ilObjUser($id);
					$addresses[] = $tmp_user->getEmail();
					continue;
				}
			}
			else
			{
				// GROUP THINGS
			}
		}
		return $addresses;
	}
		
	/**
	* check if recipients are valid
	* @access	public
	* @param    string string with login names or group names (start with #)
	* @return	bool
	*/
	function checkRecipients($a_recipients,$a_type)
	{
		$wrong_rcps = '';

		require_once "classes/class.ilObjUser.php";
		require_once "classes/class.ilGroup.php";
		
		$group = new ilGroup();

		$tmp_rcp = $this->explodeRecipients($a_recipients);

		foreach($tmp_rcp as $rcp)
		{
			if(empty($rcp))
			{
				continue;
			}
			// NO GROUP
			if(substr($rcp,0,1) != '#')
			{
				// ONLY SYSTEM AND/OR NORMAL SELECTED => NO RECIPIENTS WITH UNKNOWN EMAIL ADDRESSES
				if((in_array('normal',$a_type) or in_array('system',$a_type)) and !in_array('email',$a_type))
				{
					if(!ilObjUser::getUserIdByLogin(addslashes($rcp)) and
					   !ilObjUser::getUserIdByEmail(addslashes($rcp)))					
					{
						$wrong_rcps .= "<BR/>".$rcp;
						continue;
					}
				}
				// ONLY EMAIL SELECTED => ALL RECIPIENTS MUST HAVE A VALID EMAIL ADDRESS
				if(in_array('email',$a_type) and !in_array('normal',$a_type) and !in_array('system',$a_type))
				{
					if(!ilUtil::is_email($rcp) and
					   !ilObjUser::getUserIdByLogin(addslashes($rcp)))
					{
						$wrong_rcps .= "<BR/>".$rcp;
						continue;
					}
				}
				// ALL OTHER CASES => LOGIN OR EMAIL IS KNOWN OR EMAIL IS VALID
				if(!ilObjUser::getUserIdByLogin(addslashes($rcp)) and
				   !ilObjUser::getUserIdByEmail(addslashes($rcp)) and
				   !ilUtil::is_email($rcp))
				{
					$wrong_rcps .= "<BR/>".$rcp;
					continue;
				}
				
			}
			else
			{
				if(!$group->groupNameExists(addslashes(substr($rcp,1))))
					return false;
			}
		}
		return $wrong_rcps;
	}
	/**
	* save post data in table
	* @access	public
	* @param    int user_id
	* @param    array attachments
	* @param    string to
	* @param    string cc
	* @param    string bcc
	* @param    array type of mail (system,normal,email)
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
			"m_type = '".addslashes(serialize($a_m_type))."',".
			"m_email = '',".
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
	* send external mail using class.ilMimeMail.php
	* @param string to
	* @param string cc
	* @param string bcc
	* @param string subject
	* @param string message
	* @param array attachments
	* @param array type (normal and/or system and/or email)
	* @param integer also as email (0,1)
	* @access	public
	* @return	array of saved data
	*/
	function sendMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$a_type)
	{
		global $lng;
		$error_message = '';
		$message = '';

		if($a_attachment)
		{
			if(!$this->mfile->checkFilesExist($a_attachment))
			{
				return "YOUR LIST OF ATTACHMENTS IS NOT VALID, PLEASE EDIT THE LIST";
			}
		}
		// CHECK NECESSARY MAIL DATA FOR ALL TYPES
		if($error_message = $this->checkMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_type))
		{
			return $error_message;
		}
		// check recipients
		if($error_message = $this->checkRecipients($a_rcp_to,$a_type))
		{
			$message .= $error_message;
		}
		if($error_message = $this->checkRecipients($a_rcp_cc,$a_type))
		{
			$message .= $error_message;
		}
		if($error_message = $this->checkRecipients($a_rcp_bc,$a_type))
		{
			$message .= $error_message;
		}
		// if there was an error
		if(!empty($message))
		{
			return $this->lng->txt("mail_following_rcp_not_valid").$message;
		}

		// CHECK FOR SYSTEM MAIL
		if(in_array('system',$a_type))
		{
			if(!empty($a_attachment))
			{
				return $lng->txt("mail_no_attach_allowed");
			}
		}
		// CHECK FOR EMAIL
		if(in_array('email',$a_type))
		{
			if(!$this->getEmailOfSender())
			{
				return $lng->txt("mail_check_your_email_addr");
			}
		}

		// ACTIONS FOR ALL TYPES
		// save mail in sent box
		$sent_id = $this->saveInSentbox($a_attachment,$a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_type,
										$a_m_subject,$a_m_message);
		// ACTIONS FOR NORMAL
		// save attachments
		if(in_array('normal',$a_type))
		{
			if($error = $this->mfile->saveFiles($sent_id,$a_attachment))
			{
				return $error;
			}
		}
		// ACTIONS FOR TYPE SYSTEM AND NORMAL
		if(in_array('normal',$a_type))
		{
			if(!$this->distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$sent_id,$a_type,'normal'))
			{
				return $lng->txt("mail_send_error");
			}
		}
		if(in_array('system',$a_type))
		{
			if(!$this->distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$sent_id,$a_type,'system'))
			{
				return $lng->txt("mail_send_error");
			}
		}
		// ACTIONS FOR EMAIL
		if(in_array('email',$a_type))
		{
			$this->sendMimeMail($this->getEmailsOfRecipients($a_rcp_to),
								$this->getEmailsOfRecipients($a_rcp_cc),
								$this->getEmailsOfRecipients($a_rcp_bc),
								$a_m_subject,$a_m_message,$a_attachment);
		}
		return '';
	}


	/**
	* send mime mail using class.ilMimeMail.php
	* @param array attachments
	* @param string to
	* @param string cc
	* @param string bcc
	* @param string type
	* @param string subject
	* @param string message
	* @access	public
	* @return	int mail id
	*/
	function saveInSentbox($a_attachment,$a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_type,
						   $a_m_subject,$a_m_message)
	{
		require_once "classes/class.ilMailbox.php";

		$mbox = new ilMailbox($this->user_id);
		$sent_id = $mbox->getSentFolder();
		return $this->sendInternalMail($sent_id,$this->user_id,$a_attachment,$a_rcp_to,$a_rcp_cc,
										$a_rcp_bcc,'read',$a_type,$a_as_email,$a_m_subject,$a_m_message,$this->user_id);
	}

	/**
	* send mime mail using class.ilMimeMail.php
	* @param string to or array of recipients
	* @param string cc array of recipients
	* @param string bcc array of recipients
	* @param string subject
	* @param string message
	* @param array attachments
	* @access	public
	* @return	array of saved data
	*/
	function sendMimeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachments)
	{
		require_once "classes/class.ilMimeMail.php";

		$sender = $this->getEmailOfSender();

		$mmail = new ilMimeMail();
		$mmail->autoCheck(false);
		$mmail->From($sender);
		$mmail->To($a_rcp_to);
		// Add installation name to subject
		$a_m_subject = "[".$this->ilias->getSetting("inst_name")."] ".$a_m_subject;
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
		require_once "classes/class.ilObjUser.php";

		$umail = new ilObjUser($this->user_id);
		$sender = $umail->getEmail();
		if(ilUtil::is_email($sender))
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
	
	/**
	* explode recipient string
	* allowed seperators are ',' ';' ' '
	* @access	private
	* @return array array of recipients
	*/
	function explodeRecipients($a_recipients)
	{
		$a_recipients = trim($a_recipients);
		$a_recipients = preg_replace("/ /",",",$a_recipients);
		$a_recipients = preg_replace("/;/",",",$a_recipients);
		return explode(',',$a_recipients);
	}

			
} // END class.UserMail
?>
