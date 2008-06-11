<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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


/**
* Class Mail
* this class handles base functions for mail handling.
*
* RFC 822 compliant e-mail addresses
* ----------------------------------
* If ILIAS is configured to use standards compliant e-mail addresses, then
* this class supports RFC 822 compliant address lists as specified in
* http://www.ietf.org/rfc/rfc0822.txt
*
* Examples:
*   The following mailbox addresses work for sending an e-mail to the user with the
*   login john.doe and e-mail address jd@mail.com. The user is member of the course
*   "French Course". The member role of the course object has the name "il_crs_member_998"
*   and the object ID "1000".
*
*      john.doe
*      John Doe <john.doe>
*      john.doe@ilias
*      #member@[French Course]
*      #il_crs_member_998
*      #il_role_1000
*      jd@mail.com
*      John Doe <jd@mail.com>
*
* Syntax Rules:
*   The following excerpt from chapter 6.1 "Syntax" of RFC 822 is relevant for
*   the semantics described below:
*
*     addr-spec = local-part [ "@", domain ]
*
* Semantics:
*   User account mailbox address:
*   - The local-part denotes the login of an ILIAS user account.
*   - The domain denotes the current ILIAS client.
*   - The local-part must not start with a "#" character
*   - The domain must be omitted or must have the value "ilias"
*
*   Role object mailbox address:
*   - The local part denotes the title of an ILIAS role.
*   - The domain denotes the title of an ILIAS object.
*   - The local-part must start with a "#" character.
*   - If the domain is omitted, the title "ilias" is assumed.
*   - If the local-part starts with "#il_role_" its remaining characters
*     directly specify the object id of the role.
*     For example "#il_role_1234 identifies the role with object id "1234".
*   - If the object title identifies an object that is an ILIAS role, then
*     the local-part is ignored.
*   - If the object title identifies an object that is not an ILIAS role, then
*     the local-part is used to identify a local role for that object.
*   - The local-part can be a substring of the role name.
*     For example, "#member" can be used instead of "#il_crs_member_1234".
*
*   External E-Mail address:
*   - The local-part must not start with a "#" character
*   - The domain must be specified and it must not have the value "ilias"
*
*
* Non-compliant e-mail addresses
* ----------------------------------
* If ILIAS is not configured to use standards compliant e-mail addresses, then
* the following description applies:
*
* Examples:
*   The following mailbox addresses work for sending an e-mail to the user with the
*   login john.doe, who is member of the course "French Course". Assuming that
*   the member role of the course object has the name "il_crs_member_998"
*   and the object ID "1000".
*
*    john.doe
*    #il_crs_member_998
*    #il_role_1000
*    jd@mail.com
*
* Syntax:
*   The following syntax rule is relevant for the semantics described below:
* 
*     addr-spec = local-part [ "@", domain ]
*
* Semantics:
*   User account mailbox address:
*   - The local-part denotes the login of an ILIAS user account.
*   - The domain must be omitted.
*   - The local-part must not start with a "#" character
*
*   Role object mailbox address:
*   - The local part denotes the title of an ILIAS role.
*   - The local-part must start with a "#" character.
*   - The domain must be omitted.
*   - If the local-part start with "#il_role_" its remaining characters
*     directly specify the object id of the role.
*     For example "#il_role_1234 identifies the role with object id "1234".
*
*   External E-Mail address:
*   - The local-part must not start with a "#" character
*   - The domain must be specified and it must not have the value "ilias"
* 
*  
* @author	Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/

require_once "Services/User/classes/class.ilObjUser.php";

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

	var $mail_options;

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
	* Should sent messages be stored in sentbox of user
	* @var boolean
	* @access private
	*/
	var $save_in_sentbox;

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
	var $mail_use_placeholders = 0;

	var $soap_enabled = true;
	
	private $mlists = null;


	/**
	* Constructor
	* setup an mail object
	* @access	public
	* @param	integer	user_id
	*/
	function ilMail($a_user_id)
	{
		require_once "classes/class.ilFileDataMail.php";
		require_once "Services/Mail/classes/class.ilMailOptions.php";
		require_once "Services/Mail/classes/class.ilMailingLists.php";

		global $ilias, $lng, $ilUser;

		$lng->loadLanguageModule("mail");

		// Initiate variables
		$this->ilias =& $ilias;
		$this->lng   =& $lng;
		$this->table_mail = 'mail';
		$this->table_mail_saved = 'mail_saved';
		$this->user_id = $a_user_id;
		$this->mfile =& new ilFileDataMail($this->user_id);
		$this->mail_options =& new ilMailOptions($a_user_id);
		if(is_object($ilUser))
		{
			$this->mlists = new ilMailingLists($ilUser);
		}

		// DEFAULT: sent mail aren't stored insentbox of user.
		$this->setSaveInSentbox(false);

		// GET REFERENCE ID OF MAIL OBJECT
		$this->readMailObjectReferenceId();

	}
	
	public function doesRecipientStillExists($a_recipient, $a_existing_recipients)
	{
		if(self::_usePearMail())
		{
			$recipients = $this->explodeRecipients($a_existing_recipients);
			if(is_a($recipients, 'PEAR_Error'))
			{
				return false;				
			}
			else
			{
				foreach($recipients as $rcp)
				{
					if (substr($rcp->mailbox, 0, 1) != '#')
					{
						if(trim($rcp->mailbox) == trim($a_recipient) ||
						   trim($rcp->mailbox.'@'.$rcp->host) == trim($a_recipient))
						{
							return true;
						}
					}
					else if (substr($rcp->mailbox, 0, 7) == '#il_ml_')
					{
						if(trim($rcp->mailbox.'@'.$rcp->host) == trim($a_recipient))
						{
							return true;
						}
					}
					else
					{
						if(trim($rcp->mailbox.'@'.$rcp->host) == trim($a_recipient))
						{
							return true;
						}
					}					
				}
			}
		}
		else
		{		
			$recipients = $this->explodeRecipients($a_existing_recipients);			
			if(count($recipients))
			{
				foreach($recipients as $recipient)
				{
					if(trim($recipient) == trim($a_recipient))
					{
						return true;
					}
				}
			}
		}
		
		return false;
	}

	/**
	* Set option if external mails should be sent using soap client or not.
	* The autogenerated mails in new user registration sets this value to false, since 
	* there is no valid session 
	* @var array of send types ('system','normal','email')
	* @access	public
	*/
	function enableSOAP($a_status)
	{
		$this->soap_enabled = $a_status;
	}
	function isSOAPEnabled()
	{
		if(!extension_loaded('curl'))
		{
			return false;
		}
		return (bool) $this->soap_enabled;
	}


	function setSaveInSentbox($a_save_in_sentbox)
	{
		$this->save_in_sentbox = $a_save_in_sentbox;
	}

	function getSaveInSentbox()
	{
		return $this->save_in_sentbox;
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
		global $ilDB;
		
		// mail settings id is set by a constant in ilias.ini. Keep the select for some time until everyone has updated his ilias.ini
		if (!MAIL_SETTINGS_ID)
		{
			$query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data ".
					"WHERE tree.parent = ".$ilDB->quote(SYSTEM_FOLDER_ID)." ".
					"AND object_data.type = 'mail' ".
					"AND object_reference.ref_id = tree.child ".
					"AND object_reference.obj_id = object_data.obj_id";
			$res = $this->ilias->db->query($query);

			while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$this->mail_obj_ref_id = $row["ref_id"];
			}
		}
		else
		{
			$this->mail_obj_ref_id = MAIL_SETTINGS_ID;
		}
	}

	function getMailObjectReferenceId()
	{
		return $this->mail_obj_ref_id;
	}
	
	function formatNotificationSubject()
	{
		return $this->lng->txt('mail_notification_subject');
	}
		
	function formatNotificationMessage($user_id, $mail_data = array())
	{
		global $tpl, $lng;
		
		$tpl =& new ilTemplate('tpl.mail_notifications.html', true, true, 'Services/Mail');
		
		$tpl->setVariable('TXT_RECEIVED_MAILS', sprintf($this->lng->txt('mail_received_x_new_mails'), count($mail_data)));
				
		$counter = 0;
		foreach ($mail_data as $mail)
		{
			$tpl->setCurrentBlock('mails');
			$tpl->setVariable('NR', $counter + 1);			
			$tpl->setVariable('TXT_SENT', $this->lng->txt('sent'));
			$tpl->setVariable('SEND_TIME', ilFormat::formatDate($mail['send_time']));
			$tpl->setVariable('TXT_SUBJECT', $this->lng->txt('subject'));
			$tpl->setVariable('SUBJECT', $mail['m_subject']);		
			$tpl->parseCurrentBlock();
			
			++$counter;	
		}		
		
		$message = $this->replacePlaceholders($tpl->get(), $user_id);		
		
		return $message;
	}
	
	/**
 	 * Prepends the fullname of each ILIAS login name (is user has a public profile) found
 	 * in the passed string and brackets the ILIAS login name afterwards.
	 *
	 * @param	string	$users	String containing to, cc or bcc recipients
	 *
	 * @return	string	Formatted names
	 * 
	 * @access 	public
	 */
	public function formatNamesForOutput($users = '')
	{
		$users = trim($users);
		if($users)
		{
			if(strstr($users, ','))
			{
				$rcp_to_array = array();
				
				$recipients = explode(',', $users);
				foreach($recipients as $recipient)
				{
					$recipient = trim($recipient);
					if($uid = ilObjUser::_lookupId($recipient))
					{
						$tmp_obj = new ilObjUser($uid);

						if(ilObjUser::_lookupPref($uid, 'public_profile') == 'y')
						{								
							$rcp_to_array[] = $tmp_obj->getFullname().' ['.$recipient.']';
						}
						else
						{
							$rcp_to_array[] = $recipient;
						}
						unset($tmp_obj);		
					}
					else
					{
						$rcp_to_array[] = $recipient;
					}							
				}

				return trim(implode(', ', $rcp_to_array));
			}
			else
			{
				if($uid = ilObjUser::_lookupId($users))
				{
					$tmp_obj = new ilObjUser($uid);
					if(ilObjUser::_lookupPref($uid, 'public_profile') == 'y')
					{
						return $tmp_obj->getFullname().' ['.$users.']';
					}
					else
					{
						unset($tmp_obj);
						return $users;
					}					
				}
				else
				{
					return $users;
				}
			}
		}
		else
		{
			return $this->lng->txt('not_available');
		}
	}
	
	function getPreviousMail($a_mail_id)
	{
		global $ilDB;
		
		$query = "SELECT b.* FROM " . $this->table_mail ." AS a ".
				 "INNER JOIN ".$this->table_mail ." AS b ON b.folder_id = a.folder_id AND b.user_id = a.user_id AND b.send_time > a.send_time ".
				 "WHERE a.user_id = ".$ilDB->quote($this->user_id) ." ".				 
				 "AND a.mail_id = ".$ilDB->quote($a_mail_id)." ORDER BY b.send_time ASC LIMIT 1";
		
		$this->mail_data = $this->fetchMailData($this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT));
		
		return $this->mail_data; 
	}
	
	function getNextMail($a_mail_id)
	{
		global $ilDB;
		
		$query = "SELECT b.* FROM " . $this->table_mail ." AS a ".
				 "INNER JOIN ".$this->table_mail ." AS b ON b.folder_id = a.folder_id AND b.user_id = a.user_id AND b.send_time < a.send_time ".
				 "WHERE a.user_id = ".$ilDB->quote($this->user_id) ." ".				 
				 "AND a.mail_id = ".$ilDB->quote($a_mail_id)." ORDER BY b.send_time DESC LIMIT 1";
		
		$this->mail_data = $this->fetchMailData($this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT));
		
		return $this->mail_data;
	}

	/**
	* get all mails of a specific folder
	* @access	public
	* @param	integer id of folder
	* @return	array	mails
	*/
	function getMailsOfFolder($a_folder_id)
	{
		global $ilDB;
		
		$this->mail_counter = array();
		$this->mail_counter["read"] = 0;
		$this->mail_counter["unread"] = 0;

		$query = "SELECT * FROM $this->table_mail ".
			"WHERE user_id = ".$ilDB->quote($this->user_id) ." ".
			"AND folder_id = ".$ilDB->quote($a_folder_id)." ORDER BY send_time DESC";
		
		$res = $this->ilias->db->query($query);

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($row->sender_id and !ilObjectFactory::ObjectIdExists($row->sender_id))
			{
				continue;
			}
			$tmp = $this->fetchMailData($row);

			if ($tmp["m_status"] == 'read')
			{
				++$this->mail_counter["read"];
			}

			if ($tmp["m_status"] == 'unread')
			{
				++$this->mail_counter["unread"];
			}

			$output[] = $tmp;
		}

		$this->mail_counter["total"] = count($output);

		return $output ? $output : array();
	}
	
	/**
	* count all mails of a specific folder
	* @access	public
	* @param	integer id of folder
	* @return	bool	number of mails
	*/
	function countMailsOfFolder($a_folder_id)
	{
		global $ilDB;		

		$query = "SELECT COUNT(*) FROM $this->table_mail ".
			"WHERE user_id = ".$ilDB->quote($this->user_id) ." ".
			"AND folder_id = ".$ilDB->quote($a_folder_id)." ";
		
		if (is_object($res = $this->ilias->db->query($query)))
		{
			return $res->numRows();	
		}
		
		return 0;
	}
	
	/**
	* delete all mails of a specific folder
	* @access	public
	* @param	integer id of folder
	*
	*/
	function deleteMailsOfFolder($a_folder_id)
	{
		if ($a_folder_id)
		{		
			global $ilDB;
	
			$query = "DELETE FROM $this->table_mail ".
				"WHERE user_id = ".$ilDB->quote($this->user_id) ." ".
				"AND folder_id = ".$ilDB->quote($a_folder_id)." ";
			
			$res = $this->ilias->db->query($query);
			
			return true;
		}
		
		return false;
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
		global $ilDB;
		
		$query = "SELECT * FROM $this->table_mail ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
			"AND mail_id = ".$ilDB->quote($a_mail_id)." ";
		
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
		global $ilDB;
		// CREATE IN STATEMENT
		$in = "(". implode(",",ilUtil::quoteArray($a_mail_ids)) . ")";
		
		$query = "UPDATE $this->table_mail ".
			"SET m_status = 'read' ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
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
		global $ilDB;
		// CREATE IN STATEMENT
		$in = "(". implode(",",ilUtil::quoteArray($a_mail_ids)) . ")";
		
		$query = "UPDATE $this->table_mail ".
			"SET m_status = 'unread' ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
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
		global $ilDB;
		// CREATE IN STATEMENT
		$in = "(". implode(",",ilUtil::quoteArray($a_mail_ids)) . ")";

		$query = "UPDATE $this->table_mail ".
			"SET folder_id = ".$ilDB->quote($a_folder_id)." ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
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
		global $ilDB;
		
		foreach ($a_mail_ids as $id)
		{
			$query = "DELETE FROM $this->table_mail ".
				"WHERE user_id = ".$ilDB->quote($this->user_id)." ".
				"AND mail_id = ".$ilDB->quote($id)." ";
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
		if (!$a_row) return;
		
		return array(
			"mail_id"         => $a_row->mail_id,
			"user_id"         => $a_row->user_id,
			"folder_id"       => $a_row->folder_id,
			"sender_id"       => $a_row->sender_id,
			"attachments"     => unserialize(stripslashes($a_row->attachments)), 
			"send_time"       => $a_row->send_time,
			"rcp_to"          => $a_row->rcp_to,
			"rcp_cc"          => $a_row->rcp_cc,
			"rcp_bcc"         => $a_row->rcp_bcc,
			"m_status"        => $a_row->m_status,
			"m_type"          => unserialize(stripslashes($a_row->m_type)),
			"m_email"         => $a_row->m_email,
			"m_subject"       => $a_row->m_subject,
			"m_message"       => $a_row->m_message,			
			"import_name"	  => $a_row->import_name,
			"use_placeholders"=> $a_row->use_placeholders);
	}

	function updateDraft($a_folder_id,
						 $a_attachments,
						 $a_rcp_to,
						 $a_rcp_cc,
						 $a_rcp_bcc,
						 $a_m_type,
						 $a_m_email,
						 $a_m_subject,
						 $a_m_message,
						 $a_draft_id = 0, $a_use_placeholders = 0)
	{
		global $ilDB;
		
		$query = "UPDATE $this->table_mail ".
			"SET folder_id = ".$ilDB->quote($a_folder_id).",".
			"attachments = '".addslashes(serialize($a_attachments))."',".
			"send_time = now(),".
			"rcp_to = ".$ilDB->quote($a_rcp_to).",".
			"rcp_cc = ".$ilDB->quote($a_rcp_cc).",".
			"rcp_bcc = ".$ilDB->quote($a_rcp_bcc).",".
			"m_status = 'read',".
			"m_type = '".addslashes(serialize($a_m_type))."',".
			"m_email = ".$ilDB->quote($a_m_email).",".
			"m_subject = ".$ilDB->quote($a_m_subject).",".
			"m_message = ".$ilDB->quote($a_m_message).",".
			"use_placeholders = ".$ilDB->quote($a_use_placeholders)." ".
			"WHERE mail_id = ".$ilDB->quote($a_draft_id)."";
			
		$res = $this->ilias->db->query($query);

		return $a_draft_id;
	}

	/**
	* save mail in folder
	* @access	private
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
							  $a_user_id = 0, $a_use_placeholders = 0)
	{
		$a_user_id = $a_user_id ? $a_user_id : $this->user_id;

		global $ilDB, $log;
		//$log->write('class.ilMail->sendInternalMail to user_id:'.$a_rcp_to.' '.$a_m_message);

		if ($a_use_placeholders) $a_m_message = $this->replacePlaceholders($a_m_message, $a_user_id);

		$query = "INSERT INTO $this->table_mail ".
			"SET user_id = ".$ilDB->quote($a_user_id).",".
			"folder_id = ".$ilDB->quote($a_folder_id).",".
			"sender_id = ".$ilDB->quote($a_sender_id).",".
			"attachments = '".addslashes(serialize($a_attachments))."',".
			"send_time = now(),".
			"rcp_to = ".$ilDB->quote($a_rcp_to).",".
			"rcp_cc = ".$ilDB->quote($a_rcp_cc).",".
			"rcp_bcc = ".$ilDB->quote($a_rcp_bcc).",".
			"m_status = ".$ilDB->quote($a_status).",".
			"m_type = '".addslashes(serialize($a_m_type))."',".
			"m_email = ".$ilDB->quote($a_m_email).",".
			"m_subject = ".$ilDB->quote($a_m_subject).",".
			"m_message = ".$ilDB->quote($a_m_message)." ";

		$res = $this->ilias->db->query($query);
		$query = "SELECT LAST_INSERT_ID() as id";
		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_ASSOC);

		return $row["id"];
	}
	
	function replacePlaceholders($a_message, $a_user_id)
	{		
		global $lng;
		
		$user = new ilObjUser($a_user_id);		
		
		// determine salutation		
		switch ($user->getGender())
		{
			case 'f':	$gender_salut = $lng->txt('salutation_f');
						break;
			case 'm':	$gender_salut = $lng->txt('salutation_m');
						break;
        }

		$a_message = str_replace('[MAIL_SALUTATION]', $gender_salut, $a_message);
		$a_message = str_replace('[LOGIN]', $user->getLogin(), $a_message);
		$a_message = str_replace('[FIRST_NAME]', $user->getFirstname(), $a_message);
		$a_message = str_replace('[LAST_NAME]', $user->getLastname(), $a_message);
		$a_message = str_replace('[ILIAS_URL]', ILIAS_HTTP_PATH.'/login.php?client_id='.CLIENT_ID, $a_message);
		$a_message = str_replace('[CLIENT_NAME]', CLIENT_NAME, $a_message);
		
		unset($user);
	
		return $a_message;
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
	function distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_subject,$a_message,$a_attachments,$sent_mail_id,$a_type,$a_action, $a_use_placeholders = 0)
	{
		global $log;

		include_once 'Services/Mail/classes/class.ilMailbox.php';
		include_once './Services/User/classes/class.ilObjUser.php';

		if (!ilMail::_usePearMail())
		{
			// REPLACE ALL LOGIN NAMES WITH '@' BY ANOTHER CHARACTER
			$a_rcp_to = $this->__substituteRecipients($a_rcp_to, 'resubstitute');
			$a_rcp_cc = $this->__substituteRecipients($a_rcp_cc, 'resubstitute');
			$a_rcp_bc = $this->__substituteRecipients($a_rcp_bc, 'resubstitute');
		}

		$mbox =& new ilMailbox();
		
		if (!$a_use_placeholders) # No Placeholders
		{			
			$rcp_ids = $this->getUserIds(trim($a_rcp_to).','.trim($a_rcp_cc).','.trim($a_rcp_bcc));
			
			$as_email = array();
						
			foreach($rcp_ids as $id)
			{
				$tmp_mail_options =& new ilMailOptions($id);
	
				// DETERMINE IF THE USER CAN READ INTERNAL MAILS
				$tmp_user =& new ilObjUser($id);
				$tmp_user->read();
				$user_can_read_internal_mails = $tmp_user->hasAcceptedUserAgreement() && 
											    $tmp_user->getActive() && 
											    $tmp_user->checkTimeLimit();
	
				// CONTINUE IF SYSTEM MESSAGE AND USER CAN'T READ INTERNAL MAILS
				if (in_array('system', $a_type) && !$user_can_read_internal_mails)
				{
					continue;
				}
	
				// CONTINUE IF USER CAN'T READ INTERNAL MAILS OR IF HE/SHE WANTS HIS/HER MAIL
				// SENT TO HIS/HER EXTERNAL E-MAIL ADDRESS ONLY
				if (!$user_can_read_internal_mails ||
					$tmp_mail_options->getIncomingType() == $this->mail_options->EMAIL)
				{
					$as_email[] = $id;
					continue;
				}
	
				if ($tmp_mail_options->getIncomingType() == $this->mail_options->BOTH)
				{
					$as_email[] = $id;
				}
	
				$mbox->setUserId($id);
				$inbox_id = $mbox->getInboxFolder();
	
				$mail_id = $this->sendInternalMail($inbox_id, $this->user_id,
									  $a_attachments, $a_rcp_to,
									  $a_rcp_cc, '', 'unread', $a_type,
									  0, $a_subject, $a_message, $id, 0);
				if ($a_attachments)
				{
					$this->mfile->assignAttachmentsToDirectory($mail_id, $sent_mail_id, $a_attachments);
				}
			}
			
			// SEND EMAIL TO ALL USERS WHO DECIDED 'email' or 'both'
			$to = array();
			$bcc = array();
			
			if (count($as_email) == 1)
			{
				$to[] = ilObjUser::_lookupEmail($as_email[0]); 
			}
			else
			{
				foreach ($as_email as $id)
				{
					$bcc[] = ilObjUser::_lookupEmail($id);
				}
			}
			
			if(count($to) > 0 || count($bcc) > 0)
			{
				$this->sendMimeMail(implode(',', $to), '', implode(',', $bcc), $a_subject, $a_message, $a_attachments);
			}
		}
		else # Use Placeholders
		{
			// to
			$rcp_ids_replace = $this->getUserIds(trim($a_rcp_to));
			
			// cc / bcc
			$rcp_ids_no_replace = $this->getUserIds(trim($a_rcp_cc).','.trim($a_rcp_bcc));
			
			$as_email = array();			
			
			// to
			foreach($rcp_ids_replace as $id)
			{
				$tmp_mail_options =& new ilMailOptions($id);
	
				// DETERMINE IF THE USER CAN READ INTERNAL MAILS
				$tmp_user =& new ilObjUser($id);
				$tmp_user->read();
				$user_can_read_internal_mails = $tmp_user->hasAcceptedUserAgreement() &&
											    $tmp_user->getActive() &&
											    $tmp_user->checkTimeLimit();
	
				// CONTINUE IF SYSTEM MESSAGE AND USER CAN'T READ INTERNAL MAILS
				if (in_array('system', $a_type) && !$user_can_read_internal_mails)
				{
					continue;
				}
	
				// CONTINUE IF USER CAN'T READ INTERNAL MAILS OR IF HE/SHE WANTS HIS MAIL
				// SENT TO HIS/HER EXTERNAL E-MAIL ADDRESS ONLY
				if (!$user_can_read_internal_mails ||
					$tmp_mail_options->getIncomingType() == $this->mail_options->EMAIL)
				{
					$as_email[] = $id;
					continue;
				}
	
				if ($tmp_mail_options->getIncomingType() == $this->mail_options->BOTH)
				{
					$as_email[] = $id;
				}
	
				$mbox->setUserId($id);
				$inbox_id = $mbox->getInboxFolder();
	
				$mail_id = $this->sendInternalMail($inbox_id, $this->user_id,
									  $a_attachments, $a_rcp_to,
									  $a_rcp_cc, '', 'unread', $a_type,
									  0, $a_subject, $a_message, $id, 1);
				if ($a_attachments)
				{
					$this->mfile->assignAttachmentsToDirectory($mail_id, $sent_mail_id, $a_attachments);
				}
			}
			
			if (count($as_email))
			{
				foreach ($as_email as $id)
				{					
					$this->sendMimeMail(ilObjUser::_lookupEmail($id), '', '', $a_subject, $this->replacePlaceholders($a_message, $id), $a_attachments);
				}
			}
			
			$as_email = array();
			
			// cc / bcc
			foreach($rcp_ids_no_replace as $id)
			{
				$tmp_mail_options =& new ilMailOptions($id);
	
				// DETERMINE IF THE USER CAN READ INTERNAL MAILS
				$tmp_user =& new ilObjUser($id);
				$tmp_user->read();
				$user_can_read_internal_mails = $tmp_user->hasAcceptedUserAgreement() 
					&& $tmp_user->getActive() && $tmp_user->checkTimeLimit();
	
				// CONTINUE IF SYSTEM MESSAGE AND USER CAN'T READ INTERNAL MAILS
				if (in_array('system', $a_type) && !$user_can_read_internal_mails)
				{
					continue;
				}
	
				// CONTINUE IF USER CAN'T READ INTERNAL MAILS OR IF HE/SHE WANTS HIS MAIL
				// SENT TO HIS/HER EXTERNAL E-MAIL ADDRESS ONLY
				if (!$user_can_read_internal_mails ||
					$tmp_mail_options->getIncomingType() == $this->mail_options->EMAIL)
				{
					$as_email[] = $id;
					continue;
				}
	
				if ($tmp_mail_options->getIncomingType() == $this->mail_options->BOTH)
				{
					$as_email[] = $id;
				}
	
				$mbox->setUserId($id);
				$inbox_id = $mbox->getInboxFolder();
	
				$mail_id = $this->sendInternalMail($inbox_id, $this->user_id,
									  $a_attachments, $a_rcp_to,
									  $a_rcp_cc, '', 'unread', $a_type,
									  0, $a_subject, $a_message, $id, 0);
				if ($a_attachments)
				{
					$this->mfile->assignAttachmentsToDirectory($mail_id, $sent_mail_id, $a_attachments);
				}
			}
			
			if (count($as_email))
			{
				foreach ($as_email as $id)
				{					
					$this->sendMimeMail(ilObjUser::_lookupEmail($id), '', '', $a_subject, $a_message, $a_attachments);
				}
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
		global $log, $rbacreview;
		$ids = array();

		if (ilMail::_usePearMail())
		{
			$tmp_names = $this->explodeRecipients($a_recipients);
			if (! is_a($tmp_names, 'PEAR_Error'))
			{
				for ($i = 0;$i < count($tmp_names); $i++)
				{					
					if (substr($tmp_names[$i]->mailbox,0,1) === '#')
					{
						$role_ids = $rbacreview->searchRolesByMailboxAddressList($tmp_names[$i]->mailbox.'@'.$tmp_names[$i]->host);
						foreach($role_ids as $role_id)
						{
							foreach($rbacreview->assignedUsers($role_id) as $usr_id)
							{
								$ids[] = $usr_id;
							}
						}
					}
					else if (strtolower($tmp_names[$i]->host) == 'ilias')
					{
						if ($id = ilObjUser::getUserIdByLogin(addslashes($tmp_names[$i]->mailbox)))
						{
							//$log->write('class.ilMail->getUserIds() recipient:'.$tmp_names[$i]->mailbox.'@'.$tmp_names[$i]->host.' user_id:'.$id);
							$ids[] = $id;
						}
						else
						{
							//$log->write('class.ilMail->getUserIds() no user account found for recipient:'.$tmp_names[$i]->mailbox.'@'.$tmp_names[$i]->host);
						}
					}
					else
					{
						//$log->write('class.ilMail->getUserIds() external recipient:'.$tmp_names[$i]->mailbox.'@'.$tmp_names[$i]->host);
					}
				}
			}
			else
			{
				//$log->write('class.ilMail->getUserIds() illegal recipients:'.$a_recipients);
			}
		}
		else
		{
			$tmp_names = $this->explodeRecipients($a_recipients);
			for ($i = 0;$i < count($tmp_names); $i++)
			{
				if (substr($tmp_names[$i],0,1) == '#')
				{
					if(ilUtil::groupNameExists(addslashes(substr($tmp_names[$i],1))))
					{
						include_once("./classes/class.ilObjectFactory.php");
						include_once('./Modules/Group/classes/class.ilObjGroup.php');
						
						foreach(ilObject::_getAllReferences(ilObjGroup::_lookupIdByTitle(addslashes(substr($tmp_names[$i],1)))) as $ref_id)
						{
							$grp_object = ilObjectFactory::getInstanceByRefId($ref_id);
							break;
						}
						// STORE MEMBER IDS IN $ids
						foreach ($grp_object->getGroupMemberIds() as $id)
						{
							$ids[] = $id;
						}
					}
					// is role: get role ids
					elseif($role_id = $rbacreview->roleExists(addslashes(substr($tmp_names[$i],1))))
					{
						foreach($rbacreview->assignedUsers($role_id) as $usr_id)
						{
							$ids[] = $usr_id;
						}
					}
					
				}
				else if (!empty($tmp_names[$i]))
				{
					if ($id = ilObjUser::getUserIdByLogin(addslashes($tmp_names[$i])))
					{
						$ids[] = $id;
					}
				}
			}
		}
		return array_unique($ids);
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

		if (empty($a_m_subject))
		{
			$error_message .= $error_message ? "<br>" : '';
			$error_message .= $this->lng->txt("mail_add_subject");
		}

		if (empty($a_rcp_to))
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

		if (ilMail::_usePearMail())
		{
			$tmp_rcp = $this->explodeRecipients($a_rcp);
			if (! is_a($tmp_rcp, 'PEAR_Error'))
			{
				foreach ($tmp_rcp as $rcp)
				{
					// NO GROUP
					if (substr($rcp->mailbox,0,1) != '#')
					{
						if (strtolower($rcp->host) != 'ilias')
						{
							$addresses[] = $rcp->mailbox.'@'.$rcp->host;
							continue;
						}
		
						if ($id = ilObjUser::getUserIdByLogin(addslashes($rcp->mailbox)))
						{
							$tmp_user = new ilObjUser($id);
							$addresses[] = $tmp_user->getEmail();
							continue;
						}
					}
					else
					{
						// Roles
						$role_ids = $rbacreview->searchRolesByMailboxAddressList($rcp->mailbox.'@'.$rcp->host);
						foreach($role_ids as $role_id)
						{
							foreach($rbacreview->assignedUsers($role_id) as $usr_id)
							{
								$tmp_user = new ilObjUser($usr_id);
								$addresses[] = $tmp_user->getEmail();
							}
						}
					}
				}
			}
		}
		else
		{
			$tmp_rcp = $this->explodeRecipients($a_rcp);
	
			foreach ($tmp_rcp as $rcp)
			{
				// NO GROUP
				if (substr($rcp,0,1) != '#')
				{
					if (strpos($rcp,'@'))
					{
						$addresses[] = $rcp;
						continue;
					}
	
					if ($id = ilObjUser::getUserIdByLogin(addslashes($rcp)))
					{
						$tmp_user = new ilObjUser($id);
						$addresses[] = $tmp_user->getEmail();
						continue;
					}
				}
				else
				{
					// GROUP THINGS
					include_once("./classes/class.ilObjectFactory.php");
					include_once('./Modules/Group/classes/class.ilObjGroup.php');
	
					// Fix 
					foreach(ilObjGroup::_getAllReferences(ilObjGroup::_lookupIdByTitle(addslashes(substr($tmp_names[$i],1)))) as $ref_id)
					{
						$grp_object = ilObjectFactory::getInstanceByRefId($ref_id);
						break;
					}
					// GET EMAIL OF MEMBERS AND STORE THEM IN $addresses
					foreach ($grp_object->getGroupMemberIds() as $id)
					{
						$tmp_user = new ilObjUser($id);
						$addresses[] = $tmp_user->getEmail();
					} 
				}
			}
		}

		return $addresses;
	}
		
	/**
	* check if recipients are valid
	* @access	public
	* @param    string string with login names or group names (start with #)
	* @return   Returns an empty string, if all recipients are okay.
	*           Returns a string with invalid recipients, if some are not okay.
	*/
	function checkRecipients($a_recipients,$a_type)
	{
		global $rbacsystem,$rbacreview;
		$wrong_rcps = '';

		if (ilMail::_usePearMail())
		{
			$tmp_rcp = $this->explodeRecipients($a_recipients);
			if (is_a($tmp_rcp, 'PEAR_Error'))
			{
				$colon_pos = strpos($tmp_rcp->message, ':');
				$wrong_rcps = '<br />'.(($colon_pos === false) ? $tmp_rcp->message : substr($tmp_rcp->message, $colon_pos+2));
			}
			else
			{
				foreach ($tmp_rcp as $rcp)
				{
					// NO ROLE MAIL ADDRESS
					if (substr($rcp->mailbox,0,1) != '#')
					{
						// ALL RECIPIENTS MUST EITHER HAVE A VALID LOGIN OR A VALID EMAIL
						$user_id = ($rcp->host == 'ilias') ? ilObjUser::getUserIdByLogin(addslashes($rcp->mailbox)) : false;
						if ($user_id == false && $rcp->host == 'ilias')
						{
							$wrong_rcps .= "<br />".htmlentities($rcp->mailbox);
							continue;
						}
						
						// CHECK IF USER CAN RECEIVE MAIL
						if ($user_id)
						{
							if(!$rbacsystem->checkAccessOfUser($user_id, "mail_visible", $this->getMailObjectReferenceId()))
							{
								$wrong_rcps .= "<br />".htmlentities($rcp->mailbox).
									" (".$this->lng->txt("user_cant_receive_mail").")";
								continue;
							}
						}
					}
					else if (substr($rcp->mailbox, 0, 7) == '#il_ml_')
					{
						if (!$this->mlists->mailingListExists($rcp->mailbox))
						{					
							$wrong_rcps .= "<br />".htmlentities($rcp->mailbox).	
								" (".$this->lng->txt("mail_no_valid_mailing_list").")";
						}
						
						continue;
					}
					else
					{
						$role_ids = $rbacreview->searchRolesByMailboxAddressList($rcp->mailbox.'@'.$rcp->host);
						if (count($role_ids) == 0)
						{
							$wrong_rcps .= '<br />'.htmlentities($rcp->mailbox).
								' ('.$this->lng->txt('mail_no_recipient_found').')';
							continue;
						}
						else if (count($role_ids) > 1)
						{
							$wrong_rcps .= '<br/>'.htmlentities($rcp->mailbox).
								' ('.sprintf($this->lng->txt('mail_multiple_recipients_found'), implode(',', $role_ids)).')';
						}
					}
				}
			}
		}
		else
		{
			$tmp_rcp = $this->explodeRecipients($a_recipients);

			foreach ($tmp_rcp as $rcp)
			{
				if (empty($rcp))
				{
					continue;
				}
				// NO GROUP
				if (substr($rcp,0,1) != '#')
				{
					// ALL RECIPIENTS MUST EITHER HAVE A VALID LOGIN OR A VALID EMAIL
					if (!ilObjUser::getUserIdByLogin(addslashes($rcp)) and
						!ilUtil::is_email($rcp))
					{
						$wrong_rcps .= "<br />".htmlentities($rcp);
						continue;
					}

					// CHECK IF USER CAN RECEIVE MAIL
					if ($user_id = ilObjUser::getUserIdByLogin(addslashes($rcp)))
					{
						if(!$rbacsystem->checkAccessOfUser($user_id, "mail_visible", $this->getMailObjectReferenceId()))
						{
							$wrong_rcps .= "<br />".htmlentities($rcp).
								" (".$this->lng->txt("user_cant_receive_mail").")";
							continue;
						}
					}
				}
				else if (substr($rcp, 0, 7) == '#il_ml_')
				{
					if (!$this->mlists->mailingListExists($rcp))
					{					
						$wrong_rcps .= "<br />".htmlentities($rcp).	
							" (".$this->lng->txt("mail_no_valid_mailing_list").")";
					}
					
					continue;
				}
				else if (ilUtil::groupNameExists(addslashes(substr($rcp,1))))
				{
					continue;
				}
				else if (!$rbacreview->roleExists(addslashes(substr($rcp,1))))
				{
					$wrong_rcps .= "<br />".htmlentities($rcp).	
						" (".$this->lng->txt("mail_no_valid_group_role").")";
					continue;
				}				
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
	* @param    int use placeholders
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
						  $a_m_message,
						  $a_use_placeholders)
	{
		global $ilDB;
		
		$query = "DELETE FROM $this->table_mail_saved ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ";
		$res = $this->ilias->db->query($query);

		$query = "INSERT INTO $this->table_mail_saved ".
			"SET user_id = ".$ilDB->quote($a_user_id).",".
			"attachments = '".addslashes(serialize($a_attachments))."',".
			"rcp_to = ".$ilDB->quote($a_rcp_to).",".
			"rcp_cc = ".$ilDB->quote($a_rcp_cc).",".
			"rcp_bcc = ".$ilDB->quote($a_rcp_bcc).",".
			"m_type = '".addslashes(serialize($a_m_type))."',".
			"m_email = '',".
			"m_subject = ".$ilDB->quote($a_m_subject).",".
			"m_message = ".$ilDB->quote($a_m_message).",".
			"use_placeholders = ".$ilDB->quote($a_use_placeholders)."";
		$res = $this->ilias->db->query($query);
		
		$this->getSavedData();

		return true;
	}

	/**
	* get saved data 
	* @access	public
	* @return	array of saved data
	*/
	function getSavedData()
	{
		global $ilDB;
		
		$query = "SELECT * FROM $this->table_mail_saved ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ";

		$this->mail_data = $this->fetchMailData($this->ilias->db->getRow($query, DB_FETCHMODE_OBJECT));

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
	function sendMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$a_type, $a_use_placeholders = 0)
	{
		global $lng,$rbacsystem,$log;
		//$log->write('class.ilMail.sendMail '.$a_rcp_to.' '.$a_m_subject);
		$error_message = '';
		$message = '';

		if (in_array("system",$a_type))
		{
			$this->__checkSystemRecipients($a_rcp_to);
			$a_type = array('system');
		}

		if ($a_attachment)
		{
			if (!$this->mfile->checkFilesExist($a_attachment))
			{
				return "YOUR LIST OF ATTACHMENTS IS NOT VALID, PLEASE EDIT THE LIST";
			}
		}
		// CHECK NECESSARY MAIL DATA FOR ALL TYPES
		if ($error_message = $this->checkMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_type))
		{
			return $error_message;
		}
		// check recipients
		if ($error_message = $this->checkRecipients($a_rcp_to,$a_type))
		{
			$message .= $error_message;
		}

		if ($error_message = $this->checkRecipients($a_rcp_cc,$a_type))
		{
			$message .= $error_message;
		}

		if ($error_message = $this->checkRecipients($a_rcp_bc,$a_type))
		{
			$message .= $error_message;
		}
		// if there was an error
		if (!empty($message))
		{
			return $this->lng->txt("mail_following_rcp_not_valid").$message;
		}

		// CHECK FOR SYSTEM MAIL
		if (in_array('system',$a_type))
		{
			if (!empty($a_attachment))
			{
				return $lng->txt("mail_no_attach_allowed");
			}
		}
		
		// ACTIONS FOR ALL TYPES
		// save mail in sent box
		$sent_id = $this->saveInSentbox($a_attachment,$a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_type,
										$a_m_subject,$a_m_message);
		
		// GET RCPT OF MAILING LISTS
		$a_rcp_to = $this->parseRcptOfMailingLists($a_rcp_to);
		$a_rcp_cc = $this->parseRcptOfMailingLists($a_rcp_cc);
		$a_rcp_bc = $this->parseRcptOfMailingLists($a_rcp_bc);

		if (! ilMail::_usePearMail())
		{
			// REPLACE ALL LOGIN NAMES WITH '@' BY ANOTHER CHARACTER
			$a_rcp_to = $this->__substituteRecipients($a_rcp_to,"substitute");
			$a_rcp_cc = $this->__substituteRecipients($a_rcp_cc,"substitute");
			$a_rcp_bc = $this->__substituteRecipients($a_rcp_bc,"substitute");
		}

		// COUNT EMAILS
		$c_emails = $this->__getCountRecipients($a_rcp_to,$a_rcp_cc,$a_rcp_bc,true);
		$c_rcp = $this->__getCountRecipients($a_rcp_to,$a_rcp_cc,$a_rcp_bc,false);

		// currently disabled..
		/*
		if (count($c_emails))
		{
			if (!$this->getEmailOfSender())
			{
				return $lng->txt("mail_check_your_email_addr");
			}
		}
		*/
				
		if ($a_attachment)
		{
			$this->mfile->assignAttachmentsToDirectory($sent_id,$sent_id);

			if ($error = $this->mfile->saveFiles($sent_id,$a_attachment))
			{
				return $error;
			}
		}

		// FILTER EMAILS
		// IF EMAIL RECIPIENT
		if ($c_emails)
		{
			$this->sendMimeMail($this->__getEmailRecipients($a_rcp_to),
								$this->__getEmailRecipients($a_rcp_cc),
								$this->__getEmailRecipients($a_rcp_bc),
								$a_m_subject,
								$a_m_message,
								$a_attachment,
								0);
		}

		if (in_array('system',$a_type))
		{
			if (!$this->distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$sent_id,$a_type,'system', $a_use_placeholders))
			{
				return $lng->txt("mail_send_error");
			}
		}
		// ACTIONS FOR TYPE SYSTEM AND NORMAL
		if (in_array('normal',$a_type))
		{
			// TRY BOTH internal and email (depends on user settings)
			if (!$this->distributeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$sent_id,$a_type,'normal', $a_use_placeholders))
			{
				return $lng->txt("mail_send_error");
			}
		}

		// Temporary bugfix
		if (!$this->getSaveInSentbox())
		{
			$this->deleteMails(array($sent_id));
		}

		return '';
	}
	
	function parseRcptOfMailingLists($rcpt = '')
	{
		if ($rcpt == '') return $rcpt;
		
		$arrRcpt = $this->explodeRecipients(trim($rcpt));
		if (!is_array($arrRcpt) || empty($arrRcpt)) return $rcpt;
		
		$new_rcpt = array();
		
		foreach ($arrRcpt as $item)
		{
			if (ilMail::_usePearMail())
			{
				if (substr($item->mailbox, 0, 7) == '#il_ml_')
				{
					if ($this->mlists->mailingListExists($item->mailbox))
					{
						foreach ($this->mlists->getCurrentMailingList()->getAssignedEntries() as $entry)
						{
							$new_rcpt[] = ($entry['login'] != '' ? $entry['login'] : $entry['email']);
						}
					}
				}
				else
				{
					$new_rcpt[] = $item->mailbox.'@'.$item->host;
				}
			}
			else
			{
				if (substr($item, 0, 7) == '#il_ml_')
				{
					if ($this->mlists->mailingListExists($item))
					{
						foreach ($this->mlists->getCurrentMailingList()->getAssignedEntries() as $entry)
						{
							$new_rcpt[] = ($entry['login'] != '' ? $entry['login'] : $entry['email']);
						}
					}	
				}
				else
				{
					$new_rcpt[] = $item;
				}
			}		
		}		
		
		return implode(',', $new_rcpt);
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
		include_once "Services/Mail/classes/class.ilMailbox.php";

		$mbox = new ilMailbox($this->user_id);
		$sent_id = $mbox->getSentFolder();

		return $this->sendInternalMail($sent_id,$this->user_id,$a_attachment,$a_rcp_to,$a_rcp_cc,
										$a_rcp_bcc,'read',$a_type,$a_as_email,$a_m_subject,$a_m_message,$this->user_id, 0);
	}


	
	/**
	* add user fullname to mail 'From'
	* @param string email of sender
	* @return string 'From' field
	*/
	function addFullname($a_email)
	{
		include_once 'Services/Mail/classes/class.ilMimeMail.php';
		
		global $ilUser;

		return ilMimeMail::_mimeEncode($ilUser->getFullname()).'<'.$a_email.'>';
	}

	/**
	* send mime mail using class.ilMimeMail.php
	* All external mails are send to SOAP::sendMail starting a kind of background process
	* @param string of recipients
	* @param string of recipients
	* @param string of recipients
	* @param string subject
	* @param string message
	* @param array attachments
	* @access	public
	* @return	array of saved data
	*/
	function sendMimeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachments)
	{
		#var_dump("<pre>",$a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachments,"<pre>");
		
		$inst_name = $this->ilias->getSetting("inst_name") ? $this->ilias->getSetting("inst_name") : "ILIAS 3";
		$a_m_subject = "[".$inst_name."] ".$a_m_subject;

		if($this->isSOAPEnabled())
		{
			// Send per soap
			include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';

			$soap_client = new ilSoapClient();
			$soap_client->setTimeout(1);
			$soap_client->setResponseTimeout(1);
			$soap_client->enableWSDL(true);
			$soap_client->init();

			$attachments = array();
			$a_attachments = $a_attachments ? $a_attachments : array();
			foreach($a_attachments as $attachment)
			{
				$attachments[] = $this->mfile->getAbsolutePath($attachment);
			}
			$attachments = implode(',',$attachments);

			$soap_client->call('sendMail',array($_COOKIE['PHPSESSID'].'::'.$_COOKIE['ilClientId'],	// session id
												$a_rcp_to,
												$a_rcp_cc,
												$a_rcp_bcc,
												$this->addFullname($this->getEmailOfSender()),
												$a_m_subject,
												$a_m_message,
												$attachments));
		
			return true;
		}
		else
		{
			// send direct
			include_once "Services/Mail/classes/class.ilMimeMail.php";

			$sender = $this->addFullname($this->getEmailOfSender());

			$mmail = new ilMimeMail();
			$mmail->autoCheck(false);
			$mmail->From($sender);
			$mmail->To($a_rcp_to);
			// Add installation name to subject
			$mmail->Subject($a_m_subject);
			$mmail->Body($a_m_message);

			if ($a_rcp_cc)
			{
				$mmail->Cc($a_rcp_cc);
			}

			if ($a_rcp_bcc)
			{
				$mmail->Bcc($a_rcp_bcc);
			}

			if (is_array($a_attachments))
			{	
				foreach ($a_attachments as $attachment)
				{
					$mmail->Attach($this->mfile->getAbsolutePath($attachment));
				}
			}

			$mmail->Send();
		}
	}
	/**
	* get email of sender
	* @access	public
	* @return	string email
	*/
	function getEmailOfSender()
	{
		$umail = new ilObjUser($this->user_id);
		$sender = $umail->getEmail();

		if (ilUtil::is_email($sender))
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
		global $ilDB;
		
		$query = "UPDATE $this->table_mail_saved ".
			"SET attachments = '".addslashes(serialize($a_attachments))."' ".
			"WHERE user_id = ".$ilDB->quote($this->user_id)." ";

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
	*
	* Returns an array with recipient objects 
	*
	* @access	private
	*
	* @return array with recipient objects. array[i]->mailbox gets the mailbox
	* of the recipient. array[i]->host gets the host of the recipients. Returns
	* a PEAR_Error object, if exploding failed. Use is_a() to test, if the return
	* value is a PEAR_Error, then use $rcp->message to retrieve the error message.
	*/
	function explodeRecipients($a_recipients)
	{
		if (ilMail::_usePearMail())
		{
			if (strlen(trim($a_recipients)) > 0)
			{
				require_once 'Mail/RFC822.php';
				$parser = &new Mail_RFC822();
				return $parser->parseAddressList($a_recipients, "ilias", false, true);
			} else {
				return array();
			}
		}
		else
		{
			$a_recipients = trim($a_recipients);
	
			// WHITESPACE IS NOT ALLOWED AS SEPERATOR
			#$a_recipients = preg_replace("/ /",",",$a_recipients);
			$a_recipients = preg_replace("/;/",",",$a_recipients);
			
	
			foreach(explode(',',$a_recipients) as $tmp_rec)
			{
				if($tmp_rec)
				{
					$rcps[] = trim($tmp_rec);
				}
			}
			return is_array($rcps) ? $rcps : array();
		}
	}

	function __getCountRecipient($rcp,$a_only_email = true)
	{
		$counter = 0;

		if (ilMail::_usePearMail())
		{
			$tmp_rcp = $this->explodeRecipients($rcp);
			if (! is_a($tmp_rcp, 'PEAR_Error'))
			{
				foreach ($tmp_rcp as $to)
				{
					if ($a_only_email)
					{
						// Addresses which aren't on the ilias host, and 
						// which have a mailbox which does not start with '#',
						// are external e-mail addresses
						if ($to->host != 'ilias' && substr($to->mailbox,0,1) != '#')
						{
							++$counter;
						}
					}
					else
					{
						++$counter;
					}
				}
			}
		}
		else
		{
			foreach ($this->explodeRecipients($rcp) as $to)
			{
				if ($a_only_email)
				{
					if (strpos($to,'@'))
					{
						++$counter;
					}
				}
				else
				{
					++$counter;
				}
			}
		}
		return $counter;
	}
			

	function __getCountRecipients($a_to,$a_cc,$a_bcc,$a_only_email = true)
	{
		return $this->__getCountRecipient($a_to,$a_only_email) 
			+ $this->__getCountRecipient($a_cc,$a_only_email) 
			+ $this->__getCountRecipient($a_bcc,$a_only_email);
	}

	function __getEmailRecipients($a_rcp)
	{
		if (ilMail::_usePearMail())
		{
			$rcp = array();
			$tmp_rcp = $this->explodeRecipients($a_rcp);
			if (! is_a($tmp_rcp, 'PEAR_Error'))
			{
				foreach ($tmp_rcp as $to)
				{
					if(substr($to->mailbox,0,1) != '#' && $to->host != 'ilias')
					{
						$rcp[] = $to->mailbox.'@'.$to->host;
					}
				}
			}
			return implode(',',$rcp);
		}
		else
		{
			foreach ($this->explodeRecipients($a_rcp) as $to)
			{
				if(strpos($to,'@'))
				{
					$rcp[] = $to;
				}
			}
			return implode(',',$rcp ? $rcp : array());
		}
	}

	function __prependMessage($a_m_message,$rcp_to,$rcp_cc)
	{
		$inst_name = $this->ilias->getSetting("inst_name") ? $this->ilias->getSetting("inst_name") : "ILIAS 3";

		$message = $inst_name." To:".$rcp_to."\n";

		if ($rcp_cc)
		{
			$message .= "Cc: ".$rcp_cc;
		}

		$message .= "\n\n";
		$message .= $a_m_message;

		return $message;
	}

	function __checkSystemRecipients(&$a_rcp_to)
	{
		if (preg_match("/@all/",$a_rcp_to))
		{
			// GET ALL LOGINS
			$all = ilObjUser::_getAllUserLogins($this->ilias);
			$a_rcp_to = preg_replace("/@all/",implode(',',$all),$a_rcp_to);
		}

		return;
	}

	/**
	 * Note: This function can only be used, when ILIAS is configured to not
	 *       use standards compliant mail addresses. 
	 *       If standards compliant mail addresses are used, substitution is
	 *       not supported, because then we do the parsing of mail addresses
	 *       using the Pear Mail Extension. 
	 */
	function __substituteRecipients($a_rcp,$direction)
	{
		$new_name = array();

		$tmp_names = $this->explodeRecipients($a_rcp);


		foreach($tmp_names as $name)
		{
			if(strpos($name,"#") === 0)
			{
				$new_name[] = $name;
				continue;
			}
			switch($direction)
			{
				case "substitute":
					if(strpos($name,"@") and ilObjUser::_loginExists($name))
					{
						$new_name[] = preg_replace("/@/","�#�",$name);
					}
					else
					{
						$new_name[] = $name;
					}
					break;
					
				case "resubstitute":
					if(stristr($name,"�#�"))
					{
						$new_name[] = preg_replace("/�#�/","@",$name);
					}
					else
					{
						$new_name[] = $name;
					}
					break;
			}
		}
		return implode(",",$new_name);
	}

	/**
	 * STATIC METHOD.
	 * Returns the internal mailbox address for the specified user.
     *
     * This functions (may) perform faster, if the login, firstname and lastname 
     * are supplied as parameters aloing with the $usr_id.
     *
     *
     * @param usr_id the usr_id of the user
     * @param login optional, but if you supply it, you have to supply
	 *                 the firstname and the lastname as well
     * @param firstname optional
     * @param lastname 
	 * @access	public
	 */
	public static function _getUserInternalMailboxAddress($usr_id, $login=null, $firstname=null, $lastname=null) {
		if (ilMail::_usePearMail())
		{
			if ($login == null)
			{
				require_once './Services/User/classes/class.ilObjUser.php';
				$usr_obj = new ilObjUser($usr_id);
				$usr_obj->read();
				$login = $usr_obj->getLogin();
				$firstname = $usr_obj->getFirstname();
				$lastname = $usr_obj->getLastname();
			}
			// The following line of code creates a properly formatted mailbox
			// address. Unfortunately, it does not work, because ILIAS removes
			// everything between '<' '>' characters
			// Therefore, we just return the login - sic.
			// FIXME - Make this work in a future release 
			/*
			return preg_replace('/[()<>@,;:\\".\[\]]/','',$firstname.' '.$lastname).' <'.$login.'>';
			*/
			return $login.'hhho';
		}
		else
		{
			return $login;
		}
	}
	/**
	 * STATIC METHOD.
	 * Returns true, if Pear Mail shall be used for resolving mail addresses.
	 *
	 * @access	public
	 */
	public static function _usePearMail() {
		global $ilias;

		$result = false;
		if ($ilias->getSetting('pear_mail_enable') == true)
		{
			// Note: We use the include statement to determine whether PEAR MAIL is
			//      installed. We use the @ operator to prevent PHP from issuing a
			//      warning while we test for PEAR MAIL.
			$is_pear_mail_installed = @include_once 'Mail/RFC822.php';
			if ($is_pear_mail_installed) {
				$result = true;
			} else {
				// Disable Pear Mail, when we detect that it is not
				// installed
				global $log;
				$log->write("WARNING: ilMail::_userPearMail disabled Pear Mail support, because include 'Mail/RFC822.php' failed.");
				$ilias->setSetting('pear_mail_enable', false);
			}
		}
		return $result;
	}
	
	/**
	 * get auto generated info string 
	 *
	 * @access public
	 * @static
	 *
	 * @param string language
	 */
	public static function _getAutoGeneratedMessageString($lang = null)
	{
		global $ilSetting;
		
		if(!$lang)
		{
			include_once('./Services/Language/classes/class.ilLanguageFactory.php');
			$lang = ilLanguageFactory::_getLanguage();
		}
		$lang->loadLanguageModule('mail');
		return sprintf($lang->txt('mail_auto_generated_info'),
			$ilSetting->get('inst_name','ILIAS 3'),
			ILIAS_HTTP_PATH."\n\n");
	}
} // END class.ilMail
?>
