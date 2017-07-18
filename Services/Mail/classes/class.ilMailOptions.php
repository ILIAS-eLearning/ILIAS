<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_MAIL_LOCAL", 0);
define("IL_MAIL_EMAIL", 1);
define("IL_MAIL_BOTH", 2);
define("IL_MAIL_FIRST_EMAIL", 3);
define("IL_MAIL_SECOND_EMAIL", 4);
define("IL_MAIL_BOTH_EMAIL", 5);

/**
* Class ilMailOptions
* this class handles user mails 
* 
*  
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*/
class ilMailOptions
{
	var $ilias;

	// SOME QUASI STATIC CONSTANTS (possible values of incoming type)
	var $LOCAL = 0;
	var $EMAIL = 1;         
	var $BOTH = 2;
	
	// sub-options for mail forwarding 
	var $FIRST_EMAIL  = 3;  // first email address 
	var $SECOND_EMAIL = 4;  // second email address
	var $BOTH_EMAIL   = 5;  // both email addresses

	/**
	* linebreak
	* @var integer
	* @access public
	*/
	var $linebreak;

	/**
	* signature
	* @var string signature
	* @access public
	*/
	var $signature;
	var $incoming_type;
	public $mail_address_option;
	var $cronjob_notification;

	/**
	* Constructor
	* setup an mail object
	* @param int user_id
	* @access	public
	*/
	public function __construct($a_user_id)
	{
		global $ilias;

		define("DEFAULT_LINEBREAK",60);

		$this->ilias = $ilias;
		$this->table_mail_options = 'mail_options';

		$this->user_id = $a_user_id;
		$this->getOptions();
	}

	/**
	 * create entry in table_mail_options for a new user
	 * this method should only be called from createUser()
	 * @return bool
	 */
    public function createMailOptionsEntry()
    {
    	global $ilDB, $ilSetting;
    		
	    $incomingMail = $ilSetting->get('mail_incoming_mail') ? $ilSetting->get('mail_incoming_mail'): IL_MAIL_LOCAL;
	    $mail_address_option = $ilSetting->get('mail_address_option') ? $ilSetting->get('mail_address_option') : IL_MAIL_FIRST_EMAIL;
	    $ilDB->insert('mail_options',
				array(
						'user_id'              => array('integer', $this->user_id),
						'linebreak'            => array('integer', DEFAULT_LINEBREAK),
						'signature'            => array('text', NULL),
						'incoming_type'        => array('integer', $incomingMail),
						'mail_address_option'  => array('integer', $mail_address_option),
						'cronjob_notification' => array('integer', 0)
				));
	    
		return true;
    }

