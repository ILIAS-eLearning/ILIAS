<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMail
{
	const ILIAS_HOST = 'ilias';

	/** @var ilLanguage */
	protected $lng;

	/** @var ilDBInterface */
	protected $db;

	/** @var ilFileDataMail */
	protected $mfile;

	/** @var ilMailOptions */
	protected $mail_options;

	/** @var \ilMailbox */
	protected $mailbox;

	/** @var int */
	public $user_id;

	/** @var string */
	protected $table_mail;

	/** @var string */
	protected $table_mail_saved;

	/** @var array */
	protected $mail_data = array();

	/** @var integer */
	protected $mail_obj_ref_id;

	/** @var boolean */
	protected $save_in_sentbox;

	protected $appendInstallationSignature = false;

	/** @var ilAppEventHandler */
	private $eventHandler;

	/** @var ilMailAddressTypeFactory */
	private $mailAddressTypeFactory;

	/** @var ilMailRfc822AddressParserFactory */
	private $mailAddressParserFactory;

	/** @var mixed|null */
	protected $contextId = null;

	/** @var array */
	protected $contextParameters = [];

	/** @var \ilLogger */
	protected $logger;
	
	/** @var \ilMailOptions[] */
	protected $mailOptionsByUsrIdMap = [];

	/** @var \ilObjUser[] */
	protected $userInstancesByIdMap = [];
	
	/** @var callable|null */
	protected $usrIdByLoginCallable = null;

	/** @var int */
	protected $maxRecipientCharacterLength = 998;
	
	/** @var \ilMailMimeSenderFactory */
	protected $senderFactory;

	/**
	 * @param integer $a_user_id
	 * @param ilMailAddressTypeFactory|null $mailAddressTypeFactory
	 * @param ilMailRfc822AddressParserFactory|null $mailAddressParserFactory
	 * @param ilAppEventHandler|null $eventHandler
	 * @param ilLogger|null $logger
	 * @param ilDBInterface|null $db
	 * @param ilLanguage|null $lng
	 * @param ilFileDataMail|null $mailFileData
	 * @param ilMailOptions|null $mailOptions
	 * @param ilMailbox|null $mailBox
	 * @param ilMailMimeSenderFactory|null $senderFactory
	 * @param callable|null $usrIdByLoginCallable
	 * @param int|null $mailAdminNodeRefId
	 */
	public function __construct(
		$a_user_id,
		ilMailAddressTypeFactory $mailAddressTypeFactory = null,
		ilMailRfc822AddressParserFactory $mailAddressParserFactory = null,
		\ilAppEventHandler $eventHandler = null,
		\ilLogger $logger = null,
		\ilDBInterface $db = null,
		\ilLanguage $lng = null,
		\ilFileDataMail $mailFileData = null,
		\ilMailOptions $mailOptions = null,
		\ilMailbox $mailBox = null,
		\ilMailMimeSenderFactory $senderFactory = null,
		callable $usrIdByLoginCallable = null,
		int $mailAdminNodeRefId = null
	) {
		global $DIC;

		if ($logger === null) {
			$logger = \ilLoggerFactory::getLogger('mail');
		}
		if ($mailAddressTypeFactory === null) {
			$mailAddressTypeFactory = new \ilMailAddressTypeFactory(null, $logger);
		}
		if ($mailAddressParserFactory === null) {
			$mailAddressParserFactory = new \ilMailRfc822AddressParserFactory();
		}
		if ($eventHandler === null) {
			$eventHandler = $DIC->event();
		}
		if ($db === null) {
			$db = $DIC->database();
		}
		if ($lng === null) {
			$lng = $DIC->language();
		}
		if ($mailFileData === null) {
			$mailFileData = new \ilFileDataMail($a_user_id);
		}
		if ($mailOptions === null) {
			$mailOptions = new \ilMailOptions($a_user_id);
		}
		if ($mailBox === null) {
			$mailBox = new \ilMailbox($a_user_id);
		}
		if ($senderFactory === null) {
			$senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];
		}
		if ($usrIdByLoginCallable === null) {
			$usrIdByLoginCallable = function (string $login) {
				return \ilObjUser::_lookupId($login);
			};
		}

		$this->user_id = $a_user_id;
		$this->mailAddressParserFactory = $mailAddressParserFactory;
		$this->mailAddressTypeFactory = $mailAddressTypeFactory;
		$this->eventHandler = $eventHandler;
		$this->logger = $logger;
		$this->db = $db;
		$this->lng = $lng;
		$this->mfile = $mailFileData;
		$this->mail_options = $mailOptions;
		$this->mailbox = $mailBox;
		$this->senderFactory = $senderFactory;
		$this->usrIdByLoginCallable = $usrIdByLoginCallable;

		$this->mail_obj_ref_id = $mailAdminNodeRefId;
		if (null === $this->mail_obj_ref_id) {
			$this->readMailObjectReferenceId();
		}

		$this->lng->loadLanguageModule('mail');
		$this->table_mail       = 'mail';
		$this->table_mail_saved = 'mail_saved';
		$this->setSaveInSentbox(false);
	}

	/**
	 * @param string $contextId
	 * @return ilMail
	 */
	public function withContextId(string $contextId): self
	{
		$clone = clone $this;

		$clone->contextId = $contextId;

		return $clone;
	}

	/**
	 * @param array $parameters
	 * @return ilMail
	 */
	public function withContextParameters(array $parameters): self
	{
		$clone = clone $this;

		$clone->contextParameters = $parameters;

		return $clone;
	}

	/**
	 * @return bool
	 */
	protected function isSystemMail()
	{
		return $this->user_id == ANONYMOUS_USER_ID;
	}

	/**
	 * @param string $newRecipient
	 * @param string $existingRecipients
	 * @return bool
	 */
	public function existsRecipient(string $newRecipient, string $existingRecipients): bool
	{
		$newAddresses = new \ilMailAddressListImpl($this->parseAddresses($newRecipient));
		$addresses = new \ilMailAddressListImpl($this->parseAddresses($existingRecipients));

		$list = new \ilMailDiffAddressList($newAddresses, $addresses);

		$diffedAddresses = $list->value();

		return count($diffedAddresses) === 0;
	}

	/**
	 * @param bool $a_save_in_sentbox
	 */
	public function setSaveInSentbox($a_save_in_sentbox)
	{
		$this->save_in_sentbox = (bool)$a_save_in_sentbox;
	}

	/**
	 * @return bool
	 */
	public function getSaveInSentbox()
	{
		return (bool)$this->save_in_sentbox;
	}

	/**
	 * Read and set the mail object ref id (administration node)
	 */
	protected function readMailObjectReferenceId()
	{
		$this->mail_obj_ref_id = ilMailGlobalServices::getMailObjectRefId();
	}

	/**
	 * @return int
	 */
	public function getMailObjectReferenceId()
	{
		return $this->mail_obj_ref_id;
	}

	/**
	 * Prepends the fullname of each ILIAS login name (if user has a public profile) found
	 * in the passed string and brackets the ILIAS login name afterwards.
	 * @param  string $a_recipients A string containing to, cc or bcc recipients
	 * @return string
	 */
	public function formatNamesForOutput($a_recipients)
	{
		global $DIC;

		$a_recipients = trim($a_recipients);
		if(!strlen($a_recipients))
		{
			return $this->lng->txt('not_available');
		}

		$names = array();

		$recipients = array_filter(array_map('trim', explode(',', $a_recipients)));
		foreach($recipients as $recipient)
		{
			$usr_id = ilObjUser::_lookupId($recipient);
			if($usr_id > 0)
			{
				$pp = ilObjUser::_lookupPref($usr_id, 'public_profile');
				if($pp == 'y' || ($pp == 'g' && !$DIC->user()->isAnonymous()))
				{
					$user    = $this->getUserInstanceById($usr_id);
					$names[] = $user->getFullname() . ' [' . $recipient . ']';
					continue;
				}
			}

			$names[] = $recipient;
		}

		return implode(', ', $names);
	}

	/**
	 * @param int $a_mail_id
	 * @return array
	 */
	public function getPreviousMail($a_mail_id)
	{
		$this->db->setLimit(1);

		$res = $this->db->queryF("
			SELECT b.* FROM {$this->table_mail} a
			INNER JOIN {$this->table_mail} b ON b.folder_id = a.folder_id
			AND b.user_id = a.user_id AND b.send_time > a.send_time
			WHERE a.user_id = %s AND a.mail_id = %s ORDER BY b.send_time ASC",
			array('integer', 'integer'),
			array($this->user_id, $a_mail_id)
		);

		$this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

		return $this->mail_data;
	}

	/**
	 * @param int $a_mail_id
	 * @return array
	 */
	public function getNextMail($a_mail_id)
	{
		$this->db->setLimit(1);

		$res = $this->db->queryF("
			SELECT b.* FROM {$this->table_mail} a
			INNER JOIN {$this->table_mail} b ON b.folder_id = a.folder_id
			AND b.user_id = a.user_id AND b.send_time < a.send_time
			WHERE a.user_id = %s AND a.mail_id = %s ORDER BY b.send_time DESC",
			array('integer', 'integer'),
			array($this->user_id, $a_mail_id)
		);

		$this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

		return $this->mail_data;
	}

	/**
	 * @param  int   $a_folder_id The id of the folder
	 * @param  array $filter      An optional filter array
	 * @return array
	 */
	public function getMailsOfFolder($a_folder_id, $filter = array())
	{
		$output = array();

		$query = " 
			SELECT sender_id, m_subject, mail_id, m_status, send_time FROM {$this->table_mail}
			LEFT JOIN object_data ON obj_id = sender_id
			WHERE user_id = %s AND folder_id = %s
			AND ((sender_id > 0 AND sender_id IS NOT NULL AND obj_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) ";

		if(isset($filter['status']) && strlen($filter['status']) > 0)
		{
			$query .= ' AND m_status = ' . $this->db->quote($filter['status'], 'text');
		}

		$query .= " ORDER BY send_time DESC";

		$res = $this->db->queryF($query,
			array('integer', 'integer'),
			array($this->user_id, $a_folder_id)
		);

		while($row = $this->db->fetchAssoc($res))
		{
			$output[] = $this->fetchMailData($row);
		}

		return $output;
	}

	/**
	 * @param  int $a_folder_id
	 * @return int
	 */
	public function countMailsOfFolder($a_folder_id)
	{
		$res = $this->db->queryF("
			SELECT COUNT(*) FROM {$this->table_mail}
			WHERE user_id = %s AND folder_id = %s",
			array('integer', 'integer'),
			array($this->user_id, $a_folder_id));

		return $this->db->numRows($res);
	}

	/**
	 * @param  int $a_folder_id id of folder
	 * @return bool
	 */
	public function deleteMailsOfFolder($a_folder_id)
	{
		if($a_folder_id)
		{
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
	 * @param  int $a_mail_id
	 * @return array
	 */
	public function getMail($a_mail_id)
	{
		$res = $this->db->queryF("
			SELECT * FROM {$this->table_mail}
			WHERE user_id = %s AND mail_id = %s",
			array('integer', 'integer'),
			array($this->user_id, $a_mail_id));

		$this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

		return $this->mail_data;
	}

	/**
	* @param  array $a_mail_ids
	* @return bool
	*/
	public function markRead(array $a_mail_ids)
	{
		$data       = array();
		$data_types = array();

		$query = "UPDATE {$this->table_mail} SET m_status = %s WHERE user_id = %s ";
		array_push($data_types, 'text', 'integer');
		array_push($data, 'read', $this->user_id);

		if(count($a_mail_ids) > 0)
		{
			$in      = 'mail_id IN (';
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

			$query .= ' AND ' . $in;
		}

		$this->db->manipulateF($query, $data_types, $data);

		return true;
	}

	/**
	* @param array $a_mail_ids
	* @return bool
	*/
	public function markUnread(array $a_mail_ids)
	{
		$data       = array();
		$data_types = array();

		$query = "UPDATE {$this->table_mail} SET m_status = %s WHERE user_id = %s ";
		array_push($data_types, 'text', 'integer');
		array_push($data, 'unread', $this->user_id);

		if(count($a_mail_ids) > 0)
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

		$this->db->manipulateF($query, $data_types, $data);

		return true;
	}

	/**
	* @param int[] $mailIds
	* @param int   $folderId
	* @return bool
	*/
	public function moveMailsToFolder(array $mailIds, int $folderId): bool
	{
		$values = [];
		$dataTypes = [];

		$mailIds = array_filter(array_map('intval', $mailIds));

		if (0 === count($mailIds)) {
			return false;
		}

		$query = "
			UPDATE {$this->table_mail}
			INNER JOIN mail_obj_data
				ON mail_obj_data.obj_id = %s AND mail_obj_data.user_id = %s 
			SET {$this->table_mail}.folder_id = mail_obj_data.obj_id
			WHERE {$this->table_mail}.user_id = %s
		";
		array_push($dataTypes, 'integer', 'integer', 'integer');
		array_push($values, $folderId, $this->user_id, $this->user_id);

		$in = 'mail_id IN (';
		$counter = 0;
		foreach ($mailIds as $mailId) {
			array_push($values, $mailId);
			array_push($dataTypes, 'integer');

			if($counter > 0) $in .= ',';
			$in .= '%s';
			++$counter;
		}
		$in .= ')';

		$query .= ' AND ' . $in;

		$affectedRows = $this->db->manipulateF($query, $dataTypes, $values);

		return $affectedRows > 0;
	}

	/**
	 * @param int[] $mailIds
	 * @return bool
	 */
	public function deleteMails(array $mailIds)
	{
		$mailIds = array_filter(array_map('intval', $mailIds));
		foreach($mailIds as $id) {
			$this->db->manipulateF("
				DELETE FROM {$this->table_mail} WHERE user_id = %s AND mail_id = %s",
				array('integer', 'integer'),
				array($this->user_id, $id)
			);
			$this->mfile->deassignAttachmentFromDirectory($id);
		}

		return true;
	}

	/**
	* @param  array|null
	* @return array|null
	*/
	protected function fetchMailData($a_row)
	{
		if(!is_array($a_row) || empty($a_row))
		{
			return null;
		}

		$a_row['attachments']    = unserialize(stripslashes($a_row['attachments']));
		$a_row['tpl_ctx_params'] = (array)(@json_decode($a_row['tpl_ctx_params'], true));

		return $a_row;
	}

	/**
	 * @param int $usrId
	 * @param int $folderId
	 * @return int
	 */
	public function getNewDraftId($usrId, $folderId)
	{
		$next_id = $this->db->nextId($this->table_mail);
		$this->db->insert($this->table_mail, array(
			'mail_id'        => array('integer', $next_id),
			'user_id'        => array('integer', $usrId),
			'folder_id'      => array('integer', $folderId),
			'sender_id'      => array('integer', $usrId)
		));

		return $next_id;
	}

	public function updateDraft(
		$a_folder_id, $a_attachments, $a_rcp_to, $a_rcp_cc, $a_rcp_bcc,
		$a_m_email, $a_m_subject,  $a_m_message, $a_draft_id = 0,
		$a_use_placeholders = 0, $a_tpl_context_id = null, $a_tpl_context_params = array()
	)
	{
		$this->db->update($this->table_mail,
			array(
				'folder_id'        => array('integer', $a_folder_id),
				'attachments'      => array('clob', serialize($a_attachments)),
				'send_time'        => array('timestamp', date('Y-m-d H:i:s', time())),
				'rcp_to'           => array('clob', $a_rcp_to),
				'rcp_cc'           => array('clob', $a_rcp_cc),
				'rcp_bcc'          => array('clob', $a_rcp_bcc),
				'm_status'         => array('text', 'read'),
				'm_email'          => array('integer', $a_m_email),
				'm_subject'        => array('text', $a_m_subject),
				'm_message'        => array('clob', $a_m_message),
				'use_placeholders' => array('integer', $a_use_placeholders),
				'tpl_ctx_id'       => array('text', $a_tpl_context_id),
				'tpl_ctx_params'   => array('blob', @json_encode((array)$a_tpl_context_params))
			),
			array(
				'mail_id' => array('integer', $a_draft_id)
			)
		);

		return $a_draft_id;
	}

	/**
	* save mail in folder
	* @access	private
	* @param	integer $a_folder_id
	* @param    integer $a_sender_id
	* @param    array $a_attachments
	* @param    string $a_rcp_to
	* @param    string $a_rcp_cc
	* @param    string $a_rcp_bcc
	* @param    string $a_status
	* @param    integer $a_m_email
	* @param    string $a_m_subject
	* @param    string $a_m_message
	* @param    integer $a_user_id
	* @param    integer $a_use_placeholders
	* @param    string|null $a_tpl_context_id
	* @param    array|null  $a_tpl_context_params
	* @return	integer mail_id
	*/
	private function sendInternalMail(
		$a_folder_id, $a_sender_id, $a_attachments, $a_rcp_to, $a_rcp_cc, $a_rcp_bcc,
		$a_status, $a_m_email, $a_m_subject, $a_m_message, $a_user_id = 0,
		$a_use_placeholders = 0, $a_tpl_context_id = null, $a_tpl_context_params = array()
	)
	{
		$a_user_id = $a_user_id ? $a_user_id : $this->user_id;

		if($a_use_placeholders)
		{
			$a_m_message = $this->replacePlaceholders($a_m_message, $a_user_id);
		}
		$a_m_message = $this->formatLinebreakMessage((string)$a_m_message);

		if(!$a_user_id)		$a_user_id = '0';
		if(!$a_folder_id)	$a_folder_id = '0';
		if(!$a_sender_id)	$a_sender_id = NULL;
		if(!$a_attachments)	$a_attachments = NULL;
		if(!$a_rcp_to)		$a_rcp_to = NULL;
		if(!$a_rcp_cc)		$a_rcp_cc = NULL;
		if(!$a_rcp_bcc)		$a_rcp_bcc = NULL;
		if(!$a_status)		$a_status = NULL;
		if(!$a_m_email)		$a_m_email = NULL;
		if(!$a_m_subject)	$a_m_subject = NULL;
		if(!$a_m_message)	$a_m_message = NULL;

		$nextId = $this->db->nextId($this->table_mail);
		$this->db->insert($this->table_mail, array(
			'mail_id'        => array('integer', $nextId),
			'user_id'        => array('integer', $a_user_id),
			'folder_id'      => array('integer', $a_folder_id),
			'sender_id'      => array('integer', $a_sender_id),
			'attachments'    => array('clob', serialize($a_attachments)),
			'send_time'      => array('timestamp', date('Y-m-d H:i:s', time())),
			'rcp_to'         => array('clob', $a_rcp_to),
			'rcp_cc'         => array('clob', $a_rcp_cc),
			'rcp_bcc'        => array('clob', $a_rcp_bcc),
			'm_status'       => array('text', $a_status),
			'm_email'        => array('integer', $a_m_email),
			'm_subject'      => array('text', $a_m_subject),
			'm_message'      => array('clob', $a_m_message),
			'tpl_ctx_id'     => array('text', $a_tpl_context_id),
			'tpl_ctx_params' => array('blob', @json_encode((array)$a_tpl_context_params))
		));

		$raiseEvent = (int)$a_user_id !== (int)$this->mailbox->getUserId();
		if (!$raiseEvent) {
			$raiseEvent = (int)$a_folder_id !== (int)$this->mailbox->getSentFolder();
		}

		if ($raiseEvent) {
			$this->eventHandler->raise('Services/Mail', 'sentInternalMail', [
				'id'          => (int)$nextId,
				'subject'     => (string)$a_m_subject,
				'body'        => (string)$a_m_message,
				'from_usr_id' => (int)$a_sender_id,
				'to_usr_id'   => (int)$a_user_id,
				'rcp_to'      => (string)$a_rcp_to,
				'rcp_cc'      => (string)$a_rcp_cc,
				'rcp_bcc'     => (string)$a_rcp_bcc,
			]);
		}

		return $nextId;
	}

	/**
	 * @param string $message
	 * @param int $usrId
	 * @param boolean $replaceEmptyPlaceholders
	 * @return string
	 */
	protected function replacePlaceholders($message, $usrId = 0, $replaceEmptyPlaceholders = true)
	{
		try {
			if ($this->contextId) {
				$context = \ilMailTemplateContextService::getTemplateContextById($this->contextId);
			} else {
				$context = new \ilMailTemplateGenericContext();
			}

			$user = $usrId > 0 ? $this->getUserInstanceById($usrId) : null;

			$processor = new \ilMailTemplatePlaceholderResolver($context, $message);
			$message = $processor->resolve($user, $this->contextParameters, $replaceEmptyPlaceholders);
		} catch (Exception $e) {
			$this->logger->error(__METHOD__ . ' has been called with invalid context.');
		}

		return $message;
	}

	/**
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 * @param string $subject
	 * @param string $message
	 * @param array $attachments
	 * @param int $sentMailId
	 * @param bool $usePlaceholders
	 * @return bool
	 */
	protected function distributeMail(
		string $to,
		string $cc,
		string $bcc,
		string $subject,
		string $message,
		array $attachments,
		int $sentMailId,
		bool $usePlaceholders = false
	)
	{
		if ($usePlaceholders) {
			$toUsrIds = $this->getUserIds([$to]);
			$this->logger->debug(sprintf(
				"Parsed TO user ids from given recipients for serial letter notification: %s", implode(', ', $toUsrIds)
			));

			$this->sendChanneledMails(
				$to, $cc, $bcc, $toUsrIds, $subject, $message, $attachments, $sentMailId, true
			);

			$otherUsrIds = $this->getUserIds([$cc, $bcc]);
			$this->logger->debug(sprintf(
				"Parsed CC/BCC user ids from given recipients for serial letter notification: %s", implode(', ', $otherUsrIds)
			));

			$this->sendChanneledMails(
				$to, $cc, $bcc, $otherUsrIds, $subject, $this->replacePlaceholders($message, 0, false), $attachments, $sentMailId, false
			);
		} else {
			$usrIds = $this->getUserIds([$to, $cc, $bcc]);
			$this->logger->debug(sprintf(
				"Parsed TO/CC/BCC user ids from given recipients: %s", implode(', ', $usrIds)
			));

			$this->sendChanneledMails(
				$to, $cc, $bcc, $usrIds, $subject, $message, $attachments, $sentMailId, false
			);
		}

		return true;
	}

	/**
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 * @param array $usrIds
	 * @param string $subject
	 * @param string $message
	 * @param array $attachments
	 * @param int $sentMailId
	 * @param bool $usePlaceholders
	 */
	protected function sendChanneledMails(
		string $to,
		string $cc,
		string $bcc,
		array $usrIds,
		string $subject,
		string $message,
		array $attachments,
		int $sentMailId,
		bool $usePlaceholders = false
	) {
		$usrIdToExternalEmailAddressesMap = [];
		$usrIdToMessageMap = [];

		foreach ($usrIds as $usrId) {
			$user = $this->getUserInstanceById($usrId);
			$mailOptions = $this->getMailOptionsByUserId($user->getId());

			$canReadInternalMails = !$user->hasToAcceptTermsOfService() && $user->checkTimeLimit();

			$individualMessage = $message;
			if ($usePlaceholders) {
				$individualMessage = $this->replacePlaceholders($message, $user->getId());;
				$usrIdToMessageMap[$user->getId()] = $individualMessage;
			}

			if ($user->getActive()) {
				$wantsToReceiveExternalEmail = (
					$mailOptions->getIncomingType() == \ilMailOptions::INCOMING_EMAIL ||
					$mailOptions->getIncomingType() == \ilMailOptions::INCOMING_BOTH
				);

				if (!$canReadInternalMails || $wantsToReceiveExternalEmail) {
					$emailAddresses = $mailOptions->getExternalEmailAddresses();
					$usrIdToExternalEmailAddressesMap[$user->getId()] = $emailAddresses;

					if ($mailOptions->getIncomingType() == \ilMailOptions::INCOMING_EMAIL) {
						$this->logger->debug(sprintf(
							"Recipient with id %s will only receive external emails sent to: %s",
							$user->getId(),
							implode(', ', $emailAddresses)
						));
						continue;
					} else {
						$this->logger->debug(sprintf(
							"Recipient with id %s will additionally receive external emails sent to: %s",
							$user->getId(),
							implode(', ', $emailAddresses)
						));
					}
				}
			}

			$mbox = clone $this->mailbox;
			$mbox->setUserId($user->getId());
			$recipientInboxId = $mbox->getInboxFolder();

			$internalMailId = $this->sendInternalMail(
				$recipientInboxId, $this->user_id, $attachments, $to, $cc, '',
				'unread', 0, $subject, $individualMessage, $user->getId(), 0
			);

			if (count($attachments) > 0) {
				$this->mfile->assignAttachmentsToDirectory($internalMailId, $sentMailId);
			}
		}

		$this->delegateExternalEmails(
			$subject, $message, $attachments, $usePlaceholders,
			$usrIdToExternalEmailAddressesMap, $usrIdToMessageMap
		);
	}

	/**
	 * @param string $subject
	 * @param string $message
	 * @param array $attachments
	 * @param bool $usePlaceholders
	 * @param array $usrIdToExternalEmailAddressesMap
	 * @param array $usrIdToMessageMap
	 */
	protected function delegateExternalEmails(
		string $subject,
		string $message,
		array $attachments,
		bool $usePlaceholders,
		array $usrIdToExternalEmailAddressesMap,
		array $usrIdToMessageMap
	) {
		if (1 === count($usrIdToExternalEmailAddressesMap)) {
			if ($usePlaceholders) {
				$message = array_values($usrIdToMessageMap)[0];
			}

			$usrIdToExternalEmailAddressesMap = array_values($usrIdToExternalEmailAddressesMap);
			$firstAddresses = current($usrIdToExternalEmailAddressesMap);

			$this->sendMimeMail(
				implode(',', $firstAddresses), '', '',
				$subject, $this->formatLinebreakMessage($message), (array)$attachments
			);
		} elseif (count($usrIdToExternalEmailAddressesMap) > 1) {
			if ($usePlaceholders) {
				foreach ($usrIdToExternalEmailAddressesMap as $usrId => $addresses) {
					if (0 === count($addresses)) {
						continue;
					}

					$this->sendMimeMail(
						implode(',', $addresses), '', '',
						$subject, $this->formatLinebreakMessage($usrIdToMessageMap[$usrId]), (array)$attachments
					);
				}
			} else {
				$flattenEmailAddresses = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator(
					$usrIdToExternalEmailAddressesMap
				)), false);

				$flattenEmailAddresses = array_unique($flattenEmailAddresses);

				// https://mantis.ilias.de/view.php?id=23981 and https://www.ietf.org/rfc/rfc2822.txt
				$remainingAddresses = '';
				foreach ($flattenEmailAddresses as $emailAddress) {
					$sep = '';
					if (strlen($remainingAddresses) > 0) {
						$sep = ',';
					}

					$recipientsLineLength = \ilStr::strLen($remainingAddresses) + \ilStr::strLen($sep . $emailAddress);
					if ($recipientsLineLength >= $this->maxRecipientCharacterLength) {
						$this->sendMimeMail(
							'', '', $remainingAddresses,
							$subject, $this->formatLinebreakMessage($message), (array)$attachments
						);

						$remainingAddresses = '';
						$sep = '';
					}

					$remainingAddresses .= ($sep . $emailAddress);
				}

				if ('' !== $remainingAddresses) {
					$this->sendMimeMail(
						'', '', $remainingAddresses,
						$subject, $this->formatLinebreakMessage($message), (array)$attachments
					);
				}
			}
		}
	}

	/**
	 * @param string[] $recipients
	 * @return int[]
	 */
	protected function getUserIds(array $recipients): array
	{
		$usrIds = array();

		$joinedRecipients = implode(',', array_filter(array_map('trim', $recipients)));

		$addresses = $this->parseAddresses($joinedRecipients);
		foreach ($addresses as $address) {
			$addressType = $this->mailAddressTypeFactory->getByPrefix($address);
			$usrIds = array_merge($usrIds, $addressType->resolve());
		}

		return array_unique($usrIds);
	}

	/**
	 * @param    string $to
	 * @param    string $cc
	 * @param    string $bcc
	 * @param    string $subject
	 * @return   \ilMailError[] An array of errors determined on validation
	 */
	protected function checkMail(string $to, string $cc, string $bcc, string $subject): array
	{
		$errors = [];

		foreach (array(
			$subject => 'mail_add_subject',
			$to      => 'mail_add_recipient'
		) as $string => $error) {
			if (0 === strlen($string)) {
				$errors[] = new \ilMailError($error);
			}
		}

		return $errors;
	}

	/**
	 * Check if recipients are valid
	 * @param  string $recipients
	 * @return \ilMailError[] An array of errors determined on validation
	 * @throws \ilMailException
	 */
	protected function checkRecipients(string $recipients): array
	{
		$errors = [];

		try {
			$addresses = $this->parseAddresses($recipients);
			foreach ($addresses as $address) {
				$addressType = $this->mailAddressTypeFactory->getByPrefix($address);
				if (!$addressType->validate($this->user_id)) {
					$newErrors = $addressType->getErrors();
					$errors = array_merge($errors, $newErrors);
				}
			}
		} catch (\ilException $e) {
			$colonPosition = strpos($e->getMessage(), ':');
			throw new \ilMailException(
				($colonPosition === false) ? $e->getMessage() : substr($e->getMessage(), $colonPosition + 2)
			);
		}

		return $errors;
	}

	/**
	* save post data in table
	* @access	public
	* @param    int $a_user_id
	* @param    array $a_attachments
	* @param    string $a_rcp_to
	* @param    string $a_rcp_cc
	* @param    string $a_rcp_bcc
	* @param    int $a_m_email
	* @param    string $a_m_subject
	* @param    string $a_m_message
	* @param    int $a_use_placeholders
	* @param    string|null $a_tpl_context_id
	* @param    array|null $a_tpl_ctx_params
	* @return	bool
	*/
	public function savePostData(
		$a_user_id, $a_attachments, $a_rcp_to, $a_rcp_cc, $a_rcp_bcc,
		$a_m_email, $a_m_subject, $a_m_message,
		$a_use_placeholders, $a_tpl_context_id = null, $a_tpl_ctx_params = array()
	)
	{
		if(!$a_attachments) $a_attachments = NULL;
		if(!$a_rcp_to) $a_rcp_to = NULL;
		if(!$a_rcp_cc) $a_rcp_cc = NULL;
		if(!$a_rcp_bcc) $a_rcp_bcc = NULL;
		if(!$a_m_email) $a_m_email = NULL;
		if(!$a_m_message) $a_m_message = NULL;
		if(!$a_use_placeholders) $a_use_placeholders = '0';

		$this->db->replace(
			$this->table_mail_saved,
			array(
				'user_id' => array('integer', $this->user_id)
			),
			array(
				'attachments'      => array('clob', serialize($a_attachments)),
				'rcp_to'           => array('clob', $a_rcp_to),
				'rcp_cc'           => array('clob', $a_rcp_cc),
				'rcp_bcc'          => array('clob', $a_rcp_bcc),
				'm_email'          => array('integer', $a_m_email),
				'm_subject'        => array('text', $a_m_subject),
				'm_message'        => array('clob', $a_m_message),
				'use_placeholders' => array('integer', $a_use_placeholders),
				'tpl_ctx_id'       => array('text', $a_tpl_context_id),
				'tpl_ctx_params'   => array('blob', json_encode((array)$a_tpl_ctx_params))
			)
		);

		$this->getSavedData();

		return true;
	}

	/**
	 * @return array
	 */
	public function getSavedData()
	{
		$res = $this->db->queryF(
			"SELECT * FROM {$this->table_mail_saved} WHERE user_id = %s",
			array('integer'),
			array($this->user_id)
		);

		$this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

		return $this->mail_data;
	}

	/**
	 * Should be used to enqueue a 'mail'. A validation is executed before, errors are returned
	 * @param string   $a_rcp_to
	 * @param string   $a_rcp_cc
	 * @param string   $a_rcp_bcc
	 * @param string   $a_m_subject
	 * @param string   $a_m_message
	 * @param array    $a_attachment
	 * @param bool|int $a_use_placeholders
	 * @return \ilMailError[] 
	 */
	public function validateAndEnqueue($a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_m_subject, $a_m_message, $a_attachment, $a_use_placeholders = 0): array
	{
		global $DIC;

		$this->logger->debug(
			"New mail system task:" .
			" To: " . $a_rcp_to .
			" | CC: " . $a_rcp_cc .
			" | BCC: " . $a_rcp_bcc .
			" | Subject: " . $a_m_subject
		);

		if ($a_attachment && !$this->mfile->checkFilesExist($a_attachment)) {
			return [new \ilMailError('mail_attachment_file_not_exist', [$a_attachment])];
		}

		$errors = $this->checkMail((string)$a_rcp_to, (string)$a_rcp_cc, (string)$a_rcp_bcc, (string)$a_m_subject);
		if (count($errors) > 0) {
			return $errors;
		}

		$errors = $this->validateRecipients((string)$a_rcp_to, (string)$a_rcp_cc, (string)$a_rcp_bcc);
		if (count($errors) > 0) {
			return $errors;
		}

		$rcp_to = $a_rcp_to;
		$rcp_cc = $a_rcp_cc;
		$rcp_bcc = $a_rcp_bcc;

		if (null === $rcp_cc) {
			$rcp_cc = '';
		}

		if (null === $rcp_bcc) {
			$rcp_bcc = '';
		}

		$numberOfExternalAddresses = $this->getCountRecipients($rcp_to, $rcp_cc, $rcp_bcc, true);
		if(
			$numberOfExternalAddresses > 0 &&
			!$this->isSystemMail() &&
			!$DIC->rbac()->system()->checkAccessOfUser($this->user_id, 'smtp_mail', $this->mail_obj_ref_id)
		) {
			return [new \ilMailError('mail_no_permissions_write_smtp')];
		}

		if ($this->appendInstallationSignature()) {
			$a_m_message .= self::_getInstallationSignature();
		}

		if (\ilContext::getType() == \ilContext::CONTEXT_CRON) {
			return $this->sendMail(
				(string)$rcp_to,
				(string)$rcp_cc,
				(string)$rcp_bcc,
				(string)$a_m_subject,
				(string)$a_m_message,
				(array)$a_attachment,
				(bool)$a_use_placeholders
			);
		}

		$taskFactory = $DIC->backgroundTasks()->taskFactory();
		$taskManager = $DIC->backgroundTasks()->taskManager();

		$bucket = new \ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket();
		$bucket->setUserId($this->user_id);

		$task = $taskFactory->createTask(\ilMailDeliveryJob::class, [
			(int)$this->user_id,
			(string)$rcp_to,
			(string)$rcp_cc,
			(string)$rcp_bcc,
			(string)$a_m_subject,
			(string)$a_m_message,
			serialize($a_attachment),
			(bool)$a_use_placeholders,
			(bool)$this->getSaveInSentbox(),
			(string)$this->contextId,
			serialize($this->contextParameters)
		]);
		$interaction = $taskFactory->createTask(\ilMailDeliveryJobUserInteraction::class, [
			$task,
			(int)$this->user_id
		]);

		$bucket->setTask($interaction);
		$bucket->setTitle($this->lng->txt('mail_bg_task_title'));
		$bucket->setDescription(sprintf($this->lng->txt('mail_bg_task_desc'), $a_m_subject));

		$this->logger->info('Delegated delivery to background task');
		$taskManager->run($bucket);

		return [];
	}

	/**
	 * This method is used to finally send internal messages and external emails
	 * To use the mail system as a consumer, please use \ilMail::validateAndEnqueue
	 * @see \ilMail::validateAndEnqueue()
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 * @param string $subject
	 * @param string $message
	 * @param array $attachments
	 * @param bool $usePlaceholders
	 * @return \ilMailError[]
	 * @internal 
	 */
	public function sendMail(
		string $to,
		string $cc,
		string $bcc,
		string $subject,
		string $message,
		array $attachments,
		bool $usePlaceholders
	) {
		$internalMessageId = $this->saveInSentbox(
			$attachments,
			$to,
			$cc,
			$bcc,
			$subject,
			$message
		);

		if (count($attachments) > 0) {
			$this->mfile->assignAttachmentsToDirectory($internalMessageId, $internalMessageId);
			$this->mfile->saveFiles($internalMessageId, $attachments);
		}

		$numberOfExternalAddresses = $this->getCountRecipients($to, $cc, $bcc, true);

		if ($numberOfExternalAddresses > 0) {
			$externalMailRecipientsTo  = $this->getEmailRecipients($to);
			$externalMailRecipientsCc  = $this->getEmailRecipients($cc);
			$externalMailRecipientsBcc = $this->getEmailRecipients($bcc);

			$this->logger->debug(
				"Parsed external email addresses from given recipients /" .
				" To: " . $externalMailRecipientsTo .
				" | CC: " . $externalMailRecipientsCc .
				" | BCC: " . $externalMailRecipientsBcc .
				" | Subject: " . $subject
			);

			$this->sendMimeMail(
				$externalMailRecipientsTo,
				$externalMailRecipientsCc,
				$externalMailRecipientsBcc,
				$subject,
				$this->formatLinebreakMessage(
					$usePlaceholders ? $this->replacePlaceholders($message, 0, false) : $message
				),
				$attachments
			);
		} else {
			$this->logger->debug('No external email addresses given in recipient string');
		}

		$errors = [];

		if (!$this->distributeMail(
			$to,
			$cc,
			$bcc,
			$subject,
			$message,
			$attachments,
			$internalMessageId,
			$usePlaceholders
		)) {
			$errors['mail_send_error'] = new \ilMailError('mail_send_error');
		}

		if (!$this->getSaveInSentbox()) {
			$this->deleteMails([$internalMessageId]);
		}

		return array_values($errors);
	}

	/**
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 * @return \ilMailError[] An array of errors determined on validation
	 */
	public function validateRecipients(string $to, string $cc, string $bcc): array
	{
		try {
			$errors = array();
			$errors = array_merge($errors, $this->checkRecipients($to));
			$errors = array_merge($errors, $this->checkRecipients($cc));
			$errors = array_merge($errors, $this->checkRecipients($bcc));

			if (count($errors) > 0) {
				return array_merge([new \ilMailError('mail_following_rcp_not_valid')], $errors);
			}
		} catch (\ilMailException $e) {
			return [new \ilMailError('mail_generic_rcp_error', [$e->getMessage()])];
		}

		return [];
	}

	/**
	 * Stores a message in the sent bod of the current user
	 * @param array  $a_attachment
	 * @param string $a_rcp_to
	 * @param string $a_rcp_cc
	 * @param string $a_rcp_bcc
	 * @param string $a_m_subject
	 * @param string $a_m_message
	 * @return int mail id
	 */
	protected function saveInSentbox($a_attachment, $a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_m_subject, $a_m_message)
	{
		return $this->sendInternalMail(
			$this->mailbox->getSentFolder(), $this->user_id, $a_attachment, 
			$a_rcp_to,$a_rcp_cc, $a_rcp_bcc,
			'read', 0,
			$a_m_subject, $a_m_message, $this->user_id, 0
		);
	}

	/**
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 * @param string $subject
	 * @param string $message
	 * @param array  $attachments
	 */
	private function sendMimeMail(string $to, string $cc, string $bcc, $subject, $message, array $attachments)
	{
		$mailer = new \ilMimeMail();
		$mailer->From($this->senderFactory->getSenderByUsrId((int)$this->user_id));
		$mailer->To($to);
		$mailer->Subject($subject, true);
		$mailer->Body($message);

		if ($cc) {
			$mailer->Cc($cc);
		}

		if ($bcc) {
			$mailer->Bcc($bcc);
		}

		foreach ($attachments as $attachment) {
			$mailer->Attach(
				$this->mfile->getAbsoluteAttachmentPoolPathByFilename($attachment), '',
				'inline', 	$attachment
			);
		}

		$mailer->Send();
	}

	/**
	 * @param array $a_attachments An array of attachments
	 * @return bool
	 */
	public function saveAttachments($a_attachments)
	{
		$this->db->update($this->table_mail_saved,
			array(
				'attachments' => array('clob', serialize($a_attachments))
			),
			array(
				'user_id' => array('integer', $this->user_id)
			)
		);

		return true;
	}

	/**
	 * Explode recipient string, allowed separators are ',' ';' ' '
	 * Returns an array with recipient ilMailAddress instances
	 * @param string $addresses
	 * @return ilMailAddress[] An array with objects of type ilMailAddress
	 */
	protected function parseAddresses($addresses)
	{
		if (strlen($addresses) > 0) {
			$this->logger->debug(sprintf(
				"Started parsing of recipient string: %s", $addresses
			));
		}

		$parser = $this->mailAddressParserFactory->getParser((string)$addresses);
		$parsedAddresses = $parser->parse();

		if (strlen($addresses) > 0) {
			$this->logger->debug(sprintf(
				"Parsed addresses: %s", implode(',', array_map(function (ilMailAddress $address) {
					return (string)$address;
				}, $parsedAddresses))
			));
		}

		return $parsedAddresses;
	}

	/**
	 * @param string $recipients
	 * @param bool $onlyExternalAddresses
	 * @return int
	 */
	protected function getCountRecipient(string $recipients, $onlyExternalAddresses = true): int
	{
		$addresses = new \ilMailAddressListImpl($this->parseAddresses($recipients));
		if ($onlyExternalAddresses) {
			$addresses = new \ilMailOnlyExternalAddressList(
				$addresses,
				self::ILIAS_HOST,
				$this->usrIdByLoginCallable
			);
		}

		return count($addresses->value());
	}

	/**
	 * @param string $toRecipients
	 * @param string $ccRecipients
	 * @param $bccRecipients
	 * @param bool $onlyExternalAddresses
	 * @return int
	 */
	protected function getCountRecipients(
		string $toRecipients,
		string $ccRecipients,
		string $bccRecipients,
		$onlyExternalAddresses = true
	): int {
		return (
			$this->getCountRecipient($toRecipients, $onlyExternalAddresses) +
			$this->getCountRecipient($ccRecipients, $onlyExternalAddresses) +
			$this->getCountRecipient($bccRecipients, $onlyExternalAddresses)
		);
	}

	/**
	 * @param string $recipients
	 * @return string
	 */
	protected function getEmailRecipients(string $recipients): string 
	{
		$addresses = new \ilMailOnlyExternalAddressList(
			new \ilMailAddressListImpl($this->parseAddresses($recipients)),
			self::ILIAS_HOST,
			$this->usrIdByLoginCallable
		);

		$emailRecipients = array_map(function(\ilMailAddress $address) {
			return (string)$address;
		}, $addresses->value());

		return implode(',', $emailRecipients);
	}

	/**
	 * Get auto generated info string
	 * @param ilLanguage $lang
	 * @return string;
	 */
	public static function _getAutoGeneratedMessageString(ilLanguage $lang = null)
	{
		global $DIC;

		if(!($lang instanceof ilLanguage))
		{
			$lang = ilLanguageFactory::_getLanguage();
		}

		$lang->loadLanguageModule('mail');

		return sprintf(
			$lang->txt('mail_auto_generated_info'),
			$DIC->settings()->get('inst_name', 'ILIAS 6'),
			ilUtil::_getHttpPath()
		). "\n\n";
	}

	/**
	 * Get the name used for mails sent by the anonymous user
	 * @return string Name of sender
	 */
	public static function _getIliasMailerName()
	{
		/** @var ilMailMimeSenderFactory $senderFactory */
		$senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

		return $senderFactory->system()->getFromName();
	}

	/**
	 * Setter/Getter for appending the installation signarue
	 * @param    mixed    boolean or nothing
	 * @return    mixed    boolean if called without passing any params, otherwise $this
	 */
	public function appendInstallationSignature($a_flag = null)
	{
		if(null === $a_flag)
		{
			return $this->appendInstallationSignature;
		}

		$this->appendInstallationSignature = $a_flag;
		return $this;
	}

	/**
	 * @return string The installation mail signature
	 */
	public static function _getInstallationSignature()
	{
		global $DIC;

		$signature = $DIC->settings()->get('mail_system_sys_signature');

		$clientUrl = ilUtil::_getHttpPath();
		$clientdirs = glob(ILIAS_WEB_DIR . '/*', GLOB_ONLYDIR);
		if(is_array($clientdirs) && count($clientdirs) > 1)
		{
			$clientUrl .= '/login.php?client_id=' . CLIENT_ID; // #18051
		}

		$signature = str_ireplace('[CLIENT_NAME]', $DIC['ilClientIniFile']->readVariable('client', 'name'), $signature);
		$signature = str_ireplace('[CLIENT_DESC]', $DIC['ilClientIniFile']->readVariable('client', 'description'), $signature);
		$signature = str_ireplace('[CLIENT_URL]', $clientUrl, $signature);

		if(!preg_match('/^[\n\r]+/', $signature))
		{
			$signature = "\n" . $signature;
		}

		return $signature;
	}

	/**
	 * @param int $a_usr_id
	 * @param     $a_language ilLanguage|null
	 * @return string
	 */
	public static function getSalutation($a_usr_id, ilLanguage $a_language = null)
	{
		global $DIC;

		$lang = ($a_language instanceof ilLanguage) ? $a_language : $DIC->language();
		$lang->loadLanguageModule('mail');

		$gender = ilObjUser::_lookupGender($a_usr_id);
		$gender = $gender ? $gender : 'n';
		$name   = ilObjUser::_lookupName($a_usr_id);

		if(!strlen($name['firstname']))
		{
			return $lang->txt('mail_salutation_anonymous') . ',';
		}

		return
			$lang->txt('mail_salutation_' . $gender) . ' ' .
			($name['title'] ? $name['title'] . ' ' : '') .
			($name['firstname'] ? $name['firstname'] . ' ' : '') .
			$name['lastname'] . ',';
	}

	/**
	 * @param int $usrId
	 * @return \ilObjUser
	 */
	protected function getUserInstanceById(int $usrId): \ilObjUser
	{
		if (!isset($this->userInstancesByIdMap[$usrId])) {
			$this->userInstancesByIdMap[$usrId] = new \ilObjUser($usrId);
		}

		return $this->userInstancesByIdMap[$usrId];
	}

	/**
	 * @param \ilObjUser[] $userInstanceByIdMap
	 * @internal
	 */
	public function setUserInstanceById(array $userInstanceByIdMap)
	{
		$this->userInstancesByIdMap = $userInstanceByIdMap;
	}

	/**
	 * @param int $usrId
	 * @return \ilMailOptions
	 */
	protected function getMailOptionsByUserId(int $usrId): \ilMailOptions
	{
		if (!isset($this->mailOptionsByUsrIdMap[$usrId])) {
			$this->mailOptionsByUsrIdMap[$usrId] = new \ilMailOptions($usrId);
		}

		return $this->mailOptionsByUsrIdMap[$usrId];
	}

	/**
	 * @param \ilMailOptions[] $mailOptionsByUsrIdMap
	 * @internal
	 */
	public function setMailOptionsByUserIdMap(array $mailOptionsByUsrIdMap)
	{
		$this->mailOptionsByUsrIdMap = $mailOptionsByUsrIdMap;
	}

	/**
	 * @inheritdoc
	 */
	public function formatLinebreakMessage(string $message): string 
	{
		return $message;
	}
}
