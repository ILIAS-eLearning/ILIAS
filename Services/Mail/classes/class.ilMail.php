<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilObjUser.php';
require_once 'Services/Mail/exceptions/class.ilMailException.php';

/**
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilMail
{
    /** @var string */
    const ILIAS_HOST = 'ilias';

    const MAIL_SUBJECT_PREFIX = '[ILIAS]';

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

    protected $soap_enabled = true;
    protected $appendInstallationSignature = false;

    /** @var ilAppEventHandler */
    private $eventHandler;

    /**
     * Used to store properties which should be set/get at runtime
     * @var array
     */
    protected $properties = array();
    
    /** @var ilObjUser[] */
    protected static $userInstances = array();

    /** @var ilMailAddressTypeFactory */
    private $mailAddressTypeFactory;

    /** @var ilMailRfc822AddressParserFactory */
    private $mailAddressParserFactory;

    /** @var mixed|null */
    protected $contextId = null;

    /**
     * @var array
     */
    protected $contextParameters = [];

    /**
     * @param integer                               $a_user_id
     * @param ilMailAddressTypeFactory|null         $mailAddressTypeFactory
     * @param ilMailRfc822AddressParserFactory|null $mailAddressParserFactory
     * @param ilAppEventHandler|null                $eventHandler
     */
    public function __construct(
        $a_user_id,
        ilMailAddressTypeFactory $mailAddressTypeFactory = null,
        ilMailRfc822AddressParserFactory $mailAddressParserFactory = null,
        \ilAppEventHandler $eventHandler = null
    ) {
        global $DIC;

        require_once 'Services/Mail/classes/class.ilFileDataMail.php';
        require_once 'Services/Mail/classes/class.ilMailOptions.php';

        if ($mailAddressTypeFactory === null) {
            $mailAddressTypeFactory = new ilMailAddressTypeFactory();
        }

        if ($mailAddressParserFactory === null) {
            $mailAddressParserFactory = new ilMailRfc822AddressParserFactory();
        }

        if ($eventHandler === null) {
            $eventHandler = $DIC->event();
        }

        $this->mailAddressParserFactory = $mailAddressParserFactory;
        $this->mailAddressTypeFactory = $mailAddressTypeFactory;
        $this->eventHandler = $eventHandler;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();

        $this->lng->loadLanguageModule('mail');

        $this->table_mail = 'mail';
        $this->table_mail_saved = 'mail_saved';

        $this->user_id = $a_user_id;

        $this->mfile = new ilFileDataMail($this->user_id);
        $this->mail_options = new ilMailOptions($a_user_id);
        $this->mailbox = new ilMailbox($this->user_id);

        $this->setSaveInSentbox(false);
        $this->readMailObjectReferenceId();
    }

    /**
     * @param string $contextId
     * @return ilMail
     */
    public function withContextId(string $contextId) : self
    {
        $clone = clone $this;

        $clone->contextId = $contextId;

        return $clone;
    }

    /**
     * @param array $parameters
     * @return ilMail
     */
    public function withContextParameters(array $parameters) : self
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
     * Magic interceptor method __get
     * Used to include files / instantiate objects at runtime.
     * @param string $name The name of the class property
     * @return ilMailingLists
     */
    public function __get($name)
    {
        global $DIC;

        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        if ($name == 'mlists') {
            if (is_object($DIC->user())) {
                require_once 'Services/Contact/classes/class.ilMailingLists.php';
                $this->properties[$name] = new ilMailingLists($DIC->user());
                return $this->properties[$name];
            }
        }
    }

    /**
     * @param string $newRecipient
     * @param string $existingRecipients
     * @return bool
     */
    public function existsRecipient(string $newRecipient, string $existingRecipients) : bool
    {
        $newAddresses = new \ilMailAddressListImpl($this->parseAddresses($newRecipient));
        $addresses = new \ilMailAddressListImpl($this->parseAddresses($existingRecipients));

        $list = new \ilMailDiffAddressList($newAddresses, $addresses);

        $diffedAddresses = $list->value();

        return count($diffedAddresses) === 0;
    }

    /**
    * Define if external mails should be sent using SOAP client or not.
    * The autogenerated mails in new user registration sets this value to false, since there is no valid session.
    * @param bool $a_status
    */
    public function enableSOAP($a_status)
    {
        $this->soap_enabled = (bool) $a_status;
    }

    /**
     * @return bool
     */
    public function isSOAPEnabled()
    {
        global $DIC;

        if (!extension_loaded('curl') || !$DIC->settings()->get('soap_user_administration')) {
            return false;
        }

        if (ilContext::getType() == ilContext::CONTEXT_CRON) {
            return false;
        }

        return (bool) $this->soap_enabled;
    }

    /**
     * @param bool $a_save_in_sentbox
     */
    public function setSaveInSentbox($a_save_in_sentbox)
    {
        $this->save_in_sentbox = (bool) $a_save_in_sentbox;
    }

    /**
     * @return bool
     */
    public function getSaveInSentbox()
    {
        return (bool) $this->save_in_sentbox;
    }

    /**
     * Read and set the mail object ref id (administration node)
     */
    protected function readMailObjectReferenceId()
    {
        require_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
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
        if (!strlen($a_recipients)) {
            return $this->lng->txt('not_available');
        }

        $names = array();

        $recipients = array_filter(array_map('trim', explode(',', $a_recipients)));
        foreach ($recipients as $recipient) {
            $usr_id = ilObjUser::_lookupId($recipient);
            if ($usr_id > 0) {
                $pp = ilObjUser::_lookupPref($usr_id, 'public_profile');
                if ($pp == 'y' || ($pp == 'g' && !$DIC->user()->isAnonymous())) {
                    $user = self::getCachedUserInstance($usr_id);
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

        $res = $this->db->queryF(
            "
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

        $res = $this->db->queryF(
            "
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

        if (isset($filter['status']) && strlen($filter['status']) > 0) {
            $query .= ' AND m_status = ' . $this->db->quote($filter['status'], 'text');
        }

        if (isset($filter['type']) && strlen($filter['type']) > 0) {
            $query .= ' AND ' . $this->db->like('m_type', 'text', '%%:"' . $filter['type'] . '"%%', false);
        }

        $query .= " ORDER BY send_time DESC";

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer'),
            array($this->user_id, $a_folder_id)
        );

        while ($row = $this->db->fetchAssoc($res)) {
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
        $res = $this->db->queryF(
            "
			SELECT COUNT(*) FROM {$this->table_mail}
			WHERE user_id = %s AND folder_id = %s",
            array('integer', 'integer'),
            array($this->user_id, $a_folder_id)
        );

        return $this->db->numRows($res);
    }

    /**
     * @param  int $a_folder_id id of folder
     * @return bool
     */
    public function deleteMailsOfFolder($a_folder_id)
    {
        if ($a_folder_id) {
            $mails = $this->getMailsOfFolder($a_folder_id);
            foreach ((array) $mails as $mail_data) {
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
        $res = $this->db->queryF(
            "
			SELECT * FROM {$this->table_mail}
			WHERE user_id = %s AND mail_id = %s",
            array('integer', 'integer'),
            array($this->user_id, $a_mail_id)
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    /**
    * @param  array $a_mail_ids
    * @return bool
    */
    public function markRead(array $a_mail_ids)
    {
        $data = array();
        $data_types = array();

        $query = "UPDATE {$this->table_mail} SET m_status = %s WHERE user_id = %s ";
        array_push($data_types, 'text', 'integer');
        array_push($data, 'read', $this->user_id);

        if (count($a_mail_ids) > 0) {
            $in = 'mail_id IN (';
            $counter = 0;
            foreach ($a_mail_ids as $a_mail_id) {
                array_push($data, $a_mail_id);
                array_push($data_types, 'integer');

                if ($counter > 0) {
                    $in .= ',';
                }
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
        $data = array();
        $data_types = array();

        $query = "UPDATE {$this->table_mail} SET m_status = %s WHERE user_id = %s ";
        array_push($data_types, 'text', 'integer');
        array_push($data, 'unread', $this->user_id);

        if (count($a_mail_ids) > 0) {
            $in = 'mail_id IN (';
            $counter = 0;
            foreach ($a_mail_ids as $a_mail_id) {
                array_push($data, $a_mail_id);
                array_push($data_types, 'integer');

                if ($counter > 0) {
                    $in .= ',';
                }
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
    * @param int[] $mailIds
    * @param int   $folderId
    * @return bool
    */
    public function moveMailsToFolder(array $mailIds, int $folderId) : bool
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

            if ($counter > 0) {
                $in .= ',';
            }
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
        foreach ($mailIds as $id) {
            $this->db->manipulateF(
                "
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
        if (!is_array($a_row) || empty($a_row)) {
            return null;
        }

        $a_row['attachments'] = unserialize(stripslashes($a_row['attachments']));
        $a_row['m_type'] = unserialize(stripslashes($a_row['m_type']));
        $a_row['tpl_ctx_params'] = (array) (@json_decode($a_row['tpl_ctx_params'], true));

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
            'mail_id' => array('integer', $next_id),
            'user_id' => array('integer', $usrId),
            'folder_id' => array('integer', $folderId),
            'sender_id' => array('integer', $usrId)
        ));

        return $next_id;
    }

    public function updateDraft(
        $a_folder_id,
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
    ) {
        $this->db->update(
            $this->table_mail,
            array(
                'folder_id' => array('integer', $a_folder_id),
                'attachments' => array('clob', serialize($a_attachments)),
                'send_time' => array('timestamp', date('Y-m-d H:i:s', time())),
                'rcp_to' => array('clob', $a_rcp_to),
                'rcp_cc' => array('clob', $a_rcp_cc),
                'rcp_bcc' => array('clob', $a_rcp_bcc),
                'm_status' => array('text', 'read'),
                'm_type' => array('text', serialize($a_m_type)),
                'm_email' => array('integer', $a_m_email),
                'm_subject' => array('text', $a_m_subject),
                'm_message' => array('clob', $a_m_message),
                'use_placeholders' => array('integer', $a_use_placeholders),
                'tpl_ctx_id' => array('text', $a_tpl_context_id),
                'tpl_ctx_params' => array('blob', @json_encode((array) $a_tpl_context_params))
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
    * @param    array  $a_m_type
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
        $a_folder_id,
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
    ) {
        $a_user_id = $a_user_id ? $a_user_id : $this->user_id;

        if ($a_use_placeholders) {
            $a_m_message = $this->replacePlaceholders($a_m_message, $a_user_id);
        }
        $a_m_message = $this->formatLinebreakMessage($a_m_message);

        if (!$a_user_id) {
            $a_user_id = '0';
        }
        if (!$a_folder_id) {
            $a_folder_id = '0';
        }
        if (!$a_sender_id) {
            $a_sender_id = null;
        }
        if (!$a_attachments) {
            $a_attachments = null;
        }
        if (!$a_rcp_to) {
            $a_rcp_to = null;
        }
        if (!$a_rcp_cc) {
            $a_rcp_cc = null;
        }
        if (!$a_rcp_bcc) {
            $a_rcp_bcc = null;
        }
        if (!$a_status) {
            $a_status = null;
        }
        if (!$a_m_type) {
            $a_m_type = null;
        }
        if (!$a_m_email) {
            $a_m_email = null;
        }
        if (!$a_m_subject) {
            $a_m_subject = null;
        }
        if (!$a_m_message) {
            $a_m_message = null;
        }

        $nextId = $this->db->nextId($this->table_mail);
        $this->db->insert($this->table_mail, array(
            'mail_id' => array('integer', $nextId),
            'user_id' => array('integer', $a_user_id),
            'folder_id' => array('integer', $a_folder_id),
            'sender_id' => array('integer', $a_sender_id),
            'attachments' => array('clob', serialize($a_attachments)),
            'send_time' => array('timestamp', date('Y-m-d H:i:s', time())),
            'rcp_to' => array('clob', $a_rcp_to),
            'rcp_cc' => array('clob', $a_rcp_cc),
            'rcp_bcc' => array('clob', $a_rcp_bcc),
            'm_status' => array('text', $a_status),
            'm_type' => array('text', serialize($a_m_type)),
            'm_email' => array('integer', $a_m_email),
            'm_subject' => array('text', $a_m_subject),
            'm_message' => array('clob', $a_m_message),
            'tpl_ctx_id' => array('text', $a_tpl_context_id),
            'tpl_ctx_params' => array('blob', @json_encode((array) $a_tpl_context_params))
        ));

        $raiseEvent = (int) $a_user_id !== (int) $this->mailbox->getUserId();
        if (!$raiseEvent) {
            $raiseEvent = (int) $a_folder_id !== (int) $this->mailbox->getSentFolder();
        }

        if ($raiseEvent) {
            $this->eventHandler->raise('Services/Mail', 'sentInternalMail', [
                'id' => (int) $nextId,
                'subject' => (string) $a_m_subject,
                'body' => (string) $a_m_message,
                'from_usr_id' => (int) $a_sender_id,
                'to_usr_id' => (int) $a_user_id,
                'rcp_to' => (string) $a_rcp_to,
                'rcp_cc' => (string) $a_rcp_cc,
                'rcp_bcc' => (string) $a_rcp_bcc,
                'type' => (array) $a_m_type,
            ]);
        }

        return $nextId;
    }

    /**
     * @param string $a_message
     * @param int $a_user_id
     * @param boolean $replace_empty
     * @return string
     */
    protected function replacePlaceholders($a_message, $a_user_id = 0, $replace_empty = true)
    {
        try {
            if ($this->contextId) {
                $context = ilMailTemplateContextService::getTemplateContextById($this->contextId);
            } else {
                $context = new ilMailTemplateGenericContext();
            }

            $user = $a_user_id > 0 ? self::getCachedUserInstance($a_user_id) : null;

            $processor = new ilMailTemplatePlaceholderResolver($context, $a_message);
            $a_message = $processor->resolve($user, $this->contextParameters, $replace_empty);
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('mail')->error(__METHOD__ . ' has been called with invalid context.');
        }

        return $a_message;
    }

    /**
     * @param string  $a_rcp_to
     * @param string  $a_rcp_cc
     * @param string  $a_rcp_bcc
     * @param string  $a_subject
     * @param string  $a_message
     * @param array   $a_attachments
     * @param integer $sent_mail_id
     * @param array   $a_type
     * @param array   $a_action
     * @param array|int $a_use_placeholders
     * @return bool
     */
    protected function distributeMail($a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_subject, $a_message, $a_attachments, $sent_mail_id, $a_type, $a_action, $a_use_placeholders = 0)
    {
        require_once 'Services/Mail/classes/class.ilMailbox.php';
        require_once 'Services/User/classes/class.ilObjUser.php';

        $mbox = new ilMailbox();
        if (!$a_use_placeholders) {
            $rcp_ids = $this->getUserIds(array($a_rcp_to, $a_rcp_cc, $a_rcp_bcc));

            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                "Parsed TO/CC/BCC user ids from given recipients: %s",
                implode(', ', $rcp_ids)
            ));

            $as_email = array();

            foreach ($rcp_ids as $id) {
                $tmp_mail_options = new ilMailOptions($id);

                $tmp_user = self::getCachedUserInstance($id);
                $user_is_active = $tmp_user->getActive();
                $user_can_read_internal_mails = !$tmp_user->hasToAcceptTermsOfService() && $tmp_user->checkTimeLimit();

                if (in_array('system', $a_type) && !$user_can_read_internal_mails) {
                    ilLoggerFactory::getLogger('mail')->debug(sprintf(
                        "Message is marked as 'system', skipped recipient with id %s (Accepted User Agreement:%s|Expired Account:%s)",
                        $id,
                        var_export(!$tmp_user->hasToAcceptTermsOfService(), 1),
                        var_export(!$tmp_user->checkTimeLimit(), 1)
                    ));
                    continue;
                }

                if ($user_is_active) {
                    if (!$user_can_read_internal_mails
                        || $tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_EMAIL
                        || $tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_BOTH) {
                        $newEmailAddresses = ilMailOptions::getExternalEmailsByUser($tmp_user, $tmp_mail_options);
                        $as_email = array_unique(array_merge($newEmailAddresses, $as_email));

                        if ($tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_EMAIL) {
                            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                                "Recipient with id %s will only receive external emails sent to: %s",
                                $id,
                                implode(', ', $newEmailAddresses)
                            ));
                            continue;
                        } else {
                            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                                "Recipient with id %s will additionally receive external emails sent to: %s",
                                $id,
                                implode(', ', $newEmailAddresses)
                            ));
                        }
                    }
                }

                $mbox->setUserId($id);
                $inbox_id = $mbox->getInboxFolder();

                $mail_id = $this->sendInternalMail(
                    $inbox_id,
                    $this->user_id,
                    $a_attachments,
                    $a_rcp_to,
                    $a_rcp_cc,
                    '',
                    'unread',
                    $a_type,
                    0,
                    $a_subject,
                    $a_message,
                    $id,
                    0
                );

                if ($a_attachments) {
                    $this->mfile->assignAttachmentsToDirectory($mail_id, $sent_mail_id);
                }
            }

            $to = array();
            $bcc = array();
            
            $as_email = array_values(array_unique($as_email));
            if (count($as_email) == 1) {
                $to[] = $as_email[0];
            } else {
                foreach ($as_email as $email) {
                    $bcc[] = $email;
                }
            }

            if (count($to) > 0) {
                $this->sendMimeMail(implode(',', $to), '', implode(',', $bcc), $a_subject, $this->formatLinebreakMessage($a_message), $a_attachments);
            } elseif (count($bcc) > 0) {
                $remainingAddresses = '';
                $maxRecipientCharacterLength = 998;
                foreach ($bcc as $emailAddress) {
                    $sep = '';
                    if (strlen($remainingAddresses) > 0) {
                        $sep = ',';
                    }
    
                    $recipientsLineLength = ilStr::strLen($remainingAddresses) + ilStr::strLen($sep . $emailAddress);
                    if ($recipientsLineLength >= $maxRecipientCharacterLength) {
                        $this->sendMimeMail(
                            '',
                            '',
                            $remainingAddresses,
                            $a_subject,
                            $this->formatLinebreakMessage($a_message),
                            $a_attachments
                        );
    
                        $remainingAddresses = '';
                        $sep = '';
                    }
    
                    $remainingAddresses .= ($sep . $emailAddress);
                }
    
                if ('' !== $remainingAddresses) {
                    $this->sendMimeMail(
                        '',
                        '',
                        $remainingAddresses,
                        $a_subject,
                        $this->formatLinebreakMessage($a_message),
                        $a_attachments
                    );
                }
            }
        } else {
            $rcp_ids_replace = $this->getUserIds(array($a_rcp_to));
            $rcp_ids_no_replace = $this->getUserIds(array($a_rcp_cc, $a_rcp_bcc));

            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                "Parsed TO user ids from given recipients for serial letter notification: %s",
                implode(', ', $rcp_ids_replace)
            ));
            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                "Parsed CC/BCC user ids from given recipients for serial letter notification: %s",
                implode(', ', $rcp_ids_no_replace)
            ));

            $as_email = array();
            $id_to_message_map = array();

            foreach ($rcp_ids_replace as $id) {
                $tmp_mail_options = new ilMailOptions($id);

                $tmp_user = self::getCachedUserInstance($id);
                $user_is_active = $tmp_user->getActive();
                $user_can_read_internal_mails = !$tmp_user->hasToAcceptTermsOfService() && $tmp_user->checkTimeLimit();

                if (in_array('system', $a_type) && !$user_can_read_internal_mails) {
                    ilLoggerFactory::getLogger('mail')->debug(sprintf(
                        "Message is marked as 'system', skipped recipient with id %s (Accepted User Agreement:%s|Expired Account:%s)",
                        $id,
                        var_export(!$tmp_user->hasToAcceptTermsOfService(), 1),
                        var_export(!$tmp_user->checkTimeLimit(), 1)
                    ));
                    continue;
                }

                $id_to_message_map[$id] = $this->replacePlaceholders($a_message, $id);

                if ($user_is_active) {
                    if (!$user_can_read_internal_mails
                        || $tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_EMAIL
                        || $tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_BOTH) {
                        $as_email[$tmp_user->getId()] = ilMailOptions::getExternalEmailsByUser($tmp_user, $tmp_mail_options);
    
                        if ($tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_EMAIL) {
                            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                                "Recipient with id %s will only receive external emails sent to: %s",
                                $id,
                                implode(', ', $as_email[$tmp_user->getId()])
                            ));
                            continue;
                        } else {
                            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                                "Recipient with id %s will additionally receive external emails sent to: %s",
                                $id,
                                implode(', ', $as_email[$tmp_user->getId()])
                            ));
                        }
                    }
                }

                $mbox->setUserId($id);
                $inbox_id = $mbox->getInboxFolder();

                $mail_id = $this->sendInternalMail(
                    $inbox_id,
                    $this->user_id,
                    $a_attachments,
                    $a_rcp_to,
                    $a_rcp_cc,
                    '',
                    'unread',
                    $a_type,
                    0,
                    $a_subject,
                    $id_to_message_map[$id],
                    $id,
                    0
                );

                if ($a_attachments) {
                    $this->mfile->assignAttachmentsToDirectory($mail_id, $sent_mail_id);
                }
            }

            if (count($as_email)) {
                foreach ($as_email as $id => $emails) {
                    if (0 == count($emails)) {
                        continue;
                    }

                    $toEmailAddresses = implode(',', $emails);
                    $this->sendMimeMail($toEmailAddresses, '', '', $a_subject, $this->formatLinebreakMessage($id_to_message_map[$id]), $a_attachments);
                }
            }

            $as_email = array();

            $cc_and_bcc_message = $this->replacePlaceholders($a_message, 0, false);

            foreach ($rcp_ids_no_replace as $id) {
                $tmp_mail_options = new ilMailOptions($id);

                $tmp_user = self::getCachedUserInstance($id);
                $user_is_active = $tmp_user->getActive();
                $user_can_read_internal_mails = !$tmp_user->hasToAcceptTermsOfService() && $tmp_user->checkTimeLimit();

                if ($user_is_active) {
                    if (in_array('system', $a_type) && !$user_can_read_internal_mails) {
                        ilLoggerFactory::getLogger('mail')->debug(sprintf(
                            "Message is marked as 'system', skipped recipient with id %s (Accepted User Agreement:%s|Expired Account:%s)",
                            $id,
                            var_export(!$tmp_user->hasToAcceptTermsOfService(), 1),
                            var_export(!$tmp_user->checkTimeLimit(), 1)
                        ));
                        continue;
                    }
                    
                    
                    if (!$user_can_read_internal_mails
                        || $tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_EMAIL
                        || $tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_BOTH) {
                        $newEmailAddresses = ilMailOptions::getExternalEmailsByUser($tmp_user, $tmp_mail_options);
                        $as_email = array_unique(array_merge($newEmailAddresses, $as_email));

                        if ($tmp_mail_options->getIncomingType() == ilMailOptions::INCOMING_EMAIL) {
                            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                                "Recipient with id %s will only receive external emails sent to: %s",
                                $id,
                                implode(', ', $newEmailAddresses)
                            ));
                            continue;
                        } else {
                            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                                "Recipient with id %s will additionally receive external emails sent to: %s",
                                $id,
                                implode(', ', $newEmailAddresses)
                            ));
                        }
                    }
                }

                $mbox->setUserId($id);
                $inbox_id = $mbox->getInboxFolder();

                $mail_id = $this->sendInternalMail(
                    $inbox_id,
                    $this->user_id,
                    $a_attachments,
                    $a_rcp_to,
                    $a_rcp_cc,
                    '',
                    'unread',
                    $a_type,
                    0,
                    $a_subject,
                    $cc_and_bcc_message,
                    $id,
                    0
                );

                if ($a_attachments) {
                    $this->mfile->assignAttachmentsToDirectory($mail_id, $sent_mail_id);
                }
            }

            if (count($as_email)) {
                $this->sendMimeMail('', '', implode(',', $as_email), $a_subject, $this->formatLinebreakMessage($cc_and_bcc_message), $a_attachments);
            }
        }

        return true;
    }

    /**
     * @param string[] $recipients
     * @return int[]
     */
    protected function getUserIds(array $recipients) : array
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
    protected function checkMail(string $to, string $cc, string $bcc, string $subject) : array
    {
        $errors = [];

        foreach (array(
            $subject => 'mail_add_subject',
            $to => 'mail_add_recipient'
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
    protected function checkRecipients(string $recipients) : array
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
    * @param    array $a_m_type
    * @param    int $a_m_email
    * @param    string $a_m_subject
    * @param    string $a_m_message
    * @param    int $a_use_placeholders
    * @param    string|null $a_tpl_context_id
    * @param    array|null $a_tpl_ctx_params
    * @return	bool
    */
    public function savePostData(
        $a_user_id,
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
    ) {
        if (!$a_attachments) {
            $a_attachments = null;
        }
        if (!$a_rcp_to) {
            $a_rcp_to = null;
        }
        if (!$a_rcp_cc) {
            $a_rcp_cc = null;
        }
        if (!$a_rcp_bcc) {
            $a_rcp_bcc = null;
        }
        if (!$a_m_type) {
            $a_m_type = null;
        }
        if (!$a_m_email) {
            $a_m_email = null;
        }
        if (!$a_m_message) {
            $a_m_message = null;
        }
        if (!$a_use_placeholders) {
            $a_use_placeholders = '0';
        }

        $this->db->replace(
            $this->table_mail_saved,
            array(
                'user_id' => array('integer', $this->user_id)
            ),
            array(
                'attachments' => array('clob', serialize($a_attachments)),
                'rcp_to' => array('clob', $a_rcp_to),
                'rcp_cc' => array('clob', $a_rcp_cc),
                'rcp_bcc' => array('clob', $a_rcp_bcc),
                'm_type' => array('text', serialize($a_m_type)),
                'm_email' => array('integer', $a_m_email),
                'm_subject' => array('text', $a_m_subject),
                'm_message' => array('clob', $a_m_message),
                'use_placeholders' => array('integer', $a_use_placeholders),
                'tpl_ctx_id' => array('text', $a_tpl_context_id),
                'tpl_ctx_params' => array('blob', json_encode((array) $a_tpl_ctx_params))
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
     * Should be used to send notifcations over the internal or external mail channel
     * @param string   $a_rcp_to
     * @param string   $a_rcp_cc
     * @param string   $a_rcp_bc
     * @param string   $a_m_subject
     * @param string   $a_m_message
     * @param array    $a_attachment
     * @param array    $a_type (normal and/or system and/or email)
     * @param bool|int $a_use_placeholders
     * @return \ilMailError[]
     */
    public function sendMail($a_rcp_to, $a_rcp_cc, $a_rcp_bc, $a_m_subject, $a_m_message, $a_attachment, $a_type, $a_use_placeholders = 0) : array
    {
        global $DIC;

        ilLoggerFactory::getLogger('mail')->debug(
            "New mail system task:" .
            " To: " . $a_rcp_to .
            " | CC: " . $a_rcp_cc .
            " | BCC: " . $a_rcp_bc .
            " | Subject: " . $a_m_subject
        );

        if (in_array('system', $a_type)) {
            $a_type = array('system');
        }

        if ($a_attachment && !$this->mfile->checkFilesExist($a_attachment)) {
            return [new \ilMailError('mail_attachment_file_not_exist', [$a_attachment])];
        }

        $errors = $this->checkMail((string) $a_rcp_to, (string) $a_rcp_cc, (string) $a_rcp_bc, (string) $a_m_subject);
        if (count($errors) > 0) {
            return $errors;
        }

        $errors = $this->validateRecipients((string) $a_rcp_to, (string) $a_rcp_cc, (string) $a_rcp_bc);
        if (count($errors) > 0) {
            return $errors;
        }

        $rcp_to = $a_rcp_to;
        $rcp_cc = $a_rcp_cc;
        $rcp_bc = $a_rcp_bc;

        if (null === $rcp_cc) {
            $rcp_cc = '';
        }

        if (null === $rcp_bc) {
            $rcp_bc = '';
        }

        $numberOfExternalAddresses = $this->getCountRecipients($rcp_to, $rcp_cc, $rcp_bc, true);

        if (
            $numberOfExternalAddresses > 0 &&
            !$this->isSystemMail() &&
            !$DIC->rbac()->system()->checkAccessOfUser($this->user_id, 'smtp_mail', $this->mail_obj_ref_id)
        ) {
            return [new \ilMailError('mail_no_permissions_write_smtp')];
        }

        if ($this->appendInstallationSignature()) {
            $a_m_message .= self::_getInstallationSignature();
        }

        $sent_id = $this->saveInSentbox($a_attachment, $a_rcp_to, $a_rcp_cc, $a_rcp_bc, $a_type, $a_m_subject, $a_m_message);

        if ($a_attachment) {
            $this->mfile->assignAttachmentsToDirectory($sent_id, $sent_id);
            $this->mfile->saveFiles($sent_id, $a_attachment);
        }

        if ($numberOfExternalAddresses > 0) {
            $externalMailRecipientsTo = $this->getEmailRecipients($rcp_to);
            $externalMailRecipientsCc = $this->getEmailRecipients($rcp_cc);
            $externalMailRecipientsBcc = $this->getEmailRecipients($rcp_bc);

            ilLoggerFactory::getLogger('mail')->debug(
                "Parsed external email addresses from given recipients:" .
                " To: " . $externalMailRecipientsTo .
                " | CC: " . $externalMailRecipientsCc .
                " | BCC: " . $externalMailRecipientsBcc .
                " | Subject: " . $a_m_subject
            );

            $this->sendMimeMail(
                $externalMailRecipientsTo,
                $externalMailRecipientsCc,
                $externalMailRecipientsBcc,
                $a_m_subject,
                $this->formatLinebreakMessage($a_use_placeholders ? $this->replacePlaceholders($a_m_message, 0, false) : $a_m_message),
                $a_attachment,
                0
            );
        } else {
            ilLoggerFactory::getLogger('mail')->debug('No external email addresses given in recipient string');
        }

        if (in_array('system', $a_type) && !$this->distributeMail($rcp_to, $rcp_cc, $rcp_bc, $a_m_subject, $a_m_message, $a_attachment, $sent_id, $a_type, 'system', $a_use_placeholders)) {
            return [new \ilMailError('mail_send_error')];
        }

        if (in_array('normal', $a_type) && !$this->distributeMail($rcp_to, $rcp_cc, $rcp_bc, $a_m_subject, $a_m_message, $a_attachment, $sent_id, $a_type, 'normal', $a_use_placeholders)) {
            return [new \ilMailError('mail_send_error')];
        }

        if (!$this->getSaveInSentbox()) {
            $this->deleteMails([$sent_id]);
        }

        return [];
    }

    /**
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @return \ilMailError[] An array of errors determined on validation
     */
    public function validateRecipients(string $to, string $cc, string $bcc) : array
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
     * @param array  $a_type
     * @param string $a_m_subject
     * @param string $a_m_message
     * @return int mail id
     */
    protected function saveInSentbox($a_attachment, $a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_type, $a_m_subject, $a_m_message)
    {
        return $this->sendInternalMail(
            $this->mailbox->getSentFolder(),
            $this->user_id,
            $a_attachment,
            $a_rcp_to,
            $a_rcp_cc,
            $a_rcp_bcc,
            'read',
            $a_type,
            0,
            $a_m_subject,
            $a_m_message,
            $this->user_id,
            0
        );
    }

    /**
     * Send mime mail using class.ilMimeMail.php. All external mails are send to SOAP::sendMail (if enabled) starting a kind of background process
     * @param string $a_rcp_to
     * @param string $a_rcp_cc
     * @param string $a_rcp_bcc
     * @param string $a_m_subject
     * @param string $a_m_message
     * @param array  $a_attachments
     * @param bool   $a_no_soap
     * @deprecated Should not be called from consumers, please use sendMail()
     */
    public function sendMimeMail($a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_m_subject, $a_m_message, $a_attachments, $a_no_soap = false)
    {
        require_once 'Services/Mail/classes/class.ilMimeMail.php';

        $a_m_subject = self::getSubjectPrefix() . ' ' . $a_m_subject;

        // #10854
        if ($this->isSOAPEnabled() && !$a_no_soap) {
            require_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';
            $soap_client = new ilSoapClient();
            $soap_client->setResponseTimeout(5);
            $soap_client->enableWSDL(true);
            $soap_client->init();

            $attachments = array();
            $a_attachments = $a_attachments ? $a_attachments : array();
            foreach ($a_attachments as $attachment) {
                $attachments[] = $this->mfile->getAbsoluteAttachmentPoolPathByFilename($attachment);
            }

            // mjansen: switched separator from "," to "#:#" because of mantis bug #6039
            $attachments = implode('#:#', $attachments);
            // mjansen: use "#:#" as leading delimiter
            if (strlen($attachments)) {
                $attachments = "#:#" . $attachments;
            }

            $soap_client->call('sendMail', array(
                session_id() . '::' . $_COOKIE['ilClientId'],
                $a_rcp_to,
                $a_rcp_cc,
                $a_rcp_bcc,
                $this->user_id,
                $a_m_subject,
                $a_m_message,
                $attachments
            ));
        } else {
            /** @var ilMailMimeSenderFactory $senderFactory */
            $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

            $mmail = new ilMimeMail();
            $mmail->From($senderFactory->getSenderByUsrId($this->user_id));
            $mmail->To($a_rcp_to);
            $mmail->Subject($a_m_subject);
            $mmail->Body($a_m_message);

            if ($a_rcp_cc) {
                $mmail->Cc($a_rcp_cc);
            }

            if ($a_rcp_bcc) {
                $mmail->Bcc($a_rcp_bcc);
            }

            if (is_array($a_attachments)) {
                foreach ($a_attachments as $attachment) {
                    $mmail->Attach($this->mfile->getAbsoluteAttachmentPoolPathByFilename($attachment), '', 'inline', $attachment);
                }
            }

            $mmail->Send();
        }
    }

    /**
     * @param array $a_attachments An array of attachments
     * @return bool
     */
    public function saveAttachments($a_attachments)
    {
        $this->db->update(
            $this->table_mail_saved,
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
            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                "Started parsing of recipient string: %s",
                $addresses
            ));
        }

        $parser = $this->mailAddressParserFactory->getParser((string) $addresses);
        $parsedAddresses = $parser->parse();

        if (strlen($addresses) > 0) {
            ilLoggerFactory::getLogger('mail')->debug(sprintf(
                "Parsed addresses: %s",
                implode(',', array_map(function (ilMailAddress $address) {
                    return (string) $address;
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
    protected function getCountRecipient(string $recipients, $onlyExternalAddresses = true) : int
    {
        $addresses = new \ilMailAddressListImpl($this->parseAddresses($recipients));
        if ($onlyExternalAddresses) {
            $addresses = new \ilMailOnlyExternalAddressList($addresses, self::ILIAS_HOST);
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
    ) : int {
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
    protected function getEmailRecipients(string $recipients) : string
    {
        $addresses = new \ilMailOnlyExternalAddressList(
            new \ilMailAddressListImpl($this->parseAddresses($recipients)),
            self::ILIAS_HOST
        );

        $emailRecipients = array_map(function (\ilMailAddress $address) {
            return (string) $address;
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

        if (!($lang instanceof ilLanguage)) {
            require_once 'Services/Language/classes/class.ilLanguageFactory.php';
            $lang = ilLanguageFactory::_getLanguage();
        }

        $lang->loadLanguageModule('mail');

        return sprintf(
            $lang->txt('mail_auto_generated_info'),
            $DIC->settings()->get('inst_name', 'ILIAS 5'),
            ilUtil::_getHttpPath()
        ) . "\n\n";
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
        if (null === $a_flag) {
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
        if (is_array($clientdirs) && count($clientdirs) > 1) {
            $clientUrl .= '/login.php?client_id=' . CLIENT_ID; // #18051
        }

        $signature = str_ireplace('[CLIENT_NAME]', $DIC['ilClientIniFile']->readVariable('client', 'name'), $signature);
        $signature = str_ireplace('[CLIENT_DESC]', $DIC['ilClientIniFile']->readVariable('client', 'description'), $signature);
        $signature = str_ireplace('[CLIENT_URL]', $clientUrl, $signature);

        if (!preg_match('/^[\n\r]+/', $signature)) {
            $signature = "\n" . $signature;
        }

        return $signature;
    }

    /**
     * Get text that will be prepended to auto generated mails
     * @return string subject prefix
     */
    public static function getSubjectPrefix()
    {
        global $DIC;

        $subjectPrefix = $DIC->settings()->get('mail_subject_prefix');
        if (false === $subjectPrefix) {
            $subjectPrefix = self::MAIL_SUBJECT_PREFIX;
        }

        return $subjectPrefix;
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
        $name = ilObjUser::_lookupName($a_usr_id);

        if (!strlen($name['firstname'])) {
            return $lang->txt('mail_salutation_anonymous') . ',';
        }

        return
            $lang->txt('mail_salutation_' . $gender) . ' ' .
            ($name['title'] ? $name['title'] . ' ' : '') .
            ($name['firstname'] ? $name['firstname'] . ' ' : '') .
            $name['lastname'] . ',';
    }

    /**
     * @param int $a_usr_id
     * @return ilObjUser
     */
    protected static function getCachedUserInstance($a_usr_id)
    {
        if (isset(self::$userInstances[$a_usr_id])) {
            return self::$userInstances[$a_usr_id];
        }

        self::$userInstances[$a_usr_id] = new ilObjUser($a_usr_id);
        return self::$userInstances[$a_usr_id];
    }

    /**
     * @inheritdoc
     */
    public function formatLinebreakMessage($a_message)
    {
        return $a_message;
    }
}
