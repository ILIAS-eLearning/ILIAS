<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilMailOptions
* this class handles user mails 
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*/
class ilMailOptions
{
	const INCOMING_LOCAL = 0;
	const INCOMING_EMAIL = 1;
	const INCOMING_BOTH  = 2;

	const FIRST_EMAIL  = 3;
	const SECOND_EMAIL = 4;
	const BOTH_EMAIL   = 5;

	const DEFAULT_LINE_BREAK = 60;

	/**
	 * @var \ILIAS
	 */
	protected $ilias;

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var \ilSetting
	 */
	protected $settings;

	/**
	 * @var string
	 */
	protected $table_mail_options = 'mail_options';

	/**
	 * @var int
	 */
	protected $linebreak;

	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * @var int
	 */
	protected $cronjob_notification;

	/**
	 * @var int
	 */
	protected $incoming_type = self::INCOMING_LOCAL;

	/**
	 * @var int
	 */
	protected $mail_address_option = self::FIRST_EMAIL;

	/**
	 * @param int $a_user_id
	 */
	public function __construct($a_user_id)
	{
		global $DIC;

		$this->user_id = $a_user_id;

		$this->ilias    = $DIC['ilias'];
		$this->db       = $DIC->database();
		$this->settings = $DIC->settings();

		$this->read();
	}

	/**
	 * create entry in table_mail_options for a new user
	 * this method should only be called from createUser()
	 */
	public function createMailOptionsEntry()
	{
		$incomingMail        = strlen($this->settings->get('mail_incoming_mail'))  ? (int)$this->settings->get('mail_incoming_mail') : self::INCOMING_LOCAL;
		$mail_address_option = strlen($this->settings->get('mail_address_option')) ? (int)$this->settings->get('mail_address_option') : self::FIRST_EMAIL;

		$this->db->insert(
			$this->table_mail_options,
			array(
				'user_id'              => array('integer', $this->user_id),
				'linebreak'            => array('integer', self::DEFAULT_LINE_BREAK),
				'signature'            => array('text', null),
				'incoming_type'        => array('integer', $incomingMail),
				'mail_address_option'  => array('integer', $mail_address_option),
				'cronjob_notification' => array('integer', 0)
			)
		);
	}

	/**
	 * 
	 */
	protected function read()
	{
		$res = $this->db->queryF(
			'SELECT * FROM ' . $this->table_mail_options . ' WHERE user_id = %s',
			array('integer'),
			array($this->user_id)
		);
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

		$this->cronjob_notification = $row->cronjob_notification;
		$this->signature            = $row->signature;
		$this->linebreak            = $row->linebreak;
		$this->incoming_type        = $row->incoming_type;
		$this->mail_address_option  = strlen($row->mail_address_option) ? $row->mail_address_option : self::FIRST_EMAIL;

		if(!strlen(ilObjUser::_lookupEmail($this->user_id)))
		{
			$this->incoming_type = self::INCOMING_LOCAL;
		}
	}

	/**
	*/
	public function updateOptions()
	{
		$data = array(
			'signature'           => array('text', $this->getSignature()),
			'linebreak'           => array('integer', $this->getLinebreak()),
			'incoming_type'       => array('integer', $this->getIncomingType()),
			'mail_address_option' => array('integer', $this->getMailAddressOption())
		);

		if($this->settings->get('mail_notification'))
		{
			$data['cronjob_notification']  = array('integer', $this->getCronjobNotification());
		}
		else
		{
			$data['cronjob_notification']  = array('integer', self::lookupNotificationSetting($this->user_id));
		}

		$this->db->replace(
			$this->table_mail_options,
			array(
				'user_id' => array('integer', $this->user_id)
			),
			$data
		);
	}

	/**
	* @return string
	*/
	public function getLinebreak()
	{
		return $this->linebreak;
	}

	/**
	 * @return string
	 */
	public function getSignature()
	{
		return $this->signature;
	}

	/**
	 * @return int
	 */
	public function getIncomingType()
	{
		return $this->incoming_type;
	}

	/**
	 * @param int $linebreak
	 */
	public function setLinebreak($linebreak)
	{
		$this->linebreak = $linebreak;
	}

	/**
	 * @param string $signature
	 */
	public function setSignature($signature)
	{
		$this->signature = $signature;
	}

	/**
	 * @param int $incoming_type
	 */
	public function setIncomingType($incoming_type)
	{
		$this->incoming_type = $incoming_type;
	}

	/**
	 * @return int
	 */
	public function setCronjobNotification($cronjob_notification)
	{
		$this->cronjob_notification = $cronjob_notification;
	}

	/**
	 * @return int
	 */
	public function getCronjobNotification()
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
		global $DIC;

		$query = "SELECT cronjob_notification FROM mail_options WHERE user_id = " . $DIC->database()->quote($usr_id, 'integer');
		$row   = $DIC->database()->fetchAssoc($DIC->database()->query($query));
		return (int)$row['cronjob_notification'];
	}
	
	/**
	 * @param ilObjUser     $user
	 * @param ilMailOptions $mail_options
	 * @return string[]
	 */
	protected static function lookupExternalEmails(ilObjUser $user, ilMailOptions $mail_options)
	{
		$emailAddresses = array();

		switch($mail_options->getMailAddressOption())
		{
			case self::SECOND_EMAIL:
				if(strlen($user->getSecondEmail()))
				{
					$emailAddresses[] = $user->getSecondEmail();
				}
				else if(strlen($user->getEmail()))
				{
					// fallback, use first email address
					$emailAddresses[] = $user->getEmail();
				}
				break;
			
			case self::BOTH_EMAIL:
				if(strlen($user->getEmail()))
				{
					$emailAddresses[] = $user->getEmail();
				}
				if(strlen($user->getSecondEmail()))
				{
					$emailAddresses[] = $user->getSecondEmail();
				}
				break;
			
			case self::FIRST_EMAIL:
			default:
				if(strlen($user->getEmail()))
				{
					$emailAddresses[] = $user->getEmail();
				}
				else if(strlen($user->getSecondEmail()))
				{
					// fallback, use first email address
					$emailAddresses[] = $user->getSecondEmail();
				}
				break;
		}
		
		return $emailAddresses;
	}
	
	/**
	 * @param ilObjUser     $user
	 * @param ilMailOptions $mail_options
	 * @return string[]
	 */
	public static function getExternalEmailsByUser(ilObjUser $user, ilMailOptions $mail_options = NULL)
	{
		if(!($mail_options instanceof ilMailOptions))
		{
			$mail_options = new self($user->getId());
		}

		return self::lookupExternalEmails($user, $mail_options);
	}
	
	/**
	 * @param int $user_id
	 * @param ilMailOptions|NULL $mail_options
	 * @return string[]
	 */
	public static function getExternalEmailsByUserId($user_id, ilMailOptions $mail_options = NULL)
	{
		return self::getExternalEmailsByUser(new ilObjUser($user_id), $mail_options);
	}
} 
