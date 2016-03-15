<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_MAIL_LOCAL", 0);
define("IL_MAIL_EMAIL", 1);
define("IL_MAIL_BOTH", 2);

/**
* Class UserMail
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
    		
	    $incomingMail = $ilSetting->get('mail_incoming_mail', IL_MAIL_BOTH);
	    $ilDB->insert('mail_options',
				array(
						'user_id'              => array('integer', $this->user_id),
						'linebreak'            => array('integer', DEFAULT_LINEBREAK),
						'signature'            => array('text', NULL),
						'incoming_type'        => array('integer', $incomingMail),
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
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		$this->cronjob_notification = stripslashes($row->cronjob_notification);
		$this->signature = stripslashes($row->signature);
		$this->linebreak = stripslashes($row->linebreak);
		$this->incoming_type = $row->incoming_type;
		
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
	public function updateOptions($a_signature, $a_linebreak, $a_incoming_type, $a_cronjob_notification)
	{
		/**
		 * @var $ilDB      ilDB
		 * @var $ilSetting ilSetting
		 */
		global $ilDB, $ilSetting;

		$this->cronjob_notification = $a_cronjob_notification;
		$this->signature            = $a_signature;
		$this->linebreak            = $a_linebreak;
		$this->incoming_type        = $a_incoming_type;

		$data = array(
			'signature'     => array('text', $this->signature),
			'linebreak'     => array('integer', $this->linebreak),
			'incoming_type' => array('integer', $this->incoming_type)
		);
		if($ilSetting->get('mail_notification'))
		{
			$data['cronjob_notification']  = array('integer', $this->incoming_type);
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
	 * @param int $usr_id
	 * @return int
	 */
	protected static function lookupNotificationSetting($usr_id)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		$query = "SELECT cronjob_notification FROM mail_options WHERE user_id = " . $ilDB->quote($usr_id, 'integer');
		$row   = $ilDB->fetchAssoc($ilDB->query($query));
		return (int)$row['cronjob_notification'];
	}
} // END class.ilFormatMail
?>
