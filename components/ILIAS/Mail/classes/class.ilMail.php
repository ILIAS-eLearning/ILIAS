<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Bucket\BasicBucket;
use ILIAS\Mail\Autoresponder\AutoresponderService;
use ILIAS\LegalDocuments\Conductor;
use ILIAS\Mail\Recipient;
use ILIAS\Mail\Service\MailSignatureService;

/**
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMail
{
    public const ILIAS_HOST = 'ilias';
    public const PROP_CONTEXT_SUBJECT_PREFIX = 'subject_prefix';

    private MailSignatureService $signature_service;
    private string $table_mail;
    private string $table_mail_saved;
    /** @var array<string, mixed>|null */
    protected ?array $mail_data = [];
    private bool $save_in_sentbox;
    private bool $append_installation_signature = false;
    private bool $append_user_signature = false;

    private ?string $context_id = null;
    private array $context_parameters = [];

    /** @var array<int, ilMailOptions> */
    private array $mail_options_by_usr_id_map = [];

    /** @var array<int, null|ilObjUser> */
    private array $user_instances_by_id_map = [];
    private int $max_recipient_character_length = 998;
    private readonly Conductor $legal_documents;
    private ILIAS\Refinery\Factory $refinery;

    public function __construct(
        private int $user_id,
        private ?ilMailAddressTypeFactory $mail_address_type_factory = null,
        private ?ilMailRfc822AddressParserFactory $mail_address_parser_factory = new ilMailRfc822AddressParserFactory(),
        private ?ilAppEventHandler $event_handler = null,
        private ?ilLogger $logger = null,
        private ?ilDBInterface $db = null,
        private ?ilLanguage $lng = null,
        private ?ilFileDataMail $mail_file_data = null,
        protected ?ilMailOptions $mail_options = null,
        private ?ilMailbox $mailbox = null,
        private ?ilMailMimeSenderFactory $sender_factory = null,
        private ?Closure $usr_id_by_login_callable = null,
        private ?AutoresponderService $auto_responder_service = null,
        private ?int $mail_admin_node_ref_id = null,
        private ?int $mail_obj_ref_id = null,
        private ?ilObjUser $actor = null,
        private ?ilMailTemplatePlaceholderResolver $placeholder_resolver = null,
        ?Conductor $legal_documents = null,
        ?MailSignatureService $signature_service = null,
    ) {
        global $DIC;
        $this->user_id = $user_id;
        $this->logger = $logger ?? ilLoggerFactory::getLogger('mail');
        $this->mail_address_type_factory = $mail_address_type_factory ?? new ilMailAddressTypeFactory(null, $logger);
        $this->mail_address_parser_factory = $mail_address_parser_factory ?? new ilMailRfc822AddressParserFactory();
        $this->event_handler = $event_handler ?? $DIC->event();
        $this->db = $db ?? $DIC->database();
        $this->lng = $lng ?? $DIC->language();
        $this->actor = $actor ?? $DIC->user();
        $this->mail_file_data = $mail_file_data ?? new ilFileDataMail($user_id);
        if (is_null($this->mail_options)) {
            $this->mail_options = new ilMailOptions($user_id);
        } else {
            $this->mail_options = $mail_options;
        }
        $this->mailbox = $mailbox ?? new ilMailbox($user_id);
        $this->sender_factory = $sender_factory ?? $DIC->mail()->mime()->senderFactory();
        $this->usr_id_by_login_callable = $usr_id_by_login_callable ?? static function (string $login): int {
            return (int) ilObjUser::_lookupId($login);
        };
        $this->auto_responder_service = $auto_responder_service ?? $DIC->mail()->autoresponder();
        $this->placeholder_resolver = $placeholder_resolver ?? $DIC->mail()->placeholderResolver();

        if (null === $this->mail_obj_ref_id) {
            $this->readMailObjectReferenceId();
        }
        $this->lng->loadLanguageModule('mail');
        $this->table_mail = 'mail';
        $this->table_mail_saved = 'mail_saved';
        $this->setSaveInSentbox(false);
        $this->refinery = $DIC->refinery();
        $this->legal_documents = $legal_documents ?? $DIC['legalDocuments'];
        $this->signature_service = $signature_service ?? $DIC->mail()->signature();
    }

    public function autoresponder(): AutoresponderService
    {
        return $this->auto_responder_service;
    }

    public function withContextId(string $context_id): self
    {
        $clone = clone $this;

        $clone->context_id = $context_id;

        return $clone;
    }

    public function withContextParameters(array $parameters): self
    {
        $clone = clone $this;

        $clone->context_parameters = $parameters;

        return $clone;
    }

    private function isSystemMail(): bool
    {
        return $this->user_id === ANONYMOUS_USER_ID;
    }

    public function existsRecipient(string $newRecipient, string $existingRecipients): bool
    {
        $newAddresses = new ilMailAddressListImpl($this->parseAddresses($newRecipient));
        $addresses = new ilMailAddressListImpl($this->parseAddresses($existingRecipients));

        $list = new ilMailDiffAddressList($newAddresses, $addresses);

        $diffedAddresses = $list->value();

        return $diffedAddresses === [];
    }

    public function setSaveInSentbox(bool $saveInSentbox): void
    {
        $this->save_in_sentbox = $saveInSentbox;
    }

    public function getSaveInSentbox(): bool
    {
        return $this->save_in_sentbox;
    }

    private function readMailObjectReferenceId(): void
    {
        $this->mail_obj_ref_id = ilMailGlobalServices::getMailObjectRefId();
    }

    public function getMailObjectReferenceId(): int
    {
        return $this->mail_obj_ref_id;
    }

    public function formatNamesForOutput(string $recipients): string
    {
        $recipients = trim($recipients);
        if ($recipients === '') {
            return $this->lng->txt('not_available');
        }

        $names = [];

        $recipients = array_filter(array_map('trim', explode(',', $recipients)));
        foreach ($recipients as $recipient) {
            $usrId = ilObjUser::_lookupId($recipient);
            if (is_int($usrId) && $usrId > 0) {
                $pp = ilObjUser::_lookupPref($usrId, 'public_profile');
                if ($pp === 'g' || ($pp === 'y' && !$this->actor->isAnonymous())) {
                    $user = $this->getUserInstanceById($usrId);
                    if ($user) {
                        $names[] = $user->getFullname() . ' [' . $recipient . ']';
                        continue;
                    }
                }
            }

            $names[] = $recipient;
        }

        return implode(', ', $names);
    }

    public function getPreviousMail(int $mailId): ?array
    {
        $this->db->setLimit(1, 0);

        $query = implode(' ', [
            "SELECT b.* FROM $this->table_mail a",
            "INNER JOIN $this->table_mail b ON b.folder_id = a.folder_id",
            'AND b.user_id = a.user_id AND b.send_time > a.send_time',
            'WHERE a.user_id = %s AND a.mail_id = %s ORDER BY b.send_time ASC',
        ]);
        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$this->user_id, $mailId]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    public function getNextMail(int $mailId): ?array
    {
        $this->db->setLimit(1, 0);

        $query = implode(' ', [
            "SELECT b.* FROM $this->table_mail a",
            "INNER JOIN $this->table_mail b ON b.folder_id = a.folder_id",
            'AND b.user_id = a.user_id AND b.send_time < a.send_time',
            'WHERE a.user_id = %s AND a.mail_id = %s ORDER BY b.send_time DESC',
        ]);
        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$this->user_id, $mailId]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    public function getMailsOfFolder(int $a_folder_id, array $filter = []): array
    {
        $mails = [];

        $query =
            "SELECT sender_id, m_subject, mail_id, m_status, send_time, import_name " .
            "FROM $this->table_mail " .
            "LEFT JOIN object_data ON obj_id = sender_id " .
            "WHERE user_id = %s AND folder_id = %s " .
            "AND ((sender_id > 0 AND sender_id IS NOT NULL AND obj_id IS NOT NULL) " .
            "OR (sender_id = 0 OR sender_id IS NULL))";

        if (isset($filter['status']) && $filter['status'] !== '') {
            $query .= ' AND m_status = ' . $this->db->quote($filter['status'], 'text');
        }

        $query .= " ORDER BY send_time DESC";

        $res = $this->db->queryF(
            $query,
            ['integer', 'integer'],
            [$this->user_id, $a_folder_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $mails[] = $this->fetchMailData($row);
        }

        return array_filter($mails);
    }

    public function countMailsOfFolder(int $folderId): int
    {
        $res = $this->db->queryF(
            "SELECT COUNT(*) FROM $this->table_mail WHERE user_id = %s AND folder_id = %s",
            ['integer', 'integer'],
            [$this->user_id, $folderId]
        );

        return $this->db->numRows($res);
    }

    public function deleteMailsOfFolder(int $folderId): void
    {
        $mails = $this->getMailsOfFolder($folderId);
        foreach ($mails as $mail_data) {
            $this->deleteMails([$mail_data['mail_id']]);
        }
    }

    public function getMail(int $mailId): ?array
    {
        $res = $this->db->queryF(
            "SELECT * FROM $this->table_mail WHERE user_id = %s AND mail_id = %s",
            ['integer', 'integer'],
            [$this->user_id, $mailId]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));

        return $this->mail_data;
    }

    /**
     * @param int[] $mailIds
     */
    public function markRead(array $mailIds): void
    {
        $values = [];
        $types = [];

        $query = "UPDATE $this->table_mail SET m_status = %s WHERE user_id = %s ";
        array_push($types, 'text', 'integer');
        array_push($values, 'read', $this->user_id);

        if (count($mailIds) > 0) {
            $query .= ' AND ' . $this->db->in('mail_id', $mailIds, false, 'integer');
        }

        $this->db->manipulateF($query, $types, $values);
    }

    /**
     * @param int[] $mailIds
     */
    public function markUnread(array $mailIds): void
    {
        $values = [];
        $types = [];

        $query = "UPDATE $this->table_mail SET m_status = %s WHERE user_id = %s ";
        array_push($types, 'text', 'integer');
        array_push($values, 'unread', $this->user_id);

        if (count($mailIds) > 0) {
            $query .= ' AND ' . $this->db->in('mail_id', $mailIds, false, 'integer');
        }

        $this->db->manipulateF($query, $types, $values);
    }

    /**
     * @param int[] $mailIds
     * @param int $folderId
     * @return bool
     */
    public function moveMailsToFolder(array $mailIds, int $folderId): bool
    {
        $values = [];
        $types = [];

        $mailIds = array_filter(array_map('intval', $mailIds));

        if (0 === count($mailIds)) {
            return false;
        }

        $query =
            "UPDATE $this->table_mail " .
            "INNER JOIN mail_obj_data " .
            "ON mail_obj_data.obj_id = %s AND mail_obj_data.user_id = %s " .
            "SET $this->table_mail.folder_id = mail_obj_data.obj_id " .
            "WHERE $this->table_mail.user_id = %s";
        array_push($types, 'integer', 'integer', 'integer');
        array_push($values, $folderId, $this->user_id, $this->user_id);

        $query .= ' AND ' . $this->db->in('mail_id', $mailIds, false, 'integer');

        $affectedRows = $this->db->manipulateF($query, $types, $values);

        return $affectedRows > 0;
    }

    /**
     * @param int[] $mailIds
     */
    public function deleteMails(array $mailIds): void
    {
        $mailIds = array_filter(array_map('intval', $mailIds));
        foreach ($mailIds as $id) {
            $this->db->manipulateF(
                "DELETE FROM $this->table_mail WHERE user_id = %s AND mail_id = %s",
                ['integer', 'integer'],
                [$this->user_id, $id]
            );
            $this->mail_file_data->deassignAttachmentFromDirectory($id);
        }
    }

    /**
     * @param array|null $row
     * @return array|null
     */
    protected function fetchMailData(?array $row): ?array
    {
        if (!is_array($row) || empty($row)) {
            return null;
        }

        if (isset($row['attachments'])) {
            $unserialized = unserialize(stripslashes($row['attachments']), ['allowed_classes' => false]);
            $row['attachments'] = is_array($unserialized) ? $unserialized : [];
        } else {
            $row['attachments'] = [];
        }

        if (isset($row['tpl_ctx_params']) && is_string($row['tpl_ctx_params'])) {
            $decoded = json_decode($row['tpl_ctx_params'], true, 512, JSON_THROW_ON_ERROR);
            $row['tpl_ctx_params'] = (array) ($decoded ?? []);
        } else {
            $row['tpl_ctx_params'] = [];
        }

        if (isset($row['mail_id'])) {
            $row['mail_id'] = (int) $row['mail_id'];
        }

        if (isset($row['user_id'])) {
            $row['user_id'] = (int) $row['user_id'];
        }

        if (isset($row['folder_id'])) {
            $row['folder_id'] = (int) $row['folder_id'];
        }

        if (isset($row['sender_id'])) {
            $row['sender_id'] = (int) $row['sender_id'];
        }

        if (isset($row['use_placeholders'])) {
            $row['use_placeholders'] = (bool) $row['use_placeholders'];
        }

        $null_to_string_properties = ['m_subject', 'm_message', 'rcp_to', 'rcp_cc', 'rcp_bcc'];
        foreach ($null_to_string_properties as $null_to_string_property) {
            if (!isset($row[$null_to_string_property])) {
                $row[$null_to_string_property] = '';
            }
        }

        return $row;
    }

    public function getNewDraftId(int $folderId): int
    {
        $nextId = $this->db->nextId($this->table_mail);
        $this->db->insert($this->table_mail, [
            'mail_id' => ['integer', $nextId],
            'user_id' => ['integer', $this->user_id],
            'folder_id' => ['integer', $folderId],
            'sender_id' => ['integer', $this->user_id],
        ]);

        return $nextId;
    }

    /**
     * @param string[] $a_attachments
     */
    public function updateDraft(
        int $a_folder_id,
        array $a_attachments,
        string $a_rcp_to,
        string $a_rcp_cc,
        string $a_rcp_bcc,
        string $a_m_subject,
        string $a_m_message,
        int $a_draft_id = 0,
        bool $a_use_placeholders = false,
        ?string $a_tpl_context_id = null,
        array $a_tpl_context_params = []
    ): int {
        $this->db->update(
            $this->table_mail,
            [
                'folder_id' => ['integer', $a_folder_id],
                'attachments' => ['clob', serialize($a_attachments)],
                'send_time' => ['timestamp', date('Y-m-d H:i:s')],
                'rcp_to' => ['clob', $a_rcp_to],
                'rcp_cc' => ['clob', $a_rcp_cc],
                'rcp_bcc' => ['clob', $a_rcp_bcc],
                'm_status' => ['text', 'read'],
                'm_subject' => ['text', $a_m_subject],
                'm_message' => ['clob', $a_m_message],
                'use_placeholders' => ['integer', (int) $a_use_placeholders],
                'tpl_ctx_id' => ['text', $a_tpl_context_id],
                'tpl_ctx_params' => ['blob', json_encode($a_tpl_context_params, JSON_THROW_ON_ERROR)],
            ],
            [
                'mail_id' => ['integer', $a_draft_id],
            ]
        );

        return $a_draft_id;
    }

    private function sendInternalMail(
        int $folderId,
        int $senderUsrId,
        array $attachments,
        string $to,
        string $cc,
        string $bcc,
        string $status,
        string $subject,
        string $message,
        int $usrId = 0,
        bool $usePlaceholders = false,
        ?string $templateContextId = null,
        array $templateContextParameters = []
    ): int {
        $usrId = $usrId ?: $this->user_id;

        if ($usePlaceholders) {
            $message = $this->replacePlaceholders($message, $usrId);
        }
        $message = $this->formatLinebreakMessage($this->refinery->string()->markdown()->toHTML()->transform($message) ?? '');
        $message = str_ireplace(["<br />", "<br>", "<br/>"], "\n", $message);

        $nextId = $this->db->nextId($this->table_mail);
        $this->db->insert($this->table_mail, [
            'mail_id' => ['integer', $nextId],
            'user_id' => ['integer', $usrId],
            'folder_id' => ['integer', $folderId],
            'sender_id' => ['integer', $senderUsrId],
            'attachments' => ['clob', serialize($attachments)],
            'send_time' => ['timestamp', date('Y-m-d H:i:s')],
            'rcp_to' => ['clob', $to],
            'rcp_cc' => ['clob', $cc],
            'rcp_bcc' => ['clob', $bcc],
            'm_status' => ['text', $status],
            'm_subject' => ['text', $subject],
            'm_message' => ['clob', $message],
            'tpl_ctx_id' => ['text', $templateContextId],
            'tpl_ctx_params' => ['blob', json_encode($templateContextParameters, JSON_THROW_ON_ERROR)],
        ]);

        $sender_equals_reveiver = $usrId === $this->mailbox->getUsrId();
        $is_sent_folder_of_sender = false;
        if ($sender_equals_reveiver) {
            $current_folder_id = $this->getSubjectSentFolderId();
            $is_sent_folder_of_sender = $folderId === $current_folder_id;
        }

        $raise_event = !$sender_equals_reveiver || !$is_sent_folder_of_sender;

        if ($raise_event) {
            $this->event_handler->raise('components/ILIAS/Mail', 'sentInternalMail', [
                'id' => $nextId,
                'subject' => $subject,
                'body' => (string) $message,
                'from_usr_id' => $senderUsrId,
                'to_usr_id' => $usrId,
                'rcp_to' => $to,
                'rcp_cc' => $cc,
                'rcp_bcc' => $bcc,
            ]);
        }

        return $nextId;
    }

    protected function replacePlaceholders(
        string $message,
        int $usrId = 0,
        bool $replaceEmptyPlaceholders = true
    ): string {
        try {
            if ($this->context_id) {
                $context = ilMailTemplateContextService::getTemplateContextById($this->context_id);
            } else {
                $context = new ilMailTemplateGenericContext();
            }
            $user = $usrId > 0 ? $this->getUserInstanceById($usrId) : null;
            $message = $this->placeholder_resolver->resolve(
                $context,
                $message,
                $user,
                $this->context_parameters,
                $replaceEmptyPlaceholders
            );
        } catch (Exception $e) {
            $this->logger->error(__METHOD__ . ' has been called with invalid context. ' . $e->getMessage());
        }

        return $message;
    }

    private function distributeMail(
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message,
        array $attachments,
        int $sentMailId,
        bool $usePlaceholders = false
    ): bool {
        $this->auto_responder_service->emptyAutoresponderData();
        if ($usePlaceholders) {
            $toUsrIds = $this->getUserIds([$to]);
            $this->logger->debug(sprintf(
                "Parsed TO user ids from given recipients for serial letter notification: %s",
                implode(', ', $toUsrIds)
            ));

            $this->sendChanneledMails(
                $to,
                $cc,
                $bcc,
                $toUsrIds,
                $subject,
                $message,
                $attachments,
                $sentMailId,
                true
            );

            $otherUsrIds = $this->getUserIds([$cc, $bcc]);
            $this->logger->debug(sprintf(
                "Parsed CC/BCC user ids from given recipients for serial letter notification: %s",
                implode(', ', $otherUsrIds)
            ));

            $this->sendChanneledMails(
                $to,
                $cc,
                $bcc,
                $otherUsrIds,
                $subject,
                $this->replacePlaceholders($message, 0, false),
                $attachments,
                $sentMailId
            );
        } else {
            $usrIds = $this->getUserIds([$to, $cc, $bcc]);
            $this->logger->debug(sprintf(
                "Parsed TO/CC/BCC user ids from given recipients: %s",
                implode(', ', $usrIds)
            ));

            $this->sendChanneledMails(
                $to,
                $cc,
                $bcc,
                $usrIds,
                $subject,
                $message,
                $attachments,
                $sentMailId
            );
        }

        $this->auto_responder_service->disableAutoresponder();
        $this->auto_responder_service->handleAutoresponderMails($this->user_id);

        return true;
    }

    /**
     * @throws JsonException
     */
    protected function sendChanneledMails(
        string $to,
        string $cc,
        string $bcc,
        array $user_ids,
        string $subject,
        string $message,
        array $attachments,
        int $sent_mail_id,
        bool $use_placeholders = false
    ): void {
        $usr_i_to_external_email_addresses_map = [];
        $usr_id_to_message_map = [];

        foreach ($user_ids as $user_id) {
            $user = $this->getUserInstanceById($user_id);
            if (!$user instanceof ilObjUser) {
                $this->logger->critical(sprintf(
                    "Skipped recipient with id %s (User not found)",
                    $user_id
                ));
                continue;
            }

            $recipient = $this->createRecipient((int) $user_id);
            $can_read_internal = $recipient->evaluateInternalMailReadability();
            if ($this->isSystemMail() && !$can_read_internal->isOk()) {
                $this->logger->debug(sprintf(
                    'Skipped recipient with id %s and reson %s',
                    $recipient->getUserId(),
                    is_string($can_read_internal->error()) ? $can_read_internal->error() : $can_read_internal->error()->getMessage()
                ));
                continue;
            }

            $individualMessage = $message;
            $mailOptions = $this->getMailOptionsByUserId($user->getId());
            if ($use_placeholders) {
                $subject = $this->replacePlaceholders($subject, $user->getId());
                $individualMessage = $this->replacePlaceholders($message, $user->getId());
                $usr_id_to_message_map[$user->getId()] = $individualMessage;
            }

            if ($recipient->isUserActive()) {
                $wantsToReceiveExternalEmail = (
                    $mailOptions->getIncomingType() === ilMailOptions::INCOMING_EMAIL ||
                    $mailOptions->getIncomingType() === ilMailOptions::INCOMING_BOTH
                );

                if (!$can_read_internal->isOk() || $wantsToReceiveExternalEmail) {
                    $email_addresses = $recipient->getExternalMailAddress();
                    $usr_i_to_external_email_addresses_map[$recipient->getUserId()] = $email_addresses;

                    if ($recipient->onlyToExternalMailAddress()) {
                        $this->logger->debug(sprintf(
                            "Recipient with id %s will only receive external emails sent to: %s",
                            $recipient->getUserId(),
                            implode(', ', $email_addresses)
                        ));
                        continue;
                    }

                    $this->logger->debug(sprintf(
                        "Recipient with id %s will additionally receive external emails " .
                        "(because the user wants to receive it externally, or the user cannot access " .
                        "the internal mail system) sent to: %s",
                        $recipient->getUserId(),
                        implode(', ', $email_addresses)
                    ));
                } else {
                    $this->logger->debug(sprintf(
                        "Recipient with id %s is does not want to receive external emails",
                        $recipient->getUserId()
                    ));
                }
            } else {
                $this->logger->debug(sprintf(
                    "Recipient with id %s is inactive and will not receive external emails",
                    $recipient->getUserId()
                ));
            }

            $mbox = clone $this->mailbox;
            $mbox->setUsrId($recipient->getUserId());
            $recipient_inbox_id = $mbox->getInboxFolder();

            $internal_mail_id = $this->sendInternalMail(
                $recipient_inbox_id,
                $this->user_id,
                $attachments,
                $to,
                $cc,
                '',
                'unread',
                $subject,
                $individualMessage,
                $recipient->getUserId()
            );

            $mail_receiver_options = $this->getMailOptionsByUserId($this->user_id);

            $this->auto_responder_service->enqueueAutoresponderIfEnabled(
                $recipient->getUserId(),
                $recipient->getMailOptions(),
                $mail_receiver_options,
            );

            if ($attachments !== []) {
                $this->mail_file_data->assignAttachmentsToDirectory($internal_mail_id, $sent_mail_id);
            }
        }

        $this->delegateExternalEmails(
            $subject,
            $message,
            $attachments,
            $use_placeholders,
            $usr_i_to_external_email_addresses_map,
            $usr_id_to_message_map
        );
    }

    /**
     * @param string $subject
     * @param string $message
     * @param string[] $attachments
     * @param bool $usePlaceholders
     * @param array<int, string[]> $usrIdToExternalEmailAddressesMap
     * @param array<int, string> $usrIdToMessageMap
     */
    protected function delegateExternalEmails(
        string $subject,
        string $message,
        array $attachments,
        bool $usePlaceholders,
        array $usrIdToExternalEmailAddressesMap,
        array $usrIdToMessageMap
    ): void {
        if (1 === count($usrIdToExternalEmailAddressesMap)) {
            if ($usePlaceholders) {
                $message = array_values($usrIdToMessageMap)[0];
            }

            $usrIdToExternalEmailAddressesMap = array_values($usrIdToExternalEmailAddressesMap);
            $firstAddresses = current($usrIdToExternalEmailAddressesMap);

            $this->sendMimeMail(
                implode(',', $firstAddresses),
                '',
                '',
                $subject,
                $this->formatLinebreakMessage($message),
                $attachments
            );
        } elseif (count($usrIdToExternalEmailAddressesMap) > 1) {
            if ($usePlaceholders) {
                foreach ($usrIdToExternalEmailAddressesMap as $usrId => $addresses) {
                    if (0 === count($addresses)) {
                        continue;
                    }

                    $this->sendMimeMail(
                        implode(',', $addresses),
                        '',
                        '',
                        $subject,
                        $this->formatLinebreakMessage($usrIdToMessageMap[$usrId]),
                        $attachments
                    );
                }
            } else {
                $flattenEmailAddresses = iterator_to_array(
                    new RecursiveIteratorIterator(
                        new RecursiveArrayIterator(
                            $usrIdToExternalEmailAddressesMap
                        )
                    ),
                    false
                );

                $flattenEmailAddresses = array_unique($flattenEmailAddresses);

                // https://mantis.ilias.de/view.php?id=23981 and https://www.ietf.org/rfc/rfc2822.txt
                $remainingAddresses = '';
                foreach ($flattenEmailAddresses as $emailAddress) {
                    $sep = '';
                    if ($remainingAddresses !== '') {
                        $sep = ',';
                    }

                    $recipientsLineLength = ilStr::strLen($remainingAddresses) +
                        ilStr::strLen($sep . $emailAddress);
                    if ($recipientsLineLength >= $this->max_recipient_character_length) {
                        $this->sendMimeMail(
                            '',
                            '',
                            $remainingAddresses,
                            $subject,
                            $this->formatLinebreakMessage($message),
                            $attachments
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
                        $subject,
                        $this->formatLinebreakMessage($message),
                        $attachments
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
        $parsed_usr_ids = [];

        $joined_recipients = implode(',', array_filter(array_map('trim', $recipients)));

        $addresses = $this->parseAddresses($joined_recipients);
        foreach ($addresses as $address) {
            $address_type = $this->mail_address_type_factory->getByPrefix($address);
            $parsed_usr_ids[] = $address_type->resolve();
        }

        return array_unique(array_merge(...$parsed_usr_ids));
    }

    /**
     * @return ilMailError[]
     */
    protected function checkMail(string $to, string $cc, string $bcc, string $subject): array
    {
        $errors = [];

        $checks = [
            $subject => 'mail_add_subject',
            $to => 'mail_add_recipient',
        ];
        foreach ($checks as $string => $error) {
            if ($string === '') {
                $errors[] = new ilMailError($error);
            }
        }

        if (ilStr::strLen($subject) > 255) {
            // https://mantis.ilias.de/view.php?id=37881
            $errors[] = new ilMailError('mail_subject_too_long');
        }

        return $errors;
    }

    /**
     * @param string $recipients
     * @return ilMailError[]
     * @throws ilMailException
     */
    protected function checkRecipients(string $recipients): array
    {
        $errors = [];

        try {
            $addresses = $this->parseAddresses($recipients);
            foreach ($addresses as $address) {
                $address_type = $this->mail_address_type_factory->getByPrefix($address);
                if (!$address_type->validate($this->user_id)) {
                    $errors[] = $address_type->getErrors();
                }
            }
        } catch (ilException $e) {
            $colonPosition = strpos($e->getMessage(), ':');
            throw new ilMailException(
                ($colonPosition === false) ?
                    $e->getMessage() :
                    substr($e->getMessage(), $colonPosition + 2)
            );
        }

        return array_merge(...$errors);
    }

    public function persistToStage(
        int $a_user_id,
        array $a_attachments,
        string $a_rcp_to,
        string $a_rcp_cc,
        string $a_rcp_bcc,
        string $a_m_subject,
        string $a_m_message,
        bool $a_use_placeholders = false,
        ?string $a_tpl_context_id = null,
        ?array $a_tpl_ctx_params = []
    ): bool {
        $this->db->replace(
            $this->table_mail_saved,
            [
                'user_id' => ['integer', $this->user_id],
            ],
            [
                'attachments' => ['clob', serialize($a_attachments)],
                'rcp_to' => ['clob', $a_rcp_to],
                'rcp_cc' => ['clob', $a_rcp_cc],
                'rcp_bcc' => ['clob', $a_rcp_bcc],
                'm_subject' => ['text', $a_m_subject],
                'm_message' => ['clob', $a_m_message],
                'use_placeholders' => ['integer', (int) $a_use_placeholders],
                'tpl_ctx_id' => ['text', $a_tpl_context_id],
                'tpl_ctx_params' => ['blob', json_encode((array) $a_tpl_ctx_params, JSON_THROW_ON_ERROR)],
            ]
        );

        $this->retrieveFromStage();

        return true;
    }

    public function retrieveFromStage(): array
    {
        $res = $this->db->queryF(
            "SELECT * FROM $this->table_mail_saved WHERE user_id = %s",
            ['integer'],
            [$this->user_id]
        );

        $this->mail_data = $this->fetchMailData($this->db->fetchAssoc($res));
        if (!is_array($this->mail_data)) {
            $this->persistToStage($this->user_id, [], '', '', '', '', '', false);
        }

        return $this->mail_data;
    }

    /**
     * Should be used to enqueue a 'mail'. A validation is executed before, errors are returned
     * @return ilMailError[]
     */
    public function enqueue(
        string $a_rcp_to,
        string $a_rcp_cc,
        string $a_rcp_bcc,
        string $a_m_subject,
        string $a_m_message,
        array $a_attachment,
        bool $a_use_placeholders = false
    ): array {
        global $DIC;

        $this->logger->info(
            "New mail system task:" .
            " To: " . $a_rcp_to .
            " | CC: " . $a_rcp_cc .
            " | BCC: " . $a_rcp_bcc .
            " | Subject: " . $a_m_subject .
            " | Attachments: " . print_r($a_attachment, true)
        );

        if ($a_attachment && !$this->mail_file_data->checkFilesExist($a_attachment)) {
            return [new ilMailError('mail_attachment_file_not_exist', [$a_attachment])];
        }

        $errors = $this->checkMail($a_rcp_to, $a_rcp_cc, $a_rcp_bcc, $a_m_subject);
        if (count($errors) > 0) {
            return $errors;
        }

        $errors = $this->validateRecipients($a_rcp_to, $a_rcp_cc, $a_rcp_bcc);
        if (count($errors) > 0) {
            return $errors;
        }

        $rcp_to = $a_rcp_to;
        $rcp_cc = $a_rcp_cc;
        $rcp_bcc = $a_rcp_bcc;

        $numberOfExternalAddresses = $this->getCountRecipients($rcp_to, $rcp_cc, $rcp_bcc);
        if (
            $numberOfExternalAddresses > 0 &&
            !$this->isSystemMail() &&
            !$DIC->rbac()->system()->checkAccessOfUser($this->user_id, 'smtp_mail', $this->mail_obj_ref_id)
        ) {
            return [new ilMailError('mail_no_permissions_write_smtp')];
        }

        if ($this->appendInstallationSignature()) {
            $a_m_message .= self::_getInstallationSignature();
        }

        if (ilContext::getType() === ilContext::CONTEXT_CRON) {
            $mail_data = new MailDeliveryData(
                $rcp_to,
                $rcp_cc,
                $rcp_bcc,
                $a_m_subject,
                $a_m_message,
                $a_attachment,
                $a_use_placeholders
            );
            return $this->sendMail($mail_data);
        }

        $taskFactory = $DIC->backgroundTasks()->taskFactory();
        $taskManager = $DIC->backgroundTasks()->taskManager();

        $bucket = new BasicBucket();
        $bucket->setUserId($this->user_id);

        $task = $taskFactory->createTask(ilMailDeliveryJob::class, [
            $this->user_id,
            $rcp_to,
            $rcp_cc,
            $rcp_bcc,
            $a_m_subject,
            $a_m_message,
            serialize($a_attachment),
            $a_use_placeholders,
            $this->getSaveInSentbox(),
            (string) $this->context_id,
            serialize(array_merge(
                $this->context_parameters,
                [
                    'auto_responder' => $this->auto_responder_service->isAutoresponderEnabled()
                ]
            )),
        ]);
        $interaction = $taskFactory->createTask(
            ilMailDeliveryJobUserInteraction::class,
            [
                $task,
                $this->user_id,
            ]
        );

        $bucket->setTask($interaction);
        $bucket->setTitle($this->lng->txt('mail_bg_task_title'));
        $bucket->setDescription(sprintf($this->lng->txt('mail_bg_task_desc'), $a_m_subject));

        $this->logger->info('Delegated delivery to background task');
        $taskManager->run($bucket);

        return [];
    }

    /**
     * This method is used to finally send internal messages and external emails
     * To use the mail system as a consumer, please use ilMail::enqueue
     * @param string[] $attachments
     * @return ilMailError[]
     * @see ilMail::enqueue()
     * @internal
     */
    public function sendMail(
        MailDeliveryData $mail_data
    ): array {
        $internalMessageId = $this->saveInSentbox(
            $mail_data->getAttachments(),
            $mail_data->getTo(),
            $mail_data->getCc(),
            $mail_data->getBcc(),
            $mail_data->getSubject(),
            $mail_data->getMessage()
        );

        if ($mail_data->getAttachments() !== []) {
            $this->mail_file_data->assignAttachmentsToDirectory($internalMessageId, $internalMessageId);
            $this->mail_file_data->saveFiles($internalMessageId, $mail_data->getAttachments());
        }

        $numberOfExternalAddresses = $this->getCountRecipients(
            $mail_data->getTo(),
            $mail_data->getCc(),
            $mail_data->getBcc()
        );

        if ($numberOfExternalAddresses > 0) {
            $externalMailRecipientsTo = $this->getEmailRecipients($mail_data->getTo());
            $externalMailRecipientsCc = $this->getEmailRecipients($mail_data->getCc());
            $externalMailRecipientsBcc = $this->getEmailRecipients($mail_data->getBcc());

            $this->logger->debug(
                "Parsed external email addresses from given recipients /" .
                " To: " . $externalMailRecipientsTo .
                " | CC: " . $externalMailRecipientsCc .
                " | BCC: " . $externalMailRecipientsBcc .
                " | Subject: " . $mail_data->getSubject()
            );

            if ($mail_data->isUsePlaceholder() &&
                array_key_exists('prg_ref_id', $this->context_parameters)
            ) {
                $usr_id = ilObjUser::_lookupId($mail_data->getTo());
                $message = $this->replacePlaceholders($mail_data->getMessage(), $usr_id, false);
            }

            $this->sendMimeMail(
                $externalMailRecipientsTo,
                $externalMailRecipientsCc,
                $externalMailRecipientsBcc,
                $mail_data->getSubject(),
                $this->refinery->string()->markdown()->toHTML()->transform(
                    $mail_data->isUsePlaceholder() ? $this->replacePlaceholders($message, 0, false) : $message
                ),
                $mail_data->getAttachments()
            );
        } else {
            $this->logger->debug('No external email addresses given in recipient string');
        }

        $errors = [];

        if (!$this->distributeMail(
            $mail_data->getTo(),
            $mail_data->getCc(),
            $mail_data->getBcc(),
            $mail_data->getSubject(),
            $mail_data->getMessage(),
            $mail_data->getAttachments(),
            $internalMessageId,
            $mail_data->isUsePlaceholder()
        )) {
            $errors['mail_send_error'] = new ilMailError('mail_send_error');
        }

        if (!$this->getSaveInSentbox()) {
            $this->deleteMails([$internalMessageId]);
        }

        return array_values($errors);
    }

    /**
     * @return ilMailError[]
     */
    public function validateRecipients(string $to, string $cc, string $bcc): array
    {
        try {
            $errors = [];
            $errors = array_merge($errors, $this->checkRecipients($to));
            $errors = array_merge($errors, $this->checkRecipients($cc));
            $errors = array_merge($errors, $this->checkRecipients($bcc));

            if (count($errors) > 0) {
                return array_merge([new ilMailError('mail_following_rcp_not_valid')], $errors);
            }
        } catch (ilMailException $e) {
            return [new ilMailError('mail_generic_rcp_error', [$e->getMessage()])];
        }

        return [];
    }

    private function getSubjectSentFolderId(): int
    {
        $send_folder_id = 0;
        if (!$this->isSystemMail()) {
            $send_folder_id = $this->mailbox->getSentFolder();
        }

        return $send_folder_id;
    }

    /**
     * @param string[] $attachment
     */
    protected function saveInSentbox(
        array $attachment,
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message
    ): int {
        return $this->sendInternalMail(
            $this->getSubjectSentFolderId(),
            $this->user_id,
            $attachment,
            $to,
            $cc,
            $bcc,
            'read',
            $subject,
            $message,
            $this->user_id
        );
    }

    /**
     * @param string[] $attachments
     */
    private function sendMimeMail(
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message,
        array $attachments
    ): void {
        $mailer = new ilMimeMail();
        $mailer->From($this->sender_factory->getSenderByUsrId($this->user_id));
        $mailer->To($to);
        $mailer->Subject(
            $subject,
            true,
            (string) ($this->context_parameters[self::PROP_CONTEXT_SUBJECT_PREFIX] ?? '')
        );

        if (!$this->isSystemMail()) {
            $message .= $this->signature_service->user($this->user_id);
        }
        $mailer->Body($this->refinery->string()->markdown()->toHTML()->transform($message) ?? '');

        if ($cc) {
            $mailer->Cc($cc);
        }

        if ($bcc) {
            $mailer->Bcc($bcc);
        }

        foreach ($attachments as $attachment) {
            $mailer->Attach(
                $this->mail_file_data->getAbsoluteAttachmentPoolPathByFilename($attachment),
                '',
                'inline',
                $attachment
            );
        }

        $mailer->Send();
    }

    /**
     * @param string[] $attachments
     */
    public function saveAttachments(array $attachments): void
    {
        $this->db->update(
            $this->table_mail_saved,
            [
                'attachments' => ['clob', serialize($attachments)],
            ],
            [
                'user_id' => ['integer', $this->user_id],
            ]
        );
    }

    /**
     * Explode recipient string, allowed separators are ',' ';' ' '
     * @return ilMailAddress[]
     */
    protected function parseAddresses(string $addresses): array
    {
        if ($addresses !== '') {
            $this->logger->debug(sprintf(
                "Started parsing of recipient string: %s",
                $addresses
            ));
        }

        $parser = $this->mail_address_parser_factory->getParser($addresses);
        $parsedAddresses = $parser->parse();

        if ($addresses !== '') {
            $this->logger->debug(sprintf(
                "Parsed addresses: %s",
                implode(',', array_map(static function (ilMailAddress $address): string {
                    return (string) $address;
                }, $parsedAddresses))
            ));
        }

        return $parsedAddresses;
    }

    protected function getCountRecipient(string $recipients, bool $onlyExternalAddresses = true): int
    {
        $addresses = new ilMailAddressListImpl($this->parseAddresses($recipients));
        if ($onlyExternalAddresses) {
            $addresses = new ilMailOnlyExternalAddressList(
                $addresses,
                self::ILIAS_HOST,
                $this->usr_id_by_login_callable
            );
        }

        return count($addresses->value());
    }

    protected function getCountRecipients(
        string $toRecipients,
        string $ccRecipients,
        string $bccRecipients,
        bool $onlyExternalAddresses = true
    ): int {
        return $this->getCountRecipient($toRecipients, $onlyExternalAddresses) +
            $this->getCountRecipient($ccRecipients, $onlyExternalAddresses) +
            $this->getCountRecipient($bccRecipients, $onlyExternalAddresses);
    }

    protected function getEmailRecipients(string $recipients): string
    {
        $addresses = new ilMailOnlyExternalAddressList(
            new ilMailAddressListImpl($this->parseAddresses($recipients)),
            self::ILIAS_HOST,
            $this->usr_id_by_login_callable
        );

        $emailRecipients = array_map(static function (ilMailAddress $address): string {
            return (string) $address;
        }, $addresses->value());

        return implode(',', $emailRecipients);
    }

    public static function _getAutoGeneratedMessageString(ilLanguage $lang = null): string
    {
        global $DIC;

        if (!($lang instanceof ilLanguage)) {
            $lang = ilLanguageFactory::_getLanguage();
        }

        $lang->loadLanguageModule('mail');

        return sprintf(
            $lang->txt('mail_auto_generated_info'),
            $DIC->settings()->get('inst_name', 'ILIAS ' . ((int) ILIAS_VERSION_NUMERIC)),
            ilUtil::_getHttpPath()
        ) . "\n\n";
    }

    public static function _getIliasMailerName(): string
    {
        /** @var ilMailMimeSenderFactory $senderFactory */
        $senderFactory = $GLOBALS["DIC"]["mail.mime.sender.factory"];

        return $senderFactory->system()->getFromName();
    }

    /**
     * @param bool|null $a_flag
     * @return self|bool
     */
    public function appendInstallationSignature(bool $a_flag = null)
    {
        if (null === $a_flag) {
            return $this->append_installation_signature;
        }

        $this->append_installation_signature = $a_flag;
        return $this;
    }

    public static function _getInstallationSignature(): string
    {
        global $DIC;
        return $DIC->mail()->signature()->installation();
    }

    public static function getSalutation(int $a_usr_id, ?ilLanguage $a_language = null): string
    {
        global $DIC;

        $lang = ($a_language instanceof ilLanguage) ? $a_language : $DIC->language();
        $lang->loadLanguageModule('mail');

        $gender = ilObjUser::_lookupGender($a_usr_id);
        $gender = $gender ?: 'n';
        $name = ilObjUser::_lookupName($a_usr_id);

        if ($name['firstname'] === '') {
            return $lang->txt('mail_salutation_anonymous') . ',';
        }

        return
            $lang->txt('mail_salutation_' . $gender) . ' ' .
            ($name['title'] ? $name['title'] . ' ' : '') .
            ($name['firstname'] ? $name['firstname'] . ' ' : '') .
            $name['lastname'] . ',';
    }

    protected function getUserInstanceById(int $usrId): ?ilObjUser
    {
        if (!array_key_exists($usrId, $this->user_instances_by_id_map)) {
            try {
                $user = new ilObjUser($usrId);
            } catch (Exception $e) {
                $user = null;
            }

            $this->user_instances_by_id_map[$usrId] = $user;
        }

        return $this->user_instances_by_id_map[$usrId];
    }

    /**
     * @param array<int, ilObjUser> $userInstanceByIdMap
     */
    public function setUserInstanceById(array $userInstanceByIdMap): void
    {
        $this->user_instances_by_id_map = $userInstanceByIdMap;
    }

    protected function getMailOptionsByUserId(int $usrId): ilMailOptions
    {
        if (!isset($this->mail_options_by_usr_id_map[$usrId])) {
            $this->mail_options_by_usr_id_map[$usrId] = new ilMailOptions($usrId);
        }

        return $this->mail_options_by_usr_id_map[$usrId];
    }

    /**
     * @param ilMailOptions[] $mailOptionsByUsrIdMap
     */
    public function setMailOptionsByUserIdMap(array $mailOptionsByUsrIdMap): void
    {
        $this->mail_options_by_usr_id_map = $mailOptionsByUsrIdMap;
    }

    public function formatLinebreakMessage(string $message): string
    {
        return $message;
    }

    private function createRecipient(int $user_id): Recipient
    {
        return new Recipient(
            $user_id,
            $this->getUserInstanceById($user_id),
            $this->getMailOptionsByUserId($user_id),
            $this->legal_documents
        );
    }
}
