<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilObjUser.php';

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
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
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
	var $mail_to_global_roles = 0;

	private $use_pear = true;
	protected $appendInstallationSignature = false;
	
	/**
	 * 
	 * Used to store properties which should be set/get at runtime
	 *
	 * @var	array
	 * @access	protected
	 *
	 */
	protected $properties = array();
	
	/**
	 * @var array<ilObjUser>
	 */
	protected static $userInstances = array();

	/**
	* Constructor
	* setup an mail object
	* @access	public
	* @param	integer	user_id
	*/
	public function __construct($a_user_id)
	{
		require_once "./Services/Mail/classes/class.ilFileDataMail.php";
		require_once "Services/Mail/classes/class.ilMailOptions.php";

		global $ilias, $lng, $ilUser;

		$lng->loadLanguageModule("mail");

		// Initiate variables
		$this->ilias =& $ilias;
		$this->lng   =& $lng;
		$this->table_mail = 'mail';
		$this->table_mail_saved = 'mail_saved';
		$this->user_id = $a_user_id;
		$this->mfile = new ilFileDataMail($this->user_id);
		$this->mail_options = new ilMailOptions($a_user_id);		

		// DEFAULT: sent mail aren't stored insentbox of user.
		$this->setSaveInSentbox(false);

		// GET REFERENCE ID OF MAIL OBJECT
		$this->readMailObjectReferenceId();
	}
	
	/**
	 * 
	 * Magic interceptor method __get
	 * Used to include files / instantiate objects at runtime
	 * 
	 * @param	string	The name of the class property
	 */
	public function __get($name)
	{
		global $ilUser;
		
		if(isset($this->properties[$name]))
		{
			return $this->properties[$name];
		}
		
		// Used to include files / instantiate objects at runtime
		if($name == 'mlists')
		{
			if(is_object($ilUser))
			{
				require_once 'Services/Contact/classes/class.ilMailingLists.php';
				$this->properties[$name] = new ilMailingLists($ilUser);
				return $this->properties[$name];
			}
		}
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
		global $ilSetting;

		if(!extension_loaded('curl') || !$ilSetting->get('soap_user_administration'))
		{
			return false;
		}
		
		// Prevent using SOAP in cron context
		if(ilContext::getType() == ilContext::CONTEXT_CRON)
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
		include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
		$this->mail_obj_ref_id = ilMailGlobalServices::getMailObjectRefId();
	}

	function getMailObjectReferenceId()
	{
		return $this->mail_obj_ref_id;
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
						if (in_array(ilObjUser::_lookupPref($uid, 'public_profile'), array("y", "g")))
						{
							$tmp_obj = self::getCachedUserInstance($uid);
							$rcp_to_array[] = $tmp_obj->getFullname().' ['.$recipient.']';
						}
						else
						{
							$rcp_to_array[] = $recipient;
						}
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
					if (in_array(ilObjUser::_lookupPref($uid, 'public_profile'), array("y", "g")))
					{
						$tmp_obj = self::getCachedUserInstance($uid);
						return $tmp_obj->getFullname().' ['.$users.']';
					}
					else
					{
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

		$ilDB->setLimit(1);
		$res = $ilDB->queryf("
			SELECT b.* FROM " . $this->table_mail ." a
			INNER JOIN ".$this->table_mail ." b ON b.folder_id = a.folder_id
			AND b.user_id = a.user_id AND b.send_time > a.send_time
			WHERE a.user_id = %s
			AND a.mail_id = %s ORDER BY b.send_time ASC",
			array('integer', 'integer'),
			array($this->user_id, $a_mail_id));

		$this->mail_data = $this->fetchMailData($res->fetchRow(DB_FETCHMODE_OBJECT));

		return $this->mail_data;
	}

	function getNextMail($a_mail_id)
	{
		global $ilDB;

		$ilDB->setLimit(1);
		$res = $ilDB->queryf("
			SELECT b.* FROM " . $this->table_mail ." a
			INNER JOIN ".$this->table_mail ." b ON b.folder_id = a.folder_id
			AND b.user_id = a.user_id AND b.send_time < a.send_time
			WHERE a.user_id = %s
			AND a.mail_id = %s ORDER BY b.send_time DESC",
			array('integer', 'integer'),
			array($this->user_id, $a_mail_id));

		$this->mail_data = $this->fetchMailData($res->fetchRow(DB_FETCHMODE_OBJECT));

		return $this->mail_data;
	}

	/**
	* get all mails of a specific folder
	* @access	public
	* @param	integer id of folder
    * @param    array   optional filter array
	* @return	array	mails
	*/
	function getMailsOfFolder($a_folder_id, $filter = array())
	{
		global $ilDB;

		$this->mail_counter = array();
		$this->mail_counter['read'] = 0;
		$this->mail_counter['unread'] = 0;

        $query = "SELECT sender_id, m_subject, mail_id, m_status, send_time FROM ". $this->table_mail ."
			LEFT JOIN object_data ON obj_id = sender_id
			WHERE user_id = %s
			AND folder_id = %s
			AND ((sender_id > 0 AND sender_id IS NOT NULL AND obj_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) ";

        if($filter['status'])
        {
            $query .= ' AND m_status = '.$ilDB->quote($filter['status'], 'text');
        }
        if($filter['type'])
        {
            $query .= ' AND '.$ilDB->like('m_type', 'text', '%%:"'.$filter['type'].'"%%', false);
        }

        $query .= " ORDER BY send_time DESC";

		$res = $ilDB->queryf($query,
			array('integer', 'integer'),
			array($this->user_id, $a_folder_id));

		while ($row = $ilDB->fetchObject($res))
		{
			$tmp = $this->fetchMailData($row);

			if($tmp['m_status'] == 'read')
			{
				++$this->mail_counter['read'];
			}

			if($tmp['m_status'] == 'unread')
			{
				++$this->mail_counter['unread'];
			}

			$output[] = $tmp;
		}

		$this->mail_counter['total'] = count($output);

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

		$res = $ilDB->queryf("
			SELECT COUNT(*) FROM ". $this->table_mail ."
			WHERE user_id = %s
			AND folder_id = %s",
			array('integer', 'integer'),
			array($this->user_id, $a_folder_id));

		return $res->numRows();
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

			/*$statement = $ilDB->manipulateF("
				DELETE FROM ". $this->table_mail ."
				WHERE user_id = %s
				AND folder_id = %s",
				array('integer', 'integer'),
				array($this->user_id, $a_folder_id));*/
			$mails = $this->getMailsOfFolder($a_folder_id);
			foreach((array)$mails as $mail_data)
			{
				$this->deleteMails(array($mail_data['mail_id']));
			}

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

		$res = $ilDB->queryf("
			SELECT * FROM ". $this->table_mail ."
			WHERE user_id = %s
			AND mail_id = %s",
			array('integer', 'integer'),
			array($this->user_id, $a_mail_id));

		$this->mail_data =$this->fetchMailData($res->fetchRow(DB_FETCHMODE_OBJECT));

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

		$data = array();
		$data_types = array();

		$query = "UPDATE ". $this->table_mail ."
				SET m_status = %s
				WHERE user_id = %s ";
		array_push($data_types, 'text', 'integer');
		array_push($data, 'read', $this->user_id);

		$cnt_mail_ids = count($a_mail_ids);

			if (is_array($a_mail_ids) &&
			count($a_mail_ids) > 0)
		{

			$in = 'mail_id IN (';
			$counter = 0;
			foreach($a_mail_ids as $a_mail_id)
			{
				array_push($data, $a_mail_id);
				array_push($data_types, 'integer');

				if($counter > 0) $in .= ',';
				$in .= '%s';
				++$counter;
			}
			$in .= ')';

			$query .= ' AND '.$in;
		}

		$res = $ilDB->manipulateF($query, $data_types, $data);

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

		$data = array();
		$data_types = array();

		$query = "UPDATE ". $this->table_mail ."
				SET m_status = %s
				WHERE user_id = %s ";
		array_push($data_types, 'text', 'integer');
		array_push($data, 'unread', $this->user_id);

		$cnt_mail_ids = count($a_mail_ids);

			if (is_array($a_mail_ids) &&
			count($a_mail_ids) > 0)
		{

			$in = 'mail_id IN (';
			$counter = 0;
			foreach($a_mail_ids as $a_mail_id)
			{
				array_push($data, $a_mail_id);
				array_push($data_types, 'integer');

				if($counter > 0) $in .= ',';
				$in .= '%s';
				++$counter;
			}
			$in .= ')';

			$query .= ' AND '.$in;
		}

		$statement = $ilDB->manipulateF($query, $data_types, $data);

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

		$data = array();
		$data_types = array();

		$query = "UPDATE ". $this->table_mail ."
				SET folder_id = %s
				WHERE user_id = %s ";
		array_push($data_types, 'text', 'integer');
		array_push($data, $a_folder_id, $this->user_id);

		$cnt_mail_ids = count($a_mail_ids);

			if (is_array($a_mail_ids) &&
			count($a_mail_ids) > 0)
		{

			$in = 'mail_id IN (';
			$counter = 0;
			foreach($a_mail_ids as $a_mail_id)
			{
				array_push($data, $a_mail_id);
				array_push($data_types, 'integer');

				if($counter > 0) $in .= ',';
				$in .= '%s';
				++$counter;
			}
			$in .= ')';

			$query .= ' AND '.$in;
		}

		$statement = $ilDB->manipulateF($query, $data_types, $data);

		return true;
	}

	/**
	 * Delete mails
	 * @param array mail ids
	 * @return bool
	 */
	public function deleteMails(array $a_mail_ids)
	{
		global $ilDB;

		foreach($a_mail_ids as $id)
		{
			$ilDB->manipulateF("
				DELETE FROM ". $this->table_mail ."
				WHERE user_id = %s
				AND mail_id = %s ",
				array('integer', 'integer'),
				array($this->user_id, $id)
			);
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
			"use_placeholders"=> $a_row->use_placeholders,
			"tpl_ctx_id"      => $a_row->tpl_ctx_id,
			"tpl_ctx_params"  => (array)(@json_decode($a_row->tpl_ctx_params, true))
		);
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
						 $a_draft_id = 0,
						 $a_use_placeholders = 0,
						 $a_tpl_context_id = null,
						 $a_tpl_context_params = array()
	)
	{
		global $ilDB;

		$ilDB->update($this->table_mail,
			array(
				'folder_id'			=> array('integer', $a_folder_id),
				'attachments'		=> array('clob', serialize($a_attachments)),
				'send_time'			=> array('timestamp', date('Y-m-d H:i:s', time())),
				'rcp_to'			=> array('clob', $a_rcp_to), 
				'rcp_cc'			=> array('clob', $a_rcp_cc),
				'rcp_bcc'			=> array('clob', $a_rcp_bcc),
				'm_status'			=> array('text', 'read'),
				'm_type'			=> array('text', serialize($a_m_type)),
				'm_email'			=> array('integer', $a_m_email),
				'm_subject'			=> array('text', $a_m_subject),
				'm_message'			=> array('clob', $a_m_message),
				'use_placeholders'	=> array('integer', $a_use_placeholders),
				'tpl_ctx_id'	    => array('text', $a_tpl_context_id),
				'tpl_ctx_params'	=> array('blob', @json_encode((array)$a_tpl_context_params))
			),
			array(
				'mail_id'			=> array('integer', $a_draft_id)
			)
		);

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
	* @param    integer $a_use_placeholders
	* @param    string|null $a_tpl_context_id
	* @param    array|null  $a_tpl_context_params
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
							  $a_user_id = 0,
							  $a_use_placeholders = 0,
							  $a_tpl_context_id = null,
							  $a_tpl_context_params = array()
	)
	{
		$a_user_id = $a_user_id ? $a_user_id : $this->user_id;

		global $ilDB, $log;

		if ($a_use_placeholders)
		{
			$a_m_message = $this->replacePlaceholders($a_m_message, $a_user_id);
		}

		/**/
		if(!$a_user_id)		$a_user_id = '0';
		if(!$a_folder_id)	$a_folder_id = '0';
		if(!$a_sender_id)	$a_sender_id = NULL;
		if(!$a_attachments)	$a_attachments = NULL;
		if(!$a_rcp_to)		$a_rcp_to = NULL;
		if(!$a_rcp_cc)		$a_rcp_cc = NULL;
		if(!$a_rcp_bcc)		$a_rcp_bcc = NULL;
		if(!$a_status)		$a_status = NULL;
		if(!$a_m_type)		$a_m_type = NULL;
		if(!$a_m_email)		$a_m_email = NULL;
		if(!$a_m_subject)	$a_m_subject = NULL;
		if(!$a_m_message)	$a_m_message = NULL;
		/**/

		$next_id = $ilDB->nextId($this->table_mail);

		$ilDB->insert($this->table_mail, array(
			'mail_id'		=> array('integer', $next_id),
			'user_id'		=> array('integer', $a_user_id),
			'folder_id'		=> array('integer', $a_folder_id),
			'sender_id'		=> array('integer', $a_sender_id),
			'attachments'	=> array('clob', serialize($a_attachments)),
			'send_time'		=> array('timestamp', date('Y-m-d H:i:s', time())),
			'rcp_to'		=> array('clob', $a_rcp_to),
			'rcp_cc'		=> array('clob', $a_rcp_cc),
			'rcp_bcc'		=> array('clob', $a_rcp_bcc),
			'm_status'		=> array('text', $a_status),
			'm_type'		=> array('text', serialize($a_m_type)),
			'm_email'		=> array('integer', $a_m_email),
			'm_subject'		=> array('text', $a_m_subject),
			'm_message'		=> array('clob', $a_m_message),
			'tpl_ctx_id'	    => array('text', $a_tpl_context_id),
			'tpl_ctx_params'	=> array('blob', @json_encode((array)$a_tpl_context_params))
		));

		return $next_id; //$ilDB->getLastInsertId();

	}

	protected function replacePlaceholders($a_message, $a_user_id)
	{
		$user = self::getCachedUserInstance($a_user_id);
		try
		{
			if(ilMailFormCall::getContextId())
			{
				require_once 'Services/Mail/classes/class.ilMailTemplateService.php';
				$context = ilMailTemplateService::getTemplateContextById(ilMailFormCall::getContextId());
			}
			else
			{
				require_once 'Services/Mail/classes/class.ilMailTemplateGenericContext.php';
				$context = new ilMailTemplateGenericContext();
			}

			foreach($context->getPlaceholders() as $key => $ph_definition)
			{
				$result    = $context->resolvePlaceholder($key, ilMailFormCall::getContextParameters(), $user);
				$a_message = str_replace('[' . $ph_definition['placeholder'] . ']', $result, $a_message);
			}
		}
		catch(Exception $e)
		{
			require_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
			ilLoggerFactory::getLogger('mail')->error(__METHOD__ . ' has been called with invalid context.');
		}

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
			$a_rcp_bcc = $this->__substituteRecipients($a_rcp_bcc, 'resubstitute');
		}

		$mbox = new ilMailbox();

		if (!$a_use_placeholders) # No Placeholders
		{
			$rcp_ids = $this->getUserIds(trim($a_rcp_to).','.trim($a_rcp_cc).','.trim($a_rcp_bcc));

			$as_email = array();

			foreach($rcp_ids as $id)
			{
				$tmp_mail_options = new ilMailOptions($id);

				// DETERMINE IF THE USER CAN READ INTERNAL MAILS
				$tmp_user = self::getCachedUserInstance($id);
				$user_is_active = $tmp_user->getActive();
				$user_can_read_internal_mails = !$tmp_user->hasToAcceptTermsOfService() && $tmp_user->checkTimeLimit();

				// CONTINUE IF SYSTEM MESSAGE AND USER CAN'T READ INTERNAL MAILS
				if (in_array('system', $a_type) && !$user_can_read_internal_mails)
				{
					continue;
				}

				// CONTINUE IF USER CAN'T READ INTERNAL MAILS OR IF HE/SHE WANTS HIS/HER MAIL
				// SENT TO HIS/HER EXTERNAL E-MAIL ADDRESS ONLY

				// Do not send external mails to inactive users!!!
				if($user_is_active)
				{
					if (!$user_can_read_internal_mails ||
						$tmp_mail_options->getIncomingType() == $this->mail_options->EMAIL)
					{
						$as_email[] = $tmp_user->getEmail();
						continue;
					}

					if ($tmp_mail_options->getIncomingType() == $this->mail_options->BOTH)
					{
						$as_email[] = $tmp_user->getEmail();
					}
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

			if(count($as_email) == 1)
			{
				$to[] = $as_email[0];
			}
			else
			{
				foreach ($as_email as $email)
				{
					$bcc[] = $email;
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
				$tmp_mail_options = new ilMailOptions($id);

				// DETERMINE IF THE USER CAN READ INTERNAL MAILS
				$tmp_user = self::getCachedUserInstance($id);
				$user_is_active = $tmp_user->getActive();
				$user_can_read_internal_mails = !$tmp_user->hasToAcceptTermsOfService() && $tmp_user->checkTimeLimit();

				// CONTINUE IF SYSTEM MESSAGE AND USER CAN'T READ INTERNAL MAILS
				if (in_array('system', $a_type) && !$user_can_read_internal_mails)
				{
					continue;
				}

				// CONTINUE IF USER CAN'T READ INTERNAL MAILS OR IF HE/SHE WANTS HIS MAIL
				// SENT TO HIS/HER EXTERNAL E-MAIL ADDRESS ONLY

				// Do not send external mails to inactive users!!!
				if($user_is_active)
				{
					if (!$user_can_read_internal_mails ||
						$tmp_mail_options->getIncomingType() == $this->mail_options->EMAIL)
					{
						$as_email[$tmp_user->getId()] = $tmp_user->getEmail();
						continue;
					}

					if ($tmp_mail_options->getIncomingType() == $this->mail_options->BOTH)
					{
						$as_email[$tmp_user->getId()] = $tmp_user->getEmail();
					}
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
				foreach ($as_email as $id => $email)
				{
					$this->sendMimeMail($email, '', '', $a_subject, $this->replacePlaceholders($a_message, $id), $a_attachments);
				}
			}

			$as_email = array();

			// cc / bcc
			foreach($rcp_ids_no_replace as $id)
			{
				$tmp_mail_options = new ilMailOptions($id);

				// DETERMINE IF THE USER CAN READ INTERNAL MAILS
				$tmp_user = self::getCachedUserInstance($id);
				$user_is_active = $tmp_user->getActive();
				$user_can_read_internal_mails = !$tmp_user->hasToAcceptTermsOfService() && $tmp_user->checkTimeLimit();

				// Do not send external mails to inactive users!!!
				if($user_is_active)
				{
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
						$as_email[] = $tmp_user->getEmail();
						continue;
					}

					if ($tmp_mail_options->getIncomingType() == $this->mail_options->BOTH)
					{
						$as_email[] = $tmp_user->getEmail();
					}
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
				$this->sendMimeMail('', '', implode(',', $as_email), $a_subject, $a_message, $a_attachments);
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

		$this->validatePear($a_recipients);
		if (ilMail::_usePearMail() && $this->getUsePear() == true)
		{
			$tmp_names = $this->explodeRecipients($a_recipients );
			if (! is_a($tmp_names, 'PEAR_Error'))
			{
				for ($i = 0;$i < count($tmp_names); $i++)
				{
					if ( substr($tmp_names[$i]->mailbox,0,1) === '#' ||
					   (substr($tmp_names[$i]->mailbox,0,1) === '"' &&
						substr($tmp_names[$i]->mailbox,1,1) === '#' ) )
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
						if($id = ilObjUser::getUserIdByLogin(addslashes($tmp_names[$i]->mailbox)))
						{
							$ids[] = $id;
						}
					}
					else
					{
						// Fixed mantis bug #5875
						if($id = ilObjUser::_lookupId($tmp_names[$i]->mailbox.'@'.$tmp_names[$i]->host))
						{
							$ids[] = $id;
						}
					}
				}
			}
		}
		else
		{
			$tmp_names = $this->explodeRecipients($a_recipients,  $this->getUsePear());
			for ($i = 0;$i < count($tmp_names); $i++)
			{
				if (substr($tmp_names[$i],0,1) == '#')
				{
					if(ilUtil::groupNameExists(addslashes(substr($tmp_names[$i],1))))
					{
						include_once("./Services/Object/classes/class.ilObjectFactory.php");
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

		$a_m_subject = trim($a_m_subject);
		$a_rcp_to = trim($a_rcp_to);

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
		global $rbacreview;
		
		$addresses = array();

		$this->validatePear($a_rcp);
		if (ilMail::_usePearMail() && $this->getUsePear())
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
							$tmp_user = self::getCachedUserInstance($id);
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
								$tmp_user = self::getCachedUserInstance($usr_id);
								$addresses[] = $tmp_user->getEmail();
							}
						}
					}
				}
			}
		}
		else
		{
			$tmp_rcp = $this->explodeRecipients($a_rcp, $this->getUsePear());

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
						$tmp_user = self::getCachedUserInstance($id);
						$addresses[] = $tmp_user->getEmail();
						continue;
					}
				}
				else
				{
					// GROUP THINGS
					include_once("./Services/Object/classes/class.ilObjectFactory.php");
					include_once('./Modules/Group/classes/class.ilObjGroup.php');

					// Fix
					foreach(ilObjGroup::_getAllReferences(ilObjGroup::_lookupIdByTitle(addslashes(substr($rcp,1)))) as $ref_id)
					{
						$grp_object = ilObjectFactory::getInstanceByRefId($ref_id);
						break;
					}
					// GET EMAIL OF MEMBERS AND STORE THEM IN $addresses
					foreach ($grp_object->getGroupMemberIds() as $id)
					{
						$tmp_user = self::getCachedUserInstance($id);
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

		$this->validatePear($a_recipients);
		if (ilMail::_usePearMail() && $this->getUsePear())
		{
			$tmp_rcp = $this->explodeRecipients($a_recipients, $this->getUsePear());

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
							if(!$rbacsystem->checkAccessOfUser($user_id, "internal_mail", $this->getMailObjectReferenceId()))
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

						if(!$this->mail_to_global_roles && is_array($role_ids))
						{
							foreach($role_ids as $role_id)
							{	
								if($rbacreview->isGlobalRole($role_id))
								{
									include_once('Services/Mail/exceptions/class.ilMailException.php');
									throw new ilMailException('mail_to_global_roles_not_allowed');

								}
							}
						}
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
		else // NO PEAR
		{	
			$tmp_rcp = $this->explodeRecipients($a_recipients, $this->getUsePear());

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
						if(!$rbacsystem->checkAccessOfUser($user_id, "internal_mail", $this->getMailObjectReferenceId()))
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
				else if (!$this->mail_to_global_roles)
				{
					$role_id = $rbacreview->roleExists(addslashes(substr($rcp,1)));
					if((int)$role_id && $rbacreview->isGlobalRole($role_id))
					{
						include_once('Services/Mail/exceptions/class.ilMailException.php');
						throw new ilMailException('mail_to_global_roles_not_allowed');
					}
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
	* @param    string|null $a_tpl_context_id
	* @param    array|null $a_tpl_ctx_params
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
						  $a_use_placeholders,
						  $a_tpl_context_id = null,
						  $a_tpl_ctx_params = array()
	)
	{
		global $ilDB;

		/**/
		if(!$a_attachments) $a_attachments = NULL;
		if(!$a_rcp_to) $a_rcp_to = NULL;
		if(!$a_rcp_cc) $a_rcp_cc = NULL;
		if(!$a_rcp_bcc) $a_rcp_bcc = NULL;
		if(!$a_m_type) $a_m_type = NULL;
		if(!$a_m_email) $a_m_email = NULL;
		if(!$a_m_message) $a_m_message = NULL;
		if(!$a_use_placeholders) $a_use_placeholders = '0';
		/**/

		$ilDB->replace(
			$this->table_mail_saved,
			array(
				'user_id'			=> array('integer', $this->user_id)
			),
			array(
				'attachments'		=> array('clob', serialize($a_attachments)),
				'rcp_to'			=> array('clob', $a_rcp_to),
				'rcp_cc'			=> array('clob', $a_rcp_cc),
				'rcp_bcc'			=> array('clob', $a_rcp_bcc),
				'm_type'			=> array('text', serialize($a_m_type)),
				'm_email'			=> array('integer', $a_m_email),
				'm_subject'			=> array('text', $a_m_subject),
				'm_message'			=> array('clob', $a_m_message),
				'use_placeholders'	=> array('integer', $a_use_placeholders),
				'tpl_ctx_id'	    => array('text', $a_tpl_context_id),
				'tpl_ctx_params'	=> array('blob', json_encode((array)$a_tpl_ctx_params))
			)
		);

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

		$res = $ilDB->queryf('
			SELECT * FROM '. $this->table_mail_saved .'
			WHERE user_id = %s',
			array('integer'),
			array($this->user_id));

		$this->mail_data = $this->fetchMailData($res->fetchRow(DB_FETCHMODE_OBJECT));

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
		global $lng,$rbacsystem;

		$this->mail_to_global_roles = true;
		if($this->user_id != ANONYMOUS_USER_ID)
		{
			$this->mail_to_global_roles = $rbacsystem->checkAccessOfUser($this->user_id, 'mail_to_global_roles', $this->mail_obj_ref_id);
		}

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

		try
 		{
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
 		}

		catch(ilMailException $e)
 		{
			return $this->lng->txt($e->getMessage());
 		}

		// if there was an error
		if (!empty($message))
		{
			return $this->lng->txt("mail_following_rcp_not_valid").$message;
		}

		// ACTIONS FOR ALL TYPES
		 
		
		// GET RCPT OF MAILING LISTS
		
		$rcp_to_list = $this->parseRcptOfMailingLists($a_rcp_to, true);
		$rcp_cc_list = $this->parseRcptOfMailingLists($a_rcp_cc, true);
		$rcp_bc_list = $this->parseRcptOfMailingLists($a_rcp_bc, true);
			
		$rcp_to = $rcp_cc = $rcp_bc = array();		
		foreach($rcp_to_list as $mlist_id => $mlist_rec)
		{
			if($mlist_id)
			{
				// internal mailing lists are sent as bcc
				$mlist_id = substr($mlist_id, 7);
				if($this->mlists->get($mlist_id)->getMode() == ilMailingList::MODE_TEMPORARY)
				{
					$rcp_bc = array_merge($rcp_bc, $mlist_rec);
					continue;
				}
			}
			
			$rcp_to = array_merge($rcp_to, $mlist_rec);			
		}
		foreach($rcp_cc_list as $mlist_id => $mlist_rec)
		{
			if($mlist_id)
			{
				// internal mailing lists are sent as bcc
				$mlist_id = substr($mlist_id, 7);
				if($this->mlists->get($mlist_id)->getMode() == ilMailingList::MODE_TEMPORARY)
				{
					$rcp_bc = array_merge($rcp_bc, $mlist_rec);
					continue;
				}
			}
			
			$rcp_cc = array_merge($rcp_cc, $mlist_rec);			
		}
		foreach($rcp_bc_list as $mlist_id => $mlist_rec)
		{			
			$rcp_bc = array_merge($rcp_bc, $mlist_rec);			
		}
			
		$rcp_to = implode(',', $rcp_to);
		$rcp_cc = implode(',', $rcp_cc);
		$rcp_bc = implode(',', $rcp_bc);
		

		if (! ilMail::_usePearMail() )
		{
			// REPLACE ALL LOGIN NAMES WITH '@' BY ANOTHER CHARACTER
			$rcp_to = $this->__substituteRecipients($rcp_to,"substitute");
			$rcp_cc = $this->__substituteRecipients($rcp_cc,"substitute");
			$rcp_bc = $this->__substituteRecipients($rcp_bc,"substitute");
		}

		// COUNT EMAILS
		$c_emails = $this->__getCountRecipients($rcp_to,$rcp_cc,$rcp_bc,true);
		$c_rcp = $this->__getCountRecipients($rcp_to,$rcp_cc,$rcp_bc,false);

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

		// check smtp permission
		if($c_emails && $this->user_id != ANONYMOUS_USER_ID &&
		   !$rbacsystem->checkAccessOfUser($this->user_id, 'smtp_mail', $this->mail_obj_ref_id))
		{
			return $this->lng->txt('mail_no_permissions_write_smtp');
		}

		if($this->appendInstallationSignature())
		{
			$a_m_message .= self::_getInstallationSignature();
		}

		// save mail in sent box
		$sent_id = $this->saveInSentbox($a_attachment,$a_rcp_to,$a_rcp_cc,$a_rcp_bc,$a_type,
										$a_m_subject,$a_m_message);

		if($a_attachment)
		{
			$this->mfile->assignAttachmentsToDirectory($sent_id,$sent_id);

			if ($error = $this->mfile->saveFiles($sent_id,$a_attachment))
			{
				return $error;
			}
		}

		// FILTER EMAILS
		// IF EMAIL RECIPIENT
		if($c_emails)
		{
			$this->sendMimeMail($this->__getEmailRecipients($rcp_to),
								$this->__getEmailRecipients($rcp_cc),
								$this->__getEmailRecipients($rcp_bc),
								$a_m_subject,
								$a_m_message,
								$a_attachment,
								0);
		}

		if (in_array('system',$a_type))
		{
			if (!$this->distributeMail($rcp_to,$rcp_cc,$rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$sent_id,$a_type,'system', $a_use_placeholders))
			{
				return $lng->txt("mail_send_error");
			}
		}
		// ACTIONS FOR TYPE SYSTEM AND NORMAL
		if (in_array('normal',$a_type))
		{
			// TRY BOTH internal and email (depends on user settings)
			if (!$this->distributeMail($rcp_to,$rcp_cc,$rcp_bc,$a_m_subject,$a_m_message,$a_attachment,$sent_id,$a_type,'normal', $a_use_placeholders))
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

	function parseRcptOfMailingLists($rcpt = '', $maintain_lists = false)
	{
		if ($rcpt == '') 
		{
			if(!$maintain_lists)
			{
				return $rcpt;
			}
			else
			{
				return array();
			}
		}
//@todo check rcp pear validation
		$arrRcpt = $this->explodeRecipients(trim($rcpt));
		if (!is_array($arrRcpt) || empty($arrRcpt))
		{
			if(!$maintain_lists)
			{
				return $rcpt;
			}
			else
			{
				return array();
			}
		}

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
							$login = ilObjUser::_lookupLogin($entry['usr_id']);
							if(!$maintain_lists)
							{
								$new_rcpt[] = $login;
							}
							else
							{
								$new_rcpt[$item->mailbox][] = $login;
							}
						}
					}
				}
				else
				{
					if(!$maintain_lists)
					{
						$new_rcpt[] = $item->mailbox.'@'.$item->host;
					}
					else
					{
						$new_rcpt[0][] = $item->mailbox.'@'.$item->host;
					}
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
							$login = ilObjUser::_lookupLogin($entry['usr_id']);
							if(!$maintain_lists)
							{
								$new_rcpt[] = $login;
							}
							else
							{
								$new_rcpt[$item][] = $login;
							}
						}
					}
				}
				else
				{
					if(!$maintain_lists)
					{
						$new_rcpt[] = $item;
					}
					else
					{
						$new_rcpt[0][] = $item;
					}
				}
			}
		}

		if(!$maintain_lists)
		{
			return implode(',', $new_rcpt);
		}
		else
		{
			return $new_rcpt;
		}
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

		/** @todo: mjansen 23.12.2011 Why is $a_as_email undefined here?  */
		return $this->sendInternalMail($sent_id,$this->user_id,$a_attachment,$a_rcp_to,$a_rcp_cc,
										$a_rcp_bcc,'read',$a_type,$a_as_email,$a_m_subject,$a_m_message,$this->user_id, 0);
	}

	/**
	 * @static
	 * @param string $a_email
	 * @param string $a_fullname
	 * @return string
	 */
	public static function addFullname($a_email, $a_fullname)
	{
		include_once 'Services/Mail/classes/class.ilMimeMail.php';
		return ilMimeMail::_mimeEncode($a_fullname).' <'.$a_email.'>';
	}

	/**
	 *
	 * Returns the sender of the mime mail
	 *
	 * @return	string	sender of the mime mail
	 */
	public function getMimeMailSender()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;
		
		include_once "Services/Mail/classes/class.ilMimeMail.php";
		
		if($this->user_id && $this->user_id != ANONYMOUS_USER_ID)
		{
			$email = $ilUser->getEmail();
			$fullname = $ilUser->getFullname();
			if($ilUser->getId() != $this->user_id)
			{
				$user = self::getCachedUserInstance($this->user_id);
				$email = $user->getEmail();
				$fullname = $user->getFullname();
			}

			$sender = array($email, $fullname);
		}
		else
		{
			$sender = self::getIliasMailerAddress();
		}

		return $sender;
	}
	
	/**
	 * 
	 * Builds an email address used for system notifications 
	 * 
	 * @static
	 * @access	public
	 * @return	string
	 * 
	 */
	public static function getIliasMailerAddress()
	{
		global $ilSetting;
		
		include_once 'Services/Mail/classes/class.ilMimeMail.php';
		
		$no_reply_adress = trim($ilSetting->get('mail_external_sender_noreply'));
		if(strlen($no_reply_adress))
		{
			if(strpos($no_reply_adress, '@') === false)
				$no_reply_adress = 'noreply@'.$no_reply_adress;
			
			if(!ilUtil::is_email($no_reply_adress))
			{
				$no_reply_adress = 'noreply@'.$_SERVER['SERVER_NAME'];
			}

			$sender = array($no_reply_adress, self::_getIliasMailerName());
		}
		else
		{
			$sender = array('noreply@'.$_SERVER['SERVER_NAME'], self::_getIliasMailerName());
		}
		
		return $sender;
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
	* @param bool prevent soap
	* @access	public
	* @return	array of saved data
	*/
	function sendMimeMail($a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachments,$a_no_soap = false)
	{
		include_once "Services/Mail/classes/class.ilMimeMail.php";

		#var_dump("<pre>",$a_rcp_to,$a_rcp_cc,$a_rcp_bcc,$a_m_subject,$a_m_message,$a_attachments,"<pre>");

		#$inst_name = $this->ilias->getSetting("inst_name") ? $this->ilias->getSetting("inst_name") : "ILIAS 4";
		#$a_m_subject = "[".$inst_name."] ".$a_m_subject;

		$a_m_subject = self::getSubjectPrefix().' '.$a_m_subject;

		$sender = $this->getMimeMailSender();

		// #10854
		if($this->isSOAPEnabled() && !$a_no_soap)
		{
			// Send per soap
			include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';

			$soap_client = new ilSoapClient();
			$soap_client->setResponseTimeout(1);
			$soap_client->enableWSDL(true);
			$soap_client->init();

			$attachments = array();
			$a_attachments = $a_attachments ? $a_attachments : array();
			foreach($a_attachments as $attachment)
			{
				$attachments[] = $this->mfile->getAbsolutePath($attachment);
			}
			// mjansen: switched separator from "," to "#:#" because of mantis bug #6039
			$attachments = implode('#:#',$attachments);
			// mjansen: use "#:#" as leading delimiter
			if(strlen($attachments))  
				$attachments = "#:#".$attachments;

			$soap_client->call('sendMail',array(session_id().'::'.$_COOKIE['ilClientId'],	// session id
												$a_rcp_to,
												$a_rcp_cc,
												$a_rcp_bcc,
												is_array($sender) ? implode('#:#', $sender) : $sender,
												$a_m_subject,
												$a_m_message,
												$attachments));

			return true;
		}
		else
		{
			// send direct
			include_once "Services/Mail/classes/class.ilMimeMail.php";

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
					$mmail->Attach($this->mfile->getAbsolutePath($attachment), '', 'inline', $attachment);
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
		$umail = self::getCachedUserInstance($this->user_id);
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

		$ilDB->update($this->table_mail_saved,
			array
			(
				'attachments' => array('clob', serialize($a_attachments))
			),
			array
			(
				'user_id' => array('integer', $this->user_id)
			)
		);
		
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
	function explodeRecipients($a_recipients, $use_pear = true)
	{
		$a_recipients = trim($a_recipients);

		// WHITESPACE IS NOT ALLOWED AS SEPERATOR
		#$a_recipients = preg_replace("/ /",",",$a_recipients);
		$a_recipients = preg_replace("/;/",",",$a_recipients);
		
		if (ilMail::_usePearMail() && $use_pear == true)
		{
			if (strlen(trim($a_recipients)) > 0)
			{
				require_once './Services/PEAR/lib/Mail/RFC822.php';
				$parser = new Mail_RFC822();
				return $parser->parseAddressList($a_recipients, "ilias", false, true);
			} else {
				return array();
			}
		}
		else
		{
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

		$this->validatePear($rcp);
		if (ilMail::_usePearMail() && $this->getUsePear())
		{
			$tmp_rcp = $this->explodeRecipients($rcp);
			if (! is_a($tmp_rcp, 'PEAR_Error'))
			{
				foreach ($tmp_rcp as $to)
				{
					if ($a_only_email)
					{
						// Fixed mantis bug #5875
						if(ilObjUser::_lookupId($to->mailbox.'@'.$to->host))
						{
							continue;
						}

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
			foreach ($this->explodeRecipients($rcp,$this->getUsePear()) as $to)
			{
				if ($a_only_email)
				{
					$to = $this->__substituteRecipients($to,"resubstitute");
					if (strpos($to,'@'))
					{							
						// Fixed mantis bug #5875
						if(ilObjUser::_lookupId($to))
						{
							continue;
						}

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
						// Fixed mantis bug #5875
						if(ilObjUser::_lookupId($to->mailbox.'@'.$to->host))
						{
							continue;
						}

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
				$to = $this->__substituteRecipients($to,"resubstitute");
				if(strpos($to,'@'))
				{
					// Fixed mantis bug #5875
					if(ilObjUser::_lookupId($to))
					{
						continue;
					}

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
	public static function _getUserInternalMailboxAddress($usr_id, $login=null, $firstname=null, $lastname=null) 
	{
		if (ilMail::_usePearMail())
		{
			if ($login == null)
			{
				require_once './Services/User/classes/class.ilObjUser.php';
				$usr_obj = self::getCachedUserInstance($usr_id);
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
	public static function _usePearMail()
	{
		/**
 		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		return $ilSetting->get('pear_mail_enable', 0);
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
		$http_path = ilUtil::_getHttpPath();

		$lang->loadLanguageModule('mail');
		return sprintf($lang->txt('mail_auto_generated_info'),
			$ilSetting->get('inst_name','ILIAS 4'),
			$http_path)."\n\n";
	}

	/**
	 * Get the name used for mails sent by the anonymous user
	 * @access public
	 * @static
	 * @return string Name of sender
	 */
	public static function _getIliasMailerName()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		if(strlen($ilSetting->get('mail_system_sender_name')))
		{
			return $ilSetting->get('mail_system_sender_name');
		}
		else if(strlen($ilSetting->get('short_inst_name')))
		{
			return $ilSetting->get('short_inst_name');
		}

		return 'ILIAS';
	}

	/**
	 * Setter/Getter for appending the installation signarue
	 *
	 * @access public
	 *
	 * @param	mixed	boolean or nothing
	 * @return	mixed	boolean if called without passing any params, otherwise $this
	 */
	public function appendInstallationSignature($a_flag = null)
	{
		if(null === $a_flag) {
			return $this->appendInstallationSignature;
		}

		$this->appendInstallationSignature = $a_flag;
	
		return $this;
	}

	/**
	 * Static getter for the installation signature
	 *
	 * @access public
	 * @static
	 *
	 * @return	string	The installation mail signature
	 */
	public static function _getInstallationSignature()
	{
		global $ilClientIniFile;

		$signature = "\n\n* * * * *\n";
	
		$signature .= $ilClientIniFile->readVariable('client', 'name')."\n";
		if(strlen($desc = $ilClientIniFile->readVariable('client', 'description')))
		{
			$signature .= $desc."\n";
		}
		
		$signature .= ilUtil::_getHttpPath();

		$clientdirs = glob(ILIAS_WEB_DIR."/*", GLOB_ONLYDIR);
		if(is_array($clientdirs) && count($clientdirs) > 1)
		{
			// #18051
			$signature .= '/login.php?client_id='.CLIENT_ID;
		}
		
		$signature .= "\n\n";

		return $signature;
	}

	/**
	 * Get text that will be prepended to auto generated mails.
	 * @return string subject prefix
	 */
	public static function getSubjectPrefix()
	{
		global $ilSetting;
		static $prefix = null;

		return $prefix == null ? $ilSetting->get('mail_subject_prefix','') : $prefix;
	}

	/**
	 * Get salutation
	 * @param int $a_usr_id
	 * @return
	 */
	public static function getSalutation($a_usr_id,$a_language = null)
	{
		global $lng;

		$lang = $a_language ? $a_language : $lng;

		$lang->loadLanguageModule('mail');
		$gender = ilObjUser::_lookupGender($a_usr_id);
		$gender = $gender ? $gender : 'n';
		$name = ilObjUser::_lookupName($a_usr_id);

		if(!strlen($name['firstname']))
		{
			return $lang->txt('mail_salutation_anonymous').',';
		}

		return $lang->txt('mail_salutation_'.$gender).' '.
			($name['title'] ? $name['title'].' ' : '').
			($name['firstname'] ? $name['firstname'].' ' : '').
			$name['lastname'].',';
	}

	private function setUsePear($bool)
	{
		$this->use_pear = $bool;
	}
	
	private function getUsePear()
	{
		return $this->use_pear;
	}

	// Force fallback for sending mails via ILIAS, if internal Pear-Validation returns PEAR_Error
	/**
	 *
	 * @param <type> $a_recipients
	 */
	private function validatePear($a_recipients)
	{
		if(ilMail::_usePearMail())
		{
			$this->setUsePear(true);
			$tmp_names = $this->explodeRecipients($a_recipients, true);
			if(is_a($tmp_names, 'PEAR_Error'))
			{
				$this->setUsePear(false);
			}
		}
		else
		{
			$this->setUsePear(false);
		}
	}

	/**
	 * 
	 * Returns a cached instance of ilObjUser
	 * 
	 * @static
	 * @param integer $a_usr_id
	 * @return ilObjUser
	 */
	protected static function getCachedUserInstance($a_usr_id)
	{
		if(isset(self::$userInstances[$a_usr_id]))
		{
			return self::$userInstances[$a_usr_id];
		}

		self::$userInstances[$a_usr_id] = new ilObjUser($a_usr_id);
		return self::$userInstances[$a_usr_id];
	}
} // END class.ilMail
?>