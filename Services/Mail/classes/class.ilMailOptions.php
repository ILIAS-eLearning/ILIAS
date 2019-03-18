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

	/** @var \ILIAS */
	protected $ilias;

	/** @var \ilDBInterface */
	protected $db;

	/** @var int */
	protected $user_id;

	/** @var \ilSetting */
	protected $settings;

	/** @var string */
	protected $table_mail_options = 'mail_options';

	/** @var int */
	protected $linebreak;

	/** @var string */
	protected $signature;

	/** @var int */
	protected $cronjob_notification;

	/** @var int */
	protected $incoming_type = self::INCOMING_LOCAL;

	/** @var int */
	protected $mail_address_option = self::FIRST_EMAIL;

	/** @var ilMailTransportSettings */
	private $mailTransportSettings;

	/** @var string */
	protected $firstEmailAddress = '';

	/** @var string */
	protected $secondEmailAddress = '';

	/**
	 * @param int $a_user_id
	 * @param ilMailTransportSettings|null $mailTransportSettings
	 */
	public function __construct($a_user_id, ilMailTransportSettings $mailTransportSettings = null)
	{
		global $DIC;

		$this->user_id = $a_user_id;

		$this->db       = $DIC->database();
		$this->settings = $DIC->settings();

		if ($mailTransportSettings === null) {
			$mailTransportSettings = new ilMailTransportSettings($this);
		}
		$this->mailTransportSettings = $mailTransportSettings;

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

		$this->db->replace(
			$this->table_mail_options,
			array(
				'user_id'              => array('integer', $this->user_id),
			),
			array(
				'linebreak'            => array('integer', (int)self::DEFAULT_LINE_BREAK),
				'signature'            => array('text', null),
				'incoming_type'        => array('integer', $incomingMail),
				'mail_address_option'  => array('integer', $mail_address_option),
				'cronjob_notification' => array('integer', 0)
			)
		);
	}

	protected function read()
	{
		$res = $this->db->queryF(
			'SELECT mail_options.cronjob_notification,
					mail_options.signature,
					mail_options.linebreak,
					mail_options.incoming_type,
					mail_options.mail_address_option,
					usr_data.email,
					usr_data.second_email
			 FROM mail_options 
			 LEFT JOIN usr_data ON mail_options.user_id = usr_data.usr_id
			 WHERE mail_options.user_id = %s',
			array('integer'),
			array($this->user_id)
		);
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

		$this->cronjob_notification = $row->cronjob_notification;
		$this->signature            = $row->signature;
		$this->linebreak            = $row->linebreak;
		$this->incoming_type        = $row->incoming_type;
		$this->mail_address_option  = (int)$row->mail_address_option >= 3 ? $row->mail_address_option : self::FIRST_EMAIL;

		$this->firstEmailAddress  = $row->email;
		$this->secondEmailAddress = $row->second_email;

		$this->mailTransportSettings->adjust($this->firstEmailAddress, $this->secondEmailAddress);
	}

	/**
	*/
	public function updateOptions()
	{
		$data = array(
			'signature'           => array('text', $this->getSignature()),
			'linebreak'           => array('integer', (int)$this->getLinebreak()),
			'incoming_type'       => array('integer', $this->getIncomingType()),
			'mail_address_option' => array('integer', $this->getMailAddressOption())
		);

		if($this->settings->get('mail_notification'))
		{
			$data['cronjob_notification']  = array('integer', (int)$this->getCronjobNotification());
		}
		else
		{
			$data['cronjob_notification']  = array('integer', (int)self::lookupNotificationSetting($this->user_id));
		}

		return $this->db->replace(
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
	 * @return string[]
	 */
	public function getExternalEmailAddresses(): array
	{
		$emailAddresses = [];

		switch ($this->getMailAddressOption()) {
			case self::SECOND_EMAIL:
				if (strlen($this->secondEmailAddress)) {
					$emailAddresses[] = $this->secondEmailAddress;
				} elseif (strlen($this->firstEmailAddress)) {
					// fallback, use first email address
					$emailAddresses[] = $this->firstEmailAddress;
				}
				break;

			case self::BOTH_EMAIL:
				if (strlen($this->firstEmailAddress)) {
					$emailAddresses[] = $this->firstEmailAddress;
				}
				if (strlen($this->secondEmailAddress)) {
					$emailAddresses[] = $this->secondEmailAddress;
				}
				break;

			case self::FIRST_EMAIL:
			default:
				if (strlen($this->firstEmailAddress)) {
					$emailAddresses[] = $this->firstEmailAddress;
				} elseif (strlen($this->secondEmailAddress)) {
					// fallback, use first email address
					$emailAddresses[] = $this->secondEmailAddress;
				}
				break;
		}

		return $emailAddresses;
	}
}