	/**
	* get options of user and set variables $signature and $linebreak
	* this method shouldn't bew called from outside
	* use getSignature() and getLinebreak()
	* @access	private
	* @return	boolean
	*/
	function getOptions()
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT * FROM '.$this->table_mail_options.'
			WHERE user_id = %s',
			array('integer'), array($this->user_id));
		
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
		
		$this->cronjob_notification = stripslashes($row->cronjob_notification);
		$this->signature = stripslashes($row->signature);
		$this->linebreak = stripslashes($row->linebreak);
		$this->incoming_type = $row->incoming_type;
		$this->mail_address_option = $row->mail_address_option;
		
		if(!strlen(ilObjUser::_lookupEmail($this->user_id)))
		{
			$this->incoming_type = $this->LOCAL;
		}

		return true;
	}

	/**
	* update user options
	* @param string Signature
	* @param int linebreak
	* @param int incoming_type
	* @param int cronjob_notification
	* @return	boolean
	*/
	public function updateOptions($a_signature, $a_linebreak, $a_incoming_type, $a_cronjob_notification, $mail_address_option = IL_MAIL_FIRST_EMAIL)
	{
		/**
		 * @var $ilDB      ilDBInterface
		 * @var $ilSetting ilSetting
		 */
		global $ilDB, $ilSetting;

		$this->cronjob_notification = $a_cronjob_notification;
		$this->signature            = $a_signature;
		$this->linebreak            = $a_linebreak;
		$this->incoming_type        = $a_incoming_type;
		$this->mail_address_option  = $mail_address_option;

		$data = array(
			'signature'     => array('text', $this->signature),
			'linebreak'     => array('integer', $this->linebreak),
			'incoming_type' => array('integer', $this->incoming_type),
			'mail_address_option' => array('integer', $this->mail_address_option)
		);
		if($ilSetting->get('mail_notification'))
		{
			$data['cronjob_notification']  = array('integer', $this->cronjob_notification);
		}
		else
		{
			$data['cronjob_notification']  = array('integer', self::lookupNotificationSetting($this->user_id));
		}

		$ilDB->replace(
			$this->table_mail_options,
			array(
				'user_id' => array('integer', $this->user_id)
			),
			$data
		);

		return true;
	}
	/**
	* get linebreak of user
	* @access	public
	* @return	array	mails
	*/
	function getLinebreak()
	{
		return $this->linebreak;
	}

	/**
	* get signature of user
	* @access	public
	* @return	array	mails
	*/
	function getSignature()
	{
		return $this->signature;
	}

	function getIncomingType()
	{
		return $this->incoming_type;
	}
	
	function setCronjobNotification()
	{
		return $this->cronjob_notification;
	}
	function getCronjobNotification()
	{
		return $this->cronjob_notification;
	}
	
	/**
	 * @return int
	 */
	public function getMailAddressOption()
	{
		return $this->mail_address_option;
	}
	
	/**
	 * @param int $mail_address_option
	 */
	public function setMailAddressOption($mail_address_option)
	{
		$this->mail_address_option = $mail_address_option;
	}

	/**
	 * @param int $usr_id
	 * @return int
	 */
	protected static function lookupNotificationSetting($usr_id)
	{
		/**
		 * @var $ilDB ilDBInterface
		 */
		global $ilDB;

		$query = "SELECT cronjob_notification FROM mail_options WHERE user_id = " . $ilDB->quote($usr_id, 'integer');
		$row   = $ilDB->fetchAssoc($ilDB->query($query));
		return (int)$row['cronjob_notification'];
	}
	
	/**
	 * @param ilObjUser     $user
	 * @param ilMailOptions $mail_options
	 * @param               $as_email
	 */
	public static function lookupExternalEmails(ilObjUser $user, ilMailOptions $mail_options, &$as_email)
	{
		$incoming_type       = $mail_options->getIncomingType();
		$mail_address_option = $mail_options->getMailAddressOption();

		if($incoming_type == IL_MAIL_EMAIL || $incoming_type == IL_MAIL_BOTH)
		{
			switch($mail_address_option)
			{
				case IL_MAIL_SECOND_EMAIL:
					if(strlen($user->getSecondEmail()))
					{
						$as_email[] = $user->getSecondEmail();
					}
					break;

				case IL_MAIL_BOTH_EMAIL:
					$as_email[] = $user->getEmail();
					if(strlen($user->getSecondEmail()))
					{
						$as_email[] = $user->getSecondEmail();
					}
					break;

				case IL_MAIL_FIRST_EMAIL:
				default:
					$as_email[] = $user->getEmail();
					break;
			}
		}
	}
	
	/**
	 * @param ilObjUser     $user
	 * @param ilMailOptions $mail_options
	 * @param               $as_email
	 */
	public static function getExternalEmailsByUser(ilObjUser $user, ilMailOptions $mail_options = NULL, array &$as_email)
	{
		if(!$mail_options)
		{
			$mail_options = new self($user->getId());
		}

		self::lookupExternalEmails($user, $mail_options, $as_email);
	}
	
	/**
	 * @param int $user_id
	 * @param ilMailOptions|NULL $mail_options
	 * @param                    $as_email
	 */
	public static function getExternalEmailsByUserId($user_id, ilMailOptions $mail_options = NULL, array &$as_email)
	{
		$user = new ilObjUser($user_id);

		if(!$mail_options)
		{
			$mail_options = new self($user_id);
		}

		self::lookupExternalEmails($user, $mail_options, $as_email);
	}
} 
